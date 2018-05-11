<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Surveyor_model extends MY_Model
{
    protected $table_name       = 'master_surveyors';
    protected $rel_table_name   = 'rel_surveyor__surveyor_expertise';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['prepare_contact_data'];
    protected $before_update = ['prepare_contact_data'];
    protected $after_insert  = ['update_surveyor_expertise', 'clear_cache'];
    protected $after_update  = ['update_surveyor_expertise', 'clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'name', 'picture', 'resume', 'type', 'flag_vat_registered', 'vat_no', 'active', 'contact', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 94;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Surveyor Expertise Model
        $this->load->model('surveyor_expertise_model');

        // Load Validation Rules
        $this->_v_rules();
    }


    // ----------------------------------------------------------------

    private function _v_rules()
    {
        $surveyor_expertise_dd = $this->surveyor_expertise_model->dropdown();

        $this->validation_rules = [
            [
                'field' => 'name',
                'label' => 'Surveyor Name',
                'rules' => 'trim|required|max_length[80]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'type',
                'label' => 'Surveyor Type',
                'rules' => 'trim|required|integer|exact_length[1]|in_list['. implode(',', array_keys(IQB_SURVEYOR_TYPES) ) .']',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + IQB_SURVEYOR_TYPES,
                '_required' => true
            ],
            [
                'field' => 'flag_vat_registered',
                'label' => 'Vat Registered',
                'rules' => 'trim|integer|exact_length[1]|in_list[1]',
                '_type'           => 'checkbox',
                '_checkbox_value' => '1',
                '_required' => true
            ],
            [
                'field' => 'vat_no',
                'label' => 'VAT Number',
                'rules' => 'trim|max_length[40]',
                '_type'     => 'text',
                '_required' => false
            ],
            [
                'field' => 'surveyor_expertise[]',
                'label' => 'Surveyor Expertise',
                'rules' => 'trim|required|integer|max_length[8]|in_list['. implode(',', array_keys($surveyor_expertise_dd)) .']',
                '_key'      => 'surveyor_expertise',
                '_type'     => 'dropdown',
                '_data'     => $surveyor_expertise_dd,
                '_id'       => 'surveyor-expertise',
                '_class'     => 'form-control select-multiple',
                '_extra_attributes' => 'multiple="multiple" style="width:100%" data-placeholder="Select Expertise..."',
                '_required' => true
            ],
            [
                'field' => 'active',
                'label' => 'Is Active?',
                'rules' => 'trim|required|integer|exact_length[1]',
                '_type'     => 'dropdown',
                '_data'     => [ '' => 'Select...', '1' => 'Active', '0' => 'Not Active'],
                '_required' => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    public function prepare_contact_data($data)
    {
        $data['contact'] = get_contact_data_from_form();
        return $data;
    }

    // ----------------------------------------------------------------

    /**
     * After Insert/Update Trigger
     *
     * Update Surveyor Expertise Relations on add/edit
     *
     * @param array $data
     * @return bool
     */
    public function update_surveyor_expertise($data)
    {
        $id = $data['id'] ?? NULL;

        if($id !== NULL)
        {
            /**
             * Get the Surveyor Expertise List
             */
            $fields = $data['fields'];
            $expertise_list = array_unique($fields['surveyor_expertise']);

            /**
             * Task 1: Delete Old Relation If any
             */
            $this->_delete_expertise($id);

            /**
             * Task 2: Batch Insert Expertise Relation
             */
            $batch_data = [];
            foreach( $expertise_list as $key )
            {
                $batch_data[] = [
                    'surveyor_id' => $id,
                    'surveyor_expertise_id' => $key
                ];
            }
            if($batch_data)
            {
                $this->_batch_insert_expertise($batch_data);
            }

            return TRUE;
        }

        return FALSE;
    }

        private function _delete_expertise($id)
        {
            return $this->db->where('surveyor_id', $id)
                            ->delete($this->rel_table_name);
        }

        private function _batch_insert_expertise($data)
        {
            return $this->db->insert_batch($this->rel_table_name, $data);
        }

    // ----------------------------------------------------------------

    /**
     * Get List of Expertise of a Surveyor
     *
     * @param inte $id
     * @return array
     */
    public function expertise_list($id, $dropdown = FALSE)
    {
        $result = $this->db->select('SE.id, SE.name')
                            ->from('master_surveyor_expertise AS SE')
                            ->join($this->rel_table_name . ' REL', 'REL.surveyor_expertise_id = SE.id')
                            ->join($this->table_name . ' S', 'REL.surveyor_id = S.id')
                            ->where('S.id', $id)
                            ->get()->result();

        if($dropdown)
        {
            $data = [];
            foreach($result as $record)
            {
                $data["{$record->id}"] = $record->name;
            }
            return $data;
        }

        return $result;
    }

    // ----------------------------------------------------------------

    /**
     * Get Name
     *
     * @param integer $id
     * @return string
     */
    public function name($id)
    {
        return $this->db->select('S.name')
                 ->from($this->table_name . ' as S')
                 ->where('S.id', $id)
                 ->get()->row()->name;
    }

    // ----------------------------------------------------------------

    /**
     * Get Data Rows
     *
     * Get the filtered resulte set for listing purpose
     *
     * @param array $params
     * @return type
     */
    public function rows($params = array())
    {
        $this->db->select('S.id, S.name, S.picture, S.resume, S.type, S.flag_vat_registered, S.active')
                 ->from($this->table_name . ' as S');

        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['S.id >=' => $next_id]);
            }

            $type = $params['type'] ?? NULL;
            if( $type )
            {
                $this->db->where(['S.type' =>  $type]);
            }

            $active = $params['active'];
            $active = $active === '' ? NULL : $active; // to work with 0 value
            if( $active !== NULL )
            {
                $this->db->where(['S.active' =>  $active]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->like('S.name', $keywords, 'after');
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }

    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $records = $this->get_cache('srv_all_dd');
        if(!$records)
        {
            $records = $this->db->select('S.id, S.name')
                            ->from($this->table_name . ' as S')
                            ->get()->result();
            $this->write_cache($records, 'srv_all_dd', CACHE_DURATION_DAY);
        }
        $dropdown = [];
        foreach($records as $record)
        {
            $dropdown["{$record->id}"] = $record->name;
        }
        return $dropdown;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [
            'srv_all_dd',
        ];
    	// cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
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

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }
}