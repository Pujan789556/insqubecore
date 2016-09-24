<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Fiscal_year_model extends MY_Model
{
	public $table = 'master_fiscal_yrs'; // you MUST mention the table name

    public $primary_key = 'id'; // you MUST mention the primary key

    public $fillable = [	
    	// If you want, you can set an array with the fields that can be filled by insert/update
    	'name', 'code'
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

        // Before Create/Update Callbacks           
        // $this->before_create[] = 'capitalize_code';
        // $this->before_update[] = 'capitalize_code'; 
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
                        ->count_all_results($this->table);
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
        	$this->delete_cache('master_fiscal_yrs_all'); 
        }       
        return TRUE;
    }

    // ----------------------------------------------------------------
    
    public function log_activity($id, $action = 'C')
    {
        return TRUE;

      //    $action = is_string($action) ? $action : 'C';
      //    // Save Activity Log
            // $activity_log = [
            //  'module' => 'department',
            //  'module_id' => $id,
            //  'action' => $action
            // ];
            // return $this->activity->save($activity_log);     
    }
}