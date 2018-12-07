<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Claim_settlement_model extends MY_Model
{
    protected $table_name = 'dt_claim_settlements';

    protected $set_created  = true;
    protected $set_modified = true;
    protected $log_user     = true;

    protected $protected_attributes = ['id'];


    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'claim_id', 'category', 'sub_category', 'title', 'claimed_amount', 'assessed_amount', 'recommended_amount', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
        $category_dropdown      = CLAIM__settlement_category_dropdown(FALSE);
        $subcategory_dropdown   = CLAIM__settlement_subcategory_dropdown(FALSE);

        $this->validation_rules = [
            [
                'field' => 'category[]',
                '_key' => 'category',
                'label' => 'Category',
                'rules' => 'trim|required|alpha|in_list['.implode(',', array_keys($category_dropdown)).']',
                '_type' => 'dropdown',
                '_data'         => IQB_BLANK_SELECT + $category_dropdown,
                '_show_label'   => false,
                '_required'     => true
            ],
            [
                'field' => 'sub_category[]',
                '_key' => 'sub_category',
                'label' => 'Sub-Category',
                'rules' => 'trim|required|alpha|in_list['.implode(',', array_keys($subcategory_dropdown)).']',
                '_type' => 'dropdown',
                '_data'         => IQB_BLANK_SELECT + $subcategory_dropdown,
                '_show_label'   => false,
                '_required'     => true
            ],
            [
                'field' => 'title[]',
                '_key'  => 'title',
                'label' => 'Title',
                'rules' => 'trim|required|htmlspecialchars|max_length[200]',
                '_key'  => 'title',
                '_type' => 'text',
                '_show_label'   => false,
                '_required'     => true
            ],
            [
                'field' => 'claimed_amount[]',
                'label' => 'Claimed Amount',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_key'  => 'claimed_amount',
                '_type' => 'text',
                '_show_label'   => false,
                '_required'     => true
            ],
            [
                'field' => 'assessed_amount[]',
                'label' => 'Assessed Amount',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_key'  => 'assessed_amount',
                '_type' => 'text',
                '_show_label'   => false,
                '_required'     => true
            ],
            [
                'field' => 'recommended_amount[]',
                'label' => 'Recommended / Settled Amount',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_key'  => 'recommended_amount',
                '_type' => 'text',
                '_show_label'   => false,
                '_required'     => true
            ],
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Assign Settlement on a Claim
     *
     * @param int $claim_id
     * @param array $data
     * @return mixed
     */
    public function assign_to_claim( $claim_id, $data )
    {
        $this->load->model('claim_model');

        /**
         * Variables
         */
        $net_amt_payable_insured = 0.00;


        /**
         * Prepare Batch Data
         */
        $batch_data = [];
        $columns    = ['category', 'sub_category', 'title', 'claimed_amount', 'assessed_amount', 'recommended_amount'];
        $count      = count($data['category']);
        for($index=0; $index < $count; $index++)
        {
            $single = [
                'claim_id'      => $claim_id,
            ];
            foreach($columns as $key)
            {
                $single[$key] = $data[$key][$index];
            }
            $batch_data[] = $single;

            /**
             * Total Claim Settlement Amount
             *
             * NOTE: Excess Deductible is Subtracted from Total
             */
            $category           = $single['category'];
            $recommended_amount = $single['recommended_amount'];
            if($category == 'ED')
            {
                $net_amt_payable_insured = bcsub($net_amt_payable_insured, $recommended_amount, IQB_AC_DECIMAL_PRECISION);
            }
            else
            {
                $net_amt_payable_insured = bcadd($net_amt_payable_insured, $recommended_amount, IQB_AC_DECIMAL_PRECISION);
            }
        }


        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $done               = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            /**
             * Task 1: Delete old Records
             */
            parent::delete_by(['claim_id' => $claim_id]);
            $this->db->where('claim_id', $claim_id)
                        ->delete($this->table_name);


            /**
             * Task 2: Batch insert new data (if any)
             */
            if($batch_data)
            {
                parent::insert_batch($batch_data, TRUE);
            }

            /**
             * Task 3: Update Total Surveyor Fee On Claim Table
             */
            $claim_data = [
                'net_amt_payable_insured' => $net_amt_payable_insured
            ];
            $this->claim_model->update_data($claim_id, $claim_data);


            /**
             * Task 4: Clear cache for this claim
             */
            $this->clear_cache( 'sttlmnt_lstbyclm_' . $claim_id );


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

    public function get_many_by_claim($claim_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'sttlmnt_lstbyclm_' . $claim_id;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $list = $this->db->select( 'CS.*' )
                            ->from($this->table_name . ' CS')
                            ->where('CS.claim_id', $claim_id)
                            ->get()
                            ->result();

            $this->write_cache($list, $cache_var, CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    /**
     * Compute Net Payable Amount
     *
     * @param int $claim_id
     * @return decimal
     */
    public function compute_net_payable($claim_id)
    {
        $list = $this->get_many_by_claim($claim_id);
        $net_payable = 0.00;

        foreach($list as $single)
        {
            $category           = $single->category;
            $recommended_amount = $single->recommended_amount;
            if($category == 'ED')
            {
                $net_payable = bcsub($net_payable, $recommended_amount, IQB_AC_DECIMAL_PRECISION);
            }
            else
            {
                $net_payable = bcadd($net_payable, $recommended_amount, IQB_AC_DECIMAL_PRECISION);
            }
        }

        return $net_payable;
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
            'sttlmnt_lstbyclm_*'
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
            'module' => 'claim_settlement',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}