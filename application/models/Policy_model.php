<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Policy_model extends MY_Model
{
    protected $table_name = 'dt_policies';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['before_insert__defaults'];
    protected $before_update = [];
    protected $after_insert  = ['after_insert__defaults', 'clear_cache'];
    protected $after_update  = ['after_update__defaults', 'clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = [ "id", "code", "policy_no", "branch_id", "customer_id", "portfolio_id", "policy_package", "sold_by", "object_id", "issue_date", "start_date", "duration", "end_date", "flag_dc", "status", "created_at", "created_by", "updated_at", "updated_by"];

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

        // Policy Configuration/Helper
        $this->load->config('policy');
        $this->load->helper('policy');

        // Set validation rules
        $this->set_validation_rules();
    }


    // ----------------------------------------------------------------

    /**
     * Set Validation Rules
     *
     * Set multi section Validation Rule for Policy Creation/Edit
     *
     * @return array
     */
    public function set_validation_rules()
    {
        $this->load->model('portfolio_model');
        $this->load->model('user_model');
        $this->load->model('agent_model');
        $select = ['' => 'Select ...'];

        /**
         * List all marketing staffs of this branch
         */
        $role_id = 7;
        $branch_id = $this->dx_auth->is_admin() ? NULL : $this->dx_auth->get_branch_id();

        /**
         * If posted and Direct Discount Checked, We don't need agent
         */

        $agent_validation = 'trim|required|integer|max_length[11]';
        if($this->input->post())
        {
            $flag_dc = $this->input->post('flag_dc');
            if($flag_dc == 'D')
            {
                $agent_validation = 'trim|integer|max_length[11]';
            }
        }

        $this->validation_rules = [

            /**
             * Customer Information
             */
            'customer' => [
                [
                    'field' => 'customer_id',
                    'label' => 'Customer',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type'     => 'hidden',
                    '_id'       => 'customer-id',
                    '_required' => true
                ],
                [
                    'field' => 'customer_name',
                    'label' => 'Customer',
                    'rules' => 'trim|required',
                    '_id'       => 'customer-text',
                    '_type'     => 'hidden',
                    '_required' => true
                ],
            ],

            /**
             * Portfolio Information
             */
            'portfolio' => [
                [
                    'field' => 'portfolio_id',
                    'label' => 'Portfolio',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type'     => 'dropdown',
                    '_id'       => '_portfolio-id',
                    '_data'     => $select + $this->portfolio_model->dropdown_parent(),
                    '_required' => true
                ],
                [
                    'field' => 'policy_package',
                    'label' => 'Policy Package',
                    'rules' => 'trim|required|alpha|max_length[10]',
                    '_type'     => 'dropdown',
                    '_id'       => '_policy-package-id',
                    '_data'     => IQB_BLANK_SELECT,
                    '_required' => true
                ]
            ],

            /**
             * Policy Object Information
             */
            'object' => [
                [
                    'field' => 'object_id',
                    'label' => 'Policy Object',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type'     => 'hidden',
                    '_id'       => 'object-id', // dropdown policy object
                    '_required' => true
                ],
                [
                    'field' => 'object_name',
                    'label' => 'Policy Object',
                    'rules' => 'trim|required',
                    '_type'     => 'hidden',
                    '_id'       => 'object-text', // dropdown policy object
                    '_required' => true
                ],
            ],

            /**
             * Policy Duration Information
             */
            'duration' => [
                [
                    'field' => 'issue_date',
                    'label' => 'Policy Issue Date',
                    'rules' => 'trim|required|valid_date',
                    '_type'             => 'date',
                    '_default'          => date('Y-m-d'),
                    '_extra_attributes' => 'data-provide="datepicker-inline"',
                    '_required' => false
                ],
                [
                    'field' => 'start_date',
                    'label' => 'Policy Start Date',
                    'rules' => 'trim|required|valid_date',
                    '_type'             => 'date',
                    '_default'          => date('Y-m-d'),
                    '_extra_attributes' => 'data-provide="datepicker-inline"',
                    '_required' => false
                ],
                [
                    'field' => 'duration',
                    'label' => 'Policy Duration',
                    'rules' => 'trim|required|callback__cb_valid_policy_duration',
                    '_type'     => 'dropdown',
                    '_data'     => $select + get_policy_duration_list(),
                    '_default'  => '+1 year',
                    '_required' => false
                ],
            ],

            /**
             * Sales Info - Marketing Staff, Agent Info, Commission or Direct Discount
             */
            'sales' => [
                [
                    'field' => 'sold_by',
                    'label' => 'Marketing Staff',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_id'       => '_marketing-staff',
                    '_extra_attributes' => 'style="width:100%; display:block"',
                    '_type'     => 'dropdown',
                    '_data'     => $select + $this->user_model->dropdown($role_id, $branch_id),
                    '_required' => true
                ],
                [
                    'field' => 'flag_dc',
                    'label' => 'Direct Discount or Agent Commission',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list[D,C]',
                    '_id'       => '_flag-dc',
                    '_type'     => 'radio',
                    '_data'     => [ 'C' => 'Agent Commission', 'D' => 'Direct Discount'],
                    '_required' => true
                ],
                [
                    'field' => 'agent_id',
                    'label' => 'Agent Name',
                    'rules' => $agent_validation,
                    '_id'       => '_agent-id',
                    '_extra_attributes' => 'style="width:100%; display:block"',
                    '_type'     => 'dropdown',
                    '_data'     => $select + $this->agent_model->dropdown(true),
                    '_required' => true
                ]
            ]

        ];
    }

    // ----------------------------------------------------------------

    public function get_validation_rule($action)
    {
        // Valid action?
        if( !in_array($action, array('add', 'edit')))
        {
            return FALSE;
        }

        $v_rules = [];

        // Merge All Sections and return
        foreach($this->validation_rules as $section=>$rules)
        {
            $v_rules = array_merge($v_rules, $rules);
        }

        // if ($action === 'add')
        // {
        //     // Merge All Sections and return
        //     foreach($this->validation_rules as $section=>$rules)
        //     {
        //         $v_rules = array_merge($v_rules, $rules);
        //     }
        // }
        // else
        // {

        // }
        return $v_rules;
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
        // Selects
        $this->__row_select();

        /**
         * Apply User Scope
         */
        $this->dx_auth->apply_user_scope('P');


        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['P.id <=' => $next_id]);
            }

            $code = $params['code'] ?? NULL;
            if( $code )
            {
                $this->db->like('LOWER(P.code)', strtolower($code), 'after');
            }

            // $type = $params['type'] ?? NULL;
            // if( $type )
            // {
            //     $this->db->where(['P.type' =>  $type]);
            // }

            // $company_reg_no = $params['company_reg_no'] ?? NULL;
            // if( $company_reg_no )
            // {
            //     $this->db->where(['P.company_reg_no' =>  $company_reg_no]);
            // }

            // $citizenship_no = $params['citizenship_no'] ?? NULL;
            // if( $citizenship_no )
            // {
            //     $this->db->where(['P.citizenship_no' =>  $citizenship_no]);
            // }

            // $passport_no = $params['passport_no'] ?? NULL;
            // if( $passport_no )
            // {
            //     $this->db->where(['P.passport_no' =>  $passport_no]);
            // }

            // $keywords = $params['keywords'] ?? '';
            // if( $keywords )
            // {
            //     $this->db->where("MATCH ( P.`fts` ) AGAINST ( '{$keywords}*' IN BOOLEAN MODE)", NULL);
            //     // $this->db->like('P.full_name', $keywords, 'after');
            // }
        }
        return $this->db->limit($this->settings->per_page+1)
                        ->order_by('P.id', 'desc')
                        ->get()->result();
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
    public function row($id)
    {
        // Selects
        $this->__row_select(TRUE);

        return $this->db->where('P.id', $id)
                        ->get()->row();
    }

        private function __row_select($signle_select = FALSE)
        {

            // IF CALLED FROM row() function, we should also provide agent id and agent name
            // as it is required while editing the record
            $select = 'P.*, PRT.name_en as portfolio_name, C.full_name as customer_name';
            if($signle_select)
            {
                $select .= ', RAP.agent_id';
            }
            $this->db->select($select)
                     ->from($this->table_name . ' as P')
                     ->join('master_portfolio PRT', 'PRT.id = P.portfolio_id')
                     ->join('dt_customers C', 'C.id = P.customer_id');

            if($signle_select)
            {
                $this->db->join('rel_agent_policy RAP', 'RAP.policy_id = P.id', 'left');
            }
        }

    // ----------------------------------------------------------------

    /**
     * Before Insert Trigger
     *
     * Tasks carried
     *      1. Generate Random Policy Number & add
     *      2. Add Draft Code
     *      3. Add Branch ID
     *      4. Add End Date
     *      5. Add Status = Draft
     *
     * @param array $data
     * @return array
     */
    public function before_insert__defaults($data)
    {
        $this->load->library('Token');
        $this->load->model('portfolio_model');
        $this->load->model('fiscal_year_model');

        $portfolio_id   = $data['portfolio_id'];
        $portfolio_code = $this->portfolio_model->get_code($portfolio_id);

        $fy_record      = $this->fiscal_year_model->get_current_fiscal_year();
        $fy_code_np     = $fy_record->code_np;

        $policy_no = strtoupper($this->token->generate(10));

        /**
         * Policy Code - Draft One & Policy Number
         *
         * Format: DRAFT-<BRANCH-CODE>-<PORTFOLIO-CODE>-<SERIALNO>-<FY_CODE_NP>
         */
        $policy_code = 'DRAFT/' . $this->dx_auth->get_branch_code() . '/' . $portfolio_code . '/' . $policy_no . '/' . $fy_code_np;
        $data['code']           = $policy_code;
        $data['policy_no']      = $policy_no;



        // Branch ID
        $data['branch_id']      = $this->dx_auth->get_branch_id();

        // End Date
        $data['end_date']  = date('Y-m-d', strtotime( $data['duration'], strtotime($data['start_date']) ) );

        // Status
        $data['status'] = IQB_POLICY_STATUS_DRAFT;

        return $data;
    }


    // --------------------------------------------------------------------

    /**
     * After Insert Trigger
     *
     * Tasks that are to be performed after policy is created are
     *      1. Add Agent Policy Relation if supplied
     *      2. @TODO: Find other tasks
     *
     * $arr_record structure
     *  'fields'  contains the fields and values that were used while inserting
     *  data.
     *
     *  Example
     *      [
     *          'id'        => 'xxx',
     *          'fields'    => [
     *              'field'  => 'value',
     *              ...
     *           ]
     *      ]
     *
     *
     * @param array $arr_record
     * @return array
     */
    public function after_insert__defaults($arr_record)
    {
        /**
         * Data Structure
         *
            Array
            (
                [id] => 11
                [fields] => Array
                    (
                        [portfolio_id] => 6
                        [policy_package] => tp
                        [customer_name] => Sonam Singh
                        [customer_id] => 15
                        [object_name] => Scooter, Dio, FFF, 9879879, ADSF
                        [object_id] => 21
                        [issue_date] => 2016-12-28
                        [start_date] => 2016-12-28
                        [duration] => +1 year
                        [sold_by] => 2
                        [flag_dc] => C
                        [agent_id] => 214
                        [created_at] => 2016-12-28 16:22:46
                        [created_by] => 1
                        [code] => DRAFT/BRP/MOTOR/ASARV1VHFA/73-74
                        [policy_no] => ASARV1VHFA
                        [branch_id] => 5
                        [end_date] => 2017-12-28
                        [status] => D
                    )

                [method] => insert
            )
        */
        $id = $arr_record['id'] ?? NULL;

        if($id !== NULL)
        {
            $fields = $arr_record['fields'];
            if( isset($fields['flag_dc']) && $fields['flag_dc'] === 'C')
            {
                // Get the agent id
                $agent_id = $fields['agent_id'];
                $relation_data = [
                    'agent_id'  => $agent_id,
                    'policy_id' => $id
                ];
                $this->load->model('rel_agent_policy_model');
                return $this->rel_agent_policy_model->insert($relation_data, TRUE);
            }
        }
        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * After Update Trigger
     *
     * Tasks that are to be performed after a policy is updated
     *      1. Update Agent Relation (ADD or DELETE)
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
                            [portfolio_id] => 6
                            [policy_package] => tp
                            [customer_name] => Bishal Lepcha
                            [customer_id] => 16
                            [object_name] => Motorcycle, Pulsar 250, , 98798, 987987
                            [object_id] => 22
                            [issue_date] => 2016-12-28
                            [start_date] => 2016-12-28
                            [duration] => +1 year
                            [sold_by] => 2
                            [flag_dc] => C
                            [agent_id] => 35
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
            $this->load->model('rel_agent_policy_model');
            $relation_data = [
                'policy_id' => $id
            ];

            if( isset($fields['flag_dc']) && $fields['flag_dc'] === 'C')
            {
                // Add or Update the Relation
                // Get the agent id
                $relation_data['agent_id'] = $fields['agent_id'];
                return $this->rel_agent_policy_model->insert_or_update($relation_data);
            }
            else
            {
                // Delete if we have any existing record having this policy
                return $this->rel_agent_policy_model->delete_by($relation_data);
            }
        }
        return FALSE;
    }

    // --------------------------------------------------------------------


    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [

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
            'module' => 'policy',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}