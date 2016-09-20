<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Country_model extends MY_Model
{
	public $table = 'master_countries'; // you MUST mention the table name

    public $primary_key = 'id'; // you MUST mention the primary key

    public $fillable = [	
    	// If you want, you can set an array with the fields that can be filled by insert/update
    	'name', 'alpha2', 'alpha3', 'dial_code', 'currency_code', 'currency_name'
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
		        'label' => 'Country Name',
		        'rules' => 'trim|required|max_length[80]'
			],
            [
                'field' => 'alpha2',
                'label' => 'Country Code (alpha 2)',
                'rules' => 'trim|required|alpha|exact_length[2]|is_unique[master_countries.alpha2]'
            ],
            [
                'field' => 'alpha3',
                'label' => 'Country Code (alpha 3)',
                'rules' => 'trim|required|alpha|exact_length[3]|is_unique[master_countries.alpha3]'
            ],
            [
                'field' => 'dial_code',
                'label' => 'Dialing Code',
                'rules' => 'trim|required|max_length[20]'
            ],
            [
                'field' => 'currency_code',
                'label' => 'Currency Code',
                'rules' => 'trim|exact_length[3]'
            ],
            [
                'field' => 'currency_name',
                'label' => 'Currency Name',
                'rules' => 'trim|max_length[40]'
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
        $this->before_create[] = 'capitalize_codes';
        $this->before_update[] = 'capitalize_codes'; 
    }

    // ----------------------------------------------------------------

    public function capitalize_codes($data)
    {
        $code_cols = array('alpha2', 'alpha3', 'currency_code');
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
        	$this->delete_cache('master_countries_all'); 
        }       
        return TRUE;
    }
}