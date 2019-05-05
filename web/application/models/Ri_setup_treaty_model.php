<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_setup_treaty_model extends MY_Model
{
    protected $table_name = 'ri_setup_treaties';

    /**
     * Other dependent tables
     */
    protected static $table_treaty_types                    = 'ri_setup_treaty_types';
    protected static $table_treaty_brokers                  = 'ri_setup_treaty_brokers';
    protected static $table_treaty_distribution             = 'ri_setup_treaty_distribution';
    protected static $table_treaty_portfolios               = 'ri_setup_treaty_portfolios';
    protected static $table_comp_cession_distribution       = 'ri_setup_comp_cession_distribution';
    protected static $table_treaty_tax_and_commission       = 'ri_setup_treaty_tax_and_commission';


    protected $set_created = true;
    protected $set_modified = true;
    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $protected_attributes = ['id'];
    protected $fields = ['id', 'name', 'category', 'fiscal_yr_id', 'treaty_type_id', 'estimated_premium_income', 'treaty_effective_date', 'file', 'commission_scales', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

        $this->load->model('ri_setup_treaty_type_model');
        $this->load->model('ri_setup_treaty_broker_model');
        $this->load->model('ri_setup_treaty_portfolio_model');
        $this->load->model('ri_setup_treaty_tax_and_commission_model');
        $this->load->model('ri_setup_treaty_distribution_model');
        $this->load->model('ri_setup_comp_cession_distribution_model');

        $this->load->model('company_model');
        $this->load->model('portfolio_model');

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
        $broker_dropdown        = $this->company_model->dropdown_brokers();
        $portfolio_dropdown     = $this->portfolio_model->dropdown_children();

        $this->validation_rules = [

            // Master Table (Treaty Setup)
            'basic' => [
                [
                    'field' => 'category',
                    'label' => 'Treaty For',
                    'rules' => 'trim|required|integer|exact_length[1]|in_list['.implode(',', array_keys(IQB_RI_TREATY_CATEGORIES)).']',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + IQB_RI_TREATY_CATEGORIES,
                    '_required' => true
                ],
                [
                    'field' => 'fiscal_yr_id',
                    'label' => 'Fiscal Year',
                    'rules' => 'trim|required|integer|max_length[3]',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
                    '_required' => true
                ],
                [
                    'field' => 'treaty_type_id',
                    'label' => 'Treaty Type',
                    'rules' => 'trim|required|integer|exact_length[1]',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $this->ri_setup_treaty_type_model->dropdown(),
                    '_required' => true
                ],
                [
                    'field' => 'name',
                    'label' => 'Treaty Title',
                    'rules' => 'trim|required|max_length[100]|callback__cb_name__check_duplicate',
                    '_type'     => 'text',
                    '_required' => true
                ],

                [
                    'field' => 'estimated_premium_income',
                    'label' => 'Estimated Premium Income',
                    'rules' => 'trim|prep_decimal|decimal|max_length[20]',
                    '_type'     => 'text',
                    '_required' => false
                ],
                [
                    'field' => 'treaty_effective_date',
                    'label' => 'Treaty Effective Date',
                    'rules' => 'trim|required|valid_date',
                    '_type'     => 'date',
                    '_required' => true
                ],
            ],

            // Broker List
            'brokers' => [
                [
                    'field' => 'broker_ids[]',
                    'label' => 'Re-insurance Broker',
                    'rules' => 'trim|required|integer|max_length[8]|in_list[' . implode( ',', array_keys($broker_dropdown) ) . ']',
                    '_type'     => 'checkbox',
                    '_data'     => $broker_dropdown,
                    '_show_label'   => false,
                    '_required'     => true
                ]
            ],

            // Portfolio List
            'portfolios' => [
                [
                    'field' => 'portfolio_ids[]',
                    'label' => 'Portfolio',
                    'rules' => 'trim|required|integer|max_length[8]|in_list[' . implode( ',', array_keys($portfolio_dropdown) ) . ']|callback__cb_portfolio__check_duplicate',
                    '_type'     => 'checkbox',
                    '_data'     => $portfolio_dropdown,
                    '_show_label'   => false,
                    '_required'     => true
                ]
            ],


            // Commission Scale
            'commission_scale' => [
                [
                    'field' => 'name[]',
                    'label' => 'Title',
                    'rules' => 'trim|required|max_length[100]',
                    '_field'        => 'name',
                    '_type'         => 'text',
                    '_show_label'   => false,
                    '_required'     => true
                ],
                [
                    'field' => 'scale_min[]',
                    'label' => 'Minimum Scale(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                    '_field'            => 'scale_min',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'scale_max[]',
                    'label' => 'Maximum Scale(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                    '_field'            => 'scale_max',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'rate[]',
                    'label' => 'Commission Rate(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                    '_field'            => 'rate',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ]
            ]
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Get Validation Rules (Sectioned)
     *
     * @param array|string $sections
     * @return array
     */
    public function get_validation_rules($sections)
    {
        // If a single section is supplied, convert it into array
        $sections = is_array($sections) ? $sections : array($sections);
        $v_rules = [];
        foreach( $sections as $section)
        {
            $v_rules[$section] = $this->validation_rules[$section];
        }
        return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Get Validation Rules Formatted from Supplied sections to run Form Validation
     *
     * @param array  $sectioned_rules
     * @return void
     */
    public function get_validation_rules_formatted($sections)
    {
        $sectioned_rules = $this->get_validation_rules($sections);
        $v_rules_formatted = [];
        foreach($sectioned_rules as $section=>$rules)
        {
            $v_rules_formatted = array_merge($v_rules_formatted, $rules);
        }
        return $v_rules_formatted;
    }

    // ----------------------------------------------------------------

    /**
     * Add New Treaty
     *
     * All transactions must be carried out, else rollback.
     * The following tasks are carried during Treaty Setup:
     *      a. Insert Master Record
     *      b. Insert Broker Relation Records
     *      c. Insert Portfolio Configuration (Empty Record)
     *
     * @param array $data
     * @return mixed
     */
    public function add($data)
    {
        // Extract All Brokers, Portfolios
        $broker_ids     = $data['broker_ids'];
        $portfolio_ids  = $data['portfolio_ids'];

        // Remove unused fields
        unset($data['broker_ids']);
        unset($data['portfolio_ids']);

        // ----------------------------------------------------------------------

        $id                 = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Insert Master Record, No Validation Required as it is performed on Controller
            $id = parent::insert($data, TRUE);

            // Task b. Insert Broker Relations
            if($id)
            {
                // Add Brokers
                $this->ri_setup_treaty_broker_model->add_brokers($id, $broker_ids);

                // Insert Batch Portfolio Data
                $this->ri_setup_treaty_portfolio_model->add_portfolios($id, $portfolio_ids);

                // Insert Default Tax and Commission Record
                $this->ri_setup_treaty_tax_and_commission_model->insert(['treaty_id' => $id], TRUE);

            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $id = FALSE;
        }

        // return result/status
        return $id;
    }

    // ----------------------------------------------------------------

    /**
     * Edit Treaty
     *
     * All transactions must be carried out, else rollback.
     * The following tasks are carried during Treaty Setup:
     *      a. Update Master Record
     *      b. Update Broker Relation Records
     *      c. Update Portfolio Configuration
     *          - Old Records (if selected) - leave intact
     *          - New Records - empty
     *
     * @param array $data
     * @param array $old_data   Old reference data, such as old_portfolios, old_brokers etc.
     * @return mixed
     */
    public function edit($id, $data, $old_data)
    {
        // Extract All Brokers, Portfolios
        $broker_ids     = $data['broker_ids'];
        $portfolio_ids  = $data['portfolio_ids'];

        // Remove unused fields
        unset($data['broker_ids']);
        unset($data['portfolio_ids']);

        // Find To Insert/Delete Portfolios
        $to_insert_portfolios = array_diff($portfolio_ids, $old_data['old_portfolios']);
        $to_delete_portfolios = array_diff($old_data['old_portfolios'], $portfolio_ids);


        // Find To Insert/Delete Brokers
        $to_insert_brokers = array_diff($broker_ids, $old_data['old_brokers']);
        $to_delete_brokers = array_diff($old_data['old_brokers'], $broker_ids);

        // ----------------------------------------------------------------------

        $status             = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Update Master Record, No Validation Required as it is performed on Controller
            $status = parent::update($id, $data, TRUE);

            // Task b. Update Broker Relations
            if($status)
            {
                // Delete Unselected Brokers
                $this->ri_setup_treaty_broker_model->delete_brokers($id, $to_delete_brokers);

                // Add New Brokers
                $this->ri_setup_treaty_broker_model->add_brokers($id, $to_insert_brokers);


                // Delete Unselected Portfolios
                $this->ri_setup_treaty_portfolio_model->delete_portfolios($id, $to_delete_portfolios);

                // Add New Portfolios
                $this->ri_setup_treaty_portfolio_model->add_portfolios($id, $to_insert_portfolios);
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // return result/status
        return $status;
    }


    // ----------------------------------------------------------------

    /**
     * Save Commission Scale of a Treaty
     *
     * @param integer $id Treaty ID
     * @param array $data
     * @return bool
     */
    public function save_commission_scales($id, $data)
    {
        // Prepare JSON for commission scale
        $total_count = count($data['name']);
        $scale_data = [];
        $json = [];
        for($i = 0; $i<$total_count; $i++)
        {
            $json[] = [
                'name'      => $data['name'][$i],
                'scale_min' => $data['scale_min'][$i],
                'scale_max' => $data['scale_max'][$i],
                'rate'      => $data['rate'][$i],
            ];
        }
        $scale_data['commission_scales'] = $json ? json_encode($json) : NULL;


        $status             = TRUE;
        // Use automatic transaction
        $this->db->trans_start();

            parent::update($id, $scale_data, TRUE);

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
     * Callback Portfolio: Check Duplicate
     *
     * Checks if the supplied portfolio only exists for 1 time per fiscal year.
     * i.e. a portfolio can only be associated with one Treaty per Fiscal Year.
     *
     * @param array $where
     * @param integernull $id
     * @return bool
     */
    public function _cb_portfolio__check_duplicate($category, $fiscal_yr_id, $portfolio_id, $id=NULL)
    {
        $this->db
                ->from($this->table_name . ' AS T')
                ->join(self::$table_treaty_portfolios . ' TP', 'T.id = TP.treaty_id')
                ->where('T.category', $category)
                ->where('T.fiscal_yr_id', $fiscal_yr_id)
                ->where('TP.portfolio_id', $portfolio_id);


        if( $id )
        {
            $this->db->where('T.id !=', $id);
        }
        return $this->db->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    public function row($id)
    {
        $this->_row_select();

        return $this->db->where('T.id', $id)
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

        /**
         * Apply Filter
         */
        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['T.id <=' => $next_id]);
            }

            $category = $params['category'] ?? NULL;
            if( $category )
            {
                $this->db->where(['T.category' =>  $category]);
            }

            $fiscal_yr_id = $params['fiscal_yr_id'] ?? NULL;
            if( $fiscal_yr_id )
            {
                $this->db->where(['T.fiscal_yr_id' =>  $fiscal_yr_id]);
            }

            $treaty_type_id = $params['treaty_type_id'] ?? NULL;
            if( $treaty_type_id )
            {
                $this->db->where(['T.treaty_type_id' =>  $treaty_type_id]);
            }
        }

        return $this->db->limit($this->settings->per_page+1)
                        ->order_by('T.id', 'desc')
                        ->order_by('T.fiscal_yr_id', 'desc')
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select('T.id, T.name, T.category, T.fiscal_yr_id, T.treaty_type_id, T.estimated_premium_income, T.treaty_effective_date, T.file, FY.code_en AS fy_code_en, FY.code_np AS fy_code_np, TT.name AS treaty_type_name')
                ->from($this->table_name . ' AS T')
                ->join('master_fiscal_yrs FY', 'FY.id = T.fiscal_yr_id')
                ->join(self::$table_treaty_types . ' TT', 'TT.id = T.treaty_type_id');
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
        return $this->db->select(

                        // Main table -  all fields
                        'T.*, ' .

                        // Treaty Tax and Commission - all fields except treaty_id
                        'TTNC.*, ' .

                        // Fiscal year table
                        'FY.code_en AS fy_code_en, FY.code_np AS fy_code_np, ' .

                        // Treaty Type table
                        'TT.name AS treaty_type_name'
                        )
                ->from($this->table_name . ' AS T')
                ->join(self::$table_treaty_tax_and_commission . ' TTNC', 'TTNC.treaty_id = T.id')
                ->join('master_fiscal_yrs FY', 'FY.id = T.fiscal_yr_id')
                ->join(self::$table_treaty_types . ' TT', 'TT.id = T.treaty_type_id')
                ->where('T.id', $id)
                ->get()->row();
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
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
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}