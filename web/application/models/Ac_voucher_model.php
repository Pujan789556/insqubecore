<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_voucher_model extends MY_Model
{
    protected $table_name   = 'ac_vouchers';
    protected $set_created  = true;
    protected $set_modified = false;
    protected $log_user     = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'voucher_code', 'branch_id', 'fiscal_yr_id', 'fy_quarter', 'voucher_type_id', 'voucher_date', 'narration', 'flag_internal', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
        $this->load->helper('account');

        // Set validation rule
        $this->load->model('ac_account_group_model');
        $this->load->model('ac_voucher_type_model');
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    /**
     * Set Validation Rules
     *
     * @return void
     */
    public function validation_rules()
    {
        $this->validation_rules = [

            /**
             * Voucher Basic Validation Rules
             */
            'basic' => $this->_v_rules_basic(),

            /**
             * Voucher Details Validation Rules
             */
            'details' => $this->_v_rules_details()
        ];
    }

        private function _v_rules_basic()
        {
            /**
             * @TODO - Does this user have "back-date" allowed?
             */
            $dropdown_voucher_types     = $this->ac_voucher_type_model->dropdown();
            $v_rules = [
                // Can not go back than current quarter or future
                [
                    'field' => 'voucher_date',
                    'label' => 'Voucher Date',
                    'rules' => 'trim|required|valid_date|callback__valid_voucher_date',
                    '_type'             => 'date',
                    '_default'          => date('Y-m-d'),
                    '_extra_attributes' => 'data-provide="datepicker-inline"',
                    '_required' => true
                ],
                [
                    'field' => 'voucher_type_id',
                    'label' => 'Voucher Type',
                    'rules' => 'trim|required|integer|max_length[3]|in_list[' . implode(',', array_keys($dropdown_voucher_types)) . ']',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $dropdown_voucher_types,
                    '_required' => true
                ],
                [
                    'field' => 'narration',
                    'label' => 'Narration',
                    'rules' => 'trim|max_length[300]',
                    'rows'  => '3',
                    '_type'     => 'textarea',
                    '_required' => false
                ]
            ];

            return $v_rules;
        }

        private function _v_rules_details()
        {
            $dropdown_party_types = ac_party_types_dropdown(false);
            $v_rules = [

                /**
                 * Credit Row
                 */
                'credits' => [
                    [
                        'field' => 'account_id[cr][]',
                        'label' => 'Account',
                        'rules' => 'trim|required|integer|max_length[11]',
                        '_field'    => 'account_id',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT,
                        '_extra_attributes' => 'style="width:100%; display:block"',
                        '_required' => true
                    ],
                    [
                        'field' => 'party_type[cr][]',
                        'label' => 'Party Type',
                        'rules' => 'trim|alpha|exact_length[1]|in_list[' . implode(',', array_keys($dropdown_party_types)) . ']',
                        '_field'    => 'party_type',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT + $dropdown_party_types,
                        '_extra_attributes' => 'data-field="party_type"',
                        '_required' => false
                    ],
                    [
                        'field' => 'party_id[cr][]',
                        'label' => 'Party',
                        'rules' => 'trim|integer|max_length[11]',
                        '_field'    => 'party_id',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT,
                        '_extra_attributes' => 'style="width:100%; display:block"',
                        '_required' => false
                    ],
                    [
                        'field' => 'amount[cr][]',
                        'label' => 'Credit Amount',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                        '_field'        => 'amount',
                        '_extra_attributes' => 'data-group="cr" onkeyup="setTimeout(function(){__compute_sum(this)}, 500)"',
                        '_show_label' => false,
                        '_type'         => 'text',
                        '_show_label'   => false,
                        '_required'     => true
                    ],
                ],

                /**
                 * Debit Row
                 */
                'debits' => [
                    [
                        'field' => 'account_id[dr][]',
                        'label' => 'Account',
                        'rules' => 'trim|required|integer|max_length[11]',
                        '_field'    => 'account_id',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT,
                        '_required' => true
                    ],
                    [
                        'field' => 'party_type[dr][]',
                        'label' => 'Party Type',
                        'rules' => 'trim|alpha|exact_length[1]|in_list[' . implode(',', array_keys($dropdown_party_types)) . ']',
                        '_field'    => 'party_type',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT + $dropdown_party_types,
                        '_extra_attributes' => 'data-field="party_type"',
                        '_required' => false
                    ],
                    [
                        'field' => 'party_id[dr][]',
                        'label' => 'Party',
                        'rules' => 'trim|integer|max_length[11]',
                        '_field'    => 'party_id',
                        '_show_label' => false,
                        '_type'     => 'dropdown',
                        '_data'     => IQB_BLANK_SELECT,
                        '_required' => false
                    ],
                    [
                        'field' => 'amount[dr][]',
                        'label' => 'Debit Amount',
                        'rules' => 'trim|required|prep_decimal|decimal|max_length[20]|callback__valid_voucher_amount', // compute dr = cr
                        '_field'        => 'amount',
                        '_extra_attributes' => 'data-group="dr" onkeyup="setTimeout(function(){__compute_sum(this)}, 500)"',
                        '_show_label' => false,
                        '_type'         => 'text',
                        '_show_label'   => false,
                        '_required'     => true
                    ],
                ]
            ];

            return $v_rules;
        }

    // ----------------------------------------------------------------

    public function validation_rules_formatted()
    {
        $v_rules         = $this->_v_rules_basic();
        $sectioned_rules = $this->_v_rules_details();

        foreach($sectioned_rules as $section => $rules)
        {
            $v_rules = array_merge($v_rules, $rules);
        }

        return $v_rules;
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

    public function row($id)
    {
        $this->_row_select();

        return $this->db->where('V.id', $id)
                        ->get()->row();
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

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['V.id <=' => $next_id]);
            }

            $branch_id = $params['branch_id'] ?? NULL;
            if( $branch_id )
            {
                $this->db->where(['V.branch_id' =>  $branch_id]);
            }

            $fiscal_yr_id = $params['fiscal_yr_id'] ?? NULL;
            if( $fiscal_yr_id )
            {
                $this->db->where(['V.fiscal_yr_id' =>  $fiscal_yr_id]);
            }

            $fy_quarter = $params['fy_quarter'] ?? NULL;
            if( $fy_quarter )
            {
                $this->db->where(['V.fy_quarter' =>  $fy_quarter]);
            }

            // Start Dates
            $start_date = $params['start_date'] ?? NULL;
            if( $start_date )
            {
                $this->db->where(['V.voucher_date >=' =>  $start_date]);
            }

            // End Dates
            $end_date = $params['end_date'] ?? NULL;
            if( $end_date )
            {
                $this->db->where(['V.voucher_date <=' =>  $end_date]);
            }

            // Voucher Code
            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('V.voucher_code', $keywords, 'after');
            }
        }
        return $this->db
                    ->order_by('V.id', 'DESC')
                    ->limit($this->settings->per_page+1)
                    ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select(
                        // Voucher Table
                        'V.id, V.code, V.voucher_date, ' .

                        // Voucher Type Table
                        'VT.name AS voucher_type_name, ' .

                        // Branch Table
                        'B.name AS branch_name, ' .

                        // Fiscal Year Table
                        'FY.code_en AS fy_code_en, FY.code_np AS fy_code_np'
                    )
                 ->from($this->table_name . ' AS V')
                 ->join('ac_voucher_types VT', 'VT.id = V.voucher_type_id')
                 ->join('master_branches B', 'B.id = V.branch_id')
                ->join('master_fiscal_yrs FY', 'FY.id = V.fiscal_yr_id');
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [

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
        return FALSE;
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
            'module'    => 'ac_account',
            'module_id' => $id,
            'action'    => $action
        ];
        return $this->activity->save($activity_log);
    }
}