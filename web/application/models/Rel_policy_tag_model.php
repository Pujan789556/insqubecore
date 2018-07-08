<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rel_policy_tag_model extends MY_Model
{
    protected $table_name = 'rel_policy__tag';

    protected $skip_validation = TRUE;

    protected $set_created  = false;
    protected $set_modified = false;
    protected $log_user     = false;

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

    public function save($policy_id, $tag_ids)
    {

        /**
         * Task 1: Delete Old Tags
         */
        $this->delete_by(['policy_id' => $policy_id]);

        /**
         * Task 2: Insert Batch
         */
        $batch_data = [];
        foreach($tag_ids as $tag_id)
        {
            $batch_data[] = [
                'policy_id' => $policy_id,
                'tag_id'    => $tag_id
            ];
        }

        if($batch_data)
        {
            return parent::insert_batch($batch_data, TRUE);
        }

        return FALSE;
    }


    // ----------------------------------------------------------------
}