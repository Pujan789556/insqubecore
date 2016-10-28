<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class District_model extends MY_Model
{
    protected $table_name = 'master_districts';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_update  = ['clear_cache'];

    protected $fields = ["id", "name_en", "name_np", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
            'field' => 'name_en',
            'label' => 'Name (EN)',
            'rules' => 'trim|required|max_length[80]',
            '_type' => 'text',
            '_required' => true
        ],
        [
            'field' => 'name_np',
            'label' => 'Name (NP)',
            'rules' => 'trim|max_length[80]',
            '_type' => 'text',
            '_required' => true
        ]
    ];


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
        $list = $this->get_cache('districts_all');
        if(!$list)
        {
            $list = parent::find_all();
            $this->write_cache($list, 'districts_all', CACHE_DURATION_DAY);
        }
        return $list;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
    	// cache name without prefix
        return $this->delete_cache('districts_all');
    }

    // ----------------------------------------------------------------

    /**
     * Log Activity
     *
     * Log activities
     *      Available Activities: Edit
     *
     * @param integer $id
     * @param string $action
     * @return bool
     */
    public function log_activity($id, $action = 'E')
    {
        $action = is_string($action) ? $action : 'E';
        // Save Activity Log
        $activity_log = [
            'module' => 'district',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}