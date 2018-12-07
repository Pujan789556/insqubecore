<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Claim_surveyor_model extends MY_Model
{
    protected $table_name = 'dt_claim_surveyors';

    protected $set_created  = true;
    protected $set_modified = true;
    protected $log_user     = true;

    protected $protected_attributes = ['id'];


    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'claim_id', 'surveyor_id', 'survey_type', 'surveyor_fee', 'other_fee', 'vat_amount', 'tds_amount', 'status', 'assigned_date', 'vouchered_date', 'paid_date', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0; // Prevent first 12 records from deletion.

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // load validation rules
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $this->load->model('surveyor_model');
        $surveyor_dropdown      = $this->surveyor_model->dropdown();
        $surveyor_type_dropdown = CLAIM__surveyor_type_dropdown(FALSE);

        $this->validation_rules = [
            [
                'field' => 'surveyor_id[]',
                '_key' => 'surveyor_id',
                'label' => 'Surveyor',
                'rules' => 'trim|required|integer|in_list['.implode(',', array_keys($surveyor_dropdown)).']|callback_cb_surveyor_duplicate',
                '_type' => 'dropdown',
                '_data'         => IQB_BLANK_SELECT + $surveyor_dropdown,
                '_show_label'   => false,
                '_required'     => true
            ],
            [
                'field' => 'assigned_date[]',
                '_key'  => 'assigned_date',
                'label' => 'Assigned Date',
                'rules' => 'trim|required|valid_date',
                '_type'             => 'date',
                '_default'          => date('Y-m-d'),
                '_extra_attributes' => 'data-provide="datepicker-inline" style="min-width:100px"',
                '_show_label'       => false,
                '_required'         => true
            ],
            [
                'field' => 'survey_type[]',
                '_key' => 'survey_type',
                'label' => 'Surveyor Type',
                'rules' => 'trim|required|alpha|in_list['.implode(',', array_keys($surveyor_type_dropdown)).']',
                '_type' => 'dropdown',
                '_data'         => IQB_BLANK_SELECT + $surveyor_type_dropdown,
                '_show_label'   => false,
                '_required'     => true
            ],
            [
                'field' => 'surveyor_fee[]',
                '_key' => 'surveyor_fee',
                'label' => 'Professional Fee (Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type' => 'text',
                '_show_label'   => false,
                '_required' => true
            ],
            [
                'field' => 'other_fee[]',
                '_key' => 'other_fee',
                'label' => 'Other Fee (Rs.)',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type' => 'text',
                '_show_label'   => false,
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Assign Surveyors on a Claim
     *
     * @param int $claim_id
     * @param array $data
     * @return mixed
     */
    public function assign_to_claim( $claim_id, $data )
    {
        $this->load->model('claim_model');
        $this->load->model('surveyor_model');



        $gross_amt_surveyor_fee = 0.00;
        $vat_amt_surveyor_fee   = 0.00;
        /**
         * Prepare Data
         */
        $batch_data = [];
        $count = count($data['surveyor_id']);
        for($index =0; $index < $count; $index++ )
        {
            // index of this sid
            $surveyor_id = $data['surveyor_id'][$index];
            $surveyor = $this->surveyor_model->find($surveyor_id);

            // Individual Surveyor Fee and Other Fee
            $surveyor_fee       = floatval($data['surveyor_fee'][$index] ?? 0.00);
            $other_fee          = floatval($data['other_fee'][$index] ?? 0.00);


            // VAT & TDS
            $taxes = $this->_compute_tax($surveyor, $surveyor_fee);

            // Single Insert Data
            $single_data = [
                'claim_id'      => $claim_id,
                'surveyor_id'   => $surveyor_id,
                'assigned_date' => $data['assigned_date'][$index],
                'survey_type'   => $data['survey_type'][$index],
                'surveyor_fee'  => $surveyor_fee,
                'other_fee'     => $other_fee,
                'vat_amount'    => $taxes['vat_amount'],
                'tds_amount'    => $taxes['tds_amount']
            ];

            // Add to Batch Insert
            $batch_data[] = $single_data;


            // Update Grand Total Surveyor Fee
            $gross_amt_surveyor_fee = ac_bcsum([
                    $gross_amt_surveyor_fee,
                    $surveyor_fee,
                    $other_fee
                ],
                IQB_AC_DECIMAL_PRECISION
            );

            // Update Total VAT
            $vat_amt_surveyor_fee = bcadd($vat_amt_surveyor_fee, $taxes['vat_amount'], IQB_AC_DECIMAL_PRECISION);
        }


        // Use automatic transaction
        $done = TRUE;
        $this->db->trans_start();

            /**
             * Task 1: Delete Old Surveyors
             */
            parent::delete_by(['claim_id' => $claim_id]);

            /**
             * Task 3: Batch insert new data (if any)
             */
            if($batch_data)
            {
                parent::insert_batch($batch_data, TRUE);
            }

            /**
             * Task 4: Update Total Surveyor Fee On Claim Table
             */
            $claim_data = [
                'gross_amt_surveyor_fee' => $gross_amt_surveyor_fee,
                'vat_amt_surveyor_fee'   => $vat_amt_surveyor_fee
            ];
            $this->claim_model->update_data($claim_id, $claim_data);


            /**
             * Task 5: Clear cache for this claim
             */
            $this->clear_cache( 'srv_lstbyclm_' . $claim_id );


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
         * Compute Surveyor Taxes - VAT and TDS
         *
         * @param int   $surveyor_id
         * @param decimal $surveyor_fee Surveyor Professional Fee
         */
        private function _compute_tax($surveyor, $surveyor_fee)
        {
            $this->load->model('surveyor_model');
            $this->load->model('ac_duties_and_tax_model');

            // Get the Surveyor Record
            $surveyor = is_numeric($surveyor) ? $this->surveyor_model->find((int)$surveyor) : $surveyor;


            /**
             * VAT & TDS
             * ----------
             *
             * NOTE:
             *      VAT = Professional Fee X VAT %
             *      TDS = Professional Fee X TDS %
             *      TOTAL = Professional Fee + Other Fee + VAT - TDS
             */
            $vat_amount     = NULL;
            $tds_amount     = NULL;
            $surveyor_fee   = floatval($surveyor_fee);
            if($surveyor->flag_vat_registered == IQB_FLAG_ON)
            {
                $vat_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_VAT, $surveyor_fee, IQB_AC_DECIMAL_PRECISION);
                $tds_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_TDS_ON_SFVR, $surveyor_fee, IQB_AC_DECIMAL_PRECISION);
            }
            else{
                $tds_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_TDS_ON_SFVNR, $surveyor_fee, IQB_AC_DECIMAL_PRECISION);
            }

            return [
                'vat_amount' => $vat_amount,
                'tds_amount' => $tds_amount
            ];
        }

    // ----------------------------------------------------------------

    /**
     * Compute NET Total Fee for a Surveyor
     *
     * FORMULA = surveyor_fee + other_fee + vat - tds
     *
     * @param object Claim_surveyor Record
     * @return decimal
     */
    public function compute_net_total_fee($record)
    {
        $record = is_numeric($record) ? $this->find( (int)$record ) : $record;
        if(!$record)
        {
            throw new Exception("Exception [Model: Claim_surveyor_model][Method: compute_net_total_fee()]: Claim Surveyor information could not be found.");
        }

        /**
         * Per Surveyor Total = Professional Fee + Other Fee + VAT - TDS
         */
        return bcsub(
            ac_bcsum([$record->surveyor_fee, $record->other_fee, floatval($record->vat_amount)], IQB_AC_DECIMAL_PRECISION),
            floatval($record->tds_amount),
            IQB_AC_DECIMAL_PRECISION
        );
    }

    // ----------------------------------------------------------------

    /**
     * Compute NET Total Fee for a Claim
     *
     * @param int $claim_id
     * @return decimal
     */
    public function compute_net_total_fee_by_claim($claim_id)
    {
        $list = $this->get_many_by_claim($claim_id);

        $net_total = 0.00;
        foreach($list as $single)
        {
            $net_total = bcadd($net_total, $this->compute_net_total_fee($single));
        }

        return $net_total;
    }

    // ----------------------------------------------------------------

    /**
     * Compute Gross Total Fee for a Surveyor
     *
     * FORMULA = surveyor_fee + other_fee
     *
     * @param object Claim_surveyor Record
     * @return decimal
     */
    public function compute_gross_total_fee($record)
    {
        $record = is_numeric($record) ? $this->find( (int)$record ) : $record;
        if(!$record)
        {
            throw new Exception("Exception [Model: Claim_surveyor_model][Method: compute_gross_total_fee()]: Claim Surveyor information could not be found.");
        }

        /**
         * Per Surveyor Total = Professional Fee + Other Fee
         */
        return bcadd($record->surveyor_fee, $record->other_fee, IQB_AC_DECIMAL_PRECISION);
    }

    // ----------------------------------------------------------------

    /**
     * Compute Gross Total Fee for a Claim
     *
     * @param int $claim_id
     * @return decimal
     */
    public function compute_gross_total_fee_by_claim($claim_id)
    {
        $list = $this->get_many_by_claim($claim_id);

        $gross_total = 0.00;
        foreach($list as $single)
        {
            $gross_total = bcadd($gross_total, $this->compute_gross_total_fee($single));
        }

        return $gross_total;
    }

    // ----------------------------------------------------------------

    /**
     * Compute Total VAT for a Claim
     *
     * @param int $claim_id
     * @return decimal
     */
    public function compute_vat_total_by_claim($claim_id)
    {
        $list = $this->get_many_by_claim($claim_id);

        $vat_total = 0.00;
        foreach($list as $single)
        {
            $vat_total = bcadd($vat_total, $single->vat_amount ?? 0.00, IQB_AC_DECIMAL_PRECISION);
        }

        return $vat_total;
    }

    // ----------------------------------------------------------------

    public function get_many_by_claim($claim_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'srv_lstbyclm_' . $claim_id;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $list = $this->db->select(
                                'CS.*, ' .
                                'S.name AS surveyor_name, S.flag_vat_registered'
                            )
                            ->from($this->table_name . ' CS')
                            ->join('master_surveyors S', 'S.id = CS.surveyor_id')
                            ->where('CS.claim_id', $claim_id)
                            ->get()
                            ->result();

            $this->write_cache($list, $cache_var, CACHE_DURATION_DAY);
        }
        return $list;
    }


    // ----------------------------------------------------------------

    /**
     * Check if a Claim has surveyors assigned?
     *
     * @param int $claim_id
     * @return mixed
     */
    public function has_surveyors($claim_id)
    {
        return $this->check_duplicate(['claim_id' => $claim_id]);
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

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'srv_lstbyclm_*'
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
            // get_allenerate an error... or use the log_message() function to log your error
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
        return true;

        $action = is_string($action) ? $action : 'C';
        // Save Activity Log
        $activity_log = [
            'module' => 'claim_surveyor',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}