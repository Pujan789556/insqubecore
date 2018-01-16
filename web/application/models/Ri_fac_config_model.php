<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_fac_config_model extends MY_Model
{
    protected $table_name   = 'dt_ri_fac_config';

    protected $set_created  = true;
    protected $set_modified = true;
    protected $log_user     = true;

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $protected_attributes = [];
    protected $fields = ['policy_id', 'fac_config', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
       $this->validation_rules = [];
    }

    // ----------------------------------------------------------------

    /**
     * Add Blank Configuration record for this Policy if not already exists.
     *
     * @param int $policy_id
     * @return mixed
     */
    public function add_blank( $policy_id )
    {
        $duplicate = $this->check_duplicate($policy_id);
        if(! $duplicate )
        {
            return parent::insert( ['policy_id' => $policy_id], TRUE );
        }
        return FALSE;
    }

    // ----------------------------------------------------------------

    public function check_duplicate($policy_id)
    {
        return $this->db->where('policy_id', $policy_id)
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