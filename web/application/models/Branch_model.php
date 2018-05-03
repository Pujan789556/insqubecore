<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Branch_model extends MY_Model
{
    protected $table_name = 'master_branches';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['capitalize_code', 'prepare_contact_data'];
    protected $before_update = ['capitalize_code', 'prepare_contact_data'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];


    protected $fields = ['id', 'name_en', 'name_np', 'code', 'estd', 'contacts', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [
        [
            'field' => 'name_en',
            'label' => 'Branch Name (EN)',
            'rules' => 'trim|required|max_length[80]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'name_np',
            'label' => 'Branch Name (NP)',
            'rules' => 'trim|required|max_length[100]',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'code',
            'label' => 'Branch Code',
            'rules' => 'trim|required|alpha|max_length[4]|strtoupper|callback_check_duplicate',
            '_type'     => 'text',
            '_required' => true
        ],
        [
            'field' => 'estd',
            'label' => 'Estlablished',
            'rules' => 'trim|required|max_length[20]',
            '_type'     => 'text',
            '_required' => true
        ],
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 28; // Prevent first 28 records from deletion.

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

    public function get_all()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('branches_all');
        if(!$list)
        {
            $list = parent::find_all();
            $this->write_cache($list, 'branches_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function capitalize_code($data)
    {
        $code_cols = array('code');
        foreach($code_cols as $col)
        {
            if( isset($data[$col]) && !empty($data[$col]) )
            {
                $data[$col] = strtoupper($data[$col]);
            }
        }
        return $data;
    }

    // ----------------------------------------------------------------

    public function prepare_contact_data($data)
    {
        $data['contacts'] = get_contact_data_from_form();
        return $data;
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
     * Get Dropdown List
     */
    public function dropdown()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $records = $this->get_all();
        $list = [];
        foreach($records as $record)
        {
            $list["{$record->id}"] = $record->name_en . ' (' . $record->name_np . ')';
        }
        return $list;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [
            'branches_all'
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