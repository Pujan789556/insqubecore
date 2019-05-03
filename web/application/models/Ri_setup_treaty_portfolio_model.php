<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_setup_treaty_portfolio_model extends MY_Model
{
    protected $table_name = 'ri_setup_treaty_portfolios';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = [];

    // protected $before_insert = ['capitalize_code'];
    // protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['treaty_id', 'portfolio_id', 'ac_basic', 'flag_claim_recover_from_ri', 'flag_comp_cession_apply', 'comp_cession_percent', 'comp_cession_max_amt', 'comp_cession_comm_ri', 'comp_cession_tax_ri', 'comp_cession_tax_ib', 'treaty_max_capacity_amt', 'qs_max_ret_amt', 'qs_def_ret_amt', 'flag_qs_def_ret_apply', 'qs_retention_percent', 'qs_quota_percent', 'qs_lines_1', 'qs_lines_2', 'qs_lines_3', 'eol_layer_amount_1', 'eol_layer_amount_2', 'eol_layer_amount_3', 'eol_layer_amount_4'];

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
    }

    // --------------------------------------------------------------------

    /**
     * Add Portfolio for a Treaty
     *
     * @param int $treaty_id Treaty ID
     * @param int $portfolio_ids Portfolio IDs
     * @return bool
     */
    public function add_portfolios($treaty_id, $portfolio_ids)
    {
        $done  = TRUE;

        // Insert Individual - No batch-insert - because of Audit Log Requirement
        foreach($portfolio_ids as $portfolio_id )
        {
            $single_data = [
                'treaty_id'     => $treaty_id,
                'portfolio_id'  => $portfolio_id
            ];
            parent::insert($single_data, TRUE);
        }

        // return result/status
        return $done;
    }

    // ----------------------------------------------------------------

    /**
     * Save Treaty Portfolio Configuration
     *
     * All transactions must be carried out, else rollback.
     * Since the number of portfolios are fixed (which are added during treaty add/edit),
     * we will only update the configuration for existing portfolios
     *
     * @param int $treaty_id Treaty ID
     * @param array $data
     * @return mixed
     */
    public function save_portfolio_config($treaty_id, $data)
    {
        $status                     = TRUE;
        $treaty_portfolio_fillables = ['ac_basic','flag_claim_recover_from_ri', 'flag_comp_cession_apply', 'comp_cession_percent', 'comp_cession_max_amt', 'comp_cession_comm_ri', 'comp_cession_tax_ri', 'comp_cession_tax_ib', 'treaty_max_capacity_amt', 'qs_max_ret_amt', 'qs_def_ret_amt', 'flag_qs_def_ret_apply', 'qs_retention_percent', 'qs_quota_percent', 'qs_lines_1', 'qs_lines_2', 'qs_lines_3', 'eol_layer_amount_1', 'eol_layer_amount_2', 'eol_layer_amount_3', 'eol_layer_amount_4'];

        $total_portfolios           = count($data['portfolio_ids']);

        // -----------------------------------------------------------------------------

        // Use automatic transaction
        $this->db->trans_start();

            for($i = 0; $i < $total_portfolios; $i++)
            {
                $portfolio_id = $data['portfolio_ids'][$i];
                $treaty_portfolio_data = [];

                // Prepare Update Data
                foreach($treaty_portfolio_fillables as $column)
                {
                    $treaty_portfolio_data[$column] = $data[$column][$i] ?? NULL; // Reset to Default
                }

                // Old Data - For Audit Record
                $where = ['treaty_id' => $treaty_id, 'portfolio_id' => $portfolio_id];
                $this->audit_old_record = parent::find_by($where);

                // Save Database without MY Model ( as it can not find audit_old_record where there is no PK as id)
                $this->db->where($where)
                         ->set($treaty_portfolio_data)
                         ->update($this->table_name);

                 // Save Audit Log
                 $this->save_audit_log([
                    'method' => 'update',
                    'id'     => NULL,
                    'fields' => $treaty_portfolio_data
                ],$where);
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // -----------------------------------------------------------------------------

        // return result/status
        return $status;
    }

    // --------------------------------------------------------------------

    public function get_many_by_treaty($treaty_id)
    {
        return $this->_select()
                    ->where('T.id', $treaty_id)
                    ->get()->result();
    }

        // --------------------------------------------------------------------

        private function _select()
        {
            return $this->db->select(
                                // Treaty Details
                                'T.id, T.name as treaty_name, T.category, T.fiscal_yr_id, T.treaty_type_id, T.treaty_effective_date, ' .

                                // Treaty Portfolio Config
                                'TP.treaty_id, TP.portfolio_id, TP.ac_basic, TP.flag_claim_recover_from_ri, TP.flag_comp_cession_apply, TP.comp_cession_percent, TP.comp_cession_max_amt, TP.comp_cession_comm_ri, TP.comp_cession_tax_ri, TP.comp_cession_tax_ib, TP.treaty_max_capacity_amt, TP.qs_max_ret_amt, TP.qs_def_ret_amt, TP.flag_qs_def_ret_apply, TP.qs_retention_percent, TP.qs_quota_percent, TP.qs_lines_1, TP.qs_lines_2, TP.qs_lines_3, TP.eol_layer_amount_1, TP.eol_layer_amount_2, TP.eol_layer_amount_3, TP.eol_layer_amount_4, ' .

                                // Portfolio Detail
                                'P.code as portfolio_code, P.name_en AS portfolio_name_en, P.name_np AS portfolio_name_np, ' .
                                'PP.code as protfolio_parent_code, PP.name_en as portfolio_parent_name_en, PP.name_np as portfolio_parent_name_np'
                                )
                            ->from($this->table_name . ' AS TP')
                            ->join('ri_setup_treaties T', 'T.id = TP.treaty_id' )
                            ->join('master_portfolio P', 'P.id = TP.portfolio_id')
                            ->join('master_portfolio PP', 'P.parent_id = PP.id', 'left');
        }

    // --------------------------------------------------------------------

    /**
     * Get Portfolio Treaty for Given Fiscal Year for Given Category
     *
     * @param int $portfolio_id
     * @param int $fiscal_yr_id
     * @param int $category
     * @return object
     */
    public function get_portfolio_treaty($portfolio_id, $fiscal_yr_id, $category)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var  = 'ri_pt_' . $portfolio_id . '_' . $fiscal_yr_id . '_' . $category;
        $row        = $this->get_cache($cache_var);
        if(!$row)
        {
            $row = $this->_select()
                        ->where('T.category', $category)
                        ->where('T.fiscal_yr_id', $fiscal_yr_id)
                        ->where('P.id', $portfolio_id)
                        ->get()->row();
            if($row)
            {
                $this->write_cache($row, $cache_var, CACHE_DURATION_HR);
            }
        }
        return $row;
    }

    // --------------------------------------------------------------------

    public function portfolio_dropdown($treaty_id)
    {
        $list = $this->db->select(
                                // Treaty Portfolio Config
                                'TP.treaty_id, TP.portfolio_id, ' .

                                // Portfolio Detail
                                'P.name_en AS portfolio_name_en'
                                )
                            ->from($this->table_name . ' AS TP')
                            ->join('master_portfolio P', 'P.id = TP.portfolio_id')
                            ->where('TP.treaty_id', $treaty_id)
                            ->get()->result();
        $portfolios = [];
        foreach($list as $record)
        {
            $portfolios["{$record->portfolio_id}"] = $record->portfolio_name_en;
        }
        return $portfolios;
    }


	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'ri_pt_*'
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function delete_portfolios($treaty_id, $portfolio_ids)
    {
        $status         = FALSE;
        $old_records    = [];
        if($portfolio_ids)
        {
            $old_records = $this->db->where('treaty_id', $treaty_id)
                                    ->where_in('portfolio_id', $portfolio_ids)
                                    ->get($this->table_name)
                                    ->result();
        }

        if($old_records)
        {
            // Delete The Records
            $status = $this->db->where('treaty_id', $treaty_id)
                             ->where_in('portfolio_id', $portfolio_ids)
                             ->delete($this->table_name);

            // Manually Audit Log
            foreach($old_records as $single)
            {
                $this->audit_old_record = $single;
                $this->save_audit_log([
                    'method' => 'delete',
                    'id' => NULL
                ]);
                $this->audit_old_record = NULL;
            }
        }

        return $status;
    }
}