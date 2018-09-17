<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class State_model extends MY_Model
{
    protected $table_name = 'master_states';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'country_id', 'code', 'name_en', 'name_np', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Load dependent model
        $this->load->model('country_model');

        // Build Validation Rules
        $this->validation_rules();
    }

    // --------------------------------------------------------------------

    /**
     * Set the Validation Rules
     *
     * @return void
     */
    public function validation_rules()
    {
        $country_dropdown = $this->country_model->dropdown('id');
        $this->validation_rules = [
            [
                'field' => 'country_id',
                'label' => 'Country',
                'rules' => 'trim|required|integer|max_length[3]',
                '_type' => 'dropdown',
                '_data' => IQB_BLANK_SELECT + $country_dropdown,
                '_required' => true
            ],
            [
                'field' => 'code',
                'label' => 'State / Province Code',
                'rules' => 'trim|required|alpha_numeric|max_length[3]|callback_check_duplicate',
                '_type' => 'text',
                '_required' => true
            ],
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
    }

    // --------------------------------------------------------------------

    /**
     * Get Data Rows
     *
     * Get the filtered result-set for listing
     *
     * @param array $params
     * @return mixed
     */
    public function rows($params = array())
    {
        $this->db->select('S.*, C.name AS country_name')
                             ->from($this->table_name . ' S')
                             ->join('master_countries C', 'C.id = S.country_id');

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['S.id >=' => $next_id]);
            }

            $country_id = $params['country_id'] ?? NULL;
            if( $country_id )
            {
                $this->db->where(['S.country_id' => $country_id]);
            }

            $code = $params['code'] ?? NULL;
            if( $code )
            {
                $this->db->where(['S.code' =>  $code]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('S.name_en', $keywords, 'after');
                $this->db->or_like('S.name_np', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }

    // --------------------------------------------------------------------

    /**
     * Get a Single Record
     *
     * @param int $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->db->select('S.*, C.name AS country_name')
                             ->from($this->table_name . ' S')
                             ->join('master_countries C', 'C.id = S.country_id')
                             ->where('S.id', $id)
                             ->get()->row();
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     *
     * @return array
     */
    public function dropdown()
    {
       $dropdown = [];

       return $dropdown;
    }


	// --------------------------------------------------------------------

    public function check_duplicate($where, $id=NULL)
    {
        if( $id )
        {
            $this->db->where('id !=', $id);
        }
        // $where is array ['key' => $value]
        return $this->db->where($where)
                        ->count_all_results($this->table_name);
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
        $status             = TRUE;

        // Use automatic transaction
        $this->db->trans_start();


            parent::delete($id);


        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }
        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [

        ];

        // cache name without prefix
        $this->delete_cache($cache_names);

        return TRUE;
    }
}