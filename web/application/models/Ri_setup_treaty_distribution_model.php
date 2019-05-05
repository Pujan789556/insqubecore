<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ri_setup_treaty_distribution_model extends MY_Model
{
    protected $table_name = 'ri_setup_treaty_distribution';

    protected $set_created = true;

    protected $set_modified = true;

    protected $log_user = true;

    protected $audit_log = TRUE;

    protected $protected_attributes = [];

    // protected $before_insert = ['capitalize_code'];
    // protected $before_update = ['capitalize_code'];
    protected $after_insert  = ['clear_cache'];
    protected $after_update  = ['clear_cache'];
    protected $after_delete  = ['clear_cache'];

    protected $fields = ['treaty_id', 'broker_id', 'company_id', 'distribution_percent', 'flag_leader'];

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

    // ----------------------------------------------------------------

    /**
     * Validation Rules
     *
     * @return array
     */
    public function v_rules()
    {
        $this->load->model('company_model');
        $broker_dropdown        = $this->company_model->dropdown_brokers();
        $reinsurer_dropdown     = $this->company_model->dropdown_reinsurers();

        return [
            [
                'field' => 'broker_ids[]',
                'label' => 'Broker',
                'rules' => 'trim|integer|max_length[8]|in_list[' . implode( ',', array_keys($broker_dropdown) ) . ']',
                '_field'        => 'broker_id',
                '_type'         => 'dropdown',
                '_show_label'   => false,
                '_data'         => IQB_BLANK_SELECT + $broker_dropdown,
                '_required'     => true
            ],
            [
                'field' => 'reinsurer_ids[]',
                'label' => 'Reinsurer',
                'rules' => 'trim|required|integer|max_length[8]|in_list[' . implode( ',', array_keys($reinsurer_dropdown) ) . ']',
                '_field'        => 'company_id',
                '_type'         => 'dropdown',
                '_show_label'   => false,
                '_data'         => IQB_BLANK_SELECT + $reinsurer_dropdown,
                '_required'     => true
            ],
            [
                'field' => 'distribution_percent[]',
                'label' => 'Distribution %',
                'rules' => 'trim|required|prep_decimal|decimal|max_length[6]|callback__cb_distribution__complete',
                '_field'        => 'distribution_percent',
                '_type'         => 'text',
                '_show_label'   => false,
                '_required'     => true
            ]
        ];
    }

    // ----------------------------------------------------------------

    /**
     * Save Treaty Distribution
     *
     *
     * @param int $treaty_id Treaty ID
     * @param array $data Raw Form Data
     * @param array $old_distribution Array of Old distribution records
     * @return bool
     */
    public function save_distribution($treaty_id, $data, $old_distribution)
    {
        // Extract All Data
        $broker_ids             = $data['broker_ids'];
        $reinsurer_ids          = $data['reinsurer_ids'];
        $distribution_percent   = $data['distribution_percent'];

        // OLD Reinsurers
        $old_reinsurers_ids = [];
        $old_records = [];
        if($old_distribution)
        {
            foreach($old_distribution as $single)
            {
                $old_reinsurers_ids[] = $single->company_id;
                $old_records["{$single->company_id}"] = $single;
            }
        }
        asort($old_reinsurers_ids);


        // To del - Reinsurers
        $new_reinsurers_ids = array_values($reinsurer_ids);
        asort($new_reinsurers_ids);
        $to_del_reinsurers_ids = array_diff($old_reinsurers_ids, $new_reinsurers_ids);



        // -----------------------------------------------------------------------------
        $status                     = TRUE;
        // Use automatic transaction
        $this->db->trans_start();

            /**
             * Insert and Update
             */
            if( !empty($reinsurer_ids) && count($reinsurer_ids) === count($distribution_percent) )
            {
                for($i=0; $i < count($reinsurer_ids); $i++)
                {
                    $company_id             = $reinsurer_ids[$i];

                    $single_data = [
                        'treaty_id'             => $treaty_id,
                        'broker_id'             => $broker_ids[$i] ? $broker_ids[$i] : NULL,
                        'company_id'            => $company_id,
                        'distribution_percent'  => $distribution_percent[$i],
                        'flag_leader'           => $i == 0 ? IQB_FLAG_ON : IQB_FLAG_OFF
                    ];

                    /**
                     * Update Record if Already Exists else Insert
                     */
                    if( in_array($company_id, $old_reinsurers_ids) )
                    {
                        // Set Old Audit Record - Manually
                        $where = ['treaty_id' => $treaty_id, 'company_id' => $company_id];
                        $this->audit_old_record = $old_records["{$company_id}"];

                        // Update Data - Manually
                        $this->db->where($where)
                                 ->set($single_data)
                                 ->update($this->table_name);

                         // Save Audit Log - Manually
                         $this->save_audit_log([
                            'method' => 'update',
                            'id'     => NULL,
                            'fields' => $single_data
                        ],$where);
                        $this->audit_old_record = NULL;
                    }
                    else
                    {
                        parent::insert($single_data, TRUE);
                    }
                }
            }

            /**
             * Delete Unwanted Records
             */
            if($to_del_reinsurers_ids)
            {
                // Delete Old Records
                $this->db->where('treaty_id', $treaty_id)
                             ->where_in('company_id', $to_del_reinsurers_ids)
                             ->delete($this->table_name);

                foreach ($to_del_reinsurers_ids as $company_id)
                {
                    $this->audit_old_record = $old_records["{$company_id}"];;
                    $this->save_audit_log([
                        'method' => 'delete',
                        'id'     => NULL
                    ]);
                    $this->audit_old_record = NULL;
                }
            }

        // Commit all transactions on success, rollback else
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE)
        {
            // generate an error... or use the log_message() function to log your error
            $status = FALSE;
        }

        // -----------------------------------------------------------------------------

        // return result/status
        return $status;
    }


    // --------------------------------------------------------------------

    public function get_distribution_by_treaty($treaty_id)
    {
        return $this->db->select(
                        'TD.treaty_id, TD.broker_id, TD.company_id, TD.distribution_percent, TD.flag_leader, '.
                        'C.name_en as reinsurer_name, ' .
                        'B.name_en as broker_name'
                    )
                        ->from($this->table_name . ' TD')
                        ->join('master_companies C', 'C.id = TD.company_id')
                        ->join('master_companies B', 'B.id = TD.broker_id', 'left')
                        ->where('TD.treaty_id', $treaty_id)
                        ->order_by('TD.flag_leader', 'DESC')
                        ->get()->result();
    }



	// --------------------------------------------------------------------

    /**
     * Delete Cache on Update
     */
    public function clear_cache()
    {
        $cache_names = [
            'ri_pt_*'
        ];
        // cache name without prefix
        foreach($cache_names as $cache)
        {
            $this->delete_cache($cache);
        }
        return TRUE;
    }

    // ----------------------------------------------------------------

    public function delete_distribution($treaty_id, $company_ids)
    {
        $status         = FALSE;
        $old_records    = [];
        if($company_ids)
        {
            $old_records = $this->db->where('treaty_id', $treaty_id)
                                    ->where_in('company_id', $company_ids)
                                    ->get($this->table_name)
                                    ->result();
        }

        if($old_records)
        {
            // Delete The Records
            $status = $this->db->where('treaty_id', $treaty_id)
                             ->where_in('company_id', $company_ids)
                             ->delete($this->table_name);

            // Manually Audit Log
            foreach($old_records as $single)
            {
                $this->audit_old_record = $single;
                $this->save_audit_log([
                    'method' => 'delete',
                    'id' => NULL
                ]);
                $this->audit_old_record = NULL;
            }
        }

        return $status;
    }
}