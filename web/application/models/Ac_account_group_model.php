<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ac_account_group_model extends MY_Model
{
    protected $table_name = 'ac_account_groups';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    // protected $protected_attributes = ['id', 'parent_id', 'range_min', 'range_max'];
    protected $protected_attributes = ['id'];

    // protected $before_insert = ['capitalize_code'];
    // protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'lft', 'rgt', 'parent_id', 'name', 'created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $validation_rules = [];


    /**
     * Protect Default Records?
     */
    public static $protect_default = TRUE;
    public static $protect_max_id = 11; // Prevent first 500 records from deletion.

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct()
    {
        parent::__construct();

        // Helper
        $this->load->helper('account');

        // Set validation rules
        $this->validation_rules();
    }

    // ----------------------------------------------------------------

    /**
     * Set Validation Rules
     *
     * @return void
     */
    public function validation_rules()
    {
        $dropdown_parent = $this->dropdown_tree(null, true, 'regular');
        $this->validation_rules = [
            'add' => [
                [
                    'field' => 'parent_id',
                    'label' => 'Parent Group',
                    'rules' => 'trim|integer|max_length[11]|in_list[' . implode(',', array_keys($dropdown_parent)) . ']|callback__cb_valid_parent',
                    '_id'       => '_parent-id',
                    '_extra_attributes' => 'style="width:100%; display:block"',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_ZERO_SELECT + $dropdown_parent,
                    '_required' => true
                ],
                [
                    'field' => 'name',
                    'label' => 'Group Name',
                    'rules' => 'trim|required|max_length[80]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],
            'edit' => [
                [
                    'field' => 'name',
                    'label' => 'Group Name',
                    'rules' => 'trim|required|max_length[80]',
                    '_type'     => 'text',
                    '_required' => true
                ]
            ],
            'move' => [
                [
                    'field' => 'parent_id',
                    'label' => 'Parent Group',
                    'rules' => 'trim|required|integer|max_length[11]|in_list[' . implode(',', array_keys($dropdown_parent)) . ']|callback__cb_valid_parent',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_ZERO_SELECT + $dropdown_parent,
                    '_required' => true
                ]
            ],
            'order' => [
                [
                    'field' => 'parent_id',
                    'label' => 'Parent Group',
                    'rules' => 'trim|required|integer|max_length[11]|in_list[' . implode(',', array_keys($dropdown_parent)) . ']|callback__cb_valid_parent',
                    '_type'     => 'dropdown',
                    '_data'     => IQB_ZERO_SELECT + $dropdown_parent,
                    '_required' => true
                ]
            ],
        ];
    }

    // --------------------------------------------------------------------

    /**
     * Add a Node
     *
     * @param array $data
     * @return bool
     */
    public function add($data)
    {

        // Step 1: Bind Parameters
        $task_type  = 'insert';
        $user_id    = $this->dx_auth->get_user_id();
        $id         = NULL;
        $parent_id  = $data['parent_id'] ? $data['parent_id'] : NULL;
        $name       = $data['name'];

        $bind_params = [$task_type, $user_id, $id, $parent_id, $name ];
        $sql = "CALL `r_acg_tree_traversal`(?, ?, ?, ?, ?)";


        $id = FALSE;
        // Use automatic transaction
        $this->db->trans_start();

            $result = mysqli_store_procedure('insert', $sql, $bind_params);

            if($result)
            {
                $id = $result[0]->id;
            }

            if($id)
            {

                /**
                 * Save Audit Log Manually
                 *
                 * This is required as it does not call parent::insert().
                 * Instead, the data is inserted using store procedure
                 */
                $row = (array)parent::find($id);
                unset($row['id']); // remove id field on insert
                $audit_data = [
                    'id'     => $id,
                    'fields' => $row,
                    'method' => 'insert'
                ];
                parent::save_audit_log($audit_data);


                // Clear Cache
                $this->clear_cache();
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();

        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $id = FALSE;
        }

        // return result/status
        return $id;
    }

    // --------------------------------------------------------------------

    /**
     * Move a Node
     *
     * @param array $data
     * @return bool
     */
    public function move($id, $data)
    {

        // Step 1: Bind Parameters
        $task_type  = 'move';
        $user_id    = $this->dx_auth->get_user_id();
        $id         = (int)$id;
        $parent_id  = (int)$data['parent_id'];
        $name       = NULL;

        $bind_params = [$task_type, $user_id, $id, $parent_id, $name ];
        $sql = "CALL `r_acg_tree_traversal`(?, ?, ?, ?, ?)";

        // For Audit Log
        $this->audit_old_record = parent::find($id);

        // Use automatic transaction
        $this->db->trans_start();

            $result = mysqli_store_procedure('update', $sql, $bind_params);

            if($result)
            {
                /**
                 * Save Audit Log Manually
                 *
                 * This is required as it does not call parent::update().
                 * Instead, the data is updated using store procedure
                 */
                $row = (array)parent::find($id);
                $audit_data = [
                    'id'     => $id,
                    'fields' => $row,
                    'method' => 'update'
                ];
                parent::save_audit_log($audit_data);

                // Clear Cache
                $this->clear_cache();
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $id = FALSE;
        }

        // return result/status
        return $id;
    }

    // --------------------------------------------------------------------

    /**
     * Order a Node
     *
     * @param array $data
     * @return bool
     */
    public function order($id, $data)
    {

        // Step 1: Bind Parameters
        $task_type  = 'order';
        $user_id    = $this->dx_auth->get_user_id();
        $id         = (int)$id;
        $parent_id  = (int)$data['parent_id'];
        $name       = NULL;

        $bind_params = [$task_type, $user_id, $id, $parent_id, $name ];
        $sql = "CALL `r_acg_tree_traversal`(?, ?, ?, ?, ?)";

        // For Audit Log
        $this->audit_old_record = parent::find($id);


        // Use automatic transaction
        $this->db->trans_start();

            $result = mysqli_store_procedure('update', $sql, $bind_params);

            if($result)
            {

                /**
                 * Save Audit Log Manually
                 *
                 * This is required as it does not call parent::update().
                 * Instead, the data is updated using store procedure
                 */
                $row = (array)parent::find($id);
                $audit_data = [
                    'id'     => $id,
                    'fields' => $row,
                    'method' => 'update'
                ];
                parent::save_audit_log($audit_data);

                // Clear Cache
                $this->clear_cache();
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();
        // Check Transaction Status
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $id = FALSE;
        }

        // return result/status
        return $id;
    }

    // --------------------------------------------------------------------

    /**
     * Get Path
     *
     * Get the full path of a Node from Root node
     *
     * @param array $data
     * @return bool
     */
    public function get_path($id)
    {

        $cache_name = 'ac_ag_path_' . $id;

        /**
         * Get Cached Result, If no, cache the query result
         */
        $path_result = $this->get_cache($cache_name);
        if(!$path_result)
        {
            /**
             * Step 1: Prepare Bind Query & Params
             */
            $bind_params  = [(int)$id];
            $sql = "CALL `r_acg_return_path`(?)";

            /**
             * Step 2: Call procedure to get Result
             */
            $path_result = mysqli_store_procedure('select', $sql, $bind_params);

            $this->write_cache($path_result, $cache_name, CACHE_DURATION_DAY);
        }

        return $path_result;

    }


    // --------------------------------------------------------------------

    /**
     * Get Dropdown List
     */
    public function dropdown_tree($start_at=null, $path_formatted=false, $path_separator = 'html')
    {
        $cache_name = $start_at ? 'ac_ag_dd_tree_' . $start_at : 'ac_ag_dd_tree_full';
        if($path_formatted == true)
        {
            $cache_name = $cache_name . $path_separator ;
        }

        /**
         * Get Cached Result, If no, cache the query result
         */
        $dropdown_tree = $this->get_cache($cache_name);
        if(!$dropdown_tree)
        {
            // Get Tree from DB
            $records = $this->tree($start_at);
            $dropdown_tree = [];
            foreach($records as $record)
            {
                if($path_formatted)
                {
                    $path = $this->get_path($record->id);
                    $record->name = ac_account_group_path_formatted( $path, '', $path_separator );
                }

                $dropdown_tree["{$record->id}"] = $record->name;
            }
            $this->write_cache($dropdown_tree, $cache_name, CACHE_DURATION_DAY);
        }

        return $dropdown_tree;
    }


    // --------------------------------------------------------------------

    public function tree($exclude=null, $stuff=' | ---')
    {
        /**
         * Step 1: Prepare Bind Query & Params
         */
        $exclude = $exclude ? (int)$exclude : NULL;
        $bind_params  = [$exclude, $stuff];
        $sql = "CALL `r_acg_return_tree`(?,?)";

        /**
         * Step 2: Call procedure to get Result
         */
        $result = mysqli_store_procedure('select', $sql, $bind_params);
        return $result;
    }

    // --------------------------------------------------------------------

    public function sub_tree($id)
    {
        /**
         * Step 1: Prepare Bind Query & Params
         */
        $id = (int)$id;
        $bind_params  = [$id];
        $sql = "CALL `r_acg_return_subtree`(?)";

        /**
         * Step 2: Call procedure to get Result
         */
        $result = mysqli_store_procedure('select', $sql, $bind_params);
        return $result;
    }


    // ----------------------------------------------------------------

    public function rows()
    {
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache('ac_ag_rows');
        if(!$list)
        {
            $this->_row_select();
            $list = $this->db->get()->result();

            $this->write_cache($list, 'ac_ag_rows', CACHE_DURATION_DAY);
        }
        return $list;
    }

    // ----------------------------------------------------------------

    public function row($id)
    {
        $this->_row_select();
        return $this->db->where('AG.id', $id)
                        ->get()->row();
    }

    // ----------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select('AG.id, AG.parent_id, AG.name, AG.lft, AG.rgt, AGP.name as parent_name')
                 ->from($this->table_name . ' as AG')
                 ->join($this->table_name . ' as AGP', 'AGP.id = AG.parent_id', 'left');
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
                        ->count_all_results($this->table_name);
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'ac_ag_rows',
            'ac_ag_path_*',
            'ac_ag_dd_tree_*',
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function delete_nodes($id, $type)
    {
        $id = intval($id);
        if( !safe_to_delete( get_class(), $id ) )
        {
            return FALSE;
        }

        // Step 1: Bind Parameters
        $task_type  = $type == 'node' ? 'delete-node' : 'delete-subtree';
        $user_id    = $this->dx_auth->get_user_id();
        $id         = (int)$id;
        $parent_id  = NULL;
        $name       = NULL;

        $bind_params = [$task_type, $user_id, $id, $parent_id, $name ];
        $sql = "CALL `r_acg_tree_traversal`(?, ?, ?, ?, ?)";

        // -----------------------------------------------------------------

        /**
         * Build Audit Log Data
         */
        $old_record = parent::find($id);
        $sub_tree = $this->sub_tree($id);
        $group_ids = [];
        $sub_tree_records = [];
        foreach ($sub_tree as $single)
        {
            // Only subtree, excluding this node
            if($single->id != $id)
            {
                $group_ids[] = $single->id;
            }
        }
        if(!empty($group_ids))
        {
            $sub_tree_records = $this->db->from($this->table_name)
                                    ->where_in('id', $group_ids)
                                    ->get()->result();
        }


        // -----------------------------------------------------------------


        $status = TRUE;

        // Use automatic transaction
        $this->db->trans_start();

            // Let's delete the record(s)
            $result = mysqli_store_procedure('delete', $sql, $bind_params);

            /**
             * Save Audit Log Manually
             */
            $this->_audit_log_on_delete($task_type, $old_record, $sub_tree_records);

            // Clear Cache
            $this->clear_cache();


        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            // get_allenerate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // return result/status
        return $status;
    }

        /**
         * Save audit log on Node or Sub-tree Deletion
         *
         * @param string $task_type [delete-node|delete-subtree]
         * @param object $old_record Top Node of the Sub-tree
         * @param array $old_sub_tree Subtree except Selected node
         * @return void
         */
        private function _audit_log_on_delete($task_type, $old_record, $old_sub_tree)
        {
            // Only Selected node is deleted, its children are re-arranged to its parent node
            if($task_type == 'delete-node')
            {
                // Audit Log - Deletion of Node
                $this->audit_old_record = $old_record;
                $audit_data = [
                    'id'     => $old_record->id,
                    'method' => 'delete'
                ];
                parent::save_audit_log($audit_data);
                $this->audit_old_record = NULL;

                // Audit Log - Updation of Position on subtree
                if($old_sub_tree)
                {
                    foreach($old_sub_tree as $single)
                    {
                        $this->audit_old_record = $single; // Old Record
                        $row = (array)parent::find($single->id); // Updated Record
                        $audit_data = [
                            'id'     => $single->id,
                            'fields' => $row,
                            'method' => 'update'
                        ];
                        parent::save_audit_log($audit_data);
                    }
                }
            }
            else
            {
                // Audit Log of all Deleted sub-tree
                if($old_sub_tree)
                {
                    // Include the top node of the tree
                    $old_sub_tree[] = $old_record;
                }
                else
                {
                    $old_sub_tree = [$old_record]; // No leafs
                }
                foreach($old_sub_tree as $single)
                {
                    // Audit Log - Deletion of Node
                    $this->audit_old_record = $single;
                    $audit_data = [
                        'id'     => $single->id,
                        'method' => 'delete'
                    ];
                    parent::save_audit_log($audit_data);
                    $this->audit_old_record = NULL;
                }
            }
        }
}