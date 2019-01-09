<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_user_model extends MY_Model
{
    protected $table_name = 'auth_app_users';

    protected $set_created  = true;
    protected $set_modified = true;
    protected $log_user     = true;

    protected $protected_attributes = ['id'];

    protected $after_insert  = [];
    protected $after_update  = [];
    protected $after_delete  = [];

    protected $fields = ['id', 'mobile', 'auth_type', 'auth_type_id', 'password', 'api_key', 'last_ip', 'last_device', 'last_login', 'is_activated', 'pincode', 'pincode_resend_count', 'pincode_expires_at', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [
    ];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0;

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


    // ----------------------------------------------------------------

    public function v_rules($action)
    {
        $v_rules = [];

        switch ($action)
        {
            case 'verify_mobile':
                $v_rules = $this->_v_rules_verify_mobile();
                break;

            case 'verify_pincode':
                $v_rules = $this->_v_rules_verify_pincode();
                break;

            case 'verify_signup':
                $v_rules = $this->_v_rules_verify_signup();
                break;

            case 'pincode':
                $v_rules = $this->_v_rules_pincode();
                break;

            case 'pwd_create':
                $v_rules = $this->_v_rules_pwd_create();
                break;

            case 'pwd_change':
                $v_rules = $this->_v_rules_pwd_change();
                break;

            case 'login':
                $v_rules = $this->_v_rules_login();
                break;

            case 'register':
                $v_rules = $this->_v_rules_register();
                break;


            default:
                # code...
                break;
        }

        return $v_rules;
    }

    private function _v_rules_verify_mobile()
    {
        $v_rules = [
            [
                'field' => 'mobile',
                'label' => 'Mobile',
                'rules' => 'trim|required|valid_mobile|max_length[10]',
                '_type' => 'text',
                '_required'     => true
            ],
            [
                'field' => 'action',
                'label' => 'Action',
                'rules' => 'trim|required|alpha_dash|max_length[50]',
                '_type' => 'text',
                '_required'     => true
            ]
        ];

        return $v_rules;
    }

    private function _v_rules_verify_pincode()
    {
        $v_rules = [
            [
                'field' => 'mobile',
                'label' => 'Mobile',
                'rules' => 'trim|required|valid_mobile|max_length[10]',
                '_type' => 'text',
                '_required'     => true
            ],
            [
                'field' => 'pincode',
                'label' => 'Pincode',
                'rules' => 'trim|required|integer|exact_length[6]',
                '_type' => 'text',
                '_required'     => true
            ],
            [
                'field' => 'action',
                'label' => 'Action',
                'rules' => 'trim|required|alpha_dash|max_length[50]',
                '_type' => 'text',
                '_required'     => true
            ]
        ];

        return $v_rules;
    }

    private function _v_rules_verify_signup()
    {
        return $this->_v_rules_verify_pincode();
    }

    // ----------------------------------------------------------------

    private function _v_rules_pincode()
    {
        return $this->_v_rules_verify_mobile();
    }

    // ----------------------------------------------------------------

    // pincode, mobile, action required
    private function _v_rules_pwd_create()
    {
        $v_rules = array_merge( $this->_v_rules_verify_pincode(),[
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'trim|required|max_length[50]',
                '_type' => 'text',
                '_required'     => true
            ],
            [
                'field' => 'confirm_password',
                'label' => 'Confirm Password',
                'rules' => 'trim|required|matches[password]',
                '_type' => 'text',
                '_required'     => true
            ]
        ]);

        return $v_rules;
    }

    // ----------------------------------------------------------------

    // is performed by logged in user, so token will be there for validation
    private function _v_rules_pwd_change()
    {
        $v_rules = [
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|max_length[50]',
                '_type' => 'text',
                '_required'     => true
            ],
            [
                'field' => 'confirm_password',
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
                '_type' => 'text',
                '_required'     => true
            ]
        ];

        return $v_rules;
    }

    // ----------------------------------------------------------------

    private function _v_rules_login()
    {
        $v_rules = [
            [
                'field' => 'mobile',
                'label' => 'Mobile',
                'rules' => 'trim|required|valid_mobile|max_length[10]',
                '_type' => 'text',
                '_required'     => true
            ],
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|max_length[50]',
                '_type' => 'text',
                '_required'     => true
            ]
        ];

        return $v_rules;
    }

    // ----------------------------------------------------------------

    private function _v_rules_register()
    {
        $v_rules = [
            [
                'field' => 'auth_type',
                'label' => 'Auth Type',
                'rules' => 'trim|required|integer|exact_length[1]|in_list['.implode(',', array_keys(IQB_API_AUTH_TYPES)).']',
                '_type'     => 'number',
                '_required' => true
            ],
            [
                'field' => 'full_name_en',
                'label' => 'Full Name (EN)',
                'rules' => 'trim|required|max_length[150]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'mobile',
                'label' => 'Mobile',
                'rules' => 'trim|required|valid_mobile|max_length[10]|callback__cb_mobile_identity_duplicate',
                '_type' => 'number',
                '_required'     => true
            ],
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|max_length[50]',
                '_type' => 'text',
                '_required'     => true
            ],
            [
                'field' => 'confirm_password',
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
                '_type' => 'text',
                '_required'     => true
            ]
        ];

        return $v_rules;
    }


    // ----------------------------------------------------------------

    /**
     * Check Duplicate
     *
     * @param array $where
     * @param int|null $id
     * @return int
     */
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

    /**
     * Verify Pincode
     *
     * @param int $mobile
     * @param int $pincode
     * @return bool
     */
    public function verify_pincode($mobile, $pincode, $transaction = TRUE)
    {
        $user = $this->get_by_mobile($mobile);
        if( !$user || !$this->is_valid_pincode($user, $pincode) )
        {
            return FALSE;
        }

        // Need User Activation?
        if($user->is_activated == IQB_FLAG_ON )
        {
            return TRUE;
        }

        // Activate User
        $data = array(
            'is_activated' => IQB_FLAG_ON
        );
        return $this->save($user, $data, $transaction);
    }

    // ----------------------------------------------------------------

    /**
     * Is User's PINCODE valid?
     *
     * @param object $user
     * @param int $pincode
     * @return bool
     */
    public function is_valid_pincode($user, $pincode)
    {
        return $pincode == $user->pincode;
    }

    // ----------------------------------------------------------------

    /**
     * Is User's API KEY valid?
     *
     * @param string $api_key
     * @return bool
     */
    public function is_valid_api_key($mobile, $api_key)
    {
        $valid  = TRUE;
        $user = $this->get_by_mobile($mobile);
        if( !$user || $user->api_key !== $api_key)
        {
            $valid  = FALSE;
        }
        return $valid;
    }

    // ----------------------------------------------------------------

    /**
     * Get App User by Mobile
     *
     * @param integer $mobile
     * @return object
     */
    public function get_by_mobile($mobile)
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $cache_var  = 'app_user_mb_' . $mobile;
        $record     = $this->get_cache($cache_var);
        if(!$record)
        {
            $record = parent::find_by(['mobile' => $mobile]);
            $this->write_cache($record, $cache_var, CACHE_DURATION_HR);
        }
        return $record;
    }

    // ----------------------------------------------------------------

    /**
     * Register New App User
     *
     * @param array $post_data Form Post Data
     * @param bool $transaction Use automatic transaction??
     * @return mixed
     */
    public function register($data, $transaction = TRUE)
    {
        $data = $this->__build_register_data($data);
        if($transaction)
        {
            // Use automatic transaction
            $done = FALSE;
            $this->db->trans_start();

                // Insert Primary Record
                $done = parent::insert($data, TRUE);

            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                // generate an error... or use the log_message() function to log your error
                $done = FALSE;
            }
        }
        else
        {
            // Insert Primary Record
            $done = parent::insert($data, TRUE);
        }

        // return result/status
        return $done;
    }

        // ----------------------------------------------------------------

        /**
         * Build Registration Data
         *
         * @param array $data
         * @return array
         */
        private function __build_register_data($data)
        {
            $this->load->library('token');
            $prepared_data = [];


            /**
             * Task1: Prepare Basic Columns
             */
            $cols = ['mobile', 'auth_type', 'auth_type_id', 'password'];
            foreach($cols as $col)
            {
                $prepared_data[$col] = $data[$col] ?? NULL;
            }

            /**
             * Task 2: Create Password Hash
             */
            if($prepared_data['password'])
            {
                $prepared_data['password'] = password_hash($prepared_data['password'], PASSWORD_BCRYPT);
            }
            else
            {
                // Create a random one
                $prepared_data['password'] = password_hash(TOKEN::v2(32), PASSWORD_BCRYPT);
            }

            /**
             * Task 3: Should Not Be Activated on Register
             */
            $prepared_data['is_activated'] = IQB_FLAG_OFF;


            /**
             * Task 4: User's API Key
             */

            $prepared_data['api_key'] = TOKEN::v2(12);


            return $prepared_data;
        }

	// --------------------------------------------------------------------

    public function activate($id, $transaction = TRUE)
    {
        $id = intval($id);
        $data = array(
            'is_activated' => IQB_FLAG_ON
        );

        return $this->save($id, $data, $transaction);
    }

    // --------------------------------------------------------------------

    public function deactivate($id, $transaction = TRUE)
    {
        $id = intval($id);
        $data = array(
            'is_activated' => IQB_FLAG_OFF
        );

        return $this->save($id, $data, $transaction);
    }

    // --------------------------------------------------------------------

    // UPDATE PIN CODE
    public function pincode($id, $data, $transaction = TRUE)
    {
        $id = intval($id);
        return $this->save($id, $data, $transaction);
    }

    // --------------------------------------------------------------------

    // Update Login History
    public function login_history($id, $ip_address, $device_id, $datetime, $transaction = TRUE)
    {
        $id = intval($id);
        $data = array(
            'last_ip'       => $ip_address,
            'last_device'   => $device_id,
            'last_login'    => $datetime
        );

        return $this->save($id, $data, $transaction);
    }

    // --------------------------------------------------------------------

    public function change_mobile_by($auth_type, $auth_type_id, $mobile, $transaction = TRUE)
    {
        $user = parent::find_by([
            'auth_type'     => $auth_type,
            'auth_type_id'  => $auth_type_id
        ]);

        return $this->change_mobile($user, $mobile, $transaction);
    }

    // --------------------------------------------------------------------

    // Change Mobile, Remember You have to change the API KEY as well
    public function change_mobile($user, $mobile, $transaction = TRUE)
    {
        $user = is_numeric($user) ? parent::find(intval($user)) : $user;

        // If unchanged, do nothing
        if($user->mobile == $mobile )
        {
            return TRUE;
        }

        // Change Mobile and API Key
        $this->load->library('Token');
        $data           = array(
            'mobile'    => $mobile,
            'api_key'   => TOKEN::v2(12)
        );

        return $this->save($user, $data, $transaction);
    }

    // --------------------------------------------------------------------

    public function change_password_by($auth_type, $auth_type_id, $new_pass, $transaction = TRUE)
    {
        $user = parent::find_by([
            'auth_type'     => $auth_type,
            'auth_type_id'  => $auth_type_id
        ]);

        return $this->change_password($user, $new_pass, $transaction);
    }

    // --------------------------------------------------------------------

    // Change password, Remember You have to change the API KEY as well
    public function change_password($user, $new_pass, $transaction = TRUE)
    {
        $this->load->library('Token');
        $password_hash  = password_hash($new_pass, PASSWORD_BCRYPT);
        $data           = array(
            'password'  => $password_hash,
            'api_key'   => TOKEN::v2(12),
            'pincode'               => NULL,
            'pincode_resend_count'  => 0,
            'pincode_expires_at'    => NULL
        );

        return $this->save($user, $data, $transaction);
    }

    // ----------------------------------------------------------------

    /**
     * Save App User Data
     *
     * @param int|object $user
     * @param array $data
     * @param bool $transaction Use automatic transaction??
     * @return bool
     */
    public function save($user, $data, $transaction = TRUE)
    {
        $user   = is_numeric($user) ? parent::find(intval($user)) : $user;
        if(!$user )
        {
            return FALSE;
        }

        if($transaction)
        {
            // Use automatic transaction
            $status = FALSE;
            $this->db->trans_start();

                // Update Data
                $status = parent::update($user->id, $data, TRUE);

            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                // generate an error... or use the log_message() function to log your error
                $status = FALSE;
            }
        }
        else
        {
            // Insert Primary Record
            $status = parent::update($user->id, $data, TRUE);
        }


        // Clear Cache on Success
        if($status)
        {
            $this->_clear_user_cache($user);
        }


        // return result/status
        return $status;
    }

    // ----------------------------------------------------------------

    // Perform App Login
    public function login($mobile, $password)
    {
        $user = $this->get_by_mobile($mobile);
        if($user)
        {
            $hash = $user->password;
            if( password_verify ( $password , $hash ) )
            {
                return [
                    'auth_type'     => $user->auth_type,
                    'auth_type_id'  => $user->auth_type_id,
                    'api_key'       => $user->api_key,
                    'mobile'        => $user->mobile
                ];
            }
        }
        return FALSE;
    }

    // ----------------------------------------------------------------


    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data = NULL)
    {
        /**
         * If no data supplied, delete all caches
         */
        if( !$data )
        {
            $cache_names = [
                // User By API KEY
                'app_user_mb_*'
            ];
        }
        else
        {
            /**
             * If data supplied, we only delete the supplied
             * caches
             */
            $cache_names = is_array($data) ? $data : [$data];
        }

        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    private function _clear_user_cache($user)
    {
        $user = is_numeric($user) ? parent::find(intval($user)) : $user;
        $keys = [
            'app_user_mb_' . $user->mobile,
        ];

        return $this->clear_cache($keys);
    }

    // ----------------------------------------------------------------

    /**
     * Delete App User
     *
     * @param int $auth_type
     * @param int $auth_type_id
     * @param bool $transaction Use automatic transaction??
     * @return bool
     */
    public function delete_user($auth_type, $auth_type_id, $transaction = TRUE)
    {
        $user = parent::find_by([
            'auth_type'     => $auth_type,
            'auth_type_id'  => $auth_type_id
        ]);

        if(!$user )
        {
            return FALSE;
        }

        return $this->delete($user, $transaction);
    }

    // ----------------------------------------------------------------

    /**
     * Delete App User
     *
     * @param int|object $user
     * @param bool $transaction Use automatic transaction??
     * @return bool
     */
    public function delete($user, $transaction = TRUE)
    {
        $user   = is_numeric($user) ? parent::find(intval($user)) : $user;
        $id     = intval($user->id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        $status = TRUE;
        if($transaction)
        {
            // Use automatic transaction
            $this->db->trans_start();

                // Delete Primary Record
                parent::delete($id);

            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                // generate an error... or use the log_message() function to log your error
                $status = FALSE;
            }
        }
        else
        {
            // Insert Primary Record
            $status = parent::delete($id);
        }

        // Clear Cache on Success
        if($status)
        {
            $this->_clear_user_cache($user);
        }

        // return status
        return $status;
    }
}