<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bs_report_model extends MY_Model
{
    protected $table_name = 'dt_bs_reports';

    protected $skip_validation = TRUE;

    protected $set_created  = TRUE;
    protected $set_modified = TRUE;
    protected $log_user     = FALSE;

    protected $protected_attributes = [];

    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'category', 'type', 'fiscal_yr_id', 'fy_quarter_month', 'filename', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

        $this->validation_rules();
    }

    // --------------------------------------------------------------------

    public function validation_rules()
    {
        $fy_dropdown = $this->fiscal_year_model->dropdown();
        $this->validation_rules = [
                [
                    'field' => 'category',
                    'label' => 'Report Category',
                    'rules' => 'trim|required|alpha|exact_length[2]|in_list[' . implode(',', array_keys(IQB_BS_REPORT_CATEGORIES)) . ']',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + IQB_BS_REPORT_CATEGORIES,
                    '_required' => false
                ],
                [
                    'field' => 'type',
                    'label' => 'Report Type',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list[' . implode(',', array_keys(IQB_BS_REPORT_TYPES)) . ']',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + IQB_BS_REPORT_TYPES,
                    '_required' => false
                ],
                [
                    'field' => 'fiscal_yr_id',
                    'label' => 'Fiscal Year',
                    'rules' => 'trim|required|integer|max_length[3]|in_list[' . implode(',', array_keys($fy_dropdown)) . ']',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT + $fy_dropdown,
                    '_default'  => $this->current_fiscal_year->id,
                    '_required' => false
                ],
                [
                    'field' => 'fy_quarter_month',
                    'label' => 'Quarter/Month',
                    'rules' => 'trim|required|integer|max_length[8]|callback_check_duplicate',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_BLANK_SELECT,
                    '_required' => false
                ]
            ];
    }

    // --------------------------------------------------------------------

    public function save($data)
    {
        $where = [
            'category'          => $data['category'],
            'type'              => $data['type'],
            'fiscal_yr_id'      => $data['fiscal_yr_id'],
            'fy_quarter_month'  => $data['fy_quarter_month'],
        ];

        if( !$this->check_duplicate($where) )
        {
            return parent::insert($data);
        }
        else
        {
            return parent::update_by($where, $data);
        }
    }

    // --------------------------------------------------------------------

    public function check_duplicate($where, $id=NULL)
    {
        if( $id )
        {
            $this->db->where('id !=', $id);
        }
        // $where is array ['key' => $value]
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

	// --------------------------------------------------------------------


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
        $this->db->select('R.*, FY.code_en as fy_code_en, FY.code_np as fy_code_np')
                 ->from($this->table_name . ' as R')
                 ->join('master_fiscal_yrs FY', 'FY.id = R.fiscal_yr_id');

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['R.id <=' => $next_id]);
            }

            $category = $params['category'] ?? NULL;
            if( $category )
            {
                $this->db->where(['R.category' =>  $category]);
            }

            $type = $params['type'] ?? NULL;
            if( $type )
            {
                $this->db->where(['R.type' =>  $type]);
            }

            $fiscal_year_id = $params['fiscal_year_id'] ?? NULL;
            if( $fiscal_year_id )
            {
                $this->db->where(['R.fiscal_year_id' =>  $fiscal_year_id]);
            }

            $fy_quarter_month = $params['fy_quarter_month'] ?? NULL;
            if( $fy_quarter_month )
            {
                $this->db->where(['R.fy_quarter_month' =>  $fy_quarter_month]);
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                        ->order_by('R.id', 'DESC')
                        ->get()->result();
    }

    // --------------------------------------------------------------------

    public function pending_list()
    {
        return parent::find_many_by(['status' => IQB_FLAG_OFF]);
    }

    // --------------------------------------------------------------------


    /**
     * Get Single Record
     *
     * @param int $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->db->select('R.*, FY.code_en as fy_code_en, FY.code_np as fy_code_np')
                         ->from($this->table_name . ' as R')
                         ->join('master_fiscal_yrs FY', 'FY.id = R.fiscal_yr_id')
                         ->where('R.id', $id)
                         ->get()->row();

    }

    // ----------------------------------------------------------------


    /**
     * Delete Cache on Update
     */
    public function clear_cache()
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


        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }

    // ----------------------------------------------------------------
}