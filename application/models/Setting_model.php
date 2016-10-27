<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting_model extends MY_Model
{


    protected $table_name = 'master_settings';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_update  = ['clear_cache'];

    protected $fields = ["id", "organization", "address", "pan_no", "logo", "per_page", "flag_offline", "offline_message", "admin_email", "from_email", "replyto_email", "noreply_email", "website", "created_at", "created_by", "updated_at", "updated_by"];

    protected $validation_rules = [
        [
                'field' => 'organization',
                'label' => 'Organization Name',
                'rules' => 'trim|required|max_length[100]',
                '_type' => 'text',
                '_required' => true
            ],
            [
                'field' => 'address',
                'label' => 'Headquarter Full Address',
                'rules' => 'trim|required',
                '_type' => 'textarea',
                '_required' => true
            ],
            [
                'field' => 'pan_no',
                'label' => 'PAN Number',
                'rules' => 'trim|required',
                '_type' => 'text',
                '_required' => true
            ],
            [
                'field' => 'per_page',
                'label' => 'Pagination Limit',
                'rules' => 'trim|required|integer|in_list[2,5,10,20,50,100]',
                '_type' => 'dropdown',
                '_data' => ['2' =>'2', '5' => '5', '10' => '10', '20' => '20', '50' => '50', '100' => '100'],
                '_required' => true
            ],
            [
                'field' => 'flag_offline',
                'label' => 'Set Offline',
                'rules' => 'trim|integer',
                '_type' => 'switch',
                '_data' => '1'
            ],
            [
                'field' => 'offline_message',
                'label' => 'Offline Message',
                'rules' => 'trim|required',
                '_type' => 'textarea'
            ],
            [
                'field' => 'admin_email',
                'label' => 'Administrator Email',
                'rules' => 'trim|required|valid_email',
                '_type' => 'email',
                '_required' => true
            ],
            [
                'field' => 'from_email',
                'label' => 'From Email',
                'rules' => 'trim|required|valid_email',
                '_type' => 'email',
                '_required' => true
            ],
            [
                'field' => 'replyto_email',
                'label' => 'Reply-to Email',
                'rules' => 'trim|required|valid_email',
                '_type' => 'email',
                '_required' => true
            ],
            [
                'field' => 'noreply_email',
                'label' => 'No-reply Email',
                'rules' => 'trim|required|valid_email',
                '_type' => 'email',
                '_required' => true
            ],
            [
                'field' => 'website',
                'label' => 'Website',
                'rules' => 'trim|valid_url|prep_url',
                '_type' => 'url',
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

    // --------------------------------------------------------------------

    /**
     * Get Settings
     *
     * Cache the result so that next request will not fire DB query
     * 		Cache Variable Name: mc_settings_one (i.e. <prefix>_<table>_one)
     *
     * @param array| int $where
     * @return object
     */
    public function get( $where = NULL )
    {
    	/**
         * Get Cached Result, If no, cache the query result
         */
        $record = $this->get_cache('settings');
        if(!$record)
        {
            $record = parent::find_by($where);
            $this->write_cache($record, 'settings', CACHE_DURATION_DAY);
        }
        return $record;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        parent::delete_cache('settings');
        return TRUE;
    }

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
            'module' => 'setting',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}