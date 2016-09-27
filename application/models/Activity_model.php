<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_model extends MY_Model
{
	public $table = 'log_activities'; // you MUST mention the table name

    public $primary_key = 'id'; // you MUST mention the primary key

    public $fillable = [	
    	'module', 'module_id', 'action', 'extra'
    ]; 

    public $protected = ['id']; // ...Or you can set an array with the fields that cannot be filled by insert/update

    /**
     * Delete cache on save
     * 
     * @var boolean
     */
    public $delete_cache_on_save = TRUE;

    // --------------------------------------------------------------------

	function __construct()
	{
        // User Relationship
        $this->has_one['user'] = array('local_key'=>'created_by', 'foreign_key'=>'id', 'foreign_model'=>'User_model');

        parent::__construct();
	}

	// --------------------------------------------------------------------


    public function all($params = array())
    {
        if(!empty($params))
        {
            $this->where($params);
        }
        return $this->with_user('fields:username')
                    ->order_by('id', 'desc')
                    ->limit($this->settings->per_page+1)
                    ->get_all();
    }

}