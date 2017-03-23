<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_account_model extends MY_Model
{
    protected $table_name = 'ac_accounts';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "parent_id", "account_group_id", "ac_number", "name", "created_at", "created_by", "updated_at", "updated_by"];

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
        $this->load->model('ac_account_group_model');
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
        $dropdwon_heading_groups    = $this->ac_account_group_model->dropdown_tree();
        $dropdown_parent            = $this->dropdown_parent();

        $this->validation_rules = [
            [
                'field' => 'account_group_id',
                'label' => 'Account Group',
                'rules' => 'trim|required|integer|max_length[11]|in_list[' . implode(',', array_keys($dropdwon_heading_groups)) . ']',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $dropdwon_heading_groups,
                '_required' => true
            ],
            [
                'field' => 'parent_id',
                'label' => 'Parent Account Name',
                'rules' => 'trim|integer|max_length[11]|in_list[0,' . implode(',', array_keys($dropdown_parent)) . ']|callback__cb_valid_parent',
                '_type'     => 'dropdown',
                '_data'     => IQB_ZERO_SELECT + $dropdown_parent,
                '_required' => true
            ],
            [
                'field' => 'ac_number',
                'label' => 'Account Number',
                'rules' => 'trim|required|integer|max_length[6]|callback__cb_valid_account_group',
                '_type'     => 'text',
                '_help_text' => 'Please provide a 6 digit number which is between range of selected "Account Group"',
                '_required' => true
            ],
            [
                'field' => 'name',
                'label' => 'Account Name',
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
        return $this->db->select('AH.id, AH.account_group_id, AH.ac_number, AH.name, AHG.name_en as account_group_name_en, IAH.name as parent_name')
                 ->from($this->table_name . ' as AH')
                 ->join('ac_account_groups AHG', 'AHG.id = AH.account_group_id')
                 ->join( $this->table_name . ' IAH', 'IAH.id = AH.parent_id', 'left')
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
        $this->db->select('AH.id, AH.account_group_id, AH.ac_number, AH.name, AHG.name_en as account_group_name_en, IAH.name as parent_name')
                 ->from($this->table_name . ' as AH')
                 ->join('ac_account_groups AHG', 'AHG.id = AH.account_group_id')
                 ->join( $this->table_name . ' IAH', 'IAH.id = AH.parent_id', 'left');


        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['AH.id >=' => $next_id]);
            }

            $account_group_id = $params['account_group_id'] ?? NULL;
            if( $account_group_id )
            {
                $this->db->where(['AH.account_group_id' =>  $account_group_id]);
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

    // --------------------------------------------------------------------

    /**
     * Get Parent Dropdown List
     */
    public function dropdown_parent()
    {
        $cache_name = 'ac_account_parent';
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $records = $this->db->select('`id`, `ac_number`, `name`')
                        ->from($this->table_name)
                        ->where('parent_id', 0)
                        ->get()->result();

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
     * Get Parent Dropdown List
     */
    public function dropdown_children($parent_id)
    {
        $cache_name = 'ac_account_children_' . $parent_id;
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $records = $this->db->select('`id`, `ac_number`, `name`')
                        ->from($this->table_name)
                        ->where('parent_id', $parent_id)
                        ->get()->result();

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
            'ac_account_parent',
            'ac_account_children_*'
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
            'module'    => 'ac_account',
            'module_id' => $id,
            'action'    => $action
        ];
        return $this->activity->save($activity_log);
    }
}