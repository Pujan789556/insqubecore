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

    protected $fields = ['id', 'claim_id', 'surveyor_id', 'survey_type', 'surveyor_fee', 'vat_amount', 'tds_amount', 'status', 'assigned_date', 'vouchered_date', 'paid_date', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
                'label' => 'Surveyor Fee (Rs.)',
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
        $this->load->model('ac_duties_and_tax_model');
        $this->load->model('surveyor_model');

        /**
         * Variables
         */
        $to_del_ids         = [];
        $to_edit_sids        = [];
        $to_edit_data       = [];

        $post_sids = [];
        foreach($data['surveyor_id'] as $sid)
        {
            $post_sids[] = (int)$sid;
        }

        $total_surveyor_fee_amount = 0.00;
        foreach($data['surveyor_fee'] as $surveyor_fee)
        {
            $total_surveyor_fee_amount += (float)$surveyor_fee;
        }

        /**
         * Prepare Data
         */
        $old_surveyors = $this->get_many_by_claim($claim_id);
        $old_sids = [];
        foreach($old_surveyors as $single)
        {
            $sid = (int)$single->surveyor_id;
            $old_sids[] = $sid;

            if( !in_array($sid, $post_sids) )
            {
                $to_del_ids[] = (int)$single->id;
            }
            else
            {
                $to_edit_sids[] = $sid;

                // index of this sid
                $index = array_search($sid, $post_sids);
                $to_edit_data["{$single->id}"] = [
                    'assigned_date' => $data['assigned_date'][$index],
                    'survey_type'   => $data['survey_type'][$index],
                    'surveyor_fee'  => $data['surveyor_fee'][$index],
                ];

                // TDS and VAT
                $vat_amount = NULL;
                $tds_amount = NULL;
                if($single->flag_vat_registered == IQB_FLAG_ON)
                {
                    $vat_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_VAT, $data['surveyor_fee'][$index], IQB_AC_DECIMAL_PRECISION);
                    $tds_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_TDS_ON_SFVR, $data['surveyor_fee'][$index], IQB_AC_DECIMAL_PRECISION);
                }
                else{
                    $tds_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_TDS_ON_SFVNR, $data['surveyor_fee'][$index], IQB_AC_DECIMAL_PRECISION);
                }
                $to_edit_data["{$single->id}"]['vat_amount'] = $vat_amount;
                $to_edit_data["{$single->id}"]['tds_amount'] = $tds_amount;
            }
        }

        $to_insert_sids = array_diff($post_sids, $to_edit_sids);
        $batch_data = [];
        foreach($to_insert_sids as $sid)
        {
            // index of this sid
            $index = array_search($sid, $post_sids);
            $surveyor = $this->surveyor_model->find($sid);

            // TDS and VAT
            $vat_amount = NULL;
            $tds_amount = NULL;
            if($surveyor->flag_vat_registered == IQB_FLAG_ON)
            {
                $vat_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_VAT, $data['surveyor_fee'][$index], IQB_AC_DECIMAL_PRECISION);
                $tds_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_TDS_ON_SFVR, $data['surveyor_fee'][$index], IQB_AC_DECIMAL_PRECISION);
            }
            else{
                $tds_amount = $this->ac_duties_and_tax_model->compute_tax(IQB_AC_DNT_ID_TDS_ON_SFVNR, $data['surveyor_fee'][$index], IQB_AC_DECIMAL_PRECISION);
            }

            $batch_data[] = [
                'claim_id'      => $claim_id,
                'surveyor_id'   => $sid,
                'assigned_date' => $data['assigned_date'][$index],
                'survey_type'   => $data['survey_type'][$index],
                'surveyor_fee'  => $data['surveyor_fee'][$index],
                'vat_amount'    => $vat_amount,
                'tds_amount'    => $tds_amount,
            ];
        }


        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $done               = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            /**
             * Task 1: Delete old Records(Not in New)
             */
            if($to_del_ids)
            {
                $this->db->where_in('id', $to_del_ids)
                        ->delete($this->table_name);
            }


            /**
             * Task 2: Update on Existing Old
             */
             if($to_edit_data)
            {
                foreach($to_edit_data as $id=>$edit_data)
                {
                    parent::update($id, $edit_data, TRUE);
                }
            }

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
                'total_surveyor_fee_amount' => $total_surveyor_fee_amount
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

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $done;
    }

    // ----------------------------------------------------------------

    private function _build_update_data($old_surveyors, $data)
    {

        /**
         * !!!NOTE!!!
         *      Must use "surveyor_id" and "surveyor_type" combination as key
         *      to differntiate
         */
        /**
         * Tasks:
         *  1. Find to del ids
         *  2. To Update ids
         *  3. To New Insert Data
         */
        $update_list = [];




        // $surveyor_id = $this->input->post('surveyor_id');
        // $survey_type = $this->input->post('survey_type');

        // $total_count = count($surveyor_id);
        // $complex_list = [];
        // for($i = 0; $i< $total_count; $i++ )
        // {
        //     $complex_list[] = implode('-', [$surveyor_id[$i], $survey_type[$i]]);
        // }


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