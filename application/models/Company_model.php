<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_model extends MY_Model
{
    protected $table_name = 'master_companies';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['prepare_contact_data'];
    protected $before_update = ['prepare_contact_data'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ["id", "name", "picture", "ud_code", "pan_no", "active", "type", "contact", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name',
            'label' => 'Company Name',
            'rules' => 'trim|required|max_length[80]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'ud_code',
            'label' => 'Company UD Code',
            'rules' => 'trim|integer|max_length[15]',
            '_type'     => 'text',
            '_required' => false
        ],
        [
            'field' => 'pan_no',
            'label' => 'Company Pan No',
            'rules' => 'trim|max_length[20]',
            '_type'     => 'text',
            '_required' => false
        ],
        [
            'field' => 'type',
            'label' => 'Company Type',
            'rules' => 'trim|required|alpha|exact_length[1]|in_list[B,L,R]',
            '_type'     => 'dropdown',
            '_data'     => [ '' => 'Select...', 'B' => 'Type B', 'L' => 'Type L', 'R' => 'Type R'],
            '_required' => true
        ],
        [
            'field' => 'active',
            'label' => 'Is Active?',
            'rules' => 'trim|required|integer|exact_length[1]',
            '_type'     => 'dropdown',
            '_data'     => [ '' => 'Select...', '1' => 'Active', '0' => 'Not Active'],
            '_required' => true
        ]
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 86; // First 86; i.e. imported old data

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
        $this->db->select('C.id, C.name, C.ud_code, C.pan_no, C.type, C.active')
                 ->from($this->table_name . ' as C');

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['C.id >=' => $next_id]);
            }

            $ud_code = $params['ud_code'] ?? NULL;
            if( $ud_code )
            {
                $this->db->where(['C.ud_code' =>  $ud_code]);
            }

            $type = $params['type'] ?? NULL;
            if( $type )
            {
                $this->db->where(['C.type' =>  $type]);
            }

            $active = $params['active'];
            $active = $active === '' ? NULL : $active; // to work with 0 value
            if( $active !== NULL )
            {
                $this->db->where(['C.active' =>  $active]);
            }

            $pan_no = $params['pan_no'] ?? NULL;
            if( $pan_no )
            {
                $this->db->where(['C.pan_no' =>  $pan_no]);
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
            // 'companies_all',
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
            'module' => 'company',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}