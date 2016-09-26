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
		        'rules' => 'trim|required|max_length[30]'
			],
            [
                'field' => 'code',
                'label' => 'Branch Code',
                'rules' => 'trim|required|alpha|max_length[3]|is_unique[master_branches.code]|strtoupper'
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

        // Get Contact JSON Data 
        $this->before_create[] = 'prepare_contact_data';
        $this->before_update[] = 'prepare_contact_data'; 

        // After Create Callback
        $this->after_create[] = 'log_activity';


        // Merge Contact Validation Rules
        $this->rules['insert'] = array_merge($this->rules['insert'], get_contact_form_validation_rules());
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
     * Delete Cache on Update
     */
    public function _prep_after_write()
    {
    	if($this->delete_cache_on_save === TRUE)
        {
        	// cache name without prefix
        	$this->delete_cache('master_branches_all'); 
        }       
        return TRUE;
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