<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_fac_registration_model extends MY_Model
{
    protected $table_name   = 'dt_ri_fac_registrations';

    protected $set_created  = TRUE;
    protected $set_modified = TRUE;
    protected $log_user     = TRUE;
    protected $audit_log    = TRUE;

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $protected_attributes = ['id'];
    protected $fields = ['id', 'ri_transaction_id', 'company_id', 'fac_percent', 'fac_si_amount', 'fac_premium_amount', 'fac_commission_percent', 'fac_commission_amount', 'fac_ri_tax_percent', 'fac_ri_tax_amount', 'fac_ib_tax_percent', 'fac_ib_tax_amount', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

        $this->load->model('ri_transaction_model');

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
        $this->load->model('company_model');
        $reinsurer_dropdown = $this->company_model->dropdown_reinsurers();

       $this->validation_rules = [
            [
                'field' => 'id[]',
                '_key' => 'id',
                'label' => 'ID',
                'rules' => 'trim|integer|max_length[11]',
                '_type' => 'hidden',
                '_show_label'   => false,
                '_required'     => false
            ],
            [
                'field' => 'company_id[]',
                'label' => 'Reinsurer',
                'rules' => 'trim|required|integer|max_length[8]|in_list[' . implode( ',', array_keys($reinsurer_dropdown) ) . ']',
                '_key'        => 'company_id',
                '_type'         => 'dropdown',
                '_show_label'   => false,
                '_data'         => IQB_BLANK_SELECT + $reinsurer_dropdown,
                '_required'     => true
            ],
            [
                'field' => 'fac_percent[]',
                'label' => 'Distribution %',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[6]|callback__cb_fac_distribution__complete',
                '_key'        => 'fac_percent',
                '_type'         => 'text',
                '_show_label'   => false,
                '_required'     => true
            ],
            [
                'field' => 'fac_commission_percent[]',
                'label' => 'Commission %',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                '_key'        => 'fac_commission_percent',
                '_type'         => 'text',
                '_show_label'   => false,
                '_required'     => true
            ],
            [
                'field' => 'fac_ri_tax_percent[]',
                'label' => 'RI Tax %',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                '_key'        => 'fac_ri_tax_percent',
                '_type'         => 'text',
                '_show_label'   => false,
                '_required'     => true
            ],
            [
                'field' => 'fac_ib_tax_percent[]',
                'label' => 'IB Tax %',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                '_key'        => 'fac_ib_tax_percent',
                '_type'         => 'text',
                '_show_label'   => false,
                '_required'     => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Save FAC Distribution
     *
     * All transactions must be carried out, else rollback.
     * The following tasks are carried during FAC Setup:
     *      a. Delete old fac distribution
     *      b. Update New fac distribution
     *
     * @param object $ri_transaction_record
     * @param array $data
     * @param array $old_records OLD Distribution Records of this RI-Transaction
     * @return mixed
     */
    public function register_fac($ri_transaction_record, $data, $old_records)
    {

        $old_ids        = [];
        foreach($old_records as $single)
        {
            $old_ids[] = $single->id;
        }
        asort($old_ids);

        /**
         * Find to Del IDs
         */
        $to_update_ids = $data['id'];
        asort($to_update_ids);
        $to_del_ids = array_diff($old_ids, $to_update_ids);


        // Insert and Update Data
        $prepared_data = $this->_prepare_data($ri_transaction_record, $data, $old_ids);

        $batch_insert_data      = $prepared_data['batch_insert_data'];
        $batch_update_data      = $prepared_data['batch_update_data'];



        // ----------------------------------------------------------------

        $status             = TRUE;
        // Use automatic transaction
        $this->db->trans_start();

            /**
             * Task 1: Delete removed Surveyors
             */
            foreach($to_del_ids as $id)
            {
                if(in_array($id, $old_ids))
                {
                    parent::delete($id);
                }
            }

            /**
             * Task 2: Update Old Records (if any)
             */
            foreach($batch_update_data as $id=>$single_data)
            {
                parent::update($id, $single_data, TRUE);
            }

            /**
             * Task 3: Insert new data (if any) - One by One
             */
            foreach($batch_insert_data as $single_data)
            {
                parent::insert($single_data, TRUE);
            }


            /**
             * Task 4: Update FAC Registered Flag
             */
            $this->ri_transaction_model->update_flag_fac_registered($ri_transaction_record->id, IQB_FLAG_ON);


        // Commit all transactions on success, rollback else
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // return result/status
        return $status;
    }

    // ----------------------------------------------------------------

    private function _prepare_data($ri_transaction_record, $data, $old_ids)
    {
        $batch_update_data = [];
        $batch_insert_data = [];

        $count  = count($data['company_id']);
        for($index =0; $index < $count; $index++ )
        {
            // index of this sid
            $id             = $data['id'][$index];
            $company_id     = $data['company_id'][$index];

            // Compute Data
            $fac_percent        = $data['fac_percent'][$index];
            $fac_si_amount      = ( floatval($ri_transaction_record->si_treaty_fac) * $fac_percent ) / 100.00;
            $fac_premium_amount = ( floatval($ri_transaction_record->premium_treaty_fac) * $fac_percent ) / 100.00;

            $fac_commission_percent = $data['fac_commission_percent'][$index];
            $fac_commission_amount  = ( $fac_premium_amount * $fac_commission_percent ) / 100.00;

            $fac_ri_tax_percent     = $data['fac_ri_tax_percent'][$index];
            $fac_ri_tax_amount      = ( $fac_premium_amount * $fac_ri_tax_percent ) / 100.00;

            $fac_ib_tax_percent     = $data['fac_ib_tax_percent'][$index];
            $fac_ib_tax_amount      = ( $fac_premium_amount * $fac_ib_tax_percent ) / 100.00;


            $single_data = [
                'ri_transaction_id'        => $ri_transaction_record->id,
                'company_id'               => $company_id,
                'fac_percent'              => $fac_percent,
                'fac_si_amount'            => $fac_si_amount,
                'fac_premium_amount'       => $fac_premium_amount,
                'fac_commission_percent'   => $fac_commission_percent,
                'fac_commission_amount'    => $fac_commission_amount,
                'fac_ri_tax_percent'       => $fac_ri_tax_percent,
                'fac_ri_tax_amount'        => $fac_ri_tax_amount,
                'fac_ib_tax_percent'       => $fac_ib_tax_percent,
                'fac_ib_tax_amount'        => $fac_ib_tax_amount
            ];


            if($id && in_array($id, $old_ids))
            {
                $batch_update_data["{$id}"] = $single_data;
            }
            else
            {
                // Add to Batch Insert
                $batch_insert_data[] = $single_data;
            }
        }


        return [
            'batch_update_data' => $batch_update_data,
            'batch_insert_data' => $batch_insert_data,
        ];

    }

    // ----------------------------------------------------------------

    public function delete_fac_by_ri_transaction($ri_transaction_id)
    {
        return $this->db->where('ri_transaction_id', $ri_transaction_id)
                        ->delete($this->table_name);
    }

    // ----------------------------------------------------------------


    public function get_fac_by_ri_transaction($ri_transaction_id)
    {
        return $this->db->select('FAC.*, C.name_en AS company_name')
                        ->from($this->table_name . ' FAC')
                        ->join('master_companies C', 'C.id = FAC.company_id')
                        ->where('FAC.ri_transaction_id', $ri_transaction_id)
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    public function check_duplicate($ri_transaction_id)
    {
        return $this->db->where('ri_transaction_id', $ri_transaction_id)
                        ->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    public function row($id)
    {
        // $this->_row_select();

        // return $this->db->where('T.id', $id)
        //          ->get()->row();
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

        // $this->_row_select();

        // /**
        //  * Apply Filter
        //  */
        // if(!empty($params))
        // {
        //     $next_id = $params['next_id'] ?? NULL;
        //     if( $next_id )
        //     {
        //         $this->db->where(['T.id <=' => $next_id]);
        //     }

        //     $fiscal_yr_id = $params['fiscal_yr_id'] ?? NULL;
        //     if( $fiscal_yr_id )
        //     {
        //         $this->db->where(['T.fiscal_yr_id' =>  $fiscal_yr_id]);
        //     }

        //     $treaty_type_id = $params['treaty_type_id'] ?? NULL;
        //     if( $treaty_type_id )
        //     {
        //         $this->db->where(['T.treaty_type_id' =>  $treaty_type_id]);
        //     }
        // }

        // return $this->db->limit($this->settings->per_page+1)
        //                 ->order_by('T.fiscal_yr_id', 'desc')
        //                 ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
    //     $this->db->select('T.id, T.name, T.fiscal_yr_id, T.treaty_type_id, T.estimated_premium_income, T.treaty_effective_date, T.file, FY.code_en AS fy_code_en, FY.code_np AS fy_code_np, TT.name AS treaty_type_name')
    //             ->from($this->table_name . ' AS T')
    //             ->join('master_fiscal_yrs FY', 'FY.id = T.fiscal_yr_id')
    //             ->join(self::$table_treaty_types . ' TT', 'TT.id = T.treaty_type_id');
    //
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
        // return $this->db->select(

        //                 // Main table -  all fields
        //                 'T.*, ' .

        //                 // Treaty Tax and Commission - all fields except treaty_id
        //                 'TTNC.*, ' .

        //                 // Treaty Commission Scale
        //                 'TCS.scales as commission_scales, ' .

        //                 // Fiscal year table
        //                 'FY.code_en AS fy_code_en, FY.code_np AS fy_code_np, ' .

        //                 // Treaty Type table
        //                 'TT.name AS treaty_type_name'
        //                 )
        //         ->from($this->table_name . ' AS T')
        //         ->join(self::$table_treaty_tax_and_commission . ' TTNC', 'TTNC.treaty_id = T.id')
        //         ->join(self::$table_treaty_commission_scale . ' TCS', 'TCS.treaty_id = T.id')
        //         ->join('master_fiscal_yrs FY', 'FY.id = T.fiscal_yr_id')
        //         ->join(self::$table_treaty_types . ' TT', 'TT.id = T.treaty_type_id')
        //         ->where('T.id', $id)
        //         ->get()->row();
    }



    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [
            ''
        ];
    	// cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function delete_by_ri_transaction($ri_transaction_id, $use_auto_transaction = FALSE)
    {
        $records = $this->db->select('id')->where('ri_transaction_id', $ri_transaction_id)->get($this->table_name)->result();
        if($records)
        {
            foreach($records as $single)
            {
                $this->delete_single($single->id, $use_auto_transaction);
            }
        }
    }

    // ----------------------------------------------------------------

    public function delete_single($id, $use_auto_transaction = TRUE)
    {
        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        $status = TRUE;
        if($use_auto_transaction )
        {
            // Use automatic transaction
            $this->db->trans_start();

                parent::delete($id);

            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                // get_allenerate an error... or use the log_message() function to log your error
                $status = FALSE;
            }
        }
        else
        {
            $status = parent::delete($id);
        }

        // return result/status
        return $status;
    }
}