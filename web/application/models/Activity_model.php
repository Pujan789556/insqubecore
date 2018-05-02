<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_model extends MY_Model
{

    protected $table_name = 'log_activities';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $fields = ["id", "module", "module_id", "action", "extra", "created_by", "created_at"];


    // --------------------------------------------------------------------

	function __construct()
	{
        parent::__construct();
	}

	// --------------------------------------------------------------------


    public function all($params = array())
    {
        $this->db->select('A.id, A.module, A.module_id, A.action, A.extra, A.created_by, A.created_at, U.username')
                ->from($this->table_name . ' A')
                ->join('auth_users U', 'U.id = A.created_by', 'left');

        if(!empty($params))
        {
            $this->db->where($params);
        }

        return $this->db->order_by('id', 'desc')
                    ->limit($this->settings->per_page+1)
                    ->get()->result();
    }

}