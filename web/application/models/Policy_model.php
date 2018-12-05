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
    protected $after_insert  = ['after_insert__defaults'];
    protected $after_update  = ['after_update__defaults'];
    protected $after_delete  = [];


    protected $fields = [ 'id', 'ancestor_id', 'fiscal_yr_id', 'portfolio_id', 'branch_id', 'district_id', 'category', 'insurance_company_id', 'code', 'proposer', 'proposer_address', 'proposer_profession', 'customer_id', 'object_id', 'care_of', 'policy_package', 'sold_by', 'proposed_date', 'issued_date', 'issued_time', 'start_date', 'start_time', 'end_date', 'end_time', 'flag_on_credit', 'flag_dc', 'flag_short_term', 'status', 'created_at', 'created_by', 'verified_at', 'verified_by', 'updated_at', 'updated_by' ];

    protected $endorsement_fields = ['proposed_date', 'issued_date', 'issued_time', 'start_date', 'start_time', 'end_date', 'end_time'];

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

        // Dependent Model
        $this->load->model('object_model');
        $this->load->model('customer_model');
        $this->load->model('endorsement_model');
        $this->load->model('tag_model');
    }


    // ----------------------------------------------------------------

    /**
     * Set/Get Validation Rules
     *
     * Set the validation rules as per the action specified.
     * And returns the rules (Formatted/Sectioned)
     *
     * @param string $action
     * @param bool $formatted
     * @param object|null $record
     * @return array
     */
    public function validation_rules($action, $formatted = FALSE, $record=NULL)
    {
        /**
         * Set the Validation Rules According to the action
         */
        $this->validation_rules = [];

        switch($action)
        {
            case 'add_edit_draft':
                $this->__set_v_rules__add_edit_draft($record);
                break;

            default:
                break;
        }

        if( !$formatted )
        {
            return $this->validation_rules;
        }
        else
        {
            return $this->__get_v_rules__formatted();
        }
    }

    // ----------------------------------------------------------------

        /**
         * Set Validation Rules for Add/Edit Draft Policy
         *
         * @param object|null $record
         * @return void
         */
        private function __set_v_rules__add_edit_draft($record=NULL)
        {
            $this->load->model('portfolio_model');
            $this->load->model('user_model');
            $this->load->model('agent_model');
            $this->load->model('company_model');
            $this->load->model('company_branch_model');
            $this->load->model('district_model');

            /**
             * Dropdowns
             */
            $district_dropdown  = $this->district_model->dropdown();
            $insurance_dropdown = $this->company_model->dropdown_insurance();

            /**
             * FAC-in Company ID Validation
             */


            /**
             * Run-Time Validation Rules
             */
            $insurance_company_validation   = 'trim|integer|max_length[8]';
            $agent_validation               = 'trim|required|integer|max_length[11]';
            if($this->input->post())
            {
                $flag_dc = $this->input->post('flag_dc');
                if( in_array($flag_dc, [IQB_POLICY_FLAG_DC_DIRECT, IQB_POLICY_FLAG_DC_NONE]) )
                {
                    // If posted and Direct Discount Checked, We don't need agent
                    $agent_validation = 'trim|integer|max_length[11]';
                }

                // If Category FAC-in or CO-in, We must need FAC-in Company ID
                $category = $this->input->post('category');
                if($category != IQB_POLICY_CATEGORY_REGULAR)
                {
                    $insurance_company_validation  = 'trim|required|integer|max_length[8]';
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

            $policy_package_dropdown    = IQB_BLANK_SELECT;
            if($portfolio_id)
            {
                $portfolio_id               = (int)$portfolio_id;
                $policy_package_dropdown    = _OBJ_policy_package_dropdown($portfolio_id);
            }

            /**
             * Default End Duration as 1 Year (including start date)
             */
            $end_date = new DateTime('today + 364 days');
            $default_end_datetime  = $end_date->format('Y-m-d') . ' 23:59:00';

            // Set the Validation Rules
            $this->validation_rules = [

                /**
                 * Validation Rules For
                 * --------------------
                 *  a. Fresh Policy Add
                 *  b. Fresh Policy Draft Edit
                 *  c. Renewal Policy Add
                 *  d. Renewal Policy Draft Edit
                 */

                /**
                 * Policy Category
                 */
                'category' => [
                    [
                        'field' => 'category',
                        'label' => 'Category',
                        'rules' => 'trim|required|integer|exact_length[1]|in_list['.implode(',', array_keys(IQB_POLICY_CATEGORIES)).']',
                        '_type'     => 'dropdown',
                        '_data'     => IQB_POLICY_CATEGORIES,
                        '_default'  => IQB_POLICY_CATEGORY_REGULAR,
                        '_id'       => 'policy-category-id',
                        '_required' => true
                    ]
                ],

                /**
                 * Insurance Company  in Case Category is FAC-in or CO-Insurance.
                 */
                'insurance_company' => [
                    [
                        'field' => 'insurance_company_id',
                        'label' => 'FAC/CO-in From (Insurance Company)',
                        'rules' => $insurance_company_validation,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT + $insurance_dropdown,
                        '_extra_attributes' => 'style="width:100%; display:block"',
                        '_id'       => 'insurance-company-id',
                        '_required' => true
                    ]
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
                        '_data'     => IQB_BLANK_SELECT + $this->portfolio_model->dropdown_children_tree(TRUE),
                        '_extra_attributes' => 'style="width:100%; display:block"',
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
                        'rules' => 'trim|htmlspecialchars|max_length[255]',
                        '_id'       => 'proposer-text',
                        '_type'     => 'text',
                        '_required' => false
                    ],
                    [
                        'field' => 'proposer_address',
                        'label' => 'Address',
                        'rules' => 'trim|htmlspecialchars|max_length[250]',
                        '_id'       => 'proposer-address',
                        '_type'     => 'textarea',
                        'rows'      => 4,
                        '_required' => false
                    ],
                    [
                        'field' => 'proposer_profession',
                        'label' => 'Profession',
                        'rules' => 'trim|htmlspecialchars|max_length[100]',
                        '_id'       => 'proposer-profession',
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
                        'field' => 'customer_name_en',
                        'label' => 'Customer',
                        'rules' => 'trim|required',
                        '_id'       => 'customer-text',
                        '_type'     => 'hidden',
                        '_required' => true
                    ]
                ],

                /**
                 * Customer Information
                 */
                'care_of' => [
                    [
                        'field' => 'care_of',
                        'label' => 'Care of',
                        'rules' => 'trim|max_length[1000]',
                        '_id'       => '_care-of-text',
                        '_type'     => 'textarea',
                        'rows'      => 5,
                        '_required' => false
                    ]
                ],

                /**
                 * Risk District Information
                 */
                'district' => [
                    [
                        'field' => 'district_id',
                        'label' => 'Risk District',
                        'rules' => 'trim|required|integer|in_list['. implode(',', array_keys($district_dropdown)) .']',
                        '_type'     => 'dropdown',
                        '_id'       => '_district-id',
                        '_extra_attributes' => 'style="width:100%; display:block"',
                        '_data'       => IQB_BLANK_SELECT + $district_dropdown,
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
                        '_help_text' => '<i class="fa fa-info-circle"></i> If you have selected yes, you must supply Bank/Financial Institution information on <strong>Policy Detail Page</strong>.',
                        '_required'     => true
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
                        'field' => 'issued_datetime',
                        'label' => 'Policy Issue Date & Time',
                        'rules' => 'trim|required|valid_date',
                        '_type'             => 'datetime',
                        '_default'          => date('Y-m-d H:i:00'),
                        '_extra_attributes' => 'data-provide="datetimepicker-inline"',
                        '_required' => true
                    ],
                    [
                        'field' => 'start_datetime',
                        'label' => 'Policy Start Date & Time',
                        'rules' => 'trim|required|valid_date',
                        '_type'             => 'datetime',
                        '_default'          => date('Y-m-d H:i:00'),
                        '_id'               => '_start_datetime',
                        '_extra_attributes' => 'data-provide="datetimepicker-inline"',
                        '_required' => true
                    ],
                    [
                        'field' => 'end_datetime',
                        'label' => 'Policy End Date & Time',
                        'rules' => 'trim|required|valid_date|callback__cb_valid_policy_duration',
                        '_type'             => 'datetime',
                        '_default'          => $default_end_datetime,
                        '_id'               => '_end_datetime',
                        '_extra_attributes' => 'data-provide="datetimepicker-inline"',
                        '_required' => true
                    ]
                ],

                /**
                 * Sales Info - Marketing Staff, Agent Info, Commission or Direct Discount
                 */
                'sales' => [
                    [
                        'field' => 'sold_by',
                        'label' => 'Staff',
                        'rules' => 'trim|integer|max_length[11]',
                        '_id'       => '_marketing-staff',
                        '_extra_attributes' => 'style="width:100%; display:block"',
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT + $this->user_model->dropdown(),
                        '_required' => false
                    ],
                    [
                        'field' => 'flag_dc',
                        'label' => 'Direct Discount or Agent Commission',
                        'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode( ',', array_keys( _POLICY_flag_dc_dropdown(false) ) ).']',
                        '_id'       => '_flag-dc',
                        '_type'     => 'radio',
                        '_data'     => _POLICY_flag_dc_dropdown(false),
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
                        'field' => 'tags[]',
                        '_key'  => 'tags',
                        'label' => 'Policy Tags',
                        'rules' => 'trim|integer|max_length[11]',
                        '_id'       => '_policy-tags',
                        '_extra_attributes' => 'style="width:100%; display:block" multiple="multiple" data-placeholder="Select..."',
                        '_type'     => 'dropdown',
                        '_data'     => $this->tag_model->dropdown(),
                        '_class'     => 'form-control select-multiple',
                        '_help_text' => '<i class="fa fa-info-circle"></i> Please ask your IT Support to add "Tags" if not available in this list and try again.',
                        '_required' => false
                    ],
                ],

                /**
                 * Policy Endorsement - Txn Details (सम्पुष्टि विवरण), Remarks and Template Reference
                 */
                'endorsement_basic' => $this->endorsement_model->get_v_rules_basic_for_debit_note( IQB_POLICY_ENDORSEMENT_TYPE_FRESH, $portfolio_id, TRUE)

            ];
        }

    // ----------------------------------------------------------------

        /**
         * Get Formatted Validation Rules
         *
         * @return array
         */
        private function __get_v_rules__formatted( )
        {
            $v_rules_formatted  = [];

            // Merge All Sections and return
            foreach($this->validation_rules as $section=>$rules)
            {
                $v_rules_formatted = array_merge($v_rules_formatted, $rules);
            }
            return $v_rules_formatted;
        }

    // ----------------------------------------------------------------

    /**
     * Get Endorsement Validation Rules
     *
     * @return array
     */
    public function get_endorsement_validation_rules()
    {
        return  [
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
                        'field' => 'issued_datetime',
                        'label' => 'Policy Issue Date & Time',
                        'rules' => 'trim|required|valid_date',
                        '_type'             => 'datetime',
                        '_default'          => date('Y-m-d H:i:00'),
                        '_extra_attributes' => 'data-provide="datetimepicker-inline"',
                        '_required' => true
                    ],
                    [
                        'field' => 'start_datetime',
                        'label' => 'Policy Start Date & Time',
                        'rules' => 'trim|required|valid_date',
                        '_type'             => 'datetime',
                        '_default'          => date('Y-m-d H:i:00'),
                        '_extra_attributes' => 'data-provide="datetimepicker-inline"',
                        '_required' => true
                    ],
                    [
                        'field' => 'end_datetime',
                        'label' => 'Policy End Date & Time',
                        'rules' => 'trim|required|valid_date|callback__cb_valid_policy_duration',
                        '_type'             => 'datetime',
                        '_default'          => date('Y-m-d H:i:00', strtotime( '+1 year', strtotime( date('Y-m-d H:i:00') ) ) ),
                        '_extra_attributes' => 'data-provide="datetimepicker-inline"',
                        '_required' => true
                    ]
                ];
    }

    // ----------------------------------------------------------------

    /**
     * Get Creditor Validation Rules
     *
     * @return array
     */
    public function get_creditor_validation_rules($record = NULL)
    {
        $this->load->model('company_model');
        $this->load->model('company_branch_model');


        $creditor_id = $this->input->post('creditor_id') ? (int)$this->input->post('creditor_id') : ($record->creditor_id ?? NULL);
        $creditor_branch_dropdown   = $creditor_id ? IQB_BLANK_SELECT + $this->company_branch_model->dropdown_by_company($creditor_id) : IQB_BLANK_SELECT;

        return  [
            [
                'field' => 'creditor_id',
                'label' => 'Creditor Bank/Finance Institution',
                'rules' => 'trim|required|integer|max_length[11]',
                '_id'               => '_creditor-id',
                '_extra_attributes' => 'style="width:100%; display:block"',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $this->company_model->dropdown_creditor(true),
                '_help_text' => '<i class="fa fa-info-circle"></i> Please ask your IT Support to add "Creditor Company" if not available in this list and try again.',
                '_required' => true
            ],
            [
                'field' => 'creditor_branch_id',
                'label' => 'Creditor Branch',
                'rules' => 'trim|required|integer|max_length[11]|callback__cb_valid_creditor_branch',
                '_id'       => '_creditor-branch-id',
                '_extra_attributes' => 'style="width:100%; display:block"',
                '_type'     => 'dropdown',
                '_data'     => $creditor_branch_dropdown,
                '_help_text' => '<i class="fa fa-info-circle"></i> Please ask your IT Support to add "Company Branch" of selected "Creditor Company" if not available in this list and try again.',
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Get Beema Samiti Report Information Validation Rules
     *
     * @return array
     */
    public function get_bs_report_heading_rules( )
    {
        return  [
                    [
                        'field' => 'bsrs_heading_id[]',
                        'label' => 'BS Reporting Heading',
                        'rules' => 'trim|required|integer|max_length[8]',
                        '_key'      => 'bsrs_heading_id',
                        '_type'     => 'dropdown',
                        '_data'     => [],
                        '_class'     => 'form-control select-multiple',
                        '_extra_attributes' => 'multiple="multiple" style="width:100%" data-placeholder="Select..."',
                    ],
                ];
    }

    // ----------------------------------------------------------------

    /**
     * Add a Fresh/Renewal Policy Debit Note(Draft)
     *
     * @param array $data
     * @return mixed
     */
    public function add_debit_note($data)
    {
        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $id                 = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Insert Master Record, No Validation Required as it is performed on Controller
            $id = parent::insert($data, TRUE);


        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $id = FALSE;
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $id;
    }

    // ----------------------------------------------------------------

    public function edit_debit_note($id, $data)
    {
        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $status             = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Update Master Record, No Validation Required as it is performed on Controller
            $status = parent::update($id, $data, TRUE);


        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
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
        $policy_code = 'DRAFT-' . $this->dx_auth->get_branch_code() . '-' . $portfolio_code . '-' . $policy_no . '-' . $fy_code_np;

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

        $fy_record = $this->fiscal_year_model->get_fiscal_year($data['issued_datetime']);

        /**
         * Policy Code - Draft One & Policy Number
         *
         * Format: DRAFT-<BRANCH-CODE>-<PORTFOLIO-CODE>-<SERIALNO>-<FY_CODE_NP>
         */
        $data['code']      = $this->_get_draft_policy_code($data['portfolio_id'], $fy_record);


        // Branch ID
        $data['branch_id']      = $this->dx_auth->get_branch_id();


        // Status
        $data['status'] = IQB_POLICY_STATUS_DRAFT;


        // Fiscal Year
        $data['fiscal_yr_id'] = $fy_record->id;


        // Refactor Date & time
        $data = $this->__refactor_datetime_fields($data);


        // Category and Insurance Company ID
        $data = $this->__category_defaults($data);


        /**
         * Short Term Flag???
         * ------------------
         *
         *
         * Find if this start-end date gives a default duration or short term duration
         */
        $data['flag_short_term'] = _POLICY__get_short_term_flag( $data['portfolio_id'], $fy_record->id, $data['start_date'], $data['end_date'] );

        /**
         * No marketing staff select?
         */
        $data['sold_by'] = $data['sold_by'] ? $data['sold_by'] : NULL;

        return $data;
    }

    // ----------------------------------------------------------------


    /**
     * Before Update Trigger
     *
     * Tasks carried
     *
     *      1. Issue Date & Time
     *      2. Start Date & Time
     *      3. End Date & Time
     *      4. Short Term Flag
     *
     * @param array $data
     * @return array
     */
    public function before_update__defaults($data)
    {
        // Refactor Date & time
        $data = $this->__refactor_datetime_fields($data);

        // Category and Insurance Company ID
        $data = $this->__category_defaults($data);


        // Status
        $data['status'] = IQB_POLICY_STATUS_DRAFT;


        /**
         * Short Term Flag???
         * ------------------
         *
         *
         * Find if this start-end date gives a default duration or short term duration
         */
        $fy_record = $this->fiscal_year_model->get_fiscal_year($data['issued_date']);
        $data['flag_short_term'] = _POLICY__get_short_term_flag( $data['portfolio_id'], $fy_record->id, $data['start_date'], $data['end_date'] );


        /**
         * No marketing staff select?
         */
        $data['sold_by'] = $data['sold_by'] ? $data['sold_by'] : NULL;


        return $data;
    }

    // --------------------------------------------------------------------

        private function __refactor_datetime_fields($data)
        {
            // Dates
            $data['issued_date']    = date('Y-m-d', strtotime($data['issued_datetime']));
            $data['start_date']     = date('Y-m-d', strtotime($data['start_datetime']));
            $data['end_date']       = date('Y-m-d', strtotime($data['end_datetime']));

            // Times
            $data['issued_time']    = date('H:i:00', strtotime($data['issued_datetime']));
            $data['start_time']     = date('H:i:00', strtotime($data['start_datetime']));

            // End time is always 23:59:00
            $data['end_time']       = '23:59:00';   // date('H:i:00', strtotime($data['end_datetime']));

            // unset
            unset($data['issued_datetime']);
            unset($data['start_datetime']);
            unset($data['end_datetime']);


            // process backdate
            $data = $this->_backdate($data);


            return $data;
        }

    // --------------------------------------------------------------------

        /**
         * Before Insert/Update Defaults - Sub-Function
         *
         * Set Policy Category and Insurance Company Properly
         * i.e. Insurance Company Must be set to "NULL"
         *      for foreign key constraint
         *
         * @param array $data
         * @return array
         */
        private function __category_defaults($data)
        {
            $category = (int)$data['category'];

            if($category === IQB_POLICY_CATEGORY_REGULAR )
            {
                $data['insurance_company_id'] = NULL;
            }

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
                        [fiscal_yr_id] => x
                        [policy_package] => tp
                        [customer_name_en] => Sonam Singh
                        [customer_id] => 15
                        [object_name] => Scooter, Dio, FFF, 9879879, ADSF
                        [object_id] => 21
                        [issued_date] => 2016-12-28
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
            if( isset($fields['flag_dc']) && $fields['flag_dc'] === IQB_POLICY_FLAG_DC_AGENT_COMMISSION)
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

            /**
             * Task 2: Fresh/Renewal Endorsement Data
             * --------------------------------------
             */
            $this->_save_endorsement_basic($id, $fields);

            /**
             * Task 3: Policy Tags
             * ---------------------
             */
            $this->load->model('rel_policy_tag_model');
            $tags = $fields['tags'] ?? [];
            $this->rel_policy_tag_model->save($id, $tags);


            /**
             * Task 4: Clear Cache
             * ---------------------
             */
            $customer_id = $fields['customer_id'];
            $this->clear_cache('policy_cst_'.$customer_id);

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
                            [customer_name_en] => Bishal Lepcha
                            [customer_id] => 16
                            [object_name] => Motorcycle, Pulsar 250, , 98798, 987987
                            [object_id] => 22
                            [issued_date] => 2016-12-28
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

            /**
             * TASK 1: Update Agent Relation
             * ------------------------------
             */
            $this->load->model('rel_agent_policy_model');
            $relation_data = [
                'policy_id' => $id
            ];

            if( isset($fields['flag_dc']) && $fields['flag_dc'] === IQB_POLICY_FLAG_DC_AGENT_COMMISSION)
            {
                // Add or Update the Relation
                // Get the agent id
                $relation_data['agent_id'] = $fields['agent_id'];
                $this->rel_agent_policy_model->insert_or_update($relation_data);
            }
            else
            {
                // Delete if we have any existing record having this policy
                $this->rel_agent_policy_model->delete_by($relation_data);
            }

            /**
             * Task 2: Fresh/Renewal Endorsement Data
             * --------------------------------------
             */
            $this->_save_endorsement_basic($id, $fields);


            /**
             * Task 3: Policy Tags
             * ---------------------
             */
            $this->load->model('rel_policy_tag_model');
            $tags = $fields['tags'] ?? [];
            $this->rel_policy_tag_model->save($id, $tags);

            /**
             * Task 4: Delete Creditors if Flag on Credits is NO
             */
            if( isset($fields['flag_on_credit']) && $fields['flag_on_credit'] === IQB_FLAG_NO)
            {
                $this->load->model('rel_policy_creditor_model');
                $this->rel_policy_creditor_model->delete_by_policy($id);
            }


            /**
             * Task 5: Clear Cache
             * ---------------------
             */
            $customer_id = $fields['customer_id'];
            $this->clear_cache('policy_cst_'.$customer_id);
        }

        return TRUE;
    }

    // ----------------------------------------------------------------

    /**
     * Save Endorsement basic data on add/edit Policy Draft
     *
     * Endorsement Txn Details and Remarks on Policy Debit NOte add/edit
     *
     * @param array $data
     * @return mixed
     */
    public function _save_endorsement_basic($id, $data)
    {
        /**
         * The following fields are saved from policy debit note add/edit
         *
         * CUSTOMER
         * ISSUED DATE
         * START DATE
         * END DATE
         * SOLD BY
         * TXN DETAILS
         * REMARKS
         *
         */
        $endorsement_record = $this->endorsement_model->get_current_endorsement_by_policy($id);
        $endorsement_data = [
            'customer_id'   => $data['customer_id'],
            'issued_date'   => $data['issued_date'],
            'start_date'    => $data['start_date'],
            'end_date'      => $data['end_date'],
            'sold_by'       => $data['sold_by'],
            'txn_details'   => $data['txn_details'],
            'remarks'       => $data['remarks']
        ];

        // echo '<pre>'; print_r($endorsement_data);exit;

        return $this->endorsement_model->save($endorsement_record->id, $endorsement_data);
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
     * Update Customer on Policy on Ownership Transfer
     *
     * @param int $id
     * @param int $customer_id
     * @return bool
     */
    public function transfer_ownership($id, $customer_id)
    {
        $data = ['customer_id' => $customer_id];
        return $this->db->where('id', $id)
                        ->update($this->table_name, $data);
    }

    // ----------------------------------------------------------------

    /**
     * Update Policy End Date
     *
     * This is when an endorsement has different end date
     *
     * @param int $id
     * @param date $end_date
     * @return bool
     */
    public function update_end_date($id, $end_date)
    {
        $data = ['end_date' => $end_date];
        return $this->db->where('id', $id)
                        ->update($this->table_name, $data);
    }

    // ----------------------------------------------------------------
    //  POLICY STATUS UPDATE METHODS
    // ----------------------------------------------------------------

    /**
     * Update Policy Status
     *
     *
     * NOTE: This method must be triggered by controller method
     *  within try-catch block.
     *
     * @param integer|object $record Policy ID | Policy Record
     * @param alpha $to_status_flag Status Code
     * @return bool
     */
    public function update_status($record, $to_status_flag)
    {
        // Get the Policy Record
        $record = is_numeric($record) ? $this->get( (int)$record ) : $record;

        // Valid Record? Valid Status Code?
        if( !$record || !in_array($to_status_flag, array_keys( _POLICY_status_dropdown() ) ) )
        {
            throw new Exception("Exception [Model: Policy_model][Method: update_status()]: Either Policy Record not found or Invalid status flag supplied.");
        }

        // Status Qualified?
        if( !$this->status_qualifies($record->status, $to_status_flag) )
        {
            throw new Exception("Exception [Model: Policy_model][Method: update_status()]: Current Status does not qualify to upgrade/downgrade.");
        }

        $transaction_status = FALSE;

        /**
         * Update Status
         */
        switch($to_status_flag)
        {
            /**
             * to Draft
             */
            case IQB_POLICY_STATUS_DRAFT:
                $transaction_status = $this->to_draft($record);
                break;


            /**
             * to Verified
             */
            case IQB_POLICY_STATUS_VERIFIED:
                $transaction_status = $this->to_verified($record);
                break;

            /**
             * to Active
             */
            case IQB_POLICY_STATUS_ACTIVE:
                $transaction_status = $this->to_activated($record);
                break;

            /**
             * to Cancel/Expire
             */
            case IQB_POLICY_STATUS_CANCELED:
                $transaction_status = $this->to_canceled($record);
                break;

            case IQB_POLICY_STATUS_EXPIRED:
                $transaction_status = $this->to_expired($record);
                break;
        }

        return $transaction_status;
    }

    // ----------------------------------------------------------------

    public function status_qualifies($current_status, $to_status)
    {
        $flag_qualifies = FALSE;

        switch ($to_status)
        {
            case IQB_POLICY_STATUS_DRAFT:
                $flag_qualifies = $current_status === IQB_POLICY_STATUS_VERIFIED;
                break;

            case IQB_POLICY_STATUS_VERIFIED:
                $flag_qualifies = $current_status === IQB_POLICY_STATUS_DRAFT;
                break;

            case IQB_POLICY_STATUS_ACTIVE:
                $flag_qualifies = $current_status === IQB_POLICY_STATUS_VERIFIED;
                break;

            case IQB_POLICY_STATUS_CANCELED:
                $flag_qualifies = $current_status === IQB_POLICY_STATUS_ACTIVE;
                break;

            case IQB_POLICY_STATUS_EXPIRED:
                $flag_qualifies = $current_status === IQB_POLICY_STATUS_ACTIVE;
                break;

            default:
                break;
        }
        return $flag_qualifies;
    }

    // ----------------------------------------------------------------

    public function to_expired($id_record)
    {
        // Get the Policy Record
        $record = is_numeric($id_record) ? $this->get( (int)$id_record ) : $id_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Policy_model][Method: to_expired()]: Policy record could not be found.");
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_POLICY_STATUS_EXPIRED);

            /**
             * Unlock Policy Object and Customer
             */
            $this->object_model->update_lock($record->object_id, IQB_FLAG_UNLOCKED);
            $this->customer_model->update_lock($record->customer_id, IQB_FLAG_UNLOCKED);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;
        // Throw Exception on ERROR
        if( !$transaction_status )
        {
            throw new Exception("Exception [Model: Policy_model][Method: to_expired()]: Policy status and post-status update task could not be updated.");
        }

        // return result/status
        return $transaction_status;
    }

    // ----------------------------------------------------------------

    public function to_canceled($id_record)
    {
        // Get the Policy Record
        $record = is_numeric($id_record) ? $this->get( (int)$id_record ) : $id_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Policy_model][Method: to_canceled()]: Policy record could not be found.");
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_POLICY_STATUS_CANCELED);

            /**
             * Unlock Policy Object and Customer
             */
            $this->object_model->update_lock($record->object_id, IQB_FLAG_UNLOCKED);
            $this->customer_model->update_lock($record->customer_id, IQB_FLAG_UNLOCKED);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;
        // Throw Exception on ERROR
        if( !$transaction_status )
        {
            throw new Exception("Exception [Model: Policy_model][Method: to_canceled()]: Policy status and post-status update task could not be updated.");
        }

        // return result/status
        return $transaction_status;
    }

    // ----------------------------------------------------------------

    public function to_activated($id_record)
    {
        // Get the Policy Record
        $record = is_numeric($id_record) ? $this->get( (int)$id_record ) : $id_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Policy_model][Method: to_activated()]: Policy record could not be found.");
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_POLICY_STATUS_ACTIVE);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;
        // Throw Exception on ERROR
        if( !$transaction_status )
        {
            throw new Exception("Exception [Model: Policy_model][Method: to_activated()]: Policy status and post-status update task could not be updated.");
        }

        // return result/status
        return $transaction_status;
    }

    // ----------------------------------------------------------------

    public function to_verified($id_record)
    {
        // Get the Policy Record
        $record = is_numeric($id_record) ? $this->get( (int)$id_record ) : $id_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Policy_model][Method: to_verified()]: Policy record could not be found.");
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_POLICY_STATUS_VERIFIED);

            /**
             * Update Transaction Status, Lock Object, Customer
             */
            $endorsement_record = $this->endorsement_model->get_current_endorsement_by_policy($record->id);
            $this->endorsement_model->to_verified($endorsement_record);
            $this->object_model->update_lock($record->object_id, IQB_FLAG_LOCKED);
            $this->customer_model->update_lock($record->customer_id, IQB_FLAG_LOCKED);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;
        // Throw Exception on ERROR
        if( !$transaction_status )
        {
            throw new Exception("Exception [Model: Policy_model][Method: to_verified()]: Policy status and post-status update task could not be updated.");
        }

        // return result/status
        return $transaction_status;
    }

    // ----------------------------------------------------------------

    public function to_draft($id_record)
    {
        // Get the Policy Record
        $record = is_numeric($id_record) ? $this->get( (int)$id_record ) : $id_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Policy_model][Method: to_draft()]: Policy record could not be found.");
        }

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $transaction_status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Update Endorsement Status
            $this->_do_status_transaction($record, IQB_POLICY_STATUS_DRAFT);

            // Txn Status to draft, Editable Object & Customer
            $endorsement_record = $this->endorsement_model->get_current_endorsement_by_policy($record->id);
            $this->endorsement_model->to_draft($endorsement_record);
            $this->object_model->update_lock($record->object_id, IQB_FLAG_UNLOCKED);
            $this->customer_model->update_lock($record->customer_id, IQB_FLAG_UNLOCKED);

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $transaction_status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;
        // Throw Exception on ERROR
        if( !$transaction_status )
        {
            throw new Exception("Exception [Model: Policy_model][Method: to_draft()]: Policy status and post-status update task could not be updated.");
        }

        // return result/status
        return $transaction_status;
    }

    // ----------------------------------------------------------------

        private function _do_status_transaction($record, $status)
        {
            $data = [
                'issued_date'   => $record->issued_date,
                'start_date'    => $record->start_date,
                'end_date'      => $record->end_date,
                'status'        => $status,
                'updated_by'    => $this->dx_auth->get_user_id(),
                'updated_at'    => $this->set_date()
            ];

            /**
             * Get status Specific Data. Eg. Verified by/at
             */
            $data = $this->_prep_status_data($status, $data);

            /**
             * Process Back Dates on Every Status Transaction
             *
             * This is required because you might have verified/vouchered yesterday and today you are invoicing
             */
            $data = $this->_backdate($data);

            return $this->_to_status($record->id, $data);
        }

        // ----------------------------------------------------------------

        private function _prep_status_data($status, $data)
        {
            switch($status)
            {
                /**
                 * to Draft
                 */
                case IQB_POLICY_STATUS_DRAFT:
                    $data['verified_at'] = NULL;
                    $data['verified_by'] = NULL;
                    break;


                /**
                 * to Verified
                 */
                case IQB_POLICY_STATUS_VERIFIED:
                    $data['verified_at'] = $this->set_date();
                    $data['verified_by'] = $this->dx_auth->get_user_id();
                    break;

                default:
                    break;
            }

            return $data;
        }

        // ----------------------------------------------------------------

        private function _to_status($id, $data)
        {
            return $this->db->where('id', $id)
                    ->update($this->table_name, $data);
        }

        // --------------------------------------------------------------------

        /**
         * Validate and process Back-date
         *
         * If user has supplied backdate, please make sure that :
         *      1. The user is allowed to enter Backdate
         *      2. If so, the supplied date should be withing backdate limit
         *
         * @param array     $data
         * @return array
         */
        private function _backdate($data)
        {
            $old_issued_date = $data['issued_date'];
            $old_start_date  = $data['start_date'];
            $old_end_date    = $data['end_date'];

            $new_issued_date    = backdate_process($old_issued_date);
            $new_start_date     = backdate_process($old_start_date);

            /**
             * If backdate is not allowed, we have to recompute the end date as per new start date
             */
            if( strtotime($old_start_date) != strtotime($new_start_date))
            {
                $days               = date_difference($old_start_date, $new_start_date, 'd');
                $new_end_date       = date('Y-m-d', strtotime($old_end_date. " + {$days} days"));
                $data['end_date']   = $new_end_date;

                /**
                 * Start/Issued Time Must be NOW
                 */
                $today_time = date('H:i:s');
                $data['issued_time']    = $today_time;
                $data['start_time']     = $today_time;
            }

            // Start and Issued Date
            $data['issued_date']    = $new_issued_date;
            $data['start_date']     = $new_start_date;

            return $data;
        }

    // ----------------------------------------------------------------

    /**
     * Generate Policy Number
     *
     * @param type $id_or_record
     * @return mixed
     */
    public function generate_policy_number($id_or_record)
    {
        $record = is_numeric($id_or_record) ? $this->get( (int)$id_or_record ) : $id_or_record;

        if(!$record)
        {
            throw new Exception("Exception [Model: Policy_model][Method: generate_policy_number()]: Policy record could not be found.");
        }

        /**
         * !!! NOTE: The Txn Record's RI Approval Constraint must met.
         *
         * So, the following tasks are carried out:
         *      1. Generate Policy Number
         *      2. Save Original Schedule PDF
         *
         * !!! NOTE: Both the tasks are done by calling the stored function.
         */
        $policy_type    = $record->ancestor_id ? IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL : IQB_POLICY_ENDORSEMENT_TYPE_FRESH;
        $params         = [$policy_type, $record->id, $this->dx_auth->get_user_id()];
        $sql            = "SELECT `f_generate_policy_number`(?, ?, ?) AS policy_code";
        $result         = mysqli_store_procedure('select', $sql, $params);

        /**
         * Save Original Schedule PDF
         */
        $result_row = $result[0];

        return $result_row->policy_code;
    }



    // -------------------- END: POLICY STATUS UPDATE METHODS --------------


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

            $status = $params['status'] ?? NULL;
            if( $status )
            {
                $this->db->where(['P.status' =>  $status]);
            }

            $branch_id = $params['branch_id'] ?? NULL;
            if( $branch_id )
            {
                $this->db->where(['P.branch_id' =>  $branch_id]);
            }

            $portfolio_id = $params['portfolio_id'] ?? NULL;
            if( $portfolio_id )
            {
                $this->db->where(['P.portfolio_id' =>  $portfolio_id]);
            }

            $issued_from = $params['issued_from'] ?? NULL;
            if( $issued_from )
            {
                $this->db->where(['P.issued_date >=' =>  $issued_from]);
            }

            $issued_to = $params['issued_to'] ?? NULL;
            if( $issued_to )
            {
                $this->db->where(['P.issued_date <=' =>  $issued_to]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $keywords = addslashes($keywords);
                $this->db->where("MATCH ( C.`fts` ) AGAINST ( '+{$keywords}' IN BOOLEAN MODE)", NULL);
            }
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

    // ----------------------------------------------------------------

    public function rows_by_customer($customer_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'policy_cst_'.$customer_id;
        $rows = $this->get_cache($cache_var);
        if(!$rows)
        {
            $rows = $this->_rows_by_customer($customer_id);

            if($rows)
            {
                $this->write_cache($rows, $cache_var, CACHE_DURATION_HR);
            }
        }
        return $rows;
    }

        /**
         * Get Rows from Database
         *
         * @param int $customer_id
         * @return array
         */
        private function _rows_by_customer($customer_id)
        {
            // Common Row Select
            $this->__row_select();

            return $this->db->where('P.customer_id', $customer_id)
                            ->order_by('P.id', 'DESC')
                            ->get()->result();
        }

    // ----------------------------------------------------------------


        private function __row_select($signle_select = FALSE)
        {

            // IF CALLED FROM row() function, we should also provide agent id and agent name
            // as it is required while editing the record
            $select = "P.*,
                        TIMESTAMP( P.`issued_date`, P.`issued_time` ) AS issued_datetime,
                        TIMESTAMP( P.`start_date`, P.`start_time` ) AS start_datetime,
                        TIMESTAMP( P.`end_date`, P.`end_time` ) AS end_datetime,
                        PRT.name_en as portfolio_name, C.full_name_en as customer_name_en, C.full_name_np as customer_name_np";
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
                $this->db->join('rel_agent__policy RAP', 'RAP.policy_id = P.id', 'left');
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
        $this->load->model('address_model');

        $this->db->select(

                        /**
                         * Policy Table (all fields, formatted datetime fields)
                         */
                        "P.*,
                        TIMESTAMP( P.`issued_date`, P.`issued_time` ) AS issued_datetime,
                        TIMESTAMP( P.`start_date`, P.`start_time` ) AS start_datetime,
                        TIMESTAMP( P.`end_date`, P.`end_time` ) AS end_datetime, " .


                        /**
                         * Branch Table
                         */
                        "B.name_en AS branch_name_en, B.name_np AS branch_name_np, B.code as branch_code, " .


                        /**
                         * Portfolio Table ( code, name )
                         */
                        "PRT.name_en AS portfolio_name_en, PRT.name_np AS portfolio_name_np, PRT.code AS portfolio_code, " .

                        /**
                         * Riks District, State, Region
                         */
                        "D.name_en as district_name, ST.name_en as state_name, R.name_en as region_name, " .

                        /**
                         * Object Table (attributes, sum insured amount, lock flag)
                         */
                        "O.portfolio_id AS object_portfolio_id, O.attributes AS object_attributes, O.amt_sum_insured AS object_amt_sum_insured, O.si_breakdown AS object_si_breakdown, O.flag_locked AS object_flag_locked, " .


                        /**
                         * Customer Table (code, name, type, pan, picture, pfrofession, contact,
                         * company reg no, citizenship no, passport no, lock flag)
                         */
                        "C.code as customer_code, C.full_name_en as customer_name_en, C.full_name_np as customer_name_np, C.grandfather_name as customer_grandfather_name, C.father_name as customer_father_name, C.mother_name as customer_mother_name, C.spouse_name as customer_spouse_name, C.type as customer_type, C.pan as customer_pan, C.picture as customer_picture, C.profession as customer_profession, C.company_reg_no, C.identification_no, C.dob, C.flag_locked AS customer_flag_locked, " .


                        /**
                         * Insurance Company Name (IF Policy is FAC-inward or CO-insurance)
                         */
                        "IC.name as insurance_company_name, ".


                        /**
                         * User Table - Sales Staff Info ( username, profile)
                         */
                        "SU.username as sold_by_username, SU.code AS sold_by_code, SU.profile as sold_by_profile, " .


                        /**
                         * User Table - Created By User Info (username, code, name)
                         */
                        "CU.username as created_by_username, CU.code as created_by_code, CU.profile as created_by_profile, " .

                        /**
                         * User Table - Verified By User Info (username, code, name)
                         */
                        "VU.username as verified_by_username, VU.code as verified_by_code, VU.profile as verified_by_profile, " .


                        /**
                         * Agent Table (agent_id, name, picture, bs code, ud code, active, type)
                         */
                        "A.id as agent_id, A.name as agent_name, A.picture as agent_picture, A.bs_code as agent_bs_code, A.ud_code as agent_ud_code, A.active as agent_active, A.type as agent_type"

                    )
                 ->from($this->table_name . ' as P')
                 ->join('master_branches B', 'B.id = P.branch_id')
                 ->join('master_portfolio PRT', 'PRT.id = P.portfolio_id')
                 ->join('dt_objects O', 'O.id = P.object_id')
                 ->join('dt_customers C', 'C.id = P.customer_id')
                 ->join('master_districts D', 'D.id = P.district_id')
                 ->join('master_states ST', 'ST.id = D.state_id')
                 ->join('master_regions R', 'R.id = D.region_id')
                 ->join('auth_users SU', 'SU.id = P.sold_by', 'left')
                 ->join('auth_users CU', 'CU.id = P.created_by')
                 ->join('auth_users VU', 'VU.id = P.verified_by', 'left')
                 ->join('rel_agent__policy RAP', 'RAP.policy_id = P.id', 'left')
                 ->join('master_agents A', 'RAP.agent_id = A.id', 'left')
                 ->join('master_companies IC', 'IC.id = P.insurance_company_id', 'left');

        /**
         * Branch Address
         */
        $table_aliases = [
            // Address Table Alias
            'address' => 'ADRB',

            // Country Table Alias
            'country' => 'CNTRYB',

            // State Table Alias
            'state' => 'STATEB',

            // Local Body Table Alias
            'local_body' => 'LCLBDB',

            // Type/Module Table Alias
            'module' => 'B'
        ];
        $this->address_model->module_select(IQB_ADDRESS_TYPE_BRANCH, NULL, $table_aliases, 'addr_branch_');


        /**
         * Customer Address
         */
        $table_aliases = [
            // Address Table Alias
            'address' => 'ADRC',

            // Country Table Alias
            'country' => 'CNTRYC',

            // State Table Alias
            'state' => 'STATEC',

            // Local Body Table Alias
            'local_body' => 'LCLBDC',

            // Type/Module Table Alias
            'module' => 'C'
        ];
        $this->address_model->module_select(IQB_ADDRESS_TYPE_CUSTOMER, NULL, $table_aliases, 'addr_customer_');


        $record = $this->db->where('P.id', $id)
                     ->get()->row();

         /**
          * Add Tags
          */
         if($record)
         {
            $this->load->model('rel_policy_tag_model');
            $record->tags = $this->rel_policy_tag_model->by_policy($record->id, TRUE);
         }

         return $record;
    }

    // ----------------------------------------------------------------

    /**
     * Get policy status
     *
     * @param int $id
     * @return mixed
     */
    public function get_status($id)
    {
         return $this->db->select( 'P.status')
                        ->from($this->table_name . ' as P')
                        ->where('P.id', $id)
                        ->get()->row()->status;
    }

    // ----------------------------------------------------------------

    /**
     * Get policy status
     *
     * @param int $id
     * @return mixed
     */
    public function get_customer_id($id)
    {
         return $this->db->select( 'P.customer_id')
                        ->from($this->table_name . ' as P')
                        ->where('P.id', $id)
                        ->get()->row()->customer_id;
    }

    // ----------------------------------------------------------------


    public function get_customer_object_id($id)
    {
         return $this->db->select( 'P.id, P.customer_id, P.object_id')
                        ->from($this->table_name . ' as P')
                        ->where('P.id', $id)
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Check if Policy exists by Supplied ID
     *
     * @param integer $id
     * @return mixed
     */
    public function exists($id)
    {
        return $this->db->where('id', $id)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    /**
     *
     * @param type $where
     * @return type
     */
    public function has_active_policy_by_customer($customer_id)
    {
        $where = [
            'customer_id'  => $customer_id,
            'status'       => IQB_POLICY_STATUS_ACTIVE
        ];
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------


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
                'policy_*'
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
            /**
             * Clear Cache
             * ---------------------
             */
            $customer_id = $record->customer_id;
            $this->clear_cache('policy_cst_'.$customer_id);
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}