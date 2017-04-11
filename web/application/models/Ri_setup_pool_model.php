<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_setup_pool_model extends MY_Model
{
    protected $table_name = 'ri_setup_pools';

    /**
     * Other dependent tables
     */
    protected static $table_pool_distribution   = 'ri_setup_pool_distribution';
    protected static $table_pool_portfolios     = 'ri_setup_pool_portfolios';

    protected $set_created = true;
    protected $set_modified = true;
    protected $log_user = true;

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $protected_attributes = ['id'];
    protected $fields               = ['id', 'name', 'fiscal_yr_id', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

        // Dependent Models
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
        $reinsurer_dropdown     = $this->company_model->dropdown_reinsurers();
        $portfolio_dropdown     = $this->portfolio_model->dropdown_children();

        $this->validation_rules = [

            // Master Table (Pool Setup)
            'basic' => [
                [
                    'field' => 'fiscal_yr_id',
                    'label' => 'Fiscal Year',
                    'rules' => 'trim|required|integer|max_length[3]',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
                    '_required' => true
                ],
                [
                    'field' => 'name',
                    'label' => 'Pool Title',
                    'rules' => 'trim|required|max_length[100]|callback__cb_name__check_duplicate',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],

            // Pool Portfolios
            'portfolios' => [
                [
                    'field' => 'portfolio_id[]',
                    'label' => 'Portfolio',
                    'rules' => 'trim|required|integer|max_length[8]|in_list[' . implode( ',', array_keys($portfolio_dropdown) ) . ']|callback__cb_portfolio__check_duplicate',
                    '_field'    => 'portfolio_id',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $portfolio_dropdown,
                    '_show_label'   => false,
                    '_required'     => true
                ],
                [
                    'field' => 'retention[]',
                    'label' => 'Retention(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                    '_field'            => 'retention',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'commission[]',
                    'label' => 'Commission(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                    '_field'            => 'commission',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'ib_tax[]',
                    'label' => 'IB Tax(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                    '_field'            => 'ib_tax',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ],
                [
                    'field' => 'ri_tax[]',
                    'label' => 'RI Tax(%)',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[6]',
                    '_field'            => 'ri_tax',
                    '_type'             => 'text',
                    '_show_label'   => false,
                    '_required'         => true
                ]
            ],

            // Pool Distribution
            'reinsurers' => [
                [
                    'field' => 'company_id[]',
                    'label' => 'Reinsurer',
                    'rules' => 'trim|required|integer|max_length[8]|in_list[' . implode( ',', array_keys($reinsurer_dropdown) ) . ']',
                    '_field'        => 'company_id',
                    '_type'         => 'dropdown',
                    '_show_label'   => false,
                    '_data'         => IQB_BLANK_SELECT + $reinsurer_dropdown,
                    '_required'     => true
                ],
                [
                    'field' => 'distribution_percent[]',
                    'label' => 'Distribution %',
                    'rules' => 'trim|required|prep_decimal|decimal|max_length[6]|callback__cb_distribution__complete',
                    '_field'        => 'distribution_percent',
                    '_type'         => 'text',
                    '_show_label'   => false,
                    '_required'     => true
                ]
            ],
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
     * Add New Pool Treaty
     *
     * All transactions must be carried out, else rollback.
     * The following tasks are carried during Pool Treaty Setup:
     *      a. Insert Master Record
     *      b. Insert Portfolio Configuration
     *      d. Insert Reinsurer distribution
     *
     * @param array $data
     * @return mixed
     */
    public function add($data)
    {
        /**
         * Prepare Data
         */
        $data = $this->_prepare_to_save($data);


        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $id                 = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Insert Master Record, No Validation Required as it is performed on Controller
            $id = parent::insert($data['pool'], TRUE);

            if($id)
            {

                // Insert Batch Portfolio Data
                $this->batch_insert_pool_portfolios($id, $data['portfolios']);

                // Insert Batch  Distribution Data
                $this->batch_insert_pool_distribution($id, $data['reinsurers']);

                // Log Activity
                $this->log_activity($id, 'C');
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $id = FALSE;
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

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
    public function edit($id, $data)
    {
        /**
         * Prepare Data
         */
        $data = $this->_prepare_to_save($data);

        // Disable DB Debug for transaction to work
        $this->db->db_debug = FALSE;
        $status             = FALSE;

        // Use automatic transaction
        $this->db->trans_start();

            // Task a: Update Master Record, No Validation Required as it is performed on Controller
            $status = parent::update($id, $data['pool'], TRUE);

            // Task b. Update Broker Relations
            if($status)
            {
                // Insert Batch Portfolio Data
                $this->batch_insert_pool_portfolios($id, $data['portfolios']);

                // Insert Batch  Distribution Data
                $this->batch_insert_pool_distribution($id, $data['reinsurers']);

                // Log Activity
                $this->log_activity($id, 'E');
            }

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

    private function _prepare_to_save($data)
    {
        /**
         * Prepare Data
         */
        $pool_rules         = $this->ri_setup_pool_model->get_validation_rules_formatted(['basic']);
        $portfolio_rules    = $this->ri_setup_pool_model->get_validation_rules_formatted(['portfolios']);
        $reinsurer_rules    = $this->ri_setup_pool_model->get_validation_rules_formatted(['reinsurers']);

        // Pool Data
        $pool_data = [];
        foreach($pool_rules as $rule)
        {
            $field              = $rule['field'];
            $pool_data[$field]  = $data[$field];
        }

        // Portfolios Data
        $batch_portfolio_data = [];
        $batch_count = count($data['portfolio_id']);
        for($i=0; $i < $batch_count; $i++)
        {
            $single_data = [];
            foreach($portfolio_rules as $rule)
            {
                $field              = $rule['_field'];
                $single_data[$field]  = $data[$field][$i];
            }
            $batch_portfolio_data[] = $single_data;
        }

        // Reinsurers Data
        $batch_reinsurer_data = [];
        $batch_count = count($data['company_id']);
        for($i=0; $i < $batch_count; $i++)
        {
            $single_data = [];
            foreach($reinsurer_rules as $rule)
            {
                $field              = $rule['_field'];
                $single_data[$field]  = $data[$field][$i];
            }
            $batch_reinsurer_data[] = $single_data;
        }

        return [
            'pool'          => $pool_data,
            'portfolios'    => $batch_portfolio_data,
            'reinsurers'    => $batch_reinsurer_data
        ];
    }

    // ----------------------------------------------------------------

    public function batch_insert_pool_distribution($id, $batch_data)
    {
        // Delete old distribution for this treaty if any
        $this->delete_distribution_by_pool($id);

        // Lets Insert the data
        foreach($batch_data as &$single_data)
        {
            $single_data['pool_treaty_id'] = $id;
        }

        // Insert Batch Broker Data
        if( $batch_data )
        {
            return $this->db->insert_batch(self::$table_pool_distribution, $batch_data);
        }
        return FALSE;

    }

    // ----------------------------------------------------------------

    public function delete_distribution_by_pool($id)
    {
        return $this->db->where('pool_treaty_id', $id)
                        ->delete(self::$table_pool_distribution);
    }


    // ----------------------------------------------------------------

    public function batch_insert_pool_portfolios($id, $batch_data)
    {
        // Delete old portfolios for this treaty if any
        $this->delete_portfolios_by_pool($id);

        foreach($batch_data as &$single_data)
        {
            $single_data['pool_treaty_id'] = $id;
        }

        // Insert Batch Broker Data
        if( $batch_data )
        {
            return $this->db->insert_batch(self::$table_pool_portfolios, $batch_data);
        }
        return FALSE;
    }

    // ----------------------------------------------------------------

    public function delete_portfolios_by_pool($id)
    {
        return $this->db->where('pool_treaty_id', $id)
                        ->delete(self::$table_pool_portfolios);
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
    public function _cb_portfolio__check_duplicate($fiscal_yr_id, $portfolio_id, $id=NULL)
    {
        $this->db
                ->from($this->table_name . ' AS PL')
                ->join(self::$table_pool_portfolios . ' TP', 'PL.id = TP.pool_treaty_id')
                ->where('PL.fiscal_yr_id', $fiscal_yr_id)
                ->where('TP.portfolio_id', $portfolio_id);

        if( $id )
        {
            $this->db->where('PL.id !=', $id);
        }
        return $this->db->count_all_results($this->table_name);
    }

    // ----------------------------------------------------------------

    public function row($id)
    {
        $this->_row_select();

        return $this->db->where('PL.id', $id)
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
                $this->db->where(['PL.id <=' => $next_id]);
            }

            $fiscal_yr_id = $params['fiscal_yr_id'] ?? NULL;
            if( $fiscal_yr_id )
            {
                $this->db->where(['PL.fiscal_yr_id' =>  $fiscal_yr_id]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('PL.name', $keywords, 'after');
            }
        }

        return $this->db->limit($this->settings->per_page+1)
                        ->order_by('PL.id', 'desc')
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select('PL.id, PL.name, PL.fiscal_yr_id, FY.code_en AS fy_code_en, FY.code_np AS fy_code_np')
                ->from($this->table_name . ' AS PL')
                ->join('master_fiscal_yrs FY', 'FY.id = PL.fiscal_yr_id');
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
                        'PL.*, ' .

                        // Fiscal year table
                        'FY.code_en AS fy_code_en, FY.code_np AS fy_code_np'
                        )
                ->from($this->table_name . ' AS PL')
                ->join('master_fiscal_yrs FY', 'FY.id = PL.fiscal_yr_id')
                ->where('PL.id', $id)
                ->get()->row();
    }

    // --------------------------------------------------------------------

    public function get_portfolios_by_pool($id)
    {
        return $this->db->select(
                            // Treaty Portfolio Config
                            'TP.*, ' .

                            // Portfolio Detail
                            'P.code as portfolio_code, P.name_en AS portfolio_name_en, P.name_np AS portfolio_name_np, ' .
                            'PP.code as protfolio_parent_code, PP.name_en as portfolio_parent_name_en, PP.name_np as portfolio_parent_name_np'
                            )
                        ->from(self::$table_pool_portfolios . ' AS TP')
                        ->join('master_portfolio P', 'P.id = TP.portfolio_id')
                        ->join('master_portfolio PP', 'P.parent_id = PP.id', 'left')
                        ->where('TP.pool_treaty_id', $id)
                        ->get()->result();
    }

    // --------------------------------------------------------------------

    public function get_pool_distribution_by_pool($id)
    {
        return $this->db->select('TD.pool_treaty_id, TD.company_id, TD.distribution_percent, C.name, C.picture, C.pan_no, C.active, C.type, C.contact')
                        ->from(self::$table_pool_distribution . ' TD')
                        ->join('master_companies C', 'C.id = TD.company_id')
                        ->where('TD.pool_treaty_id', $id)
                        ->get()->result();
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
            'module'    => 'ri_setup_pool',
            'module_id' => $id,
            'action'    => $action
        ];
        return $this->activity->save($activity_log);
    }
}