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

    protected $fields = ['id', 'account_group_id', 'name', 'active', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 800;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Helper
        $this->load->helper('account');

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
        $dropdown_heading_groups    = $this->ac_account_group_model->dropdown_tree(null, true, 'html');
        $this->validation_rules = [
            [
                'field' => 'account_group_id',
                'label' => 'Account Group',
                'rules' => 'trim|required|integer|max_length[11]|in_list[' . implode(',', array_keys($dropdown_heading_groups)) . ']',
                '_id'       => '_ac_group-id',
                '_extra_attributes' => 'style="width:100%; display:block"',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $dropdown_heading_groups,
                '_required' => true
            ],
            [
                'field' => 'name',
                'label' => 'Account Name',
                'rules' => 'trim|required|max_length[100]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'active',
                'label' => 'Activate Account',
                'rules' => 'trim|integer|in_list[1]',
                '_type' => 'switch',
                '_checkbox_value' => '1'
            ],
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Check Duplicate
     *
     * @param array $where
     * @param integer|null $id
     * @return integer
     */
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

    /**
     * Get Dropdown List by Account Group ID
     *
     * @param integer $account_group_id
     * @return array
     */
    public function dropdown($account_group_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'ac_account_' . $account_group_id;

        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $where = [
                'AC.account_group_id'   => $account_group_id,
                'AC.active'             => IQB_FLAG_ON
            ];
            $records = $this->db->select('AC.id, AC.name')
                                 ->from($this->table_name . ' as AC')
                                 ->where($where)
                                 ->get()->result();

            $list = [];
            foreach($records as $record)
            {
                $list["{$record->id}"] =  $record->name;
            }
            $this->write_cache($list, $cache_name, CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function row($id)
    {
        $this->_row_select();

        return $this->db->where('AC.id', $id)
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
        $this->_row_select();

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['AC.id >=' => $next_id]);
            }

            $account_group_id = $params['account_group_id'] ?? NULL;
            if( $account_group_id )
            {
                $this->db->where(['AC.account_group_id' =>  $account_group_id]);
            }


            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                // If Numeric, Query ID
                if( is_numeric($keywords) )
                {
                    $id = (int)$keywords;
                    $this->db->where('AC.id', $id);
                }
                else
                {
                    // $this->db->group_start()
                    //          ->like('AC.name', $keywords, 'after')
                    //          ->or_like('ACG.name', $keywords, 'after')
                    //          ->group_end();
                     $this->db->where("( MATCH ( AC.name ) AGAINST ( '{$keywords}*' IN BOOLEAN MODE) OR MATCH ( ACG.name ) AGAINST ( '{$keywords}*' IN BOOLEAN MODE)  ) ", NULL);
                }

            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select('AC.id, AC.account_group_id, AC.name, AC.active, ACG.name as group_name')
                 ->from($this->table_name . ' as AC')
                 ->join('ac_account_groups ACG', 'ACG.id = AC.account_group_id');
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [
            'ac_account_*'
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