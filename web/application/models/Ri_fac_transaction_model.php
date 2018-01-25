<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_fac_transaction_model extends MY_Model
{
    protected $table_name   = 'dt_ri_fac_transactions';

    protected $set_created  = true;
    protected $set_modified = true;
    protected $log_user     = true;

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
                'rules' => 'trim|required|prep_decimal|decimal|max_length[5]|callback__cb_fac_distribution__complete',
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
     * @return mixed
     */
    public function register_fac($ri_transaction_record, $data)
    {
        // Disable DB Debug for transaction to work
        $this->db->db_debug = TRUE;
        $status             = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Delete Old Distribution
            $this->delete_fac_by_ri_transaction($ri_transaction_record->id);

            // Batch Insert distribution data
            $this->batch_insert_fac($ri_transaction_record, $data);


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

    public function batch_insert_fac($ri_transaction_record, $data)
    {
        // Extract All Data
        $company_id   = $data['company_id'];

        $batch_data = [];

        if( !empty($company_id) )
        {
            for($i=0; $i < count($company_id); $i++)
            {
                // Compute Data
                $fac_percent        = $data['fac_percent'][$i];
                $fac_si_amount      = ( floatval($ri_transaction_record->si_treaty_fac) * $fac_percent ) / 100.00;
                $fac_premium_amount = ( floatval($ri_transaction_record->premium_treaty_fac) * $fac_percent ) / 100.00;

                $fac_commission_percent = $data['fac_commission_percent'][$i];
                $fac_commission_amount  = ( $fac_premium_amount * $fac_commission_percent ) / 100.00;

                $fac_ri_tax_percent     = $data['fac_ri_tax_percent'][$i];
                $fac_ri_tax_amount      = ( $fac_premium_amount * $fac_ri_tax_percent ) / 100.00;

                $fac_ib_tax_percent     = $data['fac_ib_tax_percent'][$i];
                $fac_ib_tax_amount      = ( $fac_premium_amount * $fac_ib_tax_percent ) / 100.00;


                $batch_data[] = [
                    'ri_transaction_id'         => $ri_transaction_record->id,
                    'company_id'                => $data['company_id'][$i],
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
            }
        }

        // Insert Batch Broker Data
        if( $batch_data )
        {
            return parent::insert_batch($batch_data, TRUE);
        }
        return FALSE;
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
        return $this->db->select('FAC.*, C.name AS company_name')
                        ->from($this->table_name . ' FAC')
                        ->join('master_companies C', 'C.id = FAC.company_id')
                        ->where('FAC.ri_transaction_id', $ri_transaction_id)
                        ->get()->result();
    }

    // --------------------------------------------------------------------


    /**
     * Add Blank Configuration record for this Policy if not already exists.
     *
     * @param int $ri_transaction_id
     * @return mixed
     */
    public function add_blank( $ri_transaction_id )
    {
        $duplicate = $this->check_duplicate($ri_transaction_id);
        if(! $duplicate )
        {
            return parent::insert( ['ri_transaction_id' => $ri_transaction_id], TRUE );
        }
        return FALSE;
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

    public function delete($id = NULL)
    {
        // Let's not delete now
        return FALSE;


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
            'module'    => 'ri_fac_config',
            'module_id' => $id,
            'action'    => $action
        ];
        return $this->activity->save($activity_log);
    }
}