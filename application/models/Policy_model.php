<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Policy_model extends MY_Model
{
    protected $table_name = 'dt_policies';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $protected_attributes = ['id'];

    protected $before_insert = ['prepare_policy_defaults'];
    // protected $before_update = ['prepare_contact_data', 'prepare_customer_fts_data'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = [ "id", "code", "branch_id", "customer_id", "portfolio_id", "policy_package", "sold_by", "object_id", "issue_date", "start_date", "end_date", "flag_dc", "status", "created_at", "created_by", "updated_at", "updated_by"];

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

        // Policy Configuration/Helper
        $this->load->config('policy');
        $this->load->helper('policy');

        // Set validation rules
        $this->set_validation_rules();
    }


    // ----------------------------------------------------------------

    /**
     * Set Validation Rules
     *
     * Set multi section Validation Rule for Policy Creation/Edit
     *
     * @return array
     */
    public function set_validation_rules()
    {
        $this->load->model('portfolio_model');
        $this->load->model('user_model');
        $this->load->model('agent_model');
        $select = ['' => 'Select ...'];

        /**
         * List all marketing staffs of this branch
         */
        $role_id = 7;
        $branch_id = $this->dx_auth->is_admin() ? NULL : $this->dx_auth->get_branch_id();

        /**
         * If posted and Direct Discount Checked, We don't need agent
         */

        $agent_validation = 'trim|required|integer|max_length[11]';
        if($this->input->post())
        {
            $flag_dc = $this->input->post('flag_dc');
            if($flag_dc == 'D')
            {
                $agent_validation = 'trim|integer|max_length[11]';
            }
        }

        $this->validation_rules = [

            /**
             * Customer Information
             */
            'customer' => [
                [
                    'field' => 'customer_id',
                    'label' => 'Customer',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type'     => 'hidden',
                    '_id'       => 'customer-id',
                    '_required' => true
                ],
                [
                    'field' => 'customer_name',
                    'label' => 'Customer',
                    'rules' => 'trim|required',
                    '_id'       => 'customer-text',
                    '_type'     => 'hidden',
                    '_required' => true
                ],
            ],

            /**
             * Portfolio Information
             */
            'portfolio' => [
                [
                    'field' => 'portfolio_id',
                    'label' => 'Portfolio',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type'     => 'dropdown',
                    '_id'       => '_portfolio-id',
                    '_data'     => $select + $this->portfolio_model->dropdown_parent(),
                    '_required' => true
                ],
                [
                    'field' => 'policy_package',
                    'label' => 'Policy Package',
                    'rules' => 'trim|required|alpha|max_length[10]',
                    '_type'     => 'dropdown',
                    '_id'       => '_policy-package-id',
                    '_data'     => IQB_BLANK_SELECT,
                    '_required' => true
                ]
            ],

            /**
             * Policy Object Information
             */
            'object' => [
                [
                    'field' => 'object_id',
                    'label' => 'Policy Object',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_type'     => 'hidden',
                    '_id'       => 'object-id', // dropdown policy object
                    '_required' => true
                ],
                [
                    'field' => 'object_name',
                    'label' => 'Policy Object',
                    'rules' => 'trim|required',
                    '_type'     => 'hidden',
                    '_id'       => 'object-text', // dropdown policy object
                    '_required' => true
                ],
            ],

            /**
             * Policy Duration Information
             */
            'duration' => [
                [
                    'field' => 'issue_date',
                    'label' => 'Policy Issue Date',
                    'rules' => 'trim|required|valid_date',
                    '_type'             => 'date',
                    '_default'          => date('Y-m-d'),
                    '_extra_attributes' => 'data-provide="datepicker-inline"',
                    '_required' => false
                ],
                [
                    'field' => 'start_date',
                    'label' => 'Policy Start Date',
                    'rules' => 'trim|required|valid_date',
                    '_type'             => 'date',
                    '_default'          => date('Y-m-d'),
                    '_extra_attributes' => 'data-provide="datepicker-inline"',
                    '_required' => false
                ],
                [
                    'field' => 'duration',
                    'label' => 'Policy Duration',
                    'rules' => 'trim|required|callback__cb_valid_policy_duration',
                    '_type'     => 'dropdown',
                    '_data'     => $select + get_policy_duration_list(),
                    '_default'  => '+1 year',
                    '_required' => false
                ],
            ],

            /**
             * Sales Info - Marketing Staff, Agent Info, Commission or Direct Discount
             */
            'sales' => [
                [
                    'field' => 'sold_by',
                    'label' => 'Marketing Staff',
                    'rules' => 'trim|required|integer|max_length[11]',
                    '_id'       => '_marketing-staff',
                    '_extra_attributes' => 'style="width:100%; display:block"',
                    '_type'     => 'dropdown',
                    '_data'     => $select + $this->user_model->dropdown($role_id, $branch_id),
                    '_required' => true
                ],
                [
                    'field' => 'flag_dc',
                    'label' => 'Direct Discount or Agent Commission',
                    'rules' => 'trim|required|alpha|exact_length[1]|in_list[D,C]',
                    '_id'       => '_flag-dc',
                    '_type'     => 'radio',
                    '_data'     => [ 'C' => 'Agent Commission', 'D' => 'Direct Discount'],
                    '_required' => true
                ],
                [
                    'field' => 'agent_id',
                    'label' => 'Agent Name',
                    'rules' => $agent_validation,
                    '_id'       => '_agent-id',
                    '_extra_attributes' => 'style="width:100%; display:block"',
                    '_type'     => 'dropdown',
                    '_data'     => $select + $this->agent_model->dropdown(true),
                    '_required' => true
                ]
            ]

        ];
    }

    // ----------------------------------------------------------------

    public function get_validation_rule($action)
    {
        // Valid action?
        if( !in_array($action, array('add', 'edit')))
        {
            return FALSE;
        }

        $v_rules = [];
        if ($action === 'add')
        {
            // Merge All Sections and return
            foreach($this->validation_rules as $section=>$rules)
            {
                $v_rules = array_merge($v_rules, $rules);
            }
        }
        else
        {

        }
        return $v_rules;
    }

    // ----------------------------------------------------------------

    /**
     * Prepare Policy Defaults
     *
     * Build policy default data before inserting into database.
     *
     * Default Data:
     *  code - random at this point
     *  branch_id - current user's branch id
     *  object_ref - policy object reference according to portfolio
     *  status - Draft by default
     *
     *
     * Before Insert Trigger to generate customer code and branch_id
     *
     * @param array $data
     * @return array
     */
    public function prepare_policy_defaults($data)
    {
        $this->load->library('Token');
        $this->load->model('portfolio_model');
        $this->load->model('fiscal_year_model');

        $portfolio_id   = $data['portfolio_id'];
        $portfolio_code = $this->portfolio_model->get_code($portfolio_id);

        $fy_record      = $this->fiscal_year_model->get_current_fiscal_year();
        $fy_code_np     = $fy_record->code_np;

        /**
         * Policy Code - Draft One
         *
         * Format: DRAFT-<BRANCH-CODE>-<PORTFOLIO-CODE>-<SERIALNO>-<FY_CODE_NP>
         */
        $policy_code = 'DRAFT/' . $this->dx_auth->get_branch_code() . '/' . $portfolio_code . '/' . strtoupper($this->token->generate(10)) . '/' . $fy_code_np;
        $data['code']           = $policy_code;

        // Branch ID
        $data['branch_id']      = $this->dx_auth->get_branch_id();

        // End Date
        $data['end_date']  = date('Y-m-d', strtotime( $data['duration'], strtotime($data['start_date']) ) );

        return $data;
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
        $this->db->select('P.id, P.code, P.branch_id, P.customer_id, P.portfolio_id, P.sold_by, P.object_id,  P.start_date, P.end_date, P.status, PRT.name_en as portfolio_name')
                 ->from($this->table_name . ' as P')
                 ->join('master_portfolio PRT', 'PRT.id = P.portfolio_id');

        /**
         * Apply User Scope
         */
        $this->dx_auth->apply_user_scope('P');


        if(!empty($params))
        {
            $next_id = $params['next_id'] ?? NULL;
            if( $next_id )
            {
                $this->db->where(['P.id <=' => $next_id]);
            }

            $code = $params['code'] ?? NULL;
            if( $code )
            {
                $this->db->like('LOWER(P.code)', strtolower($code), 'after');
            }

            // $type = $params['type'] ?? NULL;
            // if( $type )
            // {
            //     $this->db->where(['P.type' =>  $type]);
            // }

            // $company_reg_no = $params['company_reg_no'] ?? NULL;
            // if( $company_reg_no )
            // {
            //     $this->db->where(['P.company_reg_no' =>  $company_reg_no]);
            // }

            // $citizenship_no = $params['citizenship_no'] ?? NULL;
            // if( $citizenship_no )
            // {
            //     $this->db->where(['P.citizenship_no' =>  $citizenship_no]);
            // }

            // $passport_no = $params['passport_no'] ?? NULL;
            // if( $passport_no )
            // {
            //     $this->db->where(['P.passport_no' =>  $passport_no]);
            // }

            // $keywords = $params['keywords'] ?? '';
            // if( $keywords )
            // {
            //     $this->db->where("MATCH ( P.`fts` ) AGAINST ( '{$keywords}*' IN BOOLEAN MODE)", NULL);
            //     // $this->db->like('P.full_name', $keywords, 'after');
            // }
        }
        return $this->db->limit($this->settings->per_page+1)
                        ->order_by('P.id', 'desc')
                        ->get()->result();
    }

    // --------------------------------------------------------------------

    /**
     * Delete Cache on Update/Delete Records
     */
    public function clear_cache($data=null)
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
        $action = is_string($action) ? $action : 'C';
        // Save Activity Log
        $activity_log = [
            'module' => 'policy',
            'module_id' => $id,
            'action' => $action
        ];
        return $this->activity->save($activity_log);
    }
}