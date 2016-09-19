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
		parent::__construct();		
	}

	// --------------------------------------------------------------------


}