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

    protected $fields = ["id", "customer_id", "portfolio_id", "sub_portfolio_id", "customer_id", "attributes", "created_at", "created_by", "updated_at", "updated_by"];

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
        // $this->validation_rules();

        // Required Helpers/Configurations
        $this->load->config('policy');
        $this->load->helper('policy');
        $this->load->helper('object');
    }


    // ----------------------------------------------------------------

    /**
     * Set/Get Validation Rules
     *
     * @param integer $portfolio_id
     * @return array
     */
    public function validation_rules( $portfolio_id=0 )
    {
        $this->load->model('portfolio_model');

        // Let's compute validtaion rule for sub-portfolio
        // Add mode, it gets portfolio id from post and calculate in_list values,
        // In edit, we supply $portfolio_id from method parameter which calculates in_list values
        // Simple, isn't it?
        $portfolio_id = $portfolio_id ?? ( $this->input->post('portfolio_id') ? (int)$this->input->post('portfolio_id') : 0 );
        $sub_portfolio_dropdown = [];
        $sub_portfolio_rules = 'trim|required|integer|max_length[11]';
        if($portfolio_id)
        {
            $sub_dropdown           = $this->portfolio_model->dropdown_children($portfolio_id);
            $sub_portfolio_dropdown = $this->portfolio_model->get_children($portfolio_id);
            $sub_portfolio_rules    .= '|in_list[' . implode(',', array_keys($sub_dropdown)) . ']';
        }
        $portfolio_rules =[
            'field' => 'portfolio_id',
            'label' => 'Portfolio',
            'rules' => 'trim|required|integer|max_length[11]',
            '_type'     => 'dropdown',
            '_data'     => IQB_BLANK_SELECT + $this->portfolio_model->dropdown_parent(),
            '_id'       => '_object-portfolio-id',
            '_required' => true
        ];
        $sub_portfolio_rules = [
            'field' => 'sub_portfolio_id',
            'label' => 'Sub-Portfolio',
            'rules' => $sub_portfolio_rules,
            '_type'     => 'dropdown',
            '_data'     => $sub_portfolio_dropdown,
            '_id'       => '_object-sub-portfolio-id',
            '_required' => true
        ];

        // Set Validation Rules
        $this->validation_rules = [$portfolio_rules, $sub_portfolio_rules];
    }

    /**
     * Get Form Elements
     *
     * We have 4 different scenarios.
     *
     *  Case 1: "add"
     *      This is regular mode, where you have to supply both portfolio and sub-portfolio
     *
     *  Case 2: "add_widget"
     *      This is when you are creating an object from Policy Add/Edit Form, You will be supplied
     *      portfolio and sub-portfolio internally, so you need not the validation rule
     *
     * Case 3: "edit_new"
     *      This is when you are editing a newly created policy object which is not assigned to any
     *      policy. Here you can edit sub-portfolio
     *
     * Case 4: "edit_old"
     *      If you are editing a policy object that is already assigned to a policy, you can not edit
     *      sub-portfolio
     *
     * @param string $action
     * @param integer $portfolio_id
     * @return array
     */
    public function form_elements($action, $portfolio_id=0)
    {
        // Set validation rule if not already
        if( empty($this->validation_rules) )
        {
            $this->validation_rules($portfolio_id);
        }

        $form_elements = [];
        switch ($action)
        {
            case 'add':
                $form_elements = ['portfolio'=>$this->validation_rules[0], 'subportfolio'=>$this->validation_rules[1]];
                break;

            case 'add_widget':
            case 'edit_old':
                $form_elements = [];
                break;

            case 'edit_new':
                $form_elements = ['subportfolio'=>$this->validation_rules[1]];
                break;

            default:
                break;
        }

        return $form_elements;
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
            $policy_record = $this->get_latest_policy($id);

            if($policy_record && is_policy_editable($policy_record->status, FALSE) === TRUE)
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
        $policy_record = $this->get_latest_policy($id);

        if(!$policy_record)
        {
            $_flag_editable  = TRUE;
        }
        else if( $policy_record && belongs_to_me($policy_record->branch_id, FALSE) === TRUE && is_policy_editable($policy_record->status, FALSE) === TRUE)
        {
            $_flag_editable  = TRUE;
        }

        return $_flag_editable;
    }

    // ----------------------------------------------------------------

    /**
     * Is Sub-Portfolio of this Object Editable?
     *
     * LOGIC:
     *      if an object is assigned to a policy, we can not change sub-portfolio
     *      because policy also has sub-portfolio and we can't afford to have a
     *      sub-portfolio mismatch
     *
     * @NOTE: This method should be called only if an object is editable
     *
     * @param integer $id
     * @return boolean
     */
    public function is_sub_portfolio_editable($id)
    {
        $_flag_editable  = TRUE;
        $policy_record  = $this->get_latest_policy($id);

        if($policy_record)
        {
            $_flag_editable  = FALSE;
        }

        return $_flag_editable;
    }

    // ----------------------------------------------------------------

    /**
     * Get the Latest policy Record of "This Object"
     *
     * @param integer $id
     * @return mixed
     */
    public function get_latest_policy($id)
    {
        return $this->db->select('P.*')
                        ->from($this->table_name . ' as O')
                        ->join('dt_policies P', 'P.object_id = O.id')
                        ->where('O.id', $id)
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
            $list = $this->db->where('O.customer_id', $customer_id)
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
        $this->db->select("O.id, O.portfolio_id, O.sub_portfolio_id, O.customer_id, O.attributes,
                            P.code as portfolio_code, P.name_en as portfolio_name,
                            SP.code as sub_portfolio_code, SP.name_en as sub_portfolio_name,
                            C.full_name as customer_name")
                 ->from($this->table_name . ' as O')
                 ->join('master_portfolio P', 'P.id = O.portfolio_id')
                 ->join('master_portfolio SP', 'SP.id = O.sub_portfolio_id')
                 ->join('dt_customers C', 'O.customer_id = C.id');
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