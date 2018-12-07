<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Claim_model extends MY_Model
{
    protected $table_name = 'dt_claims';

    protected $set_created  = true;
    protected $set_modified = true;
    protected $log_user     = true;

    protected $before_insert = ['before_insert__defaults'];
    protected $before_update = ['before_update__defaults'];
    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $protected_attributes = ['id'];
    protected $fields = ['id', 'claim_code', 'policy_id', 'claim_scheme_id', 'fiscal_yr_id', 'fy_quarter', 'branch_id', 'category', 'accident_date', 'accident_time', 'accident_location', 'accident_details', 'loss_nature', 'loss_details_ip', 'loss_amount_ip', 'loss_details_tpp', 'loss_amount_tpp', 'death_injured', 'intimation_name', 'initimation_address', 'initimation_contact', 'intimation_date', 'estimated_claim_amount', 'assessment_brief', 'supporting_docs', 'other_info', 'gross_amt_surveyor_fee', 'vat_amt_surveyor_fee', 'net_amt_payable_insured', 'cl_comp_cession', 'cl_treaty_retaintion', 'cl_treaty_quota', 'cl_treaty_1st_surplus', 'cl_treaty_2nd_surplus', 'cl_treaty_3rd_surplus', 'cl_treaty_fac', 'flag_paid', 'flag_surveyor_voucher', 'settlement_date', 'file_intimation', 'status', 'status_remarks', 'progress_remarks', 'approved_at', 'approved_by', 'created_at', 'created_by', 'updated_at', 'updated_by'];


    /**
     * Claim RI-Distribuion Columns
     */
    protected static $claim_ri_fields = [
        'cl_comp_cession'       => 'Compulsory Cession',
        'cl_treaty_retaintion'  => 'Treaty Retention',
        'cl_treaty_quota'       => 'Treaty Quota',
        'cl_treaty_1st_surplus' => 'Treaty 1st Surplus',
        'cl_treaty_2nd_surplus' => 'Treaty 2nd Surplus',
        'cl_treaty_3rd_surplus' => 'Treaty 3rd Surplus',
        'cl_treaty_fac'         => 'Treaty FAC'
    ];


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

        // Helper
        $this->load->helper('claim');

        // Set validation rule
        $this->validation_rules();

        $this->load->model('claim_surveyor_model');
        $this->load->model('claim_settlement_model');
    }

    // ----------------------------------------------------------------

    /**
     * Set Validation Rules (Sectioned)
     *
     * @return void
     */
    public function validation_rules()
    {
       $this->validation_rules = [

            /**
             * Accident Details
             */
            'incident_details' => [
                [
                    'field' => 'category',
                    'label' => 'Settlement From',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list['.implode(',', array_keys(IQB_CLAIM_CATEGORIES)).']',
                    '_type'     => 'dropdown',
                    '_default'  => IQB_CLAIM_CATEGORY_REGULAR,
                    '_data'     => IQB_BLANK_SELECT + IQB_CLAIM_CATEGORIES,
                    '_required' => true
                ],
                [
                    'field' => 'accident_date_time',
                    'label' => 'Incident Date & Time',
                    'rules' => 'trim|required|valid_date',
                    '_type'             => 'datetime',
                    '_extra_attributes' => 'data-provide="datetimepicker-inline"',
                    '_required' => true
                ],
                [
                    'field' => 'accident_location',
                    'label' => 'Incident Location',
                    'rules' => 'trim|required|htmlspecialchars|max_length[200]',
                    '_type'     => 'textarea',
                    'rows'      => 4,
                    '_required' => true
                ],
                [
                    'field' => 'accident_details',
                    'label' => 'Incident Details',
                    'rules' => 'trim|required|htmlspecialchars',
                    '_type'     => 'textarea',
                    '_required' => true
                ],
                [
                    'field' => 'file_intimation',
                    '_key' => 'file_intimation',
                    'label' => 'Upload Intimation File',
                    'rules' => '',
                    '_type'     => 'file',
                    '_required' => false
                ],

            ],

            /**
             * Damage Details
             */
            'loss_details' => [
                [
                    'field' => 'loss_nature',
                    'label' => 'Nature of Loss',
                    'rules' => 'trim|required|htmlspecialchars',
                    '_type'     => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'loss_details_ip',
                    'label' => 'Damage Details (Insured Property)',
                    'rules' => 'trim|required|htmlspecialchars',
                    '_type'     => 'textarea',
                    'rows'  => 4,
                    '_required' => true
                ],
                [
                    'field' => 'loss_amount_ip',
                    'label' => 'Estimated Amount(Insured Property) (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type' => 'text',
                    '_id'   => 'loss_amount_ip',
                    '_required'     => true
                ],
                [
                    'field' => 'loss_details_tpp',
                    'label' => 'Damage Details (Third Party Property)',
                    'rules' => 'trim|htmlspecialchars',
                    '_type'     => 'textarea',
                    'rows'      => 4,
                    '_required' => false
                ],
                [
                    'field' => 'loss_amount_tpp',
                    'label' => 'Estimated Amount(Third Party Property) (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type' => 'text',
                    '_id'   => 'loss_amount_tpp',
                    '_required'     => true
                ],
            ],

            /**
             * Death Injured Details
             */
            'death_injured_details' => [
                [
                    'field' => 'death_injured[name][]',
                    '_key' => 'name',
                    'label' => 'Name',
                    'rules' => 'trim|htmlspecialchars|max_length[150]',
                    '_type' => 'text',
                    '_show_label'   => false,
                    '_required'     => false
                ],
                [
                    'field' => 'death_injured[type][]',
                    '_key' => 'type',
                    'label' => 'Type',
                    'rules' => 'trim|alpha|exact_length[3]',
                    '_type' => 'dropdown',
                    '_data'         => CLAIM__death_injured_type_dropdown(),
                    '_show_label'   => false,
                    '_required'     => false
                ],
                [
                    'field' => 'death_injured[address][]',
                    '_key' => 'address',
                    'label' => 'Address',
                    'rules' => 'trim|htmlspecialchars|max_length[250]',
                    '_type' => 'textarea',
                    'rows'   => 4,
                    '_show_label'   => false,
                    '_required'     => false
                ],
                [
                    'field' => 'death_injured[details][]',
                    '_key' => 'details',
                    'label' => 'Details',
                    'rules' => 'trim|htmlspecialchars|max_length[500]',
                    '_type' => 'textarea',
                    'rows'   => 4,
                    '_show_label'   => false,
                    '_required'     => false
                ],
                [
                    'field' => 'death_injured[hospital][]',
                    '_key' => 'hospital',
                    'label' => 'Hospital',
                    'rules' => 'trim|htmlspecialchars|max_length[200]',
                    '_type' => 'text',
                    '_show_label'   => false,
                    '_required'     => false
                ],
            ],

            /**
             * Intimation Lodger Information
             */
            'intimation_details' => [
                [
                    'field' => 'intimation_name',
                    'label' => 'Contact Name',
                    'rules' => 'trim|required|htmlspecialchars|max_length[150]',
                    '_type' => 'text',
                    '_required'     => true
                ],
                [
                    'field' => 'initimation_address',
                    'label' => 'Contact Address',
                    'rules' => 'trim|required|htmlspecialchars|max_length[150]',
                    '_type' => 'textarea',
                    'rows'  => 4,
                    '_required'     => true
                ],
                [
                    'field' => 'initimation_contact',
                    'label' => 'Contact Phone/Mobile',
                    'rules' => 'trim|required|htmlspecialchars|max_length[40]',
                    '_type' => 'text',
                    '_required'     => true
                ],
                [
                    'field' => 'intimation_date',
                    'label' => 'Intimation Date',
                    'rules' => 'trim|required|valid_date',
                    '_type'             => 'date',
                    '_extra_attributes' => 'data-provide="datepicker-inline"',
                    '_required' => true
                ],
            ],

            /**
             * Claim Estimation
             */
            'claim_estimation' => [
                [
                    'field' => 'estimated_claim_amount',
                    'label' => 'Estimated Claim Amount (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type' => 'text',
                    '_id'   => 'estimated_claim_amount',
                    '_default'  => 0.00,
                    '_required' => true
                ]
            ]

       ];
    }

    // ----------------------------------------------------------------

    /**
     * Draft Validation Rules
     *
     * @param bool $formatted
     * @return array
     */
    public function draft_v_rules($formatted = FALSE )
    {
        $sections = ['incident_details', 'loss_details', 'death_injured_details', 'intimation_details', 'claim_estimation'];
        $rules = [];
        foreach($sections as $section)
        {
            $rules[$section] = $this->validation_rules[$section];
        }

        if($formatted)
        {
            $v_rules = [];
            foreach($rules as $section=>$r)
            {
                $v_rules = array_merge($v_rules, $r);
            }
            return $v_rules;
        }

        return $rules;
    }

    // ----------------------------------------------------------------

    /**
     * Claim Progress Validation Rules
     *
     * @param bool $formatted
     * @return array
     */
    public function progress_v_rules( )
    {
        return [
            [
                'field' => 'progress_remarks',
                'label' => 'Progress Remarks',
                'rules' => 'trim|required|htmlspecialchars|max_length[5000]',
                '_type' => 'textarea',
                '_required' => true
            ]
        ];
    }



    // ----------------------------------------------------------------

    /**
     * Claim Beema Samiti Report Heading Validation Rules
     *
     * @param bool $formatted
     * @return array
     */
    public function bs_tags_v_rules( )
    {
        return  [
            [
                'field' => 'bsrs_heading_id[]',
                'label' => 'BS Reporting Heading',
                'rules' => 'trim|integer|max_length[8]',
                '_key'      => 'bsrs_heading_id',
                '_type'     => 'dropdown',
                '_data'     => [],
                '_class'     => 'form-control select-multiple',
                '_extra_attributes' => 'multiple="multiple" style="width:100%" data-placeholder="Select Expertise..."',
            ],
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Claim Close Validation Rules
     *
     * @param bool $formatted
     * @return array
     */
    public function close_v_rules($formatted = TRUE )
    {
        return [
            [
                'field' => 'status_remarks',
                'label' => 'Reason for Claim Close',
                'rules' => 'trim|required|htmlspecialchars|max_length[5000]',
                '_type' => 'textarea',
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Claim Withdraw Validation Rules
     *
     * @param bool $formatted
     * @return array
     */
    public function withdraw_v_rules($formatted = TRUE )
    {
        return [
            [
                'field' => 'status_remarks',
                'label' => 'Reason for Claim Withdraw',
                'rules' => 'trim|required|htmlspecialchars|max_length[5000]',
                '_type' => 'textarea',
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Claim Assessment Validation Rules
     *
     * @param bool $formatted
     * @return array
     */
    public function assessment_v_rules($portfolio_id, $formatted = TRUE )
    {
        $this->load->model('portfolio_model');
        $docs_dropdown = $this->portfolio_model->dropdown_claim_docs($portfolio_id);

        return [
            [
                'field' => 'assessment_brief',
                'label' => 'Assessment Brief (Report)',
                'rules' => 'trim|required|htmlspecialchars|max_length[30000]',
                '_type' => 'textarea',
                '_required' => true
            ],
            [
                'field' => 'other_info',
                'label' => 'Other Info',
                'rules' => 'trim|htmlspecialchars|max_length[20000]',
                '_type' => 'textarea',
                '_required' => false
            ],
            [
                'field' => 'supporting_docs[]',
                'label' => 'Supporting Docs',
                'rules' => 'trim|required|alpha|max_length[20]',
                '_type' => 'checkbox-group',
                '_checkbox_value'   => [],
                '_data'             => $docs_dropdown,
                '_list_inline'      => FALSE,
                '_required'         => true,
            ],
            [
                'field' => 'status_remarks',
                'label' => 'Approval Remarks',
                'rules' => 'trim|required|htmlspecialchars|max_length[5000]',
                '_type' => 'textarea',
                '_default' => 'The mentioned loss has occurred during the period of insurance and covered under the insurance policy. The loss has been found to be reasonable and in order. Hence, it is recommended for settlement.',
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Claim Scheme Validation Rules
     *
     * @param bool $formatted
     * @return array
     */
    public function scheme_v_rules($formatted = TRUE )
    {
        // Scheme Model
        $this->load->model('claim_scheme_model');
        $dropdown = $this->claim_scheme_model->dropdown();

        return [
            [
                'field' => 'claim_scheme_id',
                'label' => 'Claim Scheme',
                'rules' => 'trim|required|integer|in_list['.implode(',', array_keys($dropdown)).']',
                '_type' => 'dropdown',
                '_data' => IQB_BLANK_SELECT + $dropdown,
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Check Duplicate
     *
     * @param array $where
     * @param int|null $id
     * @return mixed
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
     * Check if a policy has any claim
     *
     * @param int $policy_id
     * @return mixed
     */
    public function has_policy_claim($policy_id)
    {
        return $this->check_duplicate(['policy_id' => $policy_id]);
    }

    // ----------------------------------------------------------------

    /**
     * Add Claim Draft
     *
     * @param array $data
     * @return mixed
     */
    public function add_draft( $data )
    {
        // Disable DB Debug for transaction to work
        $id  = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Insert Claim Data
            $id = parent::insert($data, TRUE);

            // Task b. Insert Broker Relations
            if($id)
            {
                // Clean Cache by this Policy
                $this->clear_cache( 'claim_list_by_policy_' . $data['policy_id'] );
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $id = FALSE;
        }

        // return result/status
        return $id;
    }

    // --------------------------------------------------------------------

    /**
     * Before Insert Trigger
     *
     * Tasks carried
     *      1. Add Random Claim Number
     *      2. Add Fiscal Year ID
     *      3. Add Branch ID
     *      4. Add Status
     *      5. Build JSON Data - Death Injured
     *      6. Refactor Date & Time
     *
     * @param array $data
     * @return array
     */
    public function before_insert__defaults($data)
    {
        $this->load->library('Token');

        /**
         * Build Draft Data
         */
        $draft_data = $this->__build_draft_data($data);

        /**
         * POLICY ID
         */
        $draft_data['policy_id'] = $data['policy_id'];

        /**
         * Claim DRAFT
         *
         * Format: DRAFT-<RANDOMCHARS>
         */
        $draft_data['claim_code'] = 'DRAFT-' . $this->token->generate(10);


        // Fiscal Year ID
        $draft_data['fiscal_yr_id'] = $this->current_fiscal_year->id;

        // Fiscal Year Quarter
        $draft_data['fy_quarter'] = $this->current_fy_quarter->quarter;

        // Branch ID
        $draft_data['branch_id']      = $this->dx_auth->get_branch_id();

        // Status
        $draft_data['status'] = IQB_CLAIM_STATUS_DRAFT;

        // Creator Info
        $draft_data['created_at'] = $data['created_at'];
        $draft_data['created_by'] = $data['created_by'];

        return $draft_data;
    }

    // ----------------------------------------------------------------

    /**
     * Edit Claim Draft
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function edit_draft( $id, $data )
    {
        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Insert Claim Data
            $done = parent::update($id, $data, TRUE);

            // Task b. Insert Broker Relations
            if($done)
            {
                // Clean Cache by this Policy
                $this->clear_cache( 'claim_list_by_policy_' . $data['policy_id'] );
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $done = FALSE;
        }

        // return result/status
        return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Before Update Trigger
     *
     *  Tasks Carried:
     *      1. Build JSON Data - Death Injured
     *      2. Refactor Date & Time
     *
     *
     * @param array $data
     * @return array
     */
    public function before_update__defaults($data)
    {
        /**
         * Build Draft Data
         */
        $draft_data = $this->__build_draft_data($data);

        // Creator Info
        $draft_data['updated_at'] = $data['updated_at'];
        $draft_data['updated_by'] = $data['updated_by'];

        return $draft_data;
    }



    // ----------------------------------------------------------------

    private function __build_draft_data($data)
    {
        $columns = ['category', 'accident_location', 'accident_details', 'loss_nature', 'loss_details_ip', 'loss_amount_ip', 'loss_details_tpp', 'loss_amount_tpp', 'death_injured', 'intimation_name', 'initimation_address', 'initimation_contact', 'intimation_date', 'estimated_claim_amount', 'file_intimation'];

        $draft_data = [];
        foreach($columns as $key)
        {
            $draft_data[$key] = $data[$key];
        }

        return array_merge(
            /**
             * Datetime Columns
             */
            $this->__refactor_datetime_fields($data),

            /**
             * Other columns that need no modification
             */
            $draft_data,

            /**
             * Deathinjured json data
             */
            $this->__build_death_injured_data($data['death_injured'])
        );
    }

    // ----------------------------------------------------------------

        private function __refactor_datetime_fields($data)
        {
            return [
                'accident_date' => date('Y-m-d', strtotime($data['accident_date_time'])),
                'accident_time' => date('H:i:00', strtotime($data['accident_date_time']))
            ];
        }

    // ----------------------------------------------------------------

        private function __build_death_injured_data($data)
        {
            $count = count($data['name']);
            $records = [];
            $fields = ['name', 'type', 'address', 'details', 'hospital'];
            if( $count )
            {
                for($i=0; $i< $count; $i++ )
                {
                    $single = [];
                    foreach($fields as $field)
                    {
                        $single[$field] = $data[$field][$i];
                    }

                    // Check if all values blank, we dont save it
                    $values = array_filter( array_values($single) );

                    if($values)
                    {
                        $records[] = $single;
                    }
                }
            }

            $death_injured = NULL;
            if($records)
            {
                $death_injured = json_encode($records);
            }

            return ['death_injured' => $death_injured];
        }


    // ----------------------------------------------------------------

    /**
     * Update Claim data on various occassions.
     * 1. Status
     * 2. Claim settlement amount/breakdown
     * 3. Claim Scheme etc
     */
    public function update_data($id, $data, $policy_id = NULL)
    {
        // Updated by/at
        $data = $this->modified_on(['fields' => $data]);

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Update Data
            $done = $this->db->where('id', $id)
                        ->update($this->table_name, $data);

            // Task b. Insert Broker Relations
            if($done)
            {
                // Clean Cache by policy belonging to this policy
                if( !$policy_id )
                {
                    $policy_id = $this->policy_id($id);
                }
                $this->clear_cache( 'claim_list_by_policy_' . $policy_id );
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
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

    /**
     * Update Claim data on various occassions.
     * 1. Status
     * 2. Claim settlement amount/breakdown
     * 3. Claim Scheme etc
     */
    public function verify( $record )
    {
        $data = [
            'status' => IQB_CLAIM_STATUS_VERIFIED
        ];

        // Updated by/at
        $data = $this->modified_on(['fields' => $data]);

        // Disable DB Debug for transaction to work
        $this->db->db_debug = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Update Status
            $done = $this->db->where('id', $record->id)
                        ->update($this->table_name, $data);

            // Task b: Generate Claim Code
            $code_prefix = strtoupper(substr($record->claim_code, 0, 5));
            if( $code_prefix === 'DRAFT')
            {
                $this->generate_claim_number($record->id);
            }

            // Task b. Insert Broker Relations
            if($done)
            {
                // Clean Cache by policy belonging to this policy
                $this->clear_cache( 'claim_list_by_policy_' . $record->policy_id );
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
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

    /**
     * Approve a Claim
     *
     * While approving a claim, we also update the Claim Recovery - RI Breakdown
     *
     * @param object $record
     * @return bool
     */
    public function approve( $record )
    {
        $record = is_numeric($record) ? $this->get( (int)$record ) : $record;
        if(!$record)
        {
            throw new Exception("Exception [Model: Claim_model][Method: approve()]: Claim information could not be found.");
        }

        $data = [
            'status'                    => IQB_CLAIM_STATUS_APPROVED,
            'approved_at'               => $this->set_date(),
            'approved_by'               => $this->dx_auth->get_user_id()
        ];

        /**
         * Get Claim Recovery - Breakdown Data
         */
        $ri_breakdown_data = $this->_build_claim_ri_breakdown($record);
        $data = array_merge($data, $ri_breakdown_data);

        /**
         * Let's Update the Data
         */
        return $this->update_data($record->id, $data, $record->policy_id);
    }

    // ----------------------------------------------------------------

    /**
     * Close a Claim
     *
     * While closing a claim, we also update the Claim Recovery - RI Breakdown - If surveyors assigned
     *
     * @param object $record
     * @return bool
     */
    public function close( $record, $data )
    {
        $record = is_numeric($record) ? $this->get( (int)$record ) : $record;
        if(!$record)
        {
            throw new Exception("Exception [Model: Claim_model][Method: close()]: Claim information could not be found.");
        }

        /**
         * Get Claim Recovery - Breakdown Data and Merge with supplied data
         *
         * !!! NOTE !!!
         * Must have surveyor assigned
         */
        if( $this->claim_surveyor_model->has_surveyors($record->id) )
        {
            $ri_breakdown_data = $this->_build_claim_ri_breakdown($record, TRUE);
            $data = array_merge($data, $ri_breakdown_data);
        }


        /**
         * Let's Update the Data
         */
        return $this->update_data($record->id, $data, $record->policy_id);
    }

    // ----------------------------------------------------------------

    /**
     * Withdraw a Claim
     *
     * While withdrawing a claim, we also update the Claim Recovery - RI Breakdown - If surveyors assigned
     *
     * @param object $record
     * @return bool
     */
    public function withdraw( $record, $data )
    {
        $record = is_numeric($record) ? $this->get( (int)$record ) : $record;
        if(!$record)
        {
            throw new Exception("Exception [Model: Claim_model][Method: withdraw()]: Claim information could not be found.");
        }

        /**
         * Get Claim Recovery - Breakdown Data and Merge with supplied data
         *
         * !!! NOTE !!!
         * Must have surveyor assigned
         */
        if( $this->claim_surveyor_model->has_surveyors($record->id) )
        {
            $ri_breakdown_data = $this->_build_claim_ri_breakdown($record, TRUE);
            $data = array_merge($data, $ri_breakdown_data);
        }


        /**
         * Let's Update the Data
         */
        return $this->update_data($record->id, $data, $record->policy_id);
    }

    // ----------------------------------------------------------------

    /**
     * Settle Claim
     *
     * This function will update ri-claim data and status to "Settled" for the supplied claim.
     *
     * @param object $record
     * @return bool
     */
    public function settle( $record )
    {
        $record = is_numeric($record) ? $this->get( (int)$record ) : $record;
        if(!$record)
        {
            throw new Exception("Exception [Model: Claim_model][Method: settle()]: Claim information could not be found.");
        }

        $settlement_date = date('Y-m-d');

        $fy_record  = $this->fiscal_year_model->get_fiscal_year($settlement_date);
        if(!$fy_record)
        {
            throw new Exception("Exception [Model: Claim_model][Method: settle()]: Fiscal Year not found for supplied settlement date ({$settlement_date}).");
        }

        $fy_quarter = $this->fy_quarter_model->get_quarter_by_date($settlement_date);
        if(!$fy_quarter)
        {
            throw new Exception("Exception [Model: Claim_model][Method: settle()]: Fiscal Year Quarter not found for supplied settlement date ({$settlement_date}).");
        }


        /**
         * Build Claim Master Record
         *  - status, fiscal year id, fiscal year quarter, settlement date
         *  - claim ri breakdown
         */
        // Update Claim RI-Breakdown
        $data = $this->_build_claim_ri_breakdown($record);
        $data = array_merge($data, [
            'settlement_date'   => $settlement_date,
            'fiscal_yr_id'      => $fy_record->id,
            'fy_quarter'        => $fy_quarter->quarter,
            'status'            => IQB_CLAIM_STATUS_SETTLED,
        ]);


        /**
         * Let's Update the Data
         */
        return $this->update_data($record->id, $data, $record->policy_id);
    }

    // ----------------------------------------------------------------

    /**
     * Generate Claim Voucher
     *
     * @param object $record
     * @param bool $surveyor_fee_only If surveyor only claim voucher has to be generated
     * @return mixed
     */
    public function voucher($record, $surveyor_fee_only = FALSE )
    {
        /**
         * Account Helper
         */
        $this->load->helper('account');

        // Models
        $this->load->model('portfolio_model');

        // Get the list of surveyor
        $surveyors = $this->claim_surveyor_model->get_many_by_claim($record->id);

        // List of accounts for the portfolio
        $portfolio = $this->portfolio_model->find($record->portfolio_id);
        if( !$portfolio->account_id_ce || !$portfolio->account_id_cr )
        {
            throw new Exception("Exception [Model: Claim_model][Method: voucher()]: Default internal accounts(Claim Expense, Claim Receivable) for this portfolio not found. Please contact Administrator for this.");
        }

        /**
         * --------------------------------------------------------------
         * Account                              |   DR      |   CR      |
         * --------------------------------------------------------------
         * Claim Expense (Portfolio-wise)       |   *       |           |
         * Vat on Surveyor Fee (multiple)       |   *       |           |
         * --------------------------------------------------------------                                                             |
         * Surveyor Party (multiple)            |           |   *       |
         * TDS Payable (multiple)               |           |   *       |
         * Claim Party                          |           |   *       |
         * --------------------------------------------------------------
         */

        $narration = "Being Claim Booked of Claim No. ({$record->claim_code}) of Policy No.({$record->policy_code}).";
        $voucher_data = [
            // Master Table Data
            'master' => [
                'voucher_date'      => date('Y-m-d'),
                'voucher_type_id'   => IQB_AC_VOUCHER_TYPE_JRNL,
                'narration'         => $narration,
                'flag_internal'     => IQB_FLAG_ON
            ]
        ];

        // --------------------------------------------------------------------

        $cr_rows = [];


        /**
         * DEBIT SECTION
         */
        $dr_rows = [
            // Claim Expense (Portfolio-wise) = Claim GROSS TOTAL
            [
                'account_id' => $portfolio->account_id_ce,
                'party_type' => NULL,
                'party_id'   => NULL,
                'amount'     => $this->compute_claim_gross_total($record, $surveyor_fee_only)
            ],
        ];

        /**
         * Surveyor VATs (DR), Surveyor Party(CR), TDS Payable(CR)
         */
        foreach($surveyors as $single)
        {
            if($single->vat_amount)
            {
                $dr_rows[] = [
                    'account_id' => IQB_AC_ACCOUNT_ID_VAT_PAYABLE,
                    'party_type' => IQB_AC_PARTY_TYPE_SURVEYOR,
                    'party_id'   => $single->surveyor_id,
                    'amount'     => $single->vat_amount
                ];
            }

            /**
             * CREDIT ROWS: TDS and Surveyor Party
             */

            // Surveyor Party
            $cr_rows[] = [
                'account_id' => IQB_AC_ACCOUNT_ID_SURVEYOR_PARTY,
                'party_type' => IQB_AC_PARTY_TYPE_SURVEYOR,
                'party_id'   => $single->surveyor_id,
                'amount'     => $this->claim_surveyor_model->compute_net_total_fee($single)
            ];

            // TDS Payable
            $cr_rows[] = [
                'account_id' => IQB_AC_ACCOUNT_ID_TDS_SURVEYOR,
                'party_type' => IQB_AC_PARTY_TYPE_SURVEYOR,
                'party_id'   => $single->surveyor_id,
                'amount'     => $single->tds_amount
            ];
        }

        // --------------------------------------------------------------------

        /**
         * CREDIT SECTION
         */
        if( !$surveyor_fee_only )
        {
            // Claim Party
            $cr_rows[] = [
                'account_id' => IQB_AC_ACCOUNT_ID_CLAIM_PARTY,
                'party_type' => IQB_AC_PARTY_TYPE_CUSTOMER,
                'party_id'   => $record->customer_id,
                'amount'     => (float)$record->net_amt_payable_insured
            ];
        }

        // --------------------------------------------------------------------

        /**
         * DR === CR
         */
        $voucher_rows = ac_equate_dr_cr_rows($dr_rows, $cr_rows);

        // Add Voucher Rows on Voucher Data
        $voucher_data = array_merge($voucher_data, $voucher_rows);

        // --------------------------------------------------------------------


        /**
         * Save Voucher and Its Relation with Policy and return Voucher ID
         */
        return $this->ac_voucher_model->add($voucher_data, $record->policy_id);
    }

        private function _build_claim_ri_breakdown($record, $surveyor_fee_only = FALSE)
        {
            $this->load->model('ri_transaction_model');

            /**
             * We have to breakdwon total Claim into
             *      1. Amount paid by the Insurance company
             *      2. Rest Amount paid by Re-insurer
             */
            $total_claim_amount = $this->compute_claim_net_total($record->id, $surveyor_fee_only);

            /**
             * Current RI Transaction State
             *
             *  Compute the sum of all ri transactions of this policy
             */
            $ri_distribution    = $this->ri_transaction_model->latest_build_by_policy($record->policy_id);
            $si_gross           = $ri_distribution->si_gross;

            $ratio = bcdiv($total_claim_amount, $si_gross, IQB_AC_DECIMAL_PRECISION);

            $data               = [];
            $ri_claim_columns   = array_keys(self::$claim_ri_fields);

            foreach($ri_claim_columns as $cl_col)
            {
                // get RI distribution column name
                // cl_ prefix is replaced by si_
                $ri_col = substr_replace($cl_col, 'si', 0, 2); // replace 'cl' by 'si'

                // eg: cl_comp_cession  = bcmul( (float)$ri_distribution->si_comp_cession, $ratio, IQB_AC_DECIMAL_PRECISION )
                $data[$cl_col] = bcmul( (float)$ri_distribution->{$ri_col}, $ratio, IQB_AC_DECIMAL_PRECISION );
            }

            return $data;
        }

    // ----------------------------------------------------------------

    /**
     * Gross Total Claim Amount
     *
     *  Gross Total = Payable Insured Party + Gross Total of Surveyor
     */
    public function compute_claim_gross_total($id, $surveyor_fee_only = FALSE)
    {
        $surveyor_gross_total   = $this->claim_surveyor_model->compute_gross_total_fee_by_claim($id);
        if($surveyor_fee_only)
        {
            return $surveyor_gross_total;
        }

        $net_payable            = $this->claim_settlement_model->compute_net_payable($id);

        return bcadd($net_payable, $surveyor_gross_total, IQB_AC_DECIMAL_PRECISION);
    }

    // ----------------------------------------------------------------

    /**
     * Net Total Claim Amount
     *
     *  Net Total = Payable Insured Party + Gross Total of Surveyor + VAT of Surveyors
     */
    public function compute_claim_net_total($id, $surveyor_fee_only = FALSE)
    {
        $gross_total = $this->compute_claim_gross_total($id, $surveyor_fee_only);
        $vat_total = $this->claim_surveyor_model->compute_vat_total_by_claim($id);

        return bcadd($gross_total, $vat_total, IQB_AC_DECIMAL_PRECISION);
    }


    // ----------------------------------------------------------------

    /**
     * Get Claim RI Breakdown Data
     *
     * @param Object/Int $record Claim ID or Claim Record
     * @return array
     */
    public function ri_breakdown($record, $for_display = FALSE)
    {
        $record = is_numeric($record) ? $this->get( (int)$record ) : $record;
        if(!$record)
        {
            throw new Exception("Exception [Model: Claim_model][Method: ri_breakdown()]: Claim information could not be found.");
        }

        $data = [];

        /**
         * If status is below Approval, we need to generate runtime
         * else it will be saved on database
         */
        if( $record->status == IQB_CLAIM_STATUS_VERIFIED )
        {
            $data = $this->_build_claim_ri_breakdown($record);
        }
        else
        {
            foreach(self::$claim_ri_fields as $col => $label)
            {
                $data[$col] = $record->{$col};
            }
        }

        if( !$for_display )
        {
            return $data;
        }


        // Format for Display
        $display_data = [];
        foreach(self::$claim_ri_fields as $col => $label)
        {
            $display_data[$label] = $data[$col] ?? NULL;
        }
        return $display_data;
    }
    // ----------------------------------------------------------------


    /**
     * Generate Claim Number
     *
     * @param type $id
     * @return mixed
     */
    public function generate_claim_number($id)
    {
        $params         = [$id, $this->dx_auth->get_user_id()];
        $sql            = "SELECT `f_generate_claim_number`(?, ?) AS claim_code;";
        $result         = mysqli_store_procedure('select', $sql, $params);

        /**
         * Get the result
         */
        $result_row = $result[0];

        return $result_row->claim_code;
    }

    // ----------------------------------------------------------------


    public function row($id)
    {
        return $this->get($id);
    }

    // ----------------------------------------------------------------

    public function policy_id($id)
    {
        return $this->db->select('CLM.policy_id')
                        ->from($this->table_name . ' CLM')
                        ->where('CLM.id', $id)
                        ->get()->row()->policy_id;
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

        /**
         * Apply Filter
         */
        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['CLM.id <=' => $next_id]);
            }

            $policy_code = $params['policy_code'] ?? NULL;
            if( $policy_code )
            {
                $this->db->where(['P.code' =>  $policy_code]);
            }

            $claim_code = $params['claim_code'] ?? NULL;
            if( $claim_code )
            {
                $this->db->where(['CLM.claim_code' =>  $claim_code]);
            }
        }

        return $this->db->limit($this->settings->per_page+1)
                        ->order_by('CLM.id', 'desc')
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select(
                            // Claim Table Data
                            'CLM.*, ' .

                            // Claim Scheme Name
                            'CLMSCM.name AS claim_scheme_name, ' .

                            // Policy Table Data
                            'P.code as policy_code, P.portfolio_id, ' .

                            // Fiscal Year Data
                            'F.code_np AS fy_code_np, F.code_en AS fy_code_en'
                        )
                ->from($this->table_name . ' AS CLM')
                ->join('dt_policies P', 'P.id = CLM.policy_id')
                ->join('master_claim_schemes CLMSCM', 'CLMSCM.id = CLM.claim_scheme_id', 'left')
                ->join('master_fiscal_yrs F', 'F.id = CLM.fiscal_yr_id');
    }

    // ----------------------------------------------------------------

    /**
     * Get Details of a Single Record
     *
     * @param integer $id
     * @return object
     */
    public function get($id)
    {
        return $this->db->select(
                        // Claim Table Data
                        'CLM.*, ' .

                        // Claim Scheme Name
                        'CLMSCM.name AS claim_scheme_name, ' .

                        // Policy Table Data
                        'P.code as policy_code, P.portfolio_id, P.customer_id, ' .

                        // Fiscal Year Data
                        'F.code_np AS fy_code_np, F.code_en AS fy_code_en'
                    )
                    ->from($this->table_name . ' AS CLM')
                    ->join('dt_policies P', 'P.id = CLM.policy_id')
                    ->join('master_claim_schemes CLMSCM', 'CLMSCM.id = CLM.claim_scheme_id', 'left')
                    ->join('master_fiscal_yrs F', 'F.id = CLM.fiscal_yr_id')
                    ->where('CLM.id', $id)
                    ->get()->row();
    }

    // ----------------------------------------------------------------

    public function rows_by_policy($policy_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'claim_list_by_policy_'.$policy_id;
        $rows = $this->get_cache($cache_var);
        if(!$rows)
        {
            $rows = $this->_rows_by_policy($policy_id);

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
         * @param int $policy_id
         * @return array
         */
        private function _rows_by_policy($policy_id)
        {
            // Common Row Select
            $this->_row_select();

            // Policy Related JOIN
            return $this->db->where('P.id', $policy_id)
                        ->order_by('CLM.id', 'DESC')
                        ->get()
                        ->result();
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
                'claim_list_by_policy_*'
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
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        $record = $this->get($id);

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
            // Clear Cache
            $this->clear_cache( 'claim_list_by_policy_' . $record->policy_id );
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}
