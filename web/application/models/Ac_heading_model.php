<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_heading_model extends MY_Model
{
    protected $table_name = 'ac_account_headings';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "account_heading_group_id", "ac_number", "name", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Set validation rule
        $this->load->model('ac_heading_group_model');
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    /**
     * Set Validation Rules
     *
     * @return void
     */
    public function validation_rules()
    {
        $dropdwon_heading_groups = $this->ac_heading_group_model->dropdown();

        $this->validation_rules = [
            [
                'field' => 'account_heading_group_id',
                'label' => 'Heading Group',
                'rules' => 'trim|required|integer|max_length[10]|in_list[' . implode(',', array_keys($dropdwon_heading_groups)) . ']',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $dropdwon_heading_groups,
                '_required' => true
            ],
            [
                'field' => 'ac_number',
                'label' => 'Account Number',
                'rules' => 'trim|required|integer|max_length[6]|callback__cb_valid_heading_group',
                '_type'     => 'text',
                '_help_text' => 'Please provide a 6 digit number which is between range of selected "Heading Group"',
                '_required' => true
            ],
            [
                'field' => 'name',
                'label' => 'Heading Name',
                'rules' => 'trim|required|max_length[100]',
                '_type'     => 'text',
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    public function check_duplicate($where, $id=NULL)
    {
        if( $id )
        {
            $this->db->where('id !=', $id);
        }
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    public function row($id)
    {
        return $this->db->select('AH.id, AH.account_heading_group_id, AH.ac_number, AH.name, AHG.name as heading_group_name')
                 ->from($this->table_name . ' as AH')
                 ->join('ac_account_heading_groups AHG', 'AHG.id = AH.account_heading_group_id')
                 ->where('AH.id', $id)
                 ->get()->row();
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
        $this->db->select('AH.id, AH.account_heading_group_id, AH.ac_number, AH.name, AHG.name as heading_group_name')
                 ->from($this->table_name . ' as AH')
                 ->join('ac_account_heading_groups AHG', 'AHG.id = AH.account_heading_group_id');


        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['AH.id >=' => $next_id]);
            }

            $account_heading_group_id = $params['account_heading_group_id'] ?? NULL;
            if( $account_heading_group_id )
            {
                $this->db->where(['AH.account_heading_group_id' =>  $account_heading_group_id]);
            }


            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('AH.name', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }


    /**
     * Get Dropdown List
     */
    public function dropdown()
    {
        $cache_name = 'ac_hd_all';
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $this->db->select('`id`, `ac_number`, `name`')
                        ->from($this->table_name);



            $records = $this->db->get()->result();
            $list = [];
            foreach($records as $record)
            {
                $list["{$record->id}"] = implode(' - ', [$record->ac_number, $record->name]);
            }

            $this->write_cache($list, $cache_name, CACHE_DURATION_DAY);
        }

        return $list;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [
            'ac_hd_all'
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
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }
        else
        {
            $this->log_activity($id, 'D');
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }

    // ----------------------------------------------------------------

    /**
     * Log Activity
     *
     * Log activities
     *      Available Activities: Create|Edit|Delete
     *
     * @param integer $id
     * @param string $action
     * @return bool
     */
    public function log_activity($id, $action = 'C')
    {
        $action = is_string($action) ? $action : 'C';
        // Save Activity Log
        $activity_log = [
            'module' => 'ac_heading',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}