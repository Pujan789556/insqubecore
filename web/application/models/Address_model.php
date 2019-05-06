<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Address_model extends MY_Model
{
    protected $table_name = 'dt_addresses';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = ['id'];

    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'type', 'type_id', 'country_id', 'state_id', 'address1_id', 'alt_state_text', 'alt_address1_text', 'address2', 'city', 'zip_postal_code', 'phones', 'faxes', 'mobile', 'email', 'web', 'created_at', 'created_by', 'updated_at', 'updated_by'];


    // Columns on Edit
    public static $editable_fields = ['country_id', 'state_id', 'address1_id', 'alt_state_text', 'alt_address1_text', 'address2', 'city', 'zip_postal_code', 'phones', 'faxes', 'mobile', 'email', 'web'];

    /**
     * These are the fields selected while joining and getting the address data.
     * Each column is prefixed with certain identifier to later extract address record
     */
    protected $module_select_fields = [ 'id', 'type', 'type_id', 'country_id', 'state_id', 'address1_id', 'alt_state_text', 'alt_address1_text', 'address2', 'city', 'zip_postal_code', 'phones', 'faxes', 'mobile', 'email', 'web'];

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
        // $this->validation_rules();
    }

    // --------------------------------------------------------------------

    /**
     * Set the Validation Rules
     *
     * @param array $options  rules options e.g. mobile compulsory?
     *          [
     *              'required' => [
     *                  'mobile' => true
     *               ]
     *          ]
     * @return void
     */
    public function _v_rules(array $options = [])
    {
        $country_dropdown = $this->country_model->dropdown('id');

        $mobile_rules    = 'trim|valid_mobile|max_length[10]';
        $mobile_required = $options['required']['mobile'] ?? FALSE;
        if($mobile_required)
        {
            $mobile_rules = 'trim|required|valid_mobile|max_length[10]';
        }


        $v_rules = [

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

            // LOCAL BODY/VDC/MUNICIPALITY - NAME
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
                    'rules' => $mobile_rules,
                    '_type' => 'text',
                    '_id'   => 'address-mobile',
                    '_required' => $mobile_required
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

        return $v_rules;
    }

    // --------------------------------------------------------------------

    public function v_rules_add(array $options = [])
    {
        return $this->_v_rules($options);
    }

    public function v_rules_edit($record, array $options = [])
    {
        $v_rules = $this->_v_rules($options);

        // Get the Dropdowns
        $state_dropdown     = $record->country_id ? $this->state_model->dropdown($record->country_id) : [];
        $address1_dropdown  = $record->state_id ? $this->local_body_model->dropdown_by_state($record->state_id) : NULL;

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

    public function v_rules_on_submit(array $options = [], $formatted = FALSE)
    {
        $v_rules = $this->_v_rules($options);

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
     * It work with multiple dependant types on a single query. [Refer Policy Model -> get() function for its implementation.]
     *
     * @param int $type IQB_ADDRESS_TYPES Module Type
     * @param int|null $type_id Module ID
     * @param array $aliases Table Aliases
     * @param string $col_prefix Address Column prefix
     * @param bool $module_compulsory If module is non compulsory, we perform left join on address and country table
     * @return void
     */
    public function module_select(

        $type,

        $type_id = NULL,

        $aliases = [
            // Address Table Alias
            'address' => 'ADR',

            // Country Table Alias
            'country' => 'CNTRY',

            // State Table Alias
            'state' => 'STATE',

            // Local Body Table Alias
            'local_body' => 'LCLBD',

            // Type/Module Table Alias
            'module' => ''
        ],

        // column prefix
        $col_prefix = 'addr_',

        // Module Compulsory: If yes, we have address and country table join is "NON-LEFT"
        // else they will be "left" join - Required by Policy Model -> get()
        $module_compulsory = TRUE
     )
    {
        /**
         * Extract Aliases
         */
        $_ADR = $aliases['address'] ?? 'ADR';
        $_CNTRY = $aliases['country'] ?? 'CNTRY';
        $_STATE = $aliases['state'] ?? 'STATE';
        $_LCLBD = $aliases['local_body'] ?? 'LCLBD';
        $_MODULE = $aliases['module'] ?? NULL;


        // IF MODULE is not specified, simply return.
        if(!$_MODULE) return FALSE;

        $addr_cols = "";
        foreach ($this->module_select_fields as $col)
        {
            $col_alias = $col_prefix . $col;
            $addr_cols .= "{$_ADR}.{$col} AS {$col_alias}, ";
        }

        $this->db->select(
                            // Address Table
                            "{$addr_cols}" .

                            // Country Table
                            "{$_CNTRY}.name AS {$col_prefix}country_name, " .

                            // State Table
                            "{$_STATE}.name_en AS {$col_prefix}state_name_en, {$_STATE}.name_np AS {$col_prefix}state_name_np, ".

                            // Local Body Table
                            "{$_LCLBD}.name_en AS {$col_prefix}address1_en, {$_LCLBD}.name_np AS {$col_prefix}address1_np"
                        );

        if($module_compulsory)
        {
            $this->db->join( $this->table_name . " $_ADR", "$_ADR.type = {$type} AND $_ADR.type_id = {$_MODULE}.id")
                    ->join("master_countries {$_CNTRY}", "{$_CNTRY}.id = $_ADR.country_id");
        }
        else
        {
            $this->db->join( $this->table_name . " $_ADR", "$_ADR.type = {$type} AND $_ADR.type_id = {$_MODULE}.id", 'left')
                    ->join("master_countries {$_CNTRY}", "{$_CNTRY}.id = $_ADR.country_id", 'left');
        }

        $this->db->join("master_states {$_STATE}", "{$_STATE}.id = $_ADR.state_id", 'left')
                ->join("master_localbodies {$_LCLBD}", "{$_LCLBD}.id = $_ADR.address1_id", 'left');


        $where = [ ];
        if($type_id)
        {
            $where["{$_ADR}.type_id"] = $type_id;
        }

        $this->db->where($where);
    }

    // --------------------------------------------------------------------

    /**
     * Parse and Return Address Record from a Module Record
     * having address columns from module_select() function
     *
     * @param object $module_record
     * @param string $prefix address column prefix
     * @return object
     */
    public function parse_address_record($module_record, $prefix = 'addr_')
    {
        $address_record = new stdClass();
        $module_record = (array)$module_record;

        // Assign all the columns with "addr_" prefix removing it.
        foreach($module_record as $key=>$value)
        {
            if (strpos($key, $prefix) === 0)
            {
               // Get the New key for address record
                $addr_col = str_replace($prefix, '', $key);
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
        $record = $this->db->select('id')
                           ->from($this->table_name)
                           ->where(['type' => $type, 'type_id' => $type_id])
                           ->get()->row();

       if($record)
       {
            return parent::update($record->id, $data, TRUE);
       }

        return TRUE;
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
                         ->join('master_countries C', 'C.id = A.country_id', 'left')
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
                         ->join('master_countries C', 'C.id = A.country_id', 'left')
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