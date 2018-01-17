<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Forex_model extends MY_Model
{
    protected $table_name = 'master_forex_rates';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];


    protected $fields = ['id', 'exchange_date', 'exchange_rates', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0; // Prevent first 28 records from deletion.

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

    // ----------------------------------------------------------------

    public function duplicate_v_rules()
    {
        return [
            [
                'field' => 'exchange_date',
                'label' => 'Exchange Date',
                'rules' => 'trim|required|valid_date|callback__cb_valid_exchange_date',
                '_type'             => 'date',
                '_default'          => date('Y-m-d'),
                '_extra_attributes' => 'data-provide="datepicker-inline"',
                '_required' => true
            ]
        ];
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
                $this->db->where(['F.id <=' => $next_id]);
            }

            $exchange_date = $params['exchange_date'] ?? NULL;
            if( $exchange_date )
            {
                $this->db->where(['F.exchange_date' =>  $exchange_date]);
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->order_by('F.id', 'desc')
                    ->get()->result();
    }

    // --------------------------------------------------------------------

    public function row($id)
    {
        $this->_row_select();
        return $this->db->where('F.id', $id)->get()->row();
    }

    // --------------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select('F.*')
                 ->from($this->table_name . ' as F');
    }

    // --------------------------------------------------------------------

    public function check_duplicate($where, $ids=NULL)
    {
        if( $ids )
        {
            $this->db->where_not_in('id', $ids);
        }
        // $where is array ['key' => $value]
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
    }

    // --------------------------------------------------------------------
    public function get_by_date($date)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var = 'forex_rates_' . str_replace('-', '', $date);
        $row = $this->get_cache($cache_var);
        if(!$row)
        {
            $row = parent::find_by(['exchange_date' => $date]);
            $this->write_cache($row, $cache_var, CACHE_DURATION_DAY);
        }
        return $row;
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [
            'forex_rates_*'
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
            'module' => 'exchange_rate',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}