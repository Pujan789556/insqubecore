<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Branch_model extends MY_Model
{
	public $table = 'master_branches'; // you MUST mention the table name

    public $primary_key = 'id'; // you MUST mention the primary key

    public $fillable = [	
    	// If you want, you can set an array with the fields that can be filled by insert/update
    	'name', 'code', 'contacts'
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
		        'label' => 'Branch Name',
		        'rules' => 'trim|required|max_length[30]',
                '_type'     => 'text',
                '_required' => true
			],
            [
                'field' => 'code',
                'label' => 'Branch Code',
                'rules' => 'trim|required|alpha|max_length[3]|is_unique[master_branches.code]|strtoupper',
                '_type'     => 'text',
                '_required' => true
            ]	
		]	
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

        // Before Create/Update Callbacks           
        $this->before_create[] = 'capitalize_code';
        $this->before_update[] = 'capitalize_code'; 

        // Get Contact JSON Data 
        $this->before_create[] = 'prepare_contact_data';
        $this->before_update[] = 'prepare_contact_data'; 

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
                        ->count_all_results($this->table);
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown()
    {
        return $this->set_cache('dropdown')
                        ->as_dropdown('name')
                        ->order_by('name', 'asc')
                        ->get_all();
    }
    
	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function _prep_after_write()
    {
        $cache_names = [
            'master_branches_all',
            'master_branches_dropdown'
        ];
    	if($this->delete_cache_on_save === TRUE)
        {
        	// cache name without prefix
            foreach($cache_names as $cache)
            {
                $this->delete_cache($cache);     
            }
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
            'module' => 'branch',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);     
    }
}