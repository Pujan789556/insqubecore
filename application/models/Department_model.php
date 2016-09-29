<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Department_model extends MY_Model
{
	public $table = 'master_departments'; // you MUST mention the table name

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
			[
				'field' => 'name',
		        'label' => 'Department Name',
		        'rules' => 'trim|required|max_length[30]',
                '_type'     => 'text',
                '_required' => true
			],
            [
                'field' => 'code',
                'label' => 'Department Code',
                'rules' => 'trim|required|alpha|max_length[5]|is_unique[master_departments.code]|strtoupper',
                '_type'     => 'text',
                '_required' => true
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

        // Before Create/Update Callbacks           
        $this->before_create[] = 'capitalize_code';
        $this->before_update[] = 'capitalize_code'; 

        // After Create Callback
        $this->after_create[] = 'log_activity';
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
        	$this->delete_cache('master_departments_all'); 
        }       
        return TRUE;
    }

    // ----------------------------------------------------------------
    
    public function delete($id = NULL)
    {
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
            'module' => 'department',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);     
    }
}