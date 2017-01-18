<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_branch_model extends MY_Model
{
    protected $table_name = 'master_company_branches';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['prepare_contact_data'];
    protected $before_update = ['prepare_contact_data'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "company_id", "name", "contact", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name',
            'label' => 'Branch Name',
            'rules' => 'trim|required|max_length[50]',
            '_type'     => 'text',
            '_required' => true
        ]
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0; // First 86; i.e. imported old data

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

    public function prepare_contact_data($data)
    {
        $data['contact'] = get_contact_data_from_form();
        return $data;
    }

    // ----------------------------------------------------------------

    /**
     * Get all branches for specified company
     *
     * @param integer $company_id
     * @return mixed
     */
    public function get_by_company( $company_id )
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_name = 'branch_company_' . $company_id;

        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $list = $this->db->select('C.id, C.company_id, C.name, C.contact')
                                ->from($this->table_name . ' as C')
                                ->where('C.company_id', $company_id)
                                ->order_by('C.name', 'asc')
                                ->get()->result();

            $this->write_cache($list, $cache_name, CACHE_DURATION_DAY);
        }
        return $list;
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
        $this->db->select('C.id, C.company_id, C.name, C.contact')
                 ->from($this->table_name . ' as C');

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['C.id >=' => $next_id]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('C.name', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [
            'branch_company_*'
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
        $action = is_string($action) ? $action : 'C';
        // Save Activity Log
        $activity_log = [
            'module' => 'company_branch',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}