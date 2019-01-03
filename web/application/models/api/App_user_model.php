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

    protected $fields = [ 'id', 'mobile', 'auth_type', 'auth_type_id', 'password', 'last_ip', 'last_device', 'last_login', 'is_activated', 'pin_code', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

    /**
     * Register New App User
     *
     * @param array $post_data Form Post Data
     * @return mixed
     */
    public function register($data)
    {
        $data = $this->__build_register_data($data);


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

            /**
             * Task 3: Should Not Be Activated on Register
             */
            $prepared_data['is_activated'] = IQB_FLAG_OFF;

            return $prepared_data;
        }

	// --------------------------------------------------------------------

    public function activate($id)
    {
        $id = intval($id);
        $data = array(
            'is_activated' => IQB_FLAG_ON
        );

        return $this->update($id, $data, TRUE);
    }

    // --------------------------------------------------------------------

    public function deactivate($id)
    {
        $id = intval($id);
        $data = array(
            'is_activated' => IQB_FLAG_OFF
        );

        return $this->update($id, $data, TRUE);
    }

    // --------------------------------------------------------------------

    // UPDATE PIN CODE
    public function pin_code($id, $pin_code)
    {
        $id = intval($id);
        $data = array(
            'pin_code' => $pin_code
        );

        return $this->update($id, $data, TRUE);
    }

    // --------------------------------------------------------------------

    // Update Login History
    public function login_history($id, $ip_address, $device_id, $datetime)
    {
        $id = intval($id);
        $data = array(
            'last_ip'       => $ip_address,
            'last_device'   => $device_id,
            'last_login'    => $datetime
        );

        return $this->update($id, $data, TRUE);
    }

    // --------------------------------------------------------------------

    // Change password
    public function change_password($id, $new_pass)
    {
        $id             = intval($id);
        $password_hash  = password_hash($new_pass, PASSWORD_BCRYPT);
        $data           = array(
            'password' => $password_hash
        );

        return $this->update($id, $data, TRUE);
    }

    // ----------------------------------------------------------------

    // Perform App Login
    public function login($mobile, $password)
    {
        $record = $this->db->select('*')
                             ->from($this->table_name)
                             ->where('mobile', $mobile)
                             ->get()->row();

        if($record)
        {
            $hash = $record->password;
            if( password_verify ( $password , $hash ) )
            {
                return [
                    'id'            => $record->id,
                    'auth_type'     => $record->auth_type,
                    'auth_type_id'  => $record->auth_type_id
                ];
            }
        }
        return FALSE;
    }

    // ----------------------------------------------------------------


    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache()
    {
        $cache_names = [ ];
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

        // Use automatic transaction
        $status = TRUE;
        $this->db->trans_start();

            // Delete Primary Record
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
}