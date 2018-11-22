<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Object_model extends MY_Model
{
    protected $table_name = 'dt_objects';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = [];
    protected $after_insert  = ['after_insert__defaults'];
    protected $before_update = ['before_update__defaults'];
    protected $after_update  = ['after_update__defaults'];
    protected $after_delete  = [];

    protected $fields = ['id', 'portfolio_id', 'attributes', 'amt_sum_insured', 'si_breakdown', 'flag_locked', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $endorsement_fields = ['attributes', 'amt_sum_insured', 'si_breakdown',];

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

        // Required Helpers/Configurations
        $this->load->config('policy');
        $this->load->helper('policy');
        $this->load->helper('object');

        // Set validation rules
        $this->validation_rules();
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

        $this->validation_rules = [
            'add' => [
                [
                    'field' => 'portfolio_id',
                    'label' => 'Portfolio',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_extra_attributes' => 'style="width:100%; display:block" data-ddstyle="select"',
                    '_type'     => 'dropdown',
                    '_id'       => '_object-portfolio-id',
                    '_data'     => IQB_BLANK_SELECT + $this->portfolio_model->dropdown_children_tree(),
                    '_required' => true
                ],
            ],
            'add_widget' => [],

            'edit' => []
        ];
    }
    // --------------------------------------------------------------------

    /**
     * After Insert Trigger
     *
     * Tasks that are to be performed after an object is created
     *      1. Save Customer-Object Relation
     *
     *
     * @param array $arr_record
     * @return array
     */
    public function after_insert__defaults($arr_record)
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
                    [method] => insert
                )
        */

        $id = $arr_record['id'] ?? NULL;
        $customer_id = $arr_record['fields']['customer_id'];
        if($id !== NULL)
        {
            $this->load->model('rel_customer_object_model');
            $this->rel_customer_object_model->add_new_object_owner($id, $customer_id);

            /**
             * Clear Cache
             * ---------------------
             */
            $cache_vars = [
                'object_cst_' . $customer_id,
                'object_cst_' . $customer_id . '_*'
            ];
            $this->clear_cache($cache_vars);
        }
        return FALSE;
    }


    // --------------------------------------------------------------------

    /**
     * Before Update Trigger
     *
     * @param array $data
     * @return array
     */
    public function before_update__defaults($data)
    {
        // Task 1: Remove portfoliio_id if present ( !!! IMPORTANT: Portfolio ID is not editable )
        unset($data['portfolio_id']); //

        return $data;
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

            if($policy_record && _POLICY_is_editable($policy_record->status, FALSE) === TRUE)
            {
                $this->load->model('endorsement_model');
                $this->endorsement_model->reset_by_policy($policy_record->id);
            }

            /**
             * Clear Cache
             * ---------------------
             */
            $record = $this->row($id);
            $cache_vars = [
                'object_cst_' . $record->customer_id,
                'object_cst_' . $record->customer_id . '_*'
            ];
            $this->clear_cache($cache_vars);
        }
        return FALSE;
    }

    // ----------------------------------------------------------------


    /**
     * Transfer Ownership of this Object to New Customer
     *
     * @param int $id
     * @param int $old_customer_id
     * @param int $new_customer_id
     * @return bool
     */
    public function transfer_ownership($id, $old_customer_id, $new_customer_id)
    {
        $this->load->model('rel_customer_object_model');

        /**
         * Task 1: Reset current flag - Old Customer
         */
        $this->rel_customer_object_model->reset_current_owner($id, $old_customer_id);

        /**
         * Task 2: Add new Customer as Current Owner
         */
        $this->rel_customer_object_model->add_new_object_owner($id, $new_customer_id);

    }

    // ----------------------------------------------------------------

    /**
     * Update Lock Flag
     *
     * @param int $id
     * @param int $flag
     * @return bool
     */
    public function update_lock($id, $flag)
    {
        if( !in_array($flag, [IQB_FLAG_UNLOCKED, IQB_FLAG_LOCKED]) )
        {
            return FALSE;
        }

        // Let's Update the Flag
        $data = [
            'flag_locked'   => $flag,
            'updated_by'    => $this->dx_auth->get_user_id(),
            'updated_at'    => $this->set_date()
        ];
        $done = $this->db->where('id', $id)
                        ->update($this->table_name, $data);

        /**
         * Clear Cache for customer belonging to this object
         */
        $record = $this->row($id);
        $this->clear_cache();

        return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Update Endorsement Changes on Policy Table
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function commit_endorsement($id, $data)
    {
        return $this->db->where('id', $id)
                        ->update($this->table_name, $data);
    }

    // ----------------------------------------------------------------

    /**
     * Is object editable?
     * --------------------
     *
     * Edit Constraints:
     *      1. Flag Lock is ON
     *          - You can not edit object if it's lock flag is ON.
     *          - This flag is set ON once a policy is verified.
     *
     * Note:
     *      - Upon policy expire/cancel, the object lock flag should be released
     *
     * @param int|object $record    Object ID or Object Record
     * @return boolean
     */
    public function is_editable($record)
    {
        $_flag_editable  = FALSE;

        /**
         * Get the Object record if ID is supplied
         */
        $record = is_numeric($record) ? $this->row( (int)$record ) : $record;

        // Throw exception if we do not find an object record
        if(!$record)
        {
            throw new Exception("Exception [Model: Object_model][Method: is_editable()]: Object not found.");
        }

        /**
         * Object Locked?
         */
        $_flag_editable = (int)$record->flag_locked === IQB_FLAG_UNLOCKED;

        return $_flag_editable;
    }

    // ----------------------------------------------------------------

    /**
     * Is object deletable?
     * --------------------
     *
     * Delete Constraints:
     *      - Newly created object which is not assigned to any policy is deletable
     *
     * @param int|object $record    Object ID or Object Record
     * @return boolean
     */
    public function is_deletable($record)
    {
        $_flag_deletable  = FALSE;

        /**
         * Get the Object record if ID is supplied
         */
        $record = is_numeric($record) ? $this->row( (int)$record ) : $record;

        // Throw exception if we do not find an object record
        if(!$record)
        {
            throw new Exception("Exception [Model: Object_model][Method: is_deletable()]: Object not found.");
        }

        /**
         * Has this object been assigned to any policy?
         * --------------------------------------------
         *  MUST be editable & NOT Assigned to any policy
         */
        if( $this->is_editable($record) && !$this->assigned_to_any_policy($record->id) )
        {
            $_flag_deletable  = TRUE;
        }

        return $_flag_deletable;
    }

    // ----------------------------------------------------------------

    /**
     * Has this object been assigned to any "Policy(ies)"
     *
     * @param integer $id
     * @return mixed
     */
    public function assigned_to_any_policy($id)
    {
        return $this->db->from('dt_policies as P')
                        ->where('P.object_id', $id)
                        ->count_all_results();
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
        $this->_row_select();
        return $this->db->where('O.id', $id)
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get record for Endorsement Edit
     *
     * @param integer $policy_id
     * @param integer $txn_id
     * @param integer $id
     * @return object
     */
    public function get_for_endorsement( $policy_id, $txn_id, $id )
    {
        $where = [
            'O.id'              => $id,
            'P.id'              => $policy_id,
            'ENDRSMNT.id'           => $txn_id,
            'ENDRSMNT.flag_current' => IQB_FLAG_ON
        ];
        return $this->db->select("O.*, P.branch_id")
                 ->from($this->table_name . ' as O')
                 ->join('dt_policies P', 'P.object_id = O.id')
                 ->join('dt_endorsements ENDRSMNT', 'P.id = ENDRSMNT.policy_id')
                 ->where($where)
                 ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get all data for specified customer
     *
     * @param integer $customer_id
     * @param integer $portfolio_id
     * @return mixed
     */
    public function get_by_customer( $customer_id, $portfolio_id = NULL )
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $where = [
            'RCO.customer_id' => $customer_id,
            'RCO.flag_current' => IQB_FLAG_ON
        ];
        $cache_name = 'object_cst_' . $customer_id;
        if($portfolio_id)
        {
            $cache_name             .= '_' . $portfolio_id;
            $where['O.portfolio_id'] = $portfolio_id;
        }

        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $this->_row_select();
            $list = $this->db->where($where)
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
        $this->_row_select();

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
    private function _row_select( )
    {
        $this->db->select(
                            "O.id, O.portfolio_id, O.attributes, O.amt_sum_insured, O.flag_locked,
                            P.code as portfolio_code, P.name_en as portfolio_name,
                            C.id as customer_id, C.full_name as customer_name")
                 ->from($this->table_name . ' as O')
                 ->join('master_portfolio P', 'P.id = O.portfolio_id')
                 ->join('rel_customer__object RCO', 'RCO.object_id = O.id')
                 ->join('dt_customers C', 'RCO.customer_id = C.id')
                 ->where('RCO.flag_current', IQB_FLAG_ON);
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
         * If no data supplied, delete all caches
         */
        if( !$data )
        {
            $cache_names = [
                'object_*'
            ];
        }
        else
        {
            /**
             * If data supplied, we only delete the supplied
             * caches
             */
            $cache_names = is_array($data) ? $data : [$data];
        }

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
        if( !safe_to_delete( get_class(), $id ) || !$this->is_deletable($id) )
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
            /**
             * Clear Cache
             * ---------------------
             */
            $cache_vars = [
                'object_cst_' . $record->customer_id,
                'object_cst_' . $record->customer_id . '_*'
            ];
            $this->clear_cache($cache_vars);
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}