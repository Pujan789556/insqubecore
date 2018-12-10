<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_transaction_model extends MY_Model
{
    protected $table_name = 'dt_ri_transactions';

    protected $set_created  = true;
    protected $set_modified = true;
    protected $log_user     = true;

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $protected_attributes = ['id'];
    protected $fields = ['id', 'parent_id', 'policy_id', 'endorsement_id', 'policy_installment_id', 'treaty_id', 'fiscal_yr_id', 'fy_quarter', 'ri_txn_for', 'si_gross', 'si_comp_cession', 'si_treaty_total', 'si_treaty_retaintion', 'si_treaty_quota', 'si_treaty_1st_surplus', 'si_treaty_2nd_surplus', 'si_treaty_3rd_surplus', 'si_treaty_fac', 'premium_gross', 'premium_pool', 'premium_net', 'premium_comp_cession', 'premium_treaty_total', 'premium_treaty_retaintion', 'premium_treaty_quota', 'premium_treaty_1st_surplus', 'premium_treaty_2nd_surplus', 'premium_treaty_3rd_surplus', 'premium_treaty_fac', 'claim_gross', 'claim_comp_cession', 'claim_treaty_total', 'claim_treaty_retaintion', 'claim_treaty_quota', 'claim_treaty_1st_surplus', 'claim_treaty_2nd_surplus', 'claim_treaty_3rd_surplus', 'claim_treaty_fac', 'commission_quota', 'commission_surplus', 'commission_fac', 'flag_fac_registered', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

        // Dependent Model(s)
        $this->load->model('ri_fac_registration_model');

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
     * Add an RI Transaction
     *
     * @param array $data
     * @return mixed
     */
    public function add( $data )
    {
        /**
         * Check Duplicate
         */
        $where = [
            'policy_id'             => $data['policy_id'],
            'endorsement_id' => $data['endorsement_id'],
            'policy_installment_id' => $data['policy_installment_id'],
            'ri_txn_for'          => $data['ri_txn_for']
        ];
        $duplicate = $this->check_duplicate($where);
        if( $duplicate )
        {
            return FALSE;
        }

        return parent::insert($data, TRUE);
    }

    // ----------------------------------------------------------------

    public function update_flag_fac_registered($id, $flag)
    {
        return parent::update($id, ['flag_fac_registered' => $flag], TRUE);
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
                $this->db->where(['RTXN.id <=' => $next_id]);
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

            $ri_txn_for = $params['ri_txn_for'] ?? NULL;
            if( $ri_txn_for )
            {
                $this->db->where(['RTXN.ri_txn_for' =>  $ri_txn_for]);
            }
        }

        return $this->db->limit($this->settings->per_page+1)
                        ->order_by('RTXN.id', 'desc')
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select(
                            // RI Transactions Table Data
                            'RTXN.*, ' .

                            // Policy Table Data
                            'P.code as policy_code, ' .

                            // Treaty Type table
                            'TT.name AS treaty_type_name'
                        )
                ->from($this->table_name . ' AS RTXN')
                ->join('dt_policies P', 'P.id = RTXN.policy_id')
                ->join('ri_setup_treaties T', 'T.id = RTXN.treaty_id')
                ->join('ri_setup_treaty_types TT', 'TT.id = T.treaty_type_id');
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

        return $this->db->where('RTXN.id', $id)
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    /**
     * Get the latest ri transaction build by policy
     *
     * i.e. sum of all transactions belonging to this policy
     *
     * @param int $policy_id
     * @param int $ri_txn_for
     * @return object
     */
    public function latest_build_by_policy($policy_id)
    {
        $sum_fields = [];
        $fields     = ['si_gross', 'si_comp_cession', 'si_treaty_total', 'si_treaty_retaintion', 'si_treaty_quota', 'si_treaty_1st_surplus', 'si_treaty_2nd_surplus', 'si_treaty_3rd_surplus', 'si_treaty_fac', 'premium_gross', 'premium_pool', 'premium_net', 'premium_comp_cession', 'premium_treaty_total', 'premium_treaty_retaintion', 'premium_treaty_quota', 'premium_treaty_1st_surplus', 'premium_treaty_2nd_surplus', 'premium_treaty_3rd_surplus', 'premium_treaty_fac', 'claim_gross', 'claim_comp_cession', 'claim_treaty_total', 'claim_treaty_retaintion', 'claim_treaty_quota', 'claim_treaty_1st_surplus', 'claim_treaty_2nd_surplus', 'claim_treaty_3rd_surplus', 'claim_treaty_fac'];

        // Build SUM Fields
        foreach($fields as $field )
        {
            $sum_fields[] = "SUM({$field}) AS {$field}";
        }
        $select =  implode(', ', $sum_fields);

        return $this->db->select($select)
                        ->from($this->table_name . ' AS RTXN')
                        ->where('RTXN.policy_id', $policy_id)
                        ->where('RTXN.ri_txn_for', $ri_txn_for)
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    public function rows_by_policy($policy_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'ri_txn_list_by_policy_'.$policy_id;
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
                        ->order_by('RTXN.id', 'DESC')
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
                'ri_txn_list_by_policy_*'
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

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}