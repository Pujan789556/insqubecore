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
    protected $before_update = ['before_update__defaults'];
    protected $after_insert  = ['after_insert__defaults', 'clear_cache'];
    protected $after_update  = ['after_update__defaults', 'clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = [ "id", "fiscal_yr_id", "code", "policy_nr", "branch_id", "proposer", "customer_id", "flag_on_credit", "creditor_id", "creditor_branch_id", "care_of", "portfolio_id", "sub_portfolio_id", "policy_package", "sold_by", "object_id", "proposed_date", "issue_date", "start_date", "end_date", "flag_dc", "flag_short_term", "ref_company_id", "status", "verified_by", "verified_date", "created_at", "created_by", "updated_at", "updated_by"];

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
        // $this->set_validation_rules();
    }


    // ----------------------------------------------------------------

    /**
     * Get/Set Validation rule and return (either formatted or section-ed)
     *
     * GET/SET multi section Validation Rule for Policy Creation/Edit
     *
     * @param string $action    add|edit
     * @param bool  $formatted  Multi-sectioned or Formatted to pass into Form Validation Library
     * @param object $record    Policy Record
     * @return array
     */
    public function validation_rules($action, $formatted=FALSE, $record = NULL)
    {
        $this->__set_validation_rule($action, $record);

        // Now Return
        if($formatted)
        {
            return $this->validation_rules_formatted($this->validation_rules);
        }

        return $this->validation_rules;
    }

        private function __set_validation_rule($action, $record=NULL)
        {
            $this->load->model('portfolio_model');
            $this->load->model('user_model');
            $this->load->model('agent_model');
            $this->load->model('company_model');
            $this->load->model('company_branch_model');

            /**
             * List all marketing staffs of this branch
             */
            $branch_id = $this->dx_auth->is_admin() ? NULL : $this->dx_auth->get_branch_id();

            /**
             * If posted and Direct Discount Checked, We don't need agent
             */
            $agent_validation           = 'trim|required|integer|max_length[11]';
            $creditor_validation        = 'trim|integer|max_length[11]';
            $creditor_branch_validation = 'trim|integer|max_length[11]';
            if($this->input->post())
            {
                $flag_dc = $this->input->post('flag_dc');
                if($flag_dc == 'D')
                {
                    $agent_validation = 'trim|integer|max_length[11]';
                }

                $flag_on_credit = $this->input->post('flag_on_credit');
                if($flag_on_credit === 'Y')
                {
                    $creditor_validation        = 'trim|required|integer|max_length[11]';
                    $creditor_branch_validation = 'trim|required|integer|max_length[11]|callback__cb_valid_company_branch';
                }
            }

            /**
             * Default Dropdown Data on Edit/Post
             * -----------------------------------
             *
             *      1. Portfolio's Policy Package Dropdown
             *      2. Creditor Branch Dropdown
             */

            $portfolio_id = $this->input->post('portfolio_id') ? (int)$this->input->post('portfolio_id') : ($record->portfolio_id ?? NULL);
            $creditor_id = $this->input->post('creditor_id') ? (int)$this->input->post('creditor_id') : ($record->creditor_id ?? NULL);

            $sub_portfolio_dropdown     = IQB_BLANK_SELECT;
            $policy_package_dropdown    = IQB_BLANK_SELECT;
            $sub_portfolio_rules        = 'trim|required|integer|max_length[11]';
            if($portfolio_id)
            {
                $sub_dropdown               = $this->portfolio_model->dropdown_children($portfolio_id);
                $sub_portfolio_dropdown     = IQB_BLANK_SELECT + $sub_dropdown;

                $sub_portfolio_rules .= '|in_list[' . implode(',', array_keys($sub_dropdown)) . ']';

                $policy_package_dropdown    = _PO_policy_package_dropdown($portfolio_id);
            }


            $creditor_branch_dropdown   = $creditor_id ? IQB_BLANK_SELECT + $this->company_branch_model->dropdown_by_company($creditor_id) : IQB_BLANK_SELECT;

            $this->validation_rules = [

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
                        '_data'     => IQB_BLANK_SELECT + $this->portfolio_model->dropdown_parent(),
                        '_required' => true
                    ],
                    [
                        'field' => 'sub_portfolio_id',
                        'label' => 'Sub-Portfolio',
                        'rules' => $sub_portfolio_rules,
                        '_type'     => 'dropdown',
                        '_id'       => '_sub-portfolio-id',
                        '_data'     => $sub_portfolio_dropdown,
                        '_required' => true
                    ],
                    [
                        'field' => 'policy_package',
                        'label' => 'Policy Package',
                        'rules' => 'trim|required|alpha|max_length[10]',
                        '_type'     => 'dropdown',
                        '_id'       => '_policy-package-id',
                        '_data'     => $policy_package_dropdown,
                        '_required' => true
                    ]
                ],

                /**
                 * Proposer & Referer (Careof) Information
                 */
                'proposer' => [
                    [
                        'field' => 'proposer',
                        'label' => 'Proposed By',
                        'rules' => 'trim|max_length[255]',
                        '_id'       => 'proposer-text',
                        '_type'     => 'text',
                        '_required' => false
                    ]
                ],

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
                    ]
                ],

                /**
                 *  Policy Object on Loan?
                 *  --------------------------
                 *  If the policy object, eg. motor, is on loan/financed, then
                 *  This creditor company is the "Insured Party" & Customer is "Account Party"
                 */
                'flag_on_credit' => [
                    [
                        'field' => 'flag_on_credit',
                        'label' => 'on Loan/Financed?',
                        'rules' => 'trim|required|alpha|exact_length[1]|in_list[N,Y]',
                        '_id'       => '_flag-on-credit',
                        '_type'     => 'radio',
                        '_data'     => [ 'Y' => 'Yes', 'N' => 'No'],
                        '_default'  => 'N',
                        '_show_label'   => true,
                        '_help_text' => '<i class="fa fa-info-circle"></i> If policy object, eg. motor, is on loan/financed by a bank or financial institution, then  the "<strong>Insured Party</strong>" of this policy  will be that financial institute. The customer will be "<strong>Account Party</strong>" in this case.',
                        '_required'     => true
                    ]
                ],

                'creditor_info' => [
                    [
                        'field' => 'creditor_id',
                        'label' => 'Creditor Company',
                        'rules' => $creditor_validation,
                        '_id'       => '_creditor-id',
                        '_extra_attributes' => 'style="width:100%; display:block"',
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT + $this->company_model->dropdown_creditor(true),
                        '_help_text' => '<i class="fa fa-info-circle"></i> Please ask your IT Support to add "Creditor Company" if not available in this list and try again.',
                        '_required' => true
                    ],
                    [
                        'field' => 'creditor_branch_id',
                        'label' => 'Company Branch',
                        'rules' => $creditor_branch_validation,
                        '_id'       => '_creditor-branch-id',
                        '_extra_attributes' => 'style="width:100%; display:block"',
                        '_type'     => 'dropdown',
                        '_data'     => $creditor_branch_dropdown,
                        '_help_text' => '<i class="fa fa-info-circle"></i> Please ask your IT Support to add "Company Branch" of selected "Creditor Company" if not available in this list and try again.',
                        '_required' => true
                    ],
                    [
                        'field' => 'care_of',
                        'label' => 'Care of (or Referer)',
                        'rules' => 'trim|max_length[100]',
                        '_id'       => '_care-of-text',
                        '_type'     => 'text',
                        '_required' => false
                    ]
                ],



                /**
                 * Policy Object Information
                 */
                'object' => [
                    [
                        'field' => 'object_id',
                        'label' => 'Policy Object',
                        'rules' => 'trim|required|integer|max_length[11]|callback__cb_valid_object_defaults',
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
                        'field' => 'proposed_date',
                        'label' => 'Policy Proposed Date',
                        'rules' => 'trim|required|valid_date',
                        '_type'             => 'date',
                        '_default'          => date('Y-m-d'),
                        '_extra_attributes' => 'data-provide="datepicker-inline"',
                        '_required' => true
                    ],
                    [
                        'field' => 'issue_date',
                        'label' => 'Policy Issue Date',
                        'rules' => 'trim|required|valid_date',
                        '_type'             => 'date',
                        '_default'          => date('Y-m-d'),
                        '_extra_attributes' => 'data-provide="datepicker-inline"',
                        '_required' => true
                    ],
                    [
                        'field' => 'start_date',
                        'label' => 'Policy Start Date',
                        'rules' => 'trim|required|valid_date',
                        '_type'             => 'date',
                        '_default'          => date('Y-m-d'),
                        '_extra_attributes' => 'data-provide="datepicker-inline"',
                        '_required' => true
                    ],
                    [
                        'field' => 'end_date',
                        'label' => 'Policy End Date',
                        'rules' => 'trim|required|valid_date|callback__cb_valid_policy_duration',
                        '_type'             => 'date',
                        '_default'          => date('Y-m-d', strtotime( '+1 year', strtotime( date('Y-m-d') ) ) ),
                        '_extra_attributes' => 'data-provide="datepicker-inline"',
                        '_required' => true
                    ]
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
                        '_data'     => IQB_BLANK_SELECT + $this->user_model->dropdown($branch_id),
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
                        '_data'     => IQB_BLANK_SELECT + $this->agent_model->dropdown(true),
                        '_required' => true
                    ],
                    [
                        'field' => 'ref_company_id',
                        'label' => 'Business Referer',
                        'rules' => 'trim|integer|max_length[11]',
                        '_id'       => '_ref-company-id',
                        '_extra_attributes' => 'style="width:100%; display:block"',
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT + $this->company_model->dropdown_general(true),
                        '_help_text' => '<i class="fa fa-info-circle"></i> Please ask your IT Support to add "Business Referer" if not available in this list and try again.',
                        '_required' => false
                    ],
                ]

            ];


            /**
             * ID is compulsory in EDIT
             *
             * This is required as for some callbacks such as "_cb_valid_object_defaults"
             */
            if($action === 'edit')
            {
                $this->validation_rules['edit_extras'] = [
                    [
                        'field' => 'id',
                        'label' => 'Policy ID',
                        'rules' => 'trim|required|integer|max_length[11]',
                        '_type'     => 'hidden',
                        '_id'       => 'policy-id',
                        '_required' => true
                    ]
                ];
            }
        }

    // ----------------------------------------------------------------

    public function validation_rules_formatted($validation_rules)
    {

        $v_rules = [];

        // Merge All Sections and return
        foreach($validation_rules as $section=>$rules)
        {
            $v_rules = array_merge($v_rules, $rules);
        }
        return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Update Policy Status
     *
     * @param integer $id Policy ID
     * @param alpha $to_status_code Status Code
     * @return bool
     */
    public function update_status($id, $to_status_code)
    {
        $data = [
            'status' => $to_status_code
        ];
        return $this->db->where('id', $id)
                        ->update($this->table_name, $data);
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
            $select = "P.*,
                        PRT.name_en as portfolio_name, C.full_name as customer_name,
                        SPRT.name_en as sub_portfolio_name";
            if($signle_select)
            {
                $select .= ', RAP.agent_id';
            }
            $this->db->select($select)
                     ->from($this->table_name . ' as P')
                     ->join('master_portfolio PRT', 'PRT.id = P.portfolio_id')
                     ->join('master_portfolio SPRT', 'SPRT.id = P.sub_portfolio_id')
                     ->join('dt_customers C', 'C.id = P.customer_id');

            if($signle_select)
            {
                $this->db->join('rel_agent_policy RAP', 'RAP.policy_id = P.id', 'left');
            }
        }

    // ----------------------------------------------------------------

    /**
     * Get Details
     *
     * Get details of this Policy
     *
     * @param array $params
     * @return type
     */
    public function get($id)
    {
        return $this->db->select(  "P.*, PRT.name_en as portfolio_name, PRT.code as portfolio_code,
                                    PRM.total_amount, PRM.stamp_duty, PRM.attributes as premium_attributes,
                                    C.code as customer_code, C.full_name as customer_name, C.type as customer_type, C.pan as customer_pan, C.picture as customer_picture, C.profession as customer_profession, C.contact as customer_contact, C.company_reg_no, C.citizenship_no, C.passport_no,
                                    O.attributes as object_attributes,
                                    A.id as agent_id, A.name as agent_name, A.picture as agent_picture, A.bs_code as agent_bs_code, A.ud_code as agent_ud_code, A.contact as agent_contact, A.active as agent_active, A.type as agent_type,
                                    CRD.name as creditor_name, CRD.contact as creditor_contact,
                                    CRB.name as creditor_branch_name, CRB.contact as creditor_branch_contact,
                                    U.username as sales_staff_username, U.profile as sales_staff_profile
                            ")
                     ->from($this->table_name . ' as P')
                     ->join('dt_policy_premium PRM', 'PRM.policy_id = P.id')
                     ->join('master_portfolio PRT', 'PRT.id = P.portfolio_id')
                     ->join('dt_customers C', 'C.id = P.customer_id')
                     ->join('dt_policy_objects O', 'O.id = P.object_id')
                     ->join('auth_users U', 'U.id = P.sold_by')
                     ->join('rel_agent_policy RAP', 'RAP.policy_id = P.id', 'left')
                     ->join('master_companies CRD', 'CRD.id = P.creditor_id', 'left')
                     ->join('master_company_branches CRB', 'CRB.id = P.creditor_branch_id AND CRB.company_id = CRD.id', 'left')
                     ->join('master_agents A', 'RAP.agent_id = A.id', 'left')
                     ->where('P.id', $id)
                     ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get Policy Code (Draft)
     *
     * @param integer $portfolio_id
     * @param object $fy_record
     * @return string
     */
    private function _get_draft_policy_code($portfolio_id, $fy_record)
    {
        $this->load->library('Token');
        $this->load->model('portfolio_model');

        $portfolio_code = $this->portfolio_model->get_code($portfolio_id);
        $fy_code_np     = $fy_record->code_np;

        $policy_no = strtoupper($this->token->generate(10));

        /**
         * Construct Policy Code
         *
         * @TODO: Draft/Final Policy Code
         *
         * Format: DRAFT-<BRANCH-CODE>-<PORTFOLIO-CODE>-<SERIALNO>-<FY_CODE_NP>
         */
        $policy_code = 'DRAFT/' . $this->dx_auth->get_branch_code() . '/' . $portfolio_code . '/' . $policy_no . '/' . $fy_code_np;

        return $policy_code;
    }

    // --------------------------------------------------------------------

    /**
     * Before Insert Trigger
     *
     * Tasks carried
     *      1. Generate Random Policy Number & add
     *      2. Add Draft Code
     *      3. Add Branch ID
     *      4. Add Short Term Flag
     *      5. Add Status = Draft
     *      6. Add Fiscal Year
     *      7. Policy Duration
     *
     * @param array $data
     * @return array
     */
    public function before_insert__defaults($data)
    {
        $this->load->library('Token');
        $this->load->model('fiscal_year_model');

        $fy_record      = $this->fiscal_year_model->get_current_fiscal_year();

        /**
         * Policy Code - Draft One & Policy Number
         *
         * Format: DRAFT-<BRANCH-CODE>-<PORTFOLIO-CODE>-<SERIALNO>-<FY_CODE_NP>
         */
        $data['code']      = $this->_get_draft_policy_code($data['portfolio_id'], $fy_record);


        // Branch ID
        $data['branch_id']      = $this->dx_auth->get_branch_id();


        /**
         * Short Term Flag???
         * ------------------
         * Find if this start-end date gives a default duration or short term duration
         */
        $data['flag_short_term'] = _POLICY__get_short_term_flag( $data['portfolio_id'], $data['start_date'], $data['end_date'] );


        // Status
        $data['status'] = IQB_POLICY_STATUS_DRAFT;

        // Fiscal Year
        $data['fiscal_yr_id'] = $fy_record->id;

        return $data;
    }

    // ----------------------------------------------------------------


    /**
     * Before Update Trigger
     *
     * Tasks carried
     *      1. Short Term Flag
     *
     * @param array $data
     * @return array
     */
    public function before_update__defaults($data)
    {
        /**
         * Short Term Flag???
         * ------------------
         * Find if this start-end date gives a default duration or short term duration
         */
        $data['flag_short_term'] = _POLICY__get_short_term_flag( $data['portfolio_id'], $data['start_date'], $data['end_date'] );
        return $data;
    }

    // --------------------------------------------------------------------

    /**
     * After Insert Trigger
     *
     * Tasks that are to be performed after policy is created are
     *      1. Add Agent Policy Relation if supplied
     *      2. Add Premium Based on Portfolio and Object Details
     *      3. @TODO: Find other tasks
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
                        [fiscal_yr_id] => x
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

            /**
             * TASK 1: Add agent relation
             * --------------------------
             */
            if( isset($fields['flag_dc']) && $fields['flag_dc'] === 'C')
            {
                // Get the agent id
                $agent_id = $fields['agent_id'];
                $relation_data = [
                    'agent_id'  => $agent_id,
                    'policy_id' => $id
                ];
                $this->load->model('rel_agent_policy_model');
                $this->rel_agent_policy_model->insert($relation_data, TRUE);
            }
            return TRUE;

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
        // Safe to Delete?
        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        // Check the Record Status
        $record = $this->policy_model->find($id);
        if(!$record || $record->status !== IQB_POLICY_STATUS_DRAFT )
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