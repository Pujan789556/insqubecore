<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Object_model extends MY_Model
{
    protected $table_name = 'dt_policy_objects';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $before_insert = ['prepare_contact_data', 'prepare_customer_defaults', 'prepare_customer_fts_data'];
    // protected $before_update = ['prepare_contact_data', 'prepare_customer_fts_data'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['after_update__defaults', 'clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "customer_id", "portfolio_id", "attributes", "created_at", "created_by", "updated_at", "updated_by"];

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

        // Set validation rules
        $this->validation_rules();

        // Required Helpers/Configurations
        $this->load->config('policy');
        $this->load->helper('policy');
        $this->load->helper('object');
    }


    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $this->load->model('portfolio_model');
        $this->validation_rules =[
            [
                'field' => 'portfolio_id',
                'label' => 'Portfolio',
                'rules' => 'trim|required|integer|max_length[11]',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $this->portfolio_model->dropdown_parent(),
                '_id'       => '_object-portfolio-id',
                '_required' => true
            ]
        ];
    }

    // --------------------------------------------------------------------

    /**
     * After Update Trigger
     *
     * Tasks that are to be performed after a policy is updated
     *      1. Reset Policy Premium If this Object is Currently assigned to a Policy which is editable
     *      2. @TODO: Find other tasks
     *
     *
     * @param array $arr_record
     * @return array
     */
    public function after_update__defaults($arr_record)
    {
        /**
         *
         * Data Structure
                Array
                (
                    [id] => 10
                    [fields] => Array
                        (
                            [attributes] => ...
                        )

                    [result] => 1
                    [method] => update
                )
        */

        $id = $arr_record['id'] ?? NULL;

        if($id !== NULL)
        {
            $policy_record = $this->get_active_policy($id);

            if($policy_record)
            {
                $this->load->model('premium_model');
                $this->premium_model->reset($policy_record->id);
            }
        }
        return FALSE;
    }

    // ----------------------------------------------------------------

    /**
     * Is Object Editable?
     *
     * @param integer $id
     * @return boolean
     */
    public function is_editable($id)
    {
        /**
         * Is object editable?
         * --------------------
         *
         * If the object is currently assigned to a policy which is not editable,
         * you can not edit this object
         */

        /**
         * Find the latest policy of the current object owenr for this object.
         * If we dont have any object, We are GOOD. Else, check the editable status.
         */
        $_flag_editable  = FALSE;
        $policy_record = $this->get_active_policy($id);


        if( $policy_record && belongs_to_me($policy_record->branch_id, FALSE) === TRUE && is_policy_editable($policy_record->status, FALSE) === TRUE)
        {
            $_flag_editable  = TRUE;
        }

        return $_flag_editable;
    }

    // ----------------------------------------------------------------

    /**
     * Get active policy Record of "This Object"
     *
     * @param integer $id
     * @return mixed
     */
    public function get_active_policy($id)
    {
        return $this->db->select('P.*')
                        ->from($this->table_name . ' as O')
                        ->join('dt_policies P', 'P.object_id = O.id')
                        ->join('rel_customer_policy_object R', 'R.object_id = O.id')
                        ->where('O.id', $id)
                        ->where('R.flag_current', 1)
                        ->order_by('P.id', 'desc')
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get Single Row Data for Specific ID
     *
     * @param integer $id
     * @return mixed
     */
    public function row( $id )
    {
        $this->_prepare_row_select();
        return $this->db->where('O.id', $id)
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get all data for specified customer
     *
     * @param integer $customer_id
     * @return mixed
     */
    public function get_by_customer( $customer_id )
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'object_customer_' . $customer_id;

        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $this->_prepare_row_select();
            $list = $this->db->where('R.customer_id', $customer_id)
                             ->order_by('O.id', 'desc')
                             ->get()->result();

            $this->write_cache($list, $cache_name, CACHE_DURATION_DAY);
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
        $this->_prepare_row_select();

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['O.id <=' => $next_id]);
            }

            $portfolio_id = $params['portfolio_id'] ?? NULL;
            if( $portfolio_id )
            {
                $this->db->where(['O.portfolio_id' =>  $portfolio_id]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                // $this->db->where("MATCH ( C.`fts` ) AGAINST ( '{$keywords}*' IN BOOLEAN MODE)", NULL);
                // $this->db->like('C.full_name', $keywords, 'after');
            }
        }
        return $this->db
                        ->order_by('O.id', 'desc')
                        ->limit($this->settings->per_page+1)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    /**
     * Prepare Row Select
     *
     * @param void
     * @return void
     */
    private function _prepare_row_select( )
    {
        $this->db->select('O.id, O.portfolio_id, O.attributes, P.code as portfolio_code, P.name_en as portfolio_name, R.customer_id, C.full_name as customer_name')
                 ->from($this->table_name . ' as O')
                 ->join('master_portfolio P', 'P.id = O.portfolio_id')
                 ->join('rel_customer_policy_object R', 'R.object_id = O.id')
                 ->join('dt_customers C', 'R.customer_id = C.id')
                 ->where('R.flag_current', 1);
    }

	// --------------------------------------------------------------------

    /**
     * Callback - Motor Duplicate Checks
     *
     * @param array $where
     * @param integer|null $id
     * @return bool
     */
    public function _cb_motor_duplicate($where, $id=NULL)
    {
        if( $id )
        {
            $this->db->where('id !=', $id);
        }
        // $where is array ['key' => $value]
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        /**
         * Cache Clearance Logic:
         *
         * Every cache object belongs to specific customer ID.
         * So, when we add/edit/delete a object, we only need to
         * clear cache of the specific customer's object cache
         */
        $cache_names = [];
        $id = $data['id'] ?? null;
        $customer_id = $data['fields']['customer_id'] ?? '*';
        if( !$customer_id)
        {
            $record = $this->row($id);
            $customer_id = $record->customer_id;
        }
        $cache_names[] = 'object_customer_' . $customer_id;
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

        $record = $this->row($id);
        if(!$record)
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
            // Clear cache for this customer
            $data['fields']['customer_id'] = $record->customer_id;
            $this->clear_cache($data);

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
            'module' => 'object',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}