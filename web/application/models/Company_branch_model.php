<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_branch_model extends MY_Model
{
    protected $table_name = 'master_company_branches';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['prepare_contact_data'];
    protected $before_update = ['prepare_contact_data'];
    protected $after_insert  = ['trg_after_save', 'clear_cache'];
    protected $after_update  = ['trg_after_save', 'clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "company_id", "name", "contact", "is_head_office", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name',
            'label' => 'Branch Name',
            'rules' => 'trim|required|max_length[50]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'is_head_office',
            'label' => 'Is Head Office?',
            'rules' => 'trim|integer|in_list[1]',
            '_type' => 'switch',
            '_checkbox_value' => '1',
            '_help_text' => 'Please set this option if this is "Head Office" of the company.'
        ],
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0; // First 86; i.e. imported old data

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();
    }

    // ----------------------------------------------------------------

    public function prepare_contact_data($data)
    {
        $data['contact'] = get_contact_data_from_form();
        return $data;
    }

    // ----------------------------------------------------------------

    public function trg_after_save($arr_record)
    {
        /**
         *
         * Data Structure
                Array
                (
                    [id] => 10
                    [fields] => Array
                        (
                            [company_id] => 6
                            [is_head_office] => 1
                            [contact] => {...}

                            ...

                            [updated_at] => 2016-12-28 15:51:47
                            [updated_by] => 1
                        )

                    [result] => 1
                    [method] => update
                )
        */
        $id = $arr_record['id'] ?? NULL;

        if($id !== NULL)
        {
            $fields = $arr_record['fields'];

            $is_head_office = $fields['is_head_office'];
            $company_id     = $fields['company_id'];

            if($is_head_office)
            {
                $this->reset_head_office($id, $company_id);
            }
        }
    }

    // ----------------------------------------------------------------

    public function reset_head_office($id, $company_id)
    {
        $data = [
            'is_head_office' => IQB_FLAG_OFF
        ];

        $data = $this->modified_on(['fields' => $data]);
        return $this->db->where('id !=', $id )
                        ->where('company_id', $company_id)
                        ->set($data)
                        ->update($this->table_name);
    }

    // ----------------------------------------------------------------

    /**
     * Valid Branch ?
     *
     * @param iinteger $company_id
     * @param integer $branch_id
     * @return integer
     */
    public function valid_branch($company_id, $branch_id)
    {
        $where = [
            'id'            => $branch_id,
            'company_id'    => $company_id
        ];
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    /**
     * Get all branches for specified company
     *
     * @param integer $company_id
     * @return mixed
     */
    public function get_by_company( $company_id )
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'branch_company_' . $company_id;

        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $list = $this->db->select('C.id, C.company_id, C.name, C.contact, C.is_head_office')
                                ->from($this->table_name . ' as C')
                                ->where('C.company_id', $company_id)
                                ->order_by('C.is_head_office', 'desc')
                                ->order_by('C.name', 'asc')
                                ->get()->result();

            $this->write_cache($list, $cache_name, CACHE_DURATION_DAY);
        }
        return $list;
    }

    public function dropdown_by_company($company_id)
    {
        $records = $this->get_by_company($company_id);
        $list = [];
        foreach($records as $record)
        {
            $column = $record->id;
            $list["{$column}"] = $record->name;
        }
        return $list;
    }

    // ----------------------------------------------------------------


    /**
     * Get Data Rows
     *
     * Get the filtered resulte set for listing purpose
     *
     * @param array $params
     * @return type
     */
    public function rows($params = array())
    {
        $this->db->select('C.id, C.company_id, C.name, C.contact')
                 ->from($this->table_name . ' as C');

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['C.id >=' => $next_id]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('C.name', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }

	// --------------------------------------------------------------------

    /**
     * Update Head Office Contact
     *
     * This is used to udpate head office contact while updating company information.
     *
     * @param int $company_id
     * @param json $contact
     * @return bool
     */
    public function update_ho_contact($company_id, $contact)
    {
        $data = [ 'contact' => $contact];
        $data = $this->modified_on(['fields' => $data]);

        $result = $this->db->where(['company_id' => $company_id, 'is_head_office' => IQB_FLAG_ON])
                            ->set($data)
                            ->update($this->table_name);

        if($result)
        {
            // Delete cache for this company
            $this->delete_cache('branch_company_'.$company_id);
        }

        return $result;
    }


    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [
            'branch_company_*'
        ];
    	// cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function delete($id = NULL)
    {
        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;

        $status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            parent::delete($id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // get_allenerate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}