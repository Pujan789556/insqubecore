<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tariff Controller
 *
 * Master Portfoliio Tariff Controller
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Tariff extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Only Admin Can access this controller
		if( !$this->dx_auth->is_admin() )
		{
			$this->dx_auth->deny_access();
		}

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Master Setup | Tariff';

        // Setup Navigation
        $this->_navigation = [
			'level_0' => 'master_setup',
			'level_1' => 'portfolio',
			'level_2' => $this->router->class
		];
		$this->active_nav_primary($this->_navigation);

		// Load Model
		// $this->load->model('portfolio_model');

		// Helper
		$this->load->helper('object');
	}

	// --------------------------------------------------------------------

	/**
	 * Default Method
	 *
	 * Render the settings
	 *
	 * @return type
	 */
	function index()
	{
		// Let's Render Motor Tariff as Default
        redirect('tariff/motor');

	}

    // --------------------------------------------------------------------
    // TARIFF (MOTOR) - EXPLORE AND CRUD OPERATIONS
    // --------------------------------------------------------------------

    /**
     * Tariff Tariff - Motor
     *
     * List of all portfolio tariff fiscal-year-wise
     * @return void
     */
    public function motor( $method = "", $id_or_fy_id=null )
    {

    	// Check if we have details Method?
    	// tarrif_motor
    	/**
    	 * Valid Method?
    	 * 		Methods: add | edit | details
    	 *
    	 * Urls
    	 * 		/tariff/motor
    	 * 		/tariff/motor/add
    	 * 		/tariff/motor/details/<fiscal_year_id>
    	 * 		/tariff/motor/duplicate/<fiscal_year_id>
    	 * 		/tariff/motor/edit/<record_id>
    	 */
    	if( !empty($method) && !in_array($method, ['add', 'edit', 'duplicate', 'details']))
    	{
    		$this->template->render_404();
    	}

    	// Call the method other than "default"
    	if( $method )
    	{
    		$method_name = '__motor__' . $method;
    		return $this->{$method_name}($id_or_fy_id);
    	}


        // Site Meta
        $this->data['site_title'] = 'Master Setup | Tariff - Motor';

        $this->load->model('tariff_motor_model');

        /**
         * Update Nav Data
         */
        $this->_navigation['level_2'] = 'tariff';
        $this->_navigation['level_3'] = 'motor';
        $this->active_nav_primary($this->_navigation);

        $records = $this->tariff_motor_model->get_index_rows();


        $this->template->partial(
                            'content_header',
                            'templates/_common/_content_header',
                            [
                                'content_header' => 'Tariff - Motor',
                                'breadcrumbs' => ['Master Setup' => NULL, 'Tariff' => 'tariff', 'Motor' => NULL]
                        ])
                        ->partial('content', 'setup/tariff/motor/_index', compact('records'))
                        ->render($this->data);
    }

    // --------------------------------------------------------------------

    /**
     * List all tariff belonging to a single fiscal year
     *
     * @param integer $fiscal_year_id
     * @return void
     */
    private function __motor__details($fiscal_year_id)
    {

        $fiscal_year_id = (int)$fiscal_year_id;
        $this->load->model('tariff_motor_model');
        $records   = $this->tariff_motor_model->get_list_by_fiscal_year_rows($fiscal_year_id);
        if(!$records)
        {
            $this->template->render_404();
        }

        $single = $records[0];
        $fiscal_year_text = $single->fy_code_np . "({$single->fy_code_en})";

        // Site Meta
        $this->data['site_title'] = 'Master Setup | Motor Tariff - FY ' . $fiscal_year_text;

        $this->load->model('tariff_motor_model');


        /**
         * Update Nav Data
         */
        $this->_navigation['level_2'] = 'tariff_motor';
        $this->active_nav_primary($this->_navigation);


        $this->template->partial(
                            'content_header',
                            'templates/_common/_content_header',
                            [
                                'content_header' => 'Motor Tariff - FY - ' . $fiscal_year_text,
                                'breadcrumbs' => ['Master Setup' => NULL, 'Tariff' => 'tariff', 'Motor' => 'tariff/motor', 'Details' => NULL]
                        ])
                        ->partial('content', 'setup/tariff/motor/_index_by_fiscal_year', compact('records'))
                        ->render($this->data);
    }

    // --------------------------------------------------------------------


    /**
     * Add Default Tarrif Data
     *
     * @return void
     */
    private function __motor__add()
    {
        $this->load->model('tariff_motor_model');
        $rules = $this->tariff_motor_model->insert_validate_rules;

        if( $this->input->post() )
        {
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $data = $this->input->post();

                $fiscal_yr_id = $this->input->post('fiscal_yr_id');

                /**
                 * Insert Default Batch
                 */
                $ownership_list     = _PO_MOTOR_ownership_dropdown(FALSE);
                $sub_portfolio_list = _PO_MOTOR_sub_portfolio_dropdown(FALSE);
                $cvc_type_list      = _PO_MOTOR_CVC_type_dropdown(FALSE);

                // echo '<pre>'; print_r($cvc_type_list);exit;

                $batch_data = [];
                // $this->db->insert_batch('mytable', $data);
                foreach($ownership_list as $ownership=>$otext)
                {
                    /**
                     * Sub-portfolio: Motorcycle
                     */
                    $batch_data[] = [
                        'fiscal_yr_id'  => $fiscal_yr_id,
                        'sub_portfolio' => IQB_SUB_PORTFOLIO_MOTORCYCLE_CODE,
                        'ownership'     => $ownership,
                        'cvc_type'      => NULL
                    ];
                }
                foreach($ownership_list as $ownership=>$otext)
                {

                    /**
                     * Sub-portfolio: Private Vehicle
                     */
                    $batch_data[] = [
                        'fiscal_yr_id'  => $fiscal_yr_id,
                        'sub_portfolio' => IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_CODE,
                        'ownership'     => $ownership,
                        'cvc_type'      => NULL
                    ];
                }
                foreach($ownership_list as $ownership=>$otext)
                {

                    /**
                     * Sub-portfolio: Commercial Vehicle
                     */
                    foreach ($cvc_type_list as $cvc_type=>$ctext)
                    {
                        $batch_data[] = [
                            'fiscal_yr_id'  => $fiscal_yr_id,
                            'sub_portfolio' => IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_CODE,
                            'ownership'     => $ownership,
                            'cvc_type'      => $cvc_type
                        ];
                    }
                }

                $batch_data = array_filter($batch_data);
                $done = $this->tariff_motor_model->insert_batch($batch_data, TRUE);

                if(!$done)
                {

                	$status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                	// Clear Cache
                	$this->tariff_motor_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                }
            }
            else
            {
                $status = 'error';
                $message = 'Validation Error.';
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records    = $this->tariff_motor_model->get_index_rows();
                $html       = $this->load->view('setup/tariff/motor/_list', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                $form_data = [
                    'form_elements'         => $rules,
                    'record'                => null
                ];
                return $this->template->json([
                    'status'        => $status,
                    'message'       => $message,
                    'reloadForm'    => true,
                    'form'          => $this->load->view('setup/tariff/motor/_form_add', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view('setup/tariff/motor/_form_add',
            [
                'form_elements'         => $rules,
                'record'                => null
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Duplicate Motor Tariff form Old Fiscal Year
     *
     * @return void
     */
    private function __motor__duplicate($source_fiscal_year_id)
    {
        $this->load->model('tariff_motor_model');
        // Valid Record ?
        $source_fiscal_year_id   = (int)$source_fiscal_year_id;
        $source_record             = $this->tariff_motor_model->get_fiscal_year_row($source_fiscal_year_id);

        if(!$source_record)
        {
            $this->template->render_404();
        }

        $rules = $this->tariff_motor_model->insert_validate_rules;

        if( $this->input->post() )
        {
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $data = $this->input->post();

                $batch_data                 = [];
                $source_tarrif              = $this->tariff_motor_model->get_list_by_fiscal_year($source_fiscal_year_id);
                $destination_fiscal_year_id = $this->input->post('fiscal_yr_id');

                foreach($source_tarrif as $src)
                {
                    $source_record =(array)$src;

                    // Set Fiscal Year
                    $source_record['fiscal_yr_id'] = $destination_fiscal_year_id;

                    // Remoe Unnecessary Fields
                    unset($source_record['id']);
                    unset($source_record['created_at']);
                    unset($source_record['created_by']);
                    unset($source_record['updated_at']);
                    unset($source_record['updated_by']);

                    $batch_data[] = $source_record;
                }

                $batch_data = array_filter($batch_data);
                $done = $this->tariff_motor_model->insert_batch($batch_data, TRUE);

                if(!$done)
                {

                    $status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                    // Clear Cache
                    $this->tariff_motor_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                }
            }
            else
            {
                $status = 'error';
                $message = 'Validation Error.';
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records    = $this->tariff_motor_model->get_index_rows();
                $html       = $this->load->view('setup/tariff/motor/_list', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                $form_data = [
                    'form_elements'         => $rules,
                    'record'                => null,
                    'source_record'         => $source_record
                ];
                return $this->template->json([
                    'status'        => $status,
                    'message'       => $message,
                    'reloadForm'    => true,
                    'form'          => $this->load->view('setup/tariff/motor/_form_duplicate', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view('setup/tariff/motor/_form_duplicate',
            [
                'form_elements'         => $rules,
                'record'                => null,
                'source_record'         => $source_record
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Add Default Tarrif Data
     *
     * @return void
     */
    private function __motor__edit($id)
    {
    	$this->load->model('tariff_motor_model');

    	// Valid Record ?
		$id = (int)$id;
		$record = $this->tariff_motor_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}


        $rules = $this->tariff_motor_model->validation_rules;

        if( $this->input->post() )
        {
        	/**
        	 * Forma Validation Rule
        	 */
        	$v_rules = [];
        	foreach($rules as $group_name => $rule)
        	{
        		$v_rules = array_merge($v_rules, $rule);
        	}
            $this->form_validation->set_rules($v_rules);

            $data = $this->input->post();

            // echo '<pre>'; print_r($data);exit;

            if( $this->form_validation->run() === TRUE )
            {
                $data = $this->input->post();

                $post_data = [];
                /**
                 * Prepare Tariff
                 */
                $tariff = $data['tariff'];
                $tariff_count = count($tariff['ec_min']);
                $tariff_data = [];
                for($i = 0; $i < $tariff_count; $i++)
                {
                	$single_tarrif = [
                		'ec_min' => $tariff['ec_min'][$i],
                		'ec_max' => $tariff['ec_max'][$i],
	               		'rate' => [
							'age' 				=> $tariff['rate']['age'][$i],
							'rate' 				=> $tariff['rate']['rate'][$i],
							'minus_amount' 		=> $tariff['rate']['minus_amount'][$i],
							'ec_threshold' 		=> $tariff['rate']['ec_threshold'][$i],
							'cost_per_ec_above' => $tariff['rate']['cost_per_ec_above'][$i],
							'fragmented' 		=> $tariff['rate']['fragmented'][$i],
							'base_fragment' 	=> $tariff['rate']['base_fragment'][$i],
							'base_fragment_rate' => $tariff['rate']['base_fragment_rate'][$i],
							'rest_fragment_rate' => $tariff['rate']['rest_fragment_rate'][$i],
						],
						'age' => [
							'age1_min' => $tariff['age']['age1_min'][$i],
							'age1_max' => $tariff['age']['age1_max'][$i],
							'rate1' => $tariff['age']['rate1'][$i],
							'age2_min' => $tariff['age']['age2_min'][$i],
							'age2_max' => $tariff['age']['age2_max'][$i],
							'rate2' => $tariff['age']['rate2'][$i]
						],
						'third_party' => $tariff['third_party'][$i]
                	];

                	$tariff_data[] = $single_tarrif;
                }

                $post_data['tariff'] = json_encode($tariff_data);

                // Disabled Friendly Discount Rate
                $post_data['dr_mcy_disabled_friendly'] = $data['dr_mcy_disabled_friendly'];

                // Private Hire - Private Vehicle
                $post_data['rate_pvc_on_hire'] = $data['rate_pvc_on_hire'];

                // Commercial Vehicle : Personal Use
                $post_data['dr_cvc_on_personal_use'] = $data['dr_cvc_on_personal_use'];

                // Towing Premium Amount
                $post_data['pramt_towing'] = $data['pramt_towing'];

                /**
                 * No Claim Discount
                 */
                $no_claim_discount = $data['no_claim_discount'];
                $count = count($no_claim_discount['years']);
                $no_claim_discount_data = [];
                for($i=0; $i< $count; $i++)
                {
                	$no_claim_discount_data[] = [
                		'years' => $no_claim_discount['years'][$i],
                		'rate' => $no_claim_discount['rate'][$i]
                	];
                }
                $post_data['no_claim_discount'] = json_encode($no_claim_discount_data);

                /**
                 * Discount Rate Voluntary Excess
                 */
                $dr_voluntary_excess = $data['dr_voluntary_excess'];
                $count = count($dr_voluntary_excess['amount']);
                $dr_voluntary_excess_data = [];
                for($i=0; $i< $count; $i++)
                {
                	$dr_voluntary_excess_data[] = [
                		'amount' => $dr_voluntary_excess['amount'][$i],
                		'rate' => $dr_voluntary_excess['rate'][$i]
                	];
                }
                $post_data['dr_voluntary_excess'] = json_encode($dr_voluntary_excess_data);

                /**
                 * Permium Amount: Compulsory Excess
                 */
                $pramt_compulsory_excess = $data['pramt_compulsory_excess'];
                $count = count($pramt_compulsory_excess['min_age']);
                $pramt_compulsory_excess_data = [];
                for($i=0; $i< $count; $i++)
                {
                	$pramt_compulsory_excess_data[] = [
                		'min_age' => $pramt_compulsory_excess['min_age'][$i],
                		'max_age' => $pramt_compulsory_excess['max_age'][$i],
                		'amount' => $pramt_compulsory_excess['amount'][$i]
                	];
                }
                $post_data['pramt_compulsory_excess'] = json_encode($pramt_compulsory_excess_data);

                /**
                 * Motor Accident Premium
                 */
                $post_data['accident_premium'] = json_encode($data['accident_premium']);

                /**
                 * Risk Group
                 */
                $post_data['riks_group'] = json_encode($data['riks_group']);

                /**
                 * Insured Value Tariff (Coverred Amount)
                 */
                $post_data['insured_value_tariff'] = json_encode($data['insured_value_tariff']);



                /**
                 * Trailer/Trolly Tarrif
                 */
                $post_data['trolly_tariff'] = json_encode($data['trolly_tariff']);

                // Activate Tariff
	            $post_data['active'] = $data['active'] ?? 0;


                $done = $this->tariff_motor_model->update($record->id, $post_data, TRUE);

                if(!$done)
                {

                	$status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                	// Clear Cache
                	$this->tariff_motor_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                }
            }
            else
            {
                $status = 'error';
                $message = 'Validation Error.';
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records   = $this->tariff_motor_model->get_list_by_fiscal_year_rows($record->fiscal_yr_id);
                $html       = $this->load->view('setup/tariff/motor/_list_by_fiscal_year', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                $form_data = [
                    'form_elements'         => $rules,
                    'record'                => $record
                ];
                return $this->template->json([
                    'status'        => $status,
                    // 'message'       => $message,
                    'message'       => validation_errors(),
                    // 'reloadForm'    => true,
                    // 'form'          => $this->load->view('setup/tariff/motor/_form_edit', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view('setup/tariff/motor/_form_edit',
            [
                'form_elements'         => $rules,
                'record'                => $record
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Callback - Check Motor Tariff Duplicate
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function _cb_tariff_motor_check_duplicate($fiscal_yr_id, $id=NULL)
    {
    	$this->load->model('portfolio_setting_model');
    	$fiscal_yr_id = strtoupper( $fiscal_yr_id ? $fiscal_yr_id : $this->input->post('fiscal_yr_id') );
    	$tariff_ids = $this->input->post('tariff_ids');

        if( $this->tariff_motor_model->check_duplicate(['fiscal_yr_id' => $fiscal_yr_id], $tariff_ids))
        {
            $this->form_validation->set_message('_cb_tariff_motor_check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }


}