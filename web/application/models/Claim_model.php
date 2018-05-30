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
    protected $fields = ['id', 'claim_code', 'policy_id', 'claim_scheme_id', 'fiscal_yr_id', 'fy_quarter', 'branch_id', 'accident_date', 'accident_time', 'accident_location', 'accident_details', 'loss_nature', 'loss_details_ip', 'loss_amount_ip', 'loss_details_tpp', 'loss_amount_tpp', 'death_injured', 'intimation_name', 'initimation_address', 'initimation_contact', 'intimation_date', 'estimated_claim_amount', 'assessment_brief', 'supporting_docs', 'other_info', 'total_surveyor_fee_amount', 'settlement_claim_amount', 'cl_comp_cession', 'cl_treaty_retaintion', 'cl_treaty_quota', 'cl_treaty_1st_surplus', 'cl_treaty_2nd_surplus', 'cl_treaty_3rd_surplus', 'cl_treaty_fac', 'flag_paid', 'flag_surveyor_voucher', 'settlement_date', 'status', 'status_remarks', 'progress_remarks', 'approved_at', 'approved_by', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
            'accident_details' => [
                [
                    'field' => 'accident_date_time',
                    'label' => 'Accident Date & Time',
                    'rules' => 'trim|required|valid_date',
                    '_type'             => 'datetime',
                    '_extra_attributes' => 'data-provide="datetimepicker-inline"',
                    '_required' => true
                ],
                [
                    'field' => 'accident_location',
                    'label' => 'Accident Location',
                    'rules' => 'trim|required|htmlspecialchars|max_length[200]',
                    '_type'     => 'textarea',
                    'rows'      => 4,
                    '_required' => true
                ],
                [
                    'field' => 'accident_details',
                    'label' => 'Accident Details',
                    'rules' => 'trim|required|htmlspecialchars',
                    '_type'     => 'textarea',
                    '_required' => true
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
                    'rules' => 'trim|alpha|exact_length[1]',
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
                    'label' => 'Name',
                    'rules' => 'trim|required|htmlspecialchars|max_length[150]',
                    '_type' => 'text',
                    '_required'     => true
                ],
                [
                    'field' => 'initimation_address',
                    'label' => 'Address',
                    'rules' => 'trim|required|htmlspecialchars|max_length[150]',
                    '_type' => 'textarea',
                    'rows'  => 4,
                    '_required'     => true
                ],
                [
                    'field' => 'initimation_contact',
                    'label' => 'Contact No.',
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
                    '_default'  => 0.00,
                    '_required' => true
                ]
            ],

            /**
             * Claim Assessment
             */
            'claim_assessment' => [
                [
                    'field' => 'assessment_brief',
                    'label' => 'Assessment Brief',
                    'rules' => 'trim|required|htmlspecialchars|max_length[30000]',
                    '_type' => 'textarea',
                    '_help_text' => "Brief details of Surveyor's/Doctor's/Investigator's/Department's report and assessment.",
                    '_required' => true
                ],
                [
                    'field' => 'other_info',
                    'label' => 'Other Information',
                    'rules' => 'trim|required|htmlspecialchars|max_length[5000]',
                    '_type' => 'textarea',
                    '_required' => true
                ],
                [
                    'field' => 'supporting_docs[]',
                    'label' => 'Supporting Documents',
                    'rules' => 'trim|required|alpha|max_length[2]',
                    '_type' => 'checkbox-group',
                    '_data' => CLAIM__supporting_docs_dropdown(FALSE),
                    '_required' => true
                ]
            ],


            /**
             * Claim Settlement Amount Breakdown
             */
            'claim_settlement_breakdown' => [
                [
                    'field' => 'csb[title][]',
                    'label' => 'Title',
                    'rules' => 'trim|required|htmlspecialchars|max_length[150]',
                    '_type' => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'csb[amt_claimed][]',
                    'label' => 'Claimed Amount (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type' => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'csb[amt_assessed][]',
                    'label' => 'Assessed Amount (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type' => 'text',
                    '_required' => true
                ],
                [
                    'field' => 'csb[amt_recommended][]',
                    'label' => 'Recommended Amount (Rs.)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                    '_type' => 'text',
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
        $sections = ['accident_details', 'loss_details', 'death_injured_details', 'intimation_details', 'claim_estimation'];
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
    public function assessment_v_rules($formatted = TRUE )
    {
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
                'rules' => 'trim|required|alpha|max_length[2]',
                '_type' => 'checkbox-group',
                '_checkbox_value'   => [],
                '_data'             => CLAIM__supporting_docs_dropdown(FALSE),
                '_list_inline'      => FALSE,
                '_required'         => true,

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
        $this->db->db_debug = TRUE;
        $id                 = FALSE;

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

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

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
        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;

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

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

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
        $columns = ['accident_location', 'accident_details', 'loss_nature', 'loss_details_ip', 'loss_amount_ip', 'loss_details_tpp', 'loss_amount_tpp', 'death_injured', 'intimation_name', 'initimation_address', 'initimation_contact', 'intimation_date', 'estimated_claim_amount'];

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
     * @param object $record
     * @return bool
     */
    public function approve( $record )
    {
        $this->load->model('claim_surveyor_model');

        $data = [
            'status'                    => IQB_CLAIM_STATUS_APPROVED,
            'approved_at'               => $this->set_date(),
            'approved_by'               => $this->dx_auth->get_user_id()
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



            // Task b. activity, cache
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
     * Settle Claim
     *
     * This function will update ri-claim data and status to "Settled" for the supplied claim.
     *
     * @param object $record
     * @return bool
     */
    public function settle( $record )
    {
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
         *  - modified at/by
         */
        // Claim RI-Breakdown
        $claim_data = $this->_claim_ri_breakdown($record);

        $claim_data = array_merge($claim_data, [
            'settlement_date'   => $settlement_date,
            'fiscal_yr_id'      => $fy_record->id,
            'fy_quarter'        => $fy_quarter->quarter,
            'status'            => IQB_CLAIM_STATUS_SETTLED,
        ]);

        // Updated by/at
        $claim_data = $this->modified_on(['fields' => $claim_data]);

        // Task a: Update data
        $done = $this->db->where('id', $record->id)
                    ->update($this->table_name, $claim_data);

        // Task b. activity, cache
        if($done)
        {
            // Clean Cache by policy belonging to this policy
            $this->clear_cache( 'claim_list_by_policy_' . $record->policy_id );
        }

        // return result/status
        return $done;
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
        // Models
        $this->load->model('claim_surveyor_model');
        $this->load->model('portfolio_model');

        // Get the list of surveyor
        $surveyors = $this->claim_surveyor_model->get_many_by_claim($record->id);

        // List of accounts for the portfolio
        $portfolio = $this->portfolio_model->find($record->portfolio_id);
        if( !$portfolio->account_id_ce || !$portfolio->account_id_cr )
        {
            throw new Exception("Exception [Model: Claim_model][Method: voucher()]: Default internal accounts(Claim Expense, Claim Receivable) for this portfolio not found. Please contact Administrator for this.");
        }


        // Claim RI-Breakdown
        $claim_ri_breakdown = $this->_claim_ri_breakdown($record);


        /**
         * --------------------------------------------------------------
         * Account                              |   DR      |   CR      |
         * --------------------------------------------------------------
         * Claim Expense (Portfolio-wise)       |   *       |           |
         * Portfolio Withdrawl (Portfolio-wise) |   *       |           |
         * Vat on Surveyor Fee                  |   *       |           |
         *                                                              |
         * Surveyor Party                       |           |   *       |
         * TDS Payable                          |           |   *       |
         * Claim Party                          |           |   *       |
         * --------------------------------------------------------------
         */

        $narration = "Claim Voucher ({$record->claim_code}) for Policy({$record->policy_code}).";
        $voucher_data = [
            'voucher_date'      => date('Y-m-d'),
            'voucher_type_id'   => IQB_AC_VOUCHER_TYPE_JRNL,
            'narration'         => $narration,
            'flag_internal'     => IQB_FLAG_ON
        ];

        // --------------------------------------------------------------------

        /**
         * DEBIT SECTION
         */

        $claim_expense       = $claim_ri_breakdown['cl_treaty_retaintion'];
        $portfolio_withdrawl = $this->_total_claim_amount($record) - $claim_expense;

        $dr_accounts = [
            // Claim Expense (Portfolio-wise)
            $portfolio->account_id_ce,

            // Claim Receivable (Portfolio-wise)
            $portfolio->account_id_cr
        ];
        $dr_party_types = [NULL, NULL];
        $dr_parties     = [NULL, NULL];
        $dr_amounts     = [$claim_expense, $portfolio_withdrawl];


        // Surveyor VAT??
        foreach($surveyors as $single)
        {
            $vat = (float)$single->vat_amount;
            if($vat)
            {
                $dr_accounts[]      = IQB_AC_ACCOUNT_ID_VAT_PAYABLE;
                $dr_party_types[]   = IQB_AC_PARTY_TYPE_SURVEYOR;
                $dr_parties[]       = $single->surveyor_id;
                $dr_amounts[]       = $vat;
            }
        }



        // --------------------------------------------------------------------

        /**
         * CREDIT SECTION
         */
        $cr_accounts    = [];
        $cr_party_types = [];
        $cr_parties     = [];
        $cr_amounts     = [];
        if( !$surveyor_fee_only )
        {
            $cr_accounts[]    = IQB_AC_ACCOUNT_ID_CLAIM_PARTY;
            $cr_party_types[] = IQB_AC_PARTY_TYPE_CUSTOMER;
            $cr_parties[]     = $record->customer_id;
            $cr_amounts[]     = (float)$record->settlement_claim_amount;
        }


        // Surveyor VAT??
        foreach($surveyors as $single)
        {
            $vat = (float)$single->vat_amount;
            $tds = (float)$single->tds_amount;
            $fee = (float)$single->surveyor_fee;
            $surveyor_party = $fee + $vat - $tds;

            // Surveyor Party
            $cr_accounts[]      = IQB_AC_ACCOUNT_ID_SURVEYOR_PARTY;
            $cr_party_types[]   = IQB_AC_PARTY_TYPE_SURVEYOR;
            $cr_parties[]       = $single->surveyor_id;
            $cr_amounts[]       = $surveyor_party;

            // TDS Payable
            $cr_accounts[]      = IQB_AC_ACCOUNT_ID_TDS_SURVEYOR;
            $cr_party_types[]   = IQB_AC_PARTY_TYPE_SURVEYOR;
            $cr_parties[]       = $single->surveyor_id;
            $cr_amounts[]       = $tds;
        }

        // --------------------------------------------------------------------

        /**
         * Format Data
         */
        $voucher_data['account_id']['dr']   = $dr_accounts;
        $voucher_data['party_type']['dr']   = $dr_party_types;
        $voucher_data['party_id']['dr']     = $dr_parties;
        $voucher_data['amount']['dr']       = $dr_amounts;

        $voucher_data['account_id']['cr']   = $cr_accounts;
        $voucher_data['party_type']['cr']   = $cr_party_types;
        $voucher_data['party_id']['cr']     = $cr_parties;
        $voucher_data['amount']['cr']       = $cr_amounts;


        // --------------------------------------------------------------------

        /**
         * Save Voucher and Its Relation with Policy and return Voucher ID
         */
        return $this->ac_voucher_model->add($voucher_data, $record->policy_id);
    }

        private function _claim_ri_breakdown($record)
        {
            $this->load->model('ri_transaction_model');

            /**
             * We have to breakdwon total Claim into
             *      1. Amount paid by the Insurance company
             *      2. Rest Amount paid by Re-insurer
             */
            $total_claim_amount = $this->_total_claim_amount($record);

            /**
             * Current RI Transaction State
             *
             *  Compute the sum of all ri transactions of this policy
             */
            $ri_distribution    = $this->ri_transaction_model->latest_build_by_policy($record->policy_id);
            $si_gross           = $ri_distribution->si_gross;

            return [
                'cl_comp_cession'       => ( (float)$ri_distribution->si_comp_cession / $si_gross ) * $total_claim_amount,
                'cl_treaty_retaintion'  => ( (float)$ri_distribution->si_treaty_retaintion / $si_gross ) * $total_claim_amount,
                'cl_treaty_quota'       => ( (float)$ri_distribution->si_treaty_quota / $si_gross ) * $total_claim_amount,
                'cl_treaty_1st_surplus' => ( (float)$ri_distribution->si_treaty_1st_surplus / $si_gross ) * $total_claim_amount,
                'cl_treaty_2nd_surplus' => ( (float)$ri_distribution->si_treaty_2nd_surplus / $si_gross ) * $total_claim_amount,
                'cl_treaty_3rd_surplus' => ( (float)$ri_distribution->si_treaty_3rd_surplus / $si_gross ) * $total_claim_amount,
                'cl_treaty_fac'         => ( (float)$ri_distribution->si_treaty_fac / $si_gross ) * $total_claim_amount,
            ];
        }

        private function _total_claim_amount($record)
        {
            return (float)$record->total_surveyor_fee_amount + (float)$record->settlement_claim_amount;
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
