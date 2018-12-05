<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_branch_model extends MY_Model
{
    protected $table_name = 'master_company_branches';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $before_insert = ['prepare_contact_data'];
    // protected $before_update = ['prepare_contact_data'];

    // protected $after_insert  = ['trg_after_save', 'clear_cache'];
    // protected $after_update  = ['trg_after_save', 'clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "company_id", "name_en", "name_np", "is_head_office", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name_en',
            'label' => 'Branch Name (EN)',
            'rules' => 'trim|required|max_length[150]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'name_np',
            'label' => 'Branch Name (ने)',
            'rules' => 'trim|required|max_length[200]',
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

        $this->load->model('address_model');
    }


    // ----------------------------------------------------------------

    /**
     * Add Head Office
     *
     * This method is triggerd after adding a company,
     * so, there is no DB Transaction - which will be implemented on
     * company model's add() method
     *
     * @param array $post_data Form Post Data
     * @return mixed
     */
    public function add_ho($post_data)
    {
        $cols = ["company_id", "name_en", "name_np", "is_head_office",];
        $data = [];

        /**
         * Prepare Basic Data
         */
        foreach($cols as $col)
        {
            $data[$col] = $post_data[$col] ?? NULL;
        }


        // Insert Primary Record
        $id = parent::insert($data, TRUE);

        // Insert Address
        if($id)
        {
            $this->address_model->add(IQB_ADDRESS_TYPE_COMPANY_BRANCH, $id ,$post_data);
        }

        // Reset Head Office
        $this->reset_head_office($id, $data['company_id']);

        // Clear Cache
        $this->delete_cache('branch_company_'.$data['company_id']);

        // return result/status
        return $id;
    }

    // ----------------------------------------------------------------

    /**
     * Add New Record
     *
     * @param array $post_data Form Post Data
     * @return mixed
     */
    public function add($post_data)
    {
        $cols = ["company_id", "name_en", "name_np", "is_head_office",];
        $data = [];

        /**
         * Prepare Basic Data
         */
        foreach($cols as $col)
        {
            $data[$col] = $post_data[$col] ?? NULL;
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $done               = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Insert Primary Record
            $done = parent::insert($data, TRUE);

            // Insert Address
            if($done)
            {
                $this->address_model->add(IQB_ADDRESS_TYPE_COMPANY_BRANCH, $done ,$post_data);
            }

            // Reset Head Office
            if($data['is_head_office'])
            {
                $this->reset_head_office($done, $data['company_id']);
            }

            // Clear Cache
            $this->delete_cache('branch_company_'.$data['company_id']);

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $done = FALSE;
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $done;
    }

    /**
     * Edit a Record
     *
     * @param int $id Branch ID
     * @param inte $address_id Address ID of this Branch
     * @param array $post_data Form Post Data
     * @return mixed
     */
    public function edit($id, $address_id, $post_data)
    {
        $cols = ["company_id", "name_en", "name_np", "is_head_office",];
        $data = [];

        /**
         * Prepare Basic Data
         */
        foreach($cols as $col)
        {
            $data[$col] = $post_data[$col] ?? NULL;
        }


        /**
         * Head Office Reset !!!
         *
         * You can not reset head office from current record.
         */
        if( !$data['is_head_office'] && $this->is_head_office($id))
        {
            $data['is_head_office'] = IQB_FLAG_ON;
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $done               = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Insert Primary Record
            $done = parent::update($id, $data, TRUE);

            // Update Address
            if($done)
            {
                $this->address_model->edit($address_id ,$post_data);
            }

            // Reset Head Office
            if($data['is_head_office'])
            {
                $this->reset_head_office($id, $data['company_id']);
            }

            // Clear Cache
            $this->delete_cache('branch_company_'.$data['company_id']);

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $done = FALSE;
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $done;
    }


    // ----------------------------------------------------------------

    public function is_head_office($id)
    {
        $where = [
            'id'            => $id,
            'is_head_office' => IQB_FLAG_ON,
        ];
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
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
            $this->db->select('CB.id, CB.company_id, CB.name_en, CB.name_np, CB.is_head_office')
                                ->from($this->table_name . ' as CB');

            // Include Address Information
            $table_aliases = [
                // Address Table Alias
                'address' => 'ADR',

                // Country Table Alias
                'country' => 'CNTRY',

                // State Table Alias
                'state' => 'STATE',

                // Local Body Table Alias
                'local_body' => 'LCLBD',

                // Type/Module Table Alias
                'module' => 'CB'
            ];
            $this->address_model->module_select(IQB_ADDRESS_TYPE_COMPANY_BRANCH, NULL, $table_aliases);

            $list = $this->db->where('CB.company_id', $company_id)
                            ->order_by('CB.is_head_office', 'desc')
                            ->order_by('CB.name_en', 'asc')
                            ->get()->result();

            $this->write_cache($list, $cache_name, CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function dropdown_by_company($company_id, $lang="both")
    {
        $records = $this->get_by_company($company_id);

        return $this->_format_dropdown($records, $lang);
    }

    // ----------------------------------------------------------------

    /**
         * Format Dropdown based on Language
         *
         * @param array $records
         * @param string $lang
         * @return array
         */
        private function _format_dropdown($records, $lang)
        {
            $list = [];
            foreach($records as $record)
            {
                $column = $record->id;
                if($lang == 'both')
                {
                    $value = $record->name_en . ' (' . $record->name_np . ')';
                }
                else if($lang == 'en')
                {
                    $value = $record->name_en;
                }
                else
                {
                    $value = $record->name_np;
                }

                $list["{$column}"] = $value;
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
        $this->db->select('CB.id, CB.company_id, CB.name_en, CB.name_np')
                 ->from($this->table_name . ' as CB');

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['CB.id >=' => $next_id]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('CB.name_en', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }

	// --------------------------------------------------------------------

    /**
     * Update Head Office Address
     *
     * This is used to udpate head office contact while updating company information.
     *
     * @param int $company_id
     * @param array $post_data POST Data
     * @return bool
     */
    public function update_ho_contact($company_id, $post_data)
    {
        $address_id = $this->get_ho_address_id_by_company($company_id);
        $result = $this->address_model->edit($address_id ,$post_data);
        if($result)
        {
            // Delete cache for this company
            $this->delete_cache('branch_company_'.$company_id);
        }

        return $result;
    }

    // --------------------------------------------------------------------

    public function get_ho_address_id_by_company($company_id)
    {
        $type = IQB_ADDRESS_TYPE_COMPANY_BRANCH;
        return $this->db->select('ADR.id')
                            ->from('dt_addresses ADR')
                            ->join('master_company_branches CB', "ADR.type = {$type} AND ADR.type_id = CB.id")
                            ->where(['CB.company_id' => $company_id, 'CB.is_head_office' => IQB_FLAG_ON])
                            ->get()->row()->id;
    }

    // --------------------------------------------------------------------

    public function get_ho_address($company_id)
    {
        return $this->address_model->get($this->get_ho_address_id_by_company($company_id));
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

    public function delete_by_company($company_id)
    {
        $list = $this->db->select('id')
                         ->from($this->table_name)
                         ->where('company_id', $company_id)
                         ->get()->result();

        // NOTE: No DB Transaction as it is called by Company Model
        foreach($list as $single)
        {
            // Delete Primary Record
            parent::delete($single->id);

            // Delete Address Record
            $this->address_model->delete_by(['type' => IQB_ADDRESS_TYPE_COMPANY_BRANCH, 'type_id' => $single->id]);
        }

        // Delete cache for this company
        $this->delete_cache('branch_company_'.$company_id);

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

        $record = parent::find($id);

        $status = TRUE;
        // Use automatic transaction
        $this->db->trans_start();

            // Delete Primary Record
            parent::delete($id);

            // Delete Address Record
            $this->address_model->delete_by(['type' => IQB_ADDRESS_TYPE_COMPANY_BRANCH, 'type_id' => $id]);

            // Delete cache for this company
            $this->delete_cache('branch_company_'.$record->company_id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // get_allenerate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // return result/status
        return $status;
    }
}