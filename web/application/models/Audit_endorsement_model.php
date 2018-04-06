<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_endorsement_model extends MY_Model
{
    protected $table_name = 'audit_endorsements';

    protected $skip_validation = TRUE;

    protected $set_created  = false;
    protected $set_modified = false;
    protected $log_user     = false;

    protected $protected_attributes = [];

    // protected $after_insert  = ['clear_cache'];
    // protected $after_update  = ['clear_cache'];
    // protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'endorsement_id', 'policy_id', 'object_id', 'customer_id', 'data_policy', 'data_object', 'data_customer', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


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

    // --------------------------------------------------------------------

    /**
     * Save Endorsement Audit Data
     *
     * @param int $endorsement_id
     * @param array $data
     * @return mixed
     */
    public function save($endorsement_id, $data)
    {
        /**
         * Since We are adding audit data separately frome each modules
         * i.e. Policy, Object, Customer
         * So, we need to check if already exists for this endorsement.
         */
        $id = $this->get_id_by_endorsement($endorsement_id);

        if( $id )
        {
            return parent::update($id, $data, TRUE);
        }
        else
        {
            return parent::insert($data, TRUE);
        }
    }

    // ----------------------------------------------------------------

    public function get_id_by_endorsement($endorsement_id)
    {
        $row = $this->db->select('id')
                        ->from($this->table_name)
                        ->where('endorsement_id', $endorsement_id)
                        ->get()->row();

        return $row->id ?? NULL;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
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

    // ----------------------------------------------------------------
}