<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_policy_tag_model extends MY_Model
{
    protected $table_name = 'rel_policy__tag';

    protected $skip_validation = TRUE;

    protected $set_created  = FALSE;
    protected $set_modified = FALSE;
    protected $log_user     = FALSE;
    protected $audit_log    = TRUE;

    protected $protected_attributes = [];

    // protected $after_insert  = [];
    // protected $after_update  = [];
    // protected $after_delete  = [];

    protected $fields = ["tag_id", "policy_id"];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = FALSE;
    public static $protect_max_id = 0; // Prevent first 12 records from deletion.

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

    public function by_policy($policy_id, $tags_only = FALSE)
    {
        $list = parent::find_many_by(['policy_id'=>$policy_id]);
        if($tags_only)
        {
            $array = [];
            foreach($list as $row)
            {
                $array[] = $row->tag_id;
            }
            return $array;
        }
        return $list;
    }



    // --------------------------------------------------------------------

    /**
     * Save Relation
     * @param int $policy_id
     * @param array $tag_ids Tag IDs
     * @return bool
     */
    public function save($policy_id, $tag_ids)
    {
        /**
         * Old List
         */
        $old_ids = $this->by_policy($policy_id, TRUE);
        asort($old_ids);

        // sort new ids
        asort($tag_ids);


        // to del ids
        $to_del_tags = array_diff($old_ids, $tag_ids);

        // to insert ids
        $to_insert_tags = array_diff($tag_ids, $old_ids);


        /**
         * Task 1: Insert New
         */
        foreach($to_insert_tags as $tag_id)
        {
            $single_data = [
                'policy_id' => $policy_id,
                'tag_id'    => $tag_id
            ];
            parent::insert($single_data, TRUE);
        }


        /**
         * Task 2: Delete unwanted
         */
        foreach($to_del_tags as $tag_id)
        {
            $this->delete_single($policy_id, $tag_id, FALSE);
        }

        return TRUE;
    }

    // ----------------------------------------------------------------

    /**
     * Delete a Single Relation
     *
     * @param int $policy_id
     * @param int $tag_id
     * @param bool $use_automatic_transaction
     * @return bool
     */
    public function delete_single($policy_id, $tag_id, $use_automatic_transaction = TRUE)
    {
        $status = TRUE;

        /**
         * ==================== TRANSACTIONS BEGIN =========================
         */
        if($use_automatic_transaction)
        {
            $this->db->trans_start();
        }

                /**
                 * Task 1: Manually Delete Old Relations
                 */
                $where = ['policy_id' => $policy_id, 'tag_id' => $tag_id];
                $this->db->where($where)
                         ->delete($this->table_name);

                // --------------------------------------------------------------------

                /**
                 * Task 2: Manually Audit Log
                 */
                $this->audit_old_record = (object)$where;
                $this->save_audit_log([
                    'method' => 'delete',
                    'id'     => NULL
                ]);
                $this->audit_old_record = NULL;

                // --------------------------------------------------------------------

        if($use_automatic_transaction)
        {
            /**
             * Complete transactions or Rollback
             */
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE)
            {
                $status = FALSE;
            }
        }

        /**
         * ==================== TRANSACTIONS END =========================
         */

        return $status;
    }


    // ----------------------------------------------------------------
}