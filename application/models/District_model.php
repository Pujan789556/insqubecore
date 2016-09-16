<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class District_model extends MY_Model
{
	public $table = 'master_districts'; // you MUST mention the table name

    public $primary_key = 'id'; // you MUST mention the primary key

    public $fillable = [	
    	// If you want, you can set an array with the fields that can be filled by insert/update
    	'name_en', 'name_np'
    ]; 

    public $protected = ['id']; // ...Or you can set an array with the fields that cannot be filled by insert/update

    /**
     * Delete cache on save
     * 
     * @var boolean
     */
    public $delete_cache_on_save = TRUE;


    /**
     * Validation Rules
     * 
     * We can use model to directly save the form data
     * 
     * @var array
     */
    public  $rules = [
		'insert' => [
			[
				'field' => 'name_en',
		        'label' => 'Name (EN)',
		        'rules' => 'trim|required|max_length[80]'
			],
			[
				'field' => 'name_np',
		        'label' => 'Name (NP)',
		        'rules' => 'trim|max_length[80]',
			]
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
     * Delete Cache on Update
     */
    public function _prep_after_write()
    {
    	if($this->delete_cache_on_save === TRUE)
        {
        	// cache name without prefix
        	$this->delete_cache('master_districts_all'); 
        }       
        return TRUE;
    }
}