<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Endorsement_template_model extends MY_Model
{
    protected $table_name = 'master_endorsement_templates';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    // protected $before_insert = ['prepare_contact_data'];
    // protected $before_update = ['prepare_contact_data'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['id', 'portfolio_id', 'endorsement_type', 'title', 'body', 'created_at', 'created_by', 'updated_at', 'updated_by'];

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

        $this->load->model('portfolio_model');
        $this->load->helper('policy');

        // Load validation rules
        $this->validation_rules();
    }


    // ----------------------------------------------------------------

    public function validation_rules()
    {
        $e_type_dropdown = _ENDORSEMENT_type_dropdown(false);
        $this->validation_rules = [
            [
                'field' => 'portfolio_id',
                'label' => 'Portfolio',
                'rules' => 'trim|required|integer|max_length[11]',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $this->portfolio_model->dropdown_children_tree(),
                '_required' => true
            ],
            [
                'field' => 'endorsement_type',
                'label' => 'Endorsement Type',
                'rules' => 'trim|required|integer|max_length[2]|in_list[' . implode(',', array_keys($e_type_dropdown) ) . ']',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $e_type_dropdown,
                '_required' => true
            ],
            [
                'field' => 'title',
                'label' => 'Title',
                'rules' => 'trim|required|htmlspecialchars|max_length[250]',
                '_type'     => 'text',
                '_required' => true
            ],
            [
                'field' => 'body',
                'label' => 'Template Text',
                'rules' => 'trim|required|htmlspecialchars',
                '_type'     => 'textarea',
                '_required' => true
            ]
        ];
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
        $this->_row_select();


        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['ET.id >=' => $next_id]);
            }

            $portfolio_id = $params['portfolio_id'] ?? NULL;
            if( $portfolio_id )
            {
                $this->db->where(['ET.portfolio_id' =>  $portfolio_id]);
            }

            $endorsement_type = $params['endorsement_type'] ?? NULL;
            if( $endorsement_type )
            {
                $this->db->where(['ET.endorsement_type' =>  $endorsement_type]);
            }

            $keywords = $params['keywords'] ?? '';
            if( $keywords )
            {
                $this->db->where("MATCH ( ET.`title` ) AGAINST ( '{$keywords}*' IN BOOLEAN MODE)", NULL);
            }
        }
        return $this->db->limit($this->settings->per_page+1)
                    ->get()->result();
    }

    // --------------------------------------------------------------------

    public function row($id)
    {
        $this->_row_select();
        return $this->db->where('ET.id', $id)->get()->row();
    }

    // --------------------------------------------------------------------

    private function _row_select()
    {
        $this->db->select('ET.id, ET.portfolio_id, ET.endorsement_type, ET.title, ET.body, PRT.name_en AS portfolio_name_en')
                 ->from($this->table_name . ' as ET')
                 ->join('master_portfolio AS PRT', 'PRT.id = ET.portfolio_id');
    }

    // --------------------------------------------------------------------


    /**
     * Get Dropdown List
     */
    public function dropdown($portfolio_id, $txn_type)
    {
        $cache_name = 'etmpl_' . $portfolio_id . '_' . $txn_type;
        /**
         * Get Cached Result, If no, cache the query result
         */
        $list = $this->get_cache($cache_name);
        if(!$list)
        {
            $records = $this->db->select('ET.id, ET.portfolio_id, ET.endorsement_type, ET.title')
                                 ->from($this->table_name . ' as ET')
                                 ->join('master_portfolio AS PRT', 'PRT.id = ET.portfolio_id')
                                 ->where('ET.portfolio_id', $portfolio_id)
                                 ->where('ET.endorsement_type', $txn_type)
                                 ->get()->result();
            $list = [];
            foreach($records as $record)
            {
                $list["{$record->id}"] = _ENDORSEMENT_type_text($record->endorsement_type) . ' - ' . $record->title  ;
            }
            $this->write_cache($list, $cache_name, CACHE_DURATION_DAY);
        }

        return $list;
    }

	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
    {
        $cache_names = [
            'etmpl_*',
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
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }
        else
        {
            $this->log_activity($id, 'D');
        }

        // Enable db_debug if on development environment
        $this->db->db_debug = (ENVIRONMENT !== 'production') ? TRUE : FALSE;

        // return result/status
        return $status;
    }

    // ----------------------------------------------------------------

    /**
     * Log Activity
     *
     * Log activities
     *      Available Activities: Create|Edit|Delete
     *
     * @param integer $id
     * @param string $action
     * @return bool
     */
    public function log_activity($id, $action = 'C')
    {
        // $action = is_string($action) ? $action : 'C';
        // // Save Activity Log
        // $activity_log = [
        //     'module' => 'agent',
        //     'module_id' => $id,
        //     'action' => $action
        // ];
        // return $this->activity->save($activity_log);
    }
}