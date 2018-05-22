<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fy_month_model extends MY_Model
{
    protected $table_name = 'master_fy_months';

    protected $set_created  = true;
    protected $set_modified = true;
    protected $log_user     = true;
    protected $skip_validation = TRUE;
    protected $protected_attributes = ['id'];

    protected $before_insert = [];
    protected $before_update = [];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'fiscal_yr_id', 'month_id', 'starts_at', 'ends_at', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0; // Prevent first 100 records from deletion.

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Dependent model
        $this->load->model('month_model');

        // Load validation rules
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $month_dropdown = $this->month_model->dropdwon();
        $month_ids = array_keys($month_dropdown);

        $this->validation_rules = [
            [
                'field' => 'month_id[]',
                '_field' => 'month_id',
                'label' => 'Month',
                'rules' => 'trim|required|integer|max_length[2]|in_list['. implode(',', $month_ids) .']|callback__cb_valid_month',
                '_type'     => 'hidden',
                '_default'  => '',
                '_show_label'   => false,
                '_required' => true
            ],
            [
                'field' => 'starts_at[]',
                '_field' => 'starts_at',
                'label' => 'Start Date',
                'rules' => 'trim|required|valid_date',
                '_type'             => 'date',
                '_extra_attributes' => 'data-provide="datepicker-inline"',
                '_show_label'   => false,
                '_required' => true
            ],
            [
                'field' => 'ends_at[]',
                '_field' => 'ends_at',
                'label' => 'End Date',
                'rules' => 'trim|required|valid_date|callback__cb_valid_dates',
                '_type'             => 'date',
                '_extra_attributes' => 'data-provide="datepicker-inline"',
                '_show_label'   => false,
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Save Fiscal Year Months
     *
     * This function save all the months for a fiscal year.
     *
     * @param array $data batch data
     * @return bool
     */
    public function save($data)
    {
        // Disable DB Debug for transaction to work
        $this->db->db_debug = TRUE;
        $status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();


            foreach($data as $single)
            {
                $where = [
                    'fiscal_yr_id'  => $single['fiscal_yr_id'],
                    'month_id'      => $single['month_id']
                ];

                if($this->check_duplicate($where))
                {
                    parent::update_by($single, $where);
                }
                else
                {
                    parent::insert($single);
                }
            }

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

    /**
     * List all headings for portfolio
     *
     * @param int $fiscal_yr_id
     * @param string $for
     * @return type
     */
    public function by_fiscal_year($fiscal_yr_id)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'fy_month_fy_' . $fiscal_yr_id;
        $list = $this->get_cache($cache_var);
        if(!$list)
        {
            $where = [ 'fiscal_yr_id' => $fiscal_yr_id ];
            $list = parent::find_many_by($where);

            $this->write_cache($list, $cache_var, CACHE_DURATION_6HRS);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    /**
     * Dropdown by portfolio.
     *
     * @param inte $portfolio_id
     * @return array
     */
    public function dropdwon_by_fiscal_year($portfolio_id)
    {
        $records = $this->by_fiscal_year($portfolio_id);

        $list = [];
        foreach($records as $record)
        {
            $list["{$record->id}"] = $record->name_np . ' (' . $record->name_en . ')';
        }
        return $list;
    }


    // ----------------------------------------------------------------

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
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'fy_month_fy_*'
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
}