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
    protected $fields = ['id', 'claim_code', 'policy_id', 'claim_scheme_id', 'fiscal_yr_id', 'branch_id', 'accident_date', 'accident_time', 'accident_details', 'loss_nature', 'loss_details_ip', 'loss_amount_ip', 'loss_details_tpp', 'loss_amount_tpp', 'death_injured', 'intimation_name', 'initimation_address', 'initimation_contact', 'intimation_date', 'estimated_claim_amount', 'assessment_brief', 'supporting_docs', 'other_info', 'settlement_claim_amount', 'settlement_amount_breakdown', 'status', 'status_remarks', 'approved_at', 'approved_by', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
                    'field' => 'accident_details',
                    'label' => 'Accident Details',
                    'rules' => 'trim|required|htmlspecialchars',
                    '_type'     => 'textarea',
                    '_required' => true
                ]
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
     * Add Claim Draft
     *
     * @param array $data
     * @return mixed
     */
    public function add_draft( $data )
    {
        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $id                 = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Insert Claim Data
            $id = parent::insert($data, TRUE);

            // Task b. Insert Broker Relations
            if($id)
            {
                // Log Activity
                $this->log_activity($id, 'C');

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
         * Policy Code - Draft One & Policy Number
         *
         * Format: DRAFT-<BRANCH-CODE>-<PORTFOLIO-CODE>-<SERIALNO>-<FY_CODE_NP>
         */
        $data['claim_code'] = 'DRAFT-' . $this->token->generate(28);


        // Fiscal Year ID
        $data['fiscal_yr_id'] = $this->current_fiscal_year->id;

        // Branch ID
        $data['branch_id']      = $this->dx_auth->get_branch_id();


        // Status
        $data['status'] = IQB_CLAIM_STATUS_DRAFT;

        /**
         * Death Injured Data
         */
        $data['death_injured'] = $this->__build_death_injured_data($data['death_injured']);


        // Refactor Date & time
        $data = $this->__refactor_datetime_fields($data);

        return $data;
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
                // Log Activity
                $this->log_activity($id, 'U');

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
         * Death Injured Data
         */
        $data['death_injured'] = $this->__build_death_injured_data($data['death_injured']);


        // Refactor Date & time
        $data = $this->__refactor_datetime_fields($data);

        return $data;
    }



    // ----------------------------------------------------------------

        private function __refactor_datetime_fields($data)
        {
            // Date
            $data['accident_date']    = date('Y-m-d', strtotime($data['accident_date_time']));

            // Time
            $data['accident_time']    = date('H:i:00', strtotime($data['accident_date_time']));

            // unset
            unset($data['accident_date_time']);

            return $data;
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

            return $death_injured;
        }


    // ----------------------------------------------------------------

    public function row($id)
    {
        return $this->get($id);
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

            $policy_id = $params['policy_id'] ?? NULL;
            if( $policy_id )
            {
                $this->db->where(['P.id' =>  $policy_id]);
            }

            $policy_code = $params['policy_code'] ?? NULL;
            if( $policy_code )
            {
                $this->db->where(['P.code' =>  $policy_code]);
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

                            // Policy Table Data
                            'P.code as policy_code, '
                        )
                ->from($this->table_name . ' AS CLM')
                ->join('dt_policies P', 'P.id = CLM.policy_id');
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
        $this->_row_select();

        return $this->db->where('CLM.id', $id)
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
            $this->log_activity($id, 'D');

            // Clear Cache
            $this->clear_cache( 'claim_list_by_policy_' . $record->policy_id );
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
            'module'    => 'claims',
            'module_id' => $id,
            'action'    => $action
        ];
        return $this->activity->save($activity_log);
    }
}