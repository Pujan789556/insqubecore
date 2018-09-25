<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Address_model extends MY_Model
{
    protected $table_name = 'dt_addresses';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'type', 'type_id', 'country_id', 'state_id', 'address1_id', 'alt_state_text', 'alt_address1_text', 'address2', 'city', 'zip_postal_code', 'phones', 'faxes', 'mobile', 'email', 'web', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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
        $this->load->model('state_model');
        $this->load->model('local_body_model');

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

            'country' => [
                [
                    'field' => 'country_id',
                    'label' => 'Country',
                    'rules' => 'trim|required|integer|max_length[3]',
                    '_type' => 'dropdown',
                    '_data' => IQB_BLANK_SELECT + $country_dropdown,
                    '_id'   => 'country-id',
                    '_required' => true
                ]
            ],

            'state' => [
                [
                    'field' => 'state_id',
                    'label' => 'State / Province',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type' => 'dropdown',
                    '_data' => IQB_BLANK_SELECT,
                    '_id'   => 'state-id',
                    '_required' => true
                ]
            ],

            'address1' => [
                [
                    'field' => 'address1_id',
                    'label' => 'Address',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type' => 'dropdown',
                    '_data' => IQB_BLANK_SELECT,
                    '_id'   => 'address1-id',
                    '_help_text' => 'VDC/Municipality or City Area',
                    '_required' => true
                ]
            ],

            'address_other' => [
                [
                    'field' => 'address2',
                    'label' => 'Address2',
                    'rules' => 'trim|max_length[150]',
                    '_type' => 'text',
                    '_help_text' => 'Example - Ward No - 8, Adarsha Tole',
                    '_required' => false
                ],
                [
                    'field' => 'city',
                    'label' => 'City',
                    'rules' => 'trim|max_length[150]',
                    '_type' => 'text',
                    '_required' => false
                ],
                [
                    'field' => 'zip_postal_code',
                    'label' => 'ZIP/Postal Code',
                    'rules' => 'trim|max_length[20]',
                    '_type' => 'text',
                    '_required' => false
                ]
            ],

            'contacts' => [
                [
                    'field' => 'phones',
                    'label' => 'Phone(s)',
                    'rules' => 'trim|max_length[50]',
                    '_type' => 'text',
                    '_required' => false
                ],
                [
                    'field' => 'faxes',
                    'label' => 'Fax(es)',
                    'rules' => 'trim|max_length[50]',
                    '_type' => 'text',
                    '_required' => false
                ],
                [
                    'field' => 'mobile',
                    'label' => 'Mobile',
                    'rules' => 'trim|valid_mobile|max_length[10]',
                    '_type' => 'text',
                    '_required' => false
                ],
                [
                    'field' => 'email',
                    'label' => 'Email',
                    'rules' => 'trim|valid_email|max_length[80]',
                    '_type' => 'text',
                    '_required' => false
                ],
                [
                    'field' => 'web',
                    'label' => 'Website',
                    'rules' => 'trim|valid_url|prep_url|max_length[150]',
                    '_type' => 'text',
                    '_required' => false
                ]
            ],

            'state_template' => [
                [
                    'field' => 'state_id',
                    'label' => 'State / Province',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type' => 'dropdown',
                    '_data' => IQB_BLANK_SELECT,
                    '_id'   => 'state-id',
                    '_required' => true
                ],
                [
                    'field' => 'alt_state_text',
                    'label' => 'State / Province',
                    'rules' => 'trim|required|max_length[150]',
                    '_type' => 'text',
                    '_id'   => 'alt-state-text',
                    '_required' => true
                ],
            ],

            'address1_template' => [
                [
                    'field' => 'address1_id',
                    'label' => 'Address',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type' => 'dropdown',
                    '_data' => IQB_BLANK_SELECT,
                    '_id'   => 'address1-id',
                    '_help_text' => 'VDC/Municipality or City Area',
                    '_required' => true
                ],

                [
                    'field' => 'alt_address1_text',
                    'label' => 'Address1',
                    'rules' => 'trim|required|max_length[150]',
                    '_type' => 'text',
                    '_id'   => 'alt-address1-text',
                    '_help_text' => 'VDC/Municipality or City Area',
                    '_required' => true
                ],
            ],
        ];
    }

    // --------------------------------------------------------------------

    public function v_rules_add()
    {
        return $this->validation_rules;
    }

    public function v_rules_edit($record)
    {
        $v_rules = $this->validation_rules;

        // Get the Dropdowns
        $state_dropdown = $this->state_model->dropdown($record->country_id);
        $address1_dropdown = $record->state_id ? $this->local_body_model->dropdown_by_state($record->state_id) : NULL;

        if($state_dropdown)
        {
            // Update the state dropdown
            $v_rules['state'][0]['_data'] = IQB_BLANK_SELECT + $state_dropdown;
        }
        else
        {
            // Copy & Replace by State Alt Text field
            $v_rules['state'] = [
                $v_rules['state_template'][1]
            ];
        }

        if($address1_dropdown)
        {
            // Update the address1 dropdown
            $v_rules['address1'][0]['_data'] = IQB_BLANK_SELECT + $address1_dropdown;
        }
        else
        {
            // Copy & Replace by Address1 Alt Text field
            $v_rules['address1'] = [
                $v_rules['address1_template'][1]
            ];
        }
        return $v_rules;
    }

    public function v_rules_on_submit($formatted = FALSE)
    {
        $v_rules = $this->validation_rules;

        $country_id = (int)$this->input->post('country_id');
        $state_id   = (int)$this->input->post('state_id');

        // Get the Dropdowns
        $state_dropdown     = $this->state_model->dropdown($country_id);
        $address1_dropdown  = $this->local_body_model->dropdown_by_state($state_id);
        if($state_dropdown)
        {
            // Update the Country dropdown
            $v_rules['state'][0]['_data'] = IQB_BLANK_SELECT + $state_dropdown;
        }
        else
        {
            // Copy & Replace by State Alt Text field
            $v_rules['state'] = [
                $v_rules['state_template'][1]
            ];
        }

        if($address1_dropdown)
        {
            // Update the address1 dropdown
            $v_rules['address1'][0]['_data'] = IQB_BLANK_SELECT + $address1_dropdown;
        }
        else
        {
            // Copy & Replace by Address1 Alt Text field
            $v_rules['address1'] = [
                $v_rules['address1_template'][1]
            ];
        }

        // Unset Template
        unset($v_rules['state_template']);
        unset($v_rules['address1_template']);

        if($formatted)
        {
            $formatted_rules = [];
            foreach($v_rules as $section=>$rules)
            {
                $formatted_rules = array_merge($formatted_rules, $rules);
            }

            return $formatted_rules;
        }

        return $v_rules;

    }

    // --------------------------------------------------------------------

    /**
     * Select Address columns for joining with dependant module.
     *
     * It will prefix "addr_" in front of all the address related columns so that we can create separate address record
     * which can be passed on helper function for rendering address widget
     *
     * @param chars $type_table_alias Dependant Module Table Alias
     * @param int $type IQB_ADDRESS_TYPES
     * @param int $type_id Module ID
     * @return void
     */
    public function module_select($type_table_alias, $type, $type_id = NULL)
    {
        $columns = [ 'id', 'type', 'type_id', 'country_id', 'state_id', 'address1_id', 'alt_state_text', 'alt_address1_text', 'address2', 'city', 'zip_postal_code', 'phones', 'faxes', 'mobile', 'email', 'web'];

        $addr_cols = "";
        foreach ($columns as $col)
        {
            $addr_cols .= "ADR.{$col} AS addr_{$col}, ";
        }

        $this->db->select(
                            // Address Table
                            "{$addr_cols}" .

                            // Country Table
                            "CNTRY.name AS addr_country_name, " .

                            // State Table
                            "STATE.name_en AS addr_state_name_en, STATE.name_np AS addr_state_name_np, ".

                            // Local Body Table
                            "LCLBD.name_en AS addr_address1_en, LCLBD.name_np AS addr_address1_np"
                        )
                    ->join( $this->table_name . ' ADR', "ADR.type = {$type} AND ADR.type_id = {$type_table_alias}.id")
                    ->join('master_countries CNTRY', 'CNTRY.id = ADR.country_id')
                    ->join('master_states STATE', 'STATE.id = ADR.state_id', 'left')
                    ->join('master_localbodies LCLBD', 'LCLBD.id = ADR.address1_id', 'left');


        $where = [ "ADR.type" => $type ];
        if($type_id)
        {
            $where["ADR.type_id"] = $type_id;
        }

        $this->db->where($where);
    }

    // --------------------------------------------------------------------

    /**
     * Parse and Return Address Record from a Module Record
     * having address columns from module_select() function
     *
     * @param object $module_record
     * @return object
     */
    public function parse_address_record($module_record)
    {
        $address_record = new stdClass();
        $module_record = (array)$module_record;

        // Assign all the columns with "addr_" prefix removing it.
        foreach($module_record as $key=>$value)
        {
            if (strpos($key, 'addr_') === 0)
            {
               // Get the New key for address record
                $addr_col = str_replace('addr_', '', $key);
                $address_record->{$addr_col} = $value;
            }
        }
        return $address_record;
    }

    // --------------------------------------------------------------------

    /**
     * Add Address Record for Specific Module Item
     *
     * @param int $type Module Type
     * @param int $type_id Module ID
     * @param array $post_data POST Data
     * @return mixed
     */
    public function add($type, $type_id, $post_data)
    {
        $cols = ['country_id', 'state_id', 'address1_id', 'alt_state_text', 'alt_address1_text', 'address2', 'city', 'zip_postal_code', 'phones', 'faxes', 'mobile', 'email', 'web'];

        $data = [
            'type'      => $type,
            'type_id'   => $type_id
        ];

        foreach ($cols as $col)
        {
            $data[$col] = $post_data[$col] ?? NULL;
        }

        return parent::insert($data, TRUE);
    }

    // --------------------------------------------------------------------

    /**
     * Edit Address Record
     *
     * @param int $id Address ID
     * @param array $post_data POST Data
     * @return mixed
     */
    public function edit($id, $post_data)
    {
        $cols = ['country_id', 'state_id', 'address1_id', 'alt_state_text', 'alt_address1_text', 'address2', 'city', 'zip_postal_code', 'phones', 'faxes', 'mobile', 'email', 'web'];

        $data = [];

        foreach ($cols as $col)
        {
            $data[$col] = $post_data[$col] ?? NULL;
        }

        return parent::update($id, $data, TRUE);
    }

    // --------------------------------------------------------------------

    /**
     * Commit Endorsement Record of Customer Address
     *
     * @param int $type Module Type
     * @param int $type_id Module ID
     * @param array $data
     * @return boolean
     */
    public function commit_endorsement($type, $type_id, $data)
    {
        return $this->db->where(['type' => $type, 'type_id' => $type_id])
                        ->update($this->table_name, $data);
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
        // $this->db->select('S.*, C.name AS country_name')
        //                      ->from($this->table_name . ' S')
        //                      ->join('master_countries C', 'C.id = S.country_id');

        // if(!empty($params))
        // {
        //     $next_id = $params['next_id'] ?? NULL;
        //     if( $next_id )
        //     {
        //         $this->db->where(['S.id >=' => $next_id]);
        //     }

        //     $country_id = $params['country_id'] ?? NULL;
        //     if( $country_id )
        //     {
        //         $this->db->where(['S.country_id' => $country_id]);
        //     }

        //     $code = $params['code'] ?? NULL;
        //     if( $code )
        //     {
        //         $this->db->where(['S.code' =>  $code]);
        //     }

        //     $keywords = $params['keywords'] ?? '';
        //     if( $keywords )
        //     {
        //         $this->db->like('S.name_en', $keywords, 'after');
        //         $this->db->or_like('S.name_np', $keywords, 'after');
        //     }
        // }
        // return $this->db->limit($this->settings->per_page+1)
        //             ->get()->result();
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
        return $this->db->select('A.*, C.name AS country_name, S.name_en AS state_name_en, S.name_np AS state_name_np, LB.name_en AS address1_en, LB.name_np AS address1_np')
                         ->from($this->table_name . ' A')
                         ->join('master_countries C', 'C.id = A.country_id')
                         ->join('master_states S', 'S.id = A.state_id', 'left')
                         ->join('master_localbodies LB', 'LB.id = A.address1_id', 'left')
                         ->where('A.id', $id)
                         ->get()->row();
    }

    // --------------------------------------------------------------------

    /**
     * Get a Single Record by Specific Module Item
     *
     * @param int $id
     * @return mixed
     */
    public function get_by_type($type, $type_id)
    {
        return $this->db->select('A.*, C.name AS country_name, S.name_en AS state_name_en, S.name_np AS state_name_np, LB.name_en AS address1_en, LB.name_np AS address1_np')
                         ->from($this->table_name . ' A')
                         ->join('master_countries C', 'C.id = A.country_id')
                         ->join('master_states S', 'S.id = A.state_id', 'left')
                         ->join('master_localbodies LB', 'LB.id = A.address1_id', 'left')
                         ->where(['A.type' => $type, 'A.type_id' => $type_id])
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