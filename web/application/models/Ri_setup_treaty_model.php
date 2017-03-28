<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_setup_treaty_model extends MY_Model
{
    protected $table_name = 'ri_setup_treaties';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'name', 'fiscal_yr_id', 'treaty_type_id', 'currency_contract', 'currency_settlement', 'estimated_premium_income', 'treaty_effective_date', 'file', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

        // Set validation rule
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
                'rules' => 'trim|required|integer|exact_length[1]|callback__cb_treaty_type__check_duplicate',
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
                'field' => 'currency_contract',
                'label' => 'Contract Currency',
                'rules' => 'trim|required|alpha|max_length[10]|strtoupper',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'currency_settlement',
                'label' => 'Settlement Currency',
                'rules' => 'trim|required|alpha|max_length[10]|strtoupper',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'estimated_premium_income',
                'label' => 'Estimated Premium Income',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[20]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'treaty_effective_date',
                'label' => 'Treaty Effective Date',
                'rules' => 'trim|required|valid_date',
                '_type'     => 'date',
                '_required' => true
            ]
        ];
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
                        ->get()->result();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select('T.id, T.name, T.fiscal_yr_id, T.treaty_type_id, T.currency_contract, T.currency_settlement, T.estimated_premium_income, T.treaty_effective_date, T.file, FY.code_en AS fy_code_en, FY.code_np AS fy_code_np, TT.name AS treaty_type_name')
                ->from($this->table_name . ' as T')
                ->join('master_fiscal_yrs FY', 'FY.id = T.fiscal_yr_id')
                ->join('ri_setup_treaty_types TT', 'TT.id = T.treaty_type_id');
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
            'module'    => 'ri_setup_treaty',
            'module_id' => $id,
            'action'    => $action
        ];
        return $this->activity->save($activity_log);
    }
}