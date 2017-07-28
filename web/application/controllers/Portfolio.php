<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Portfolio Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Portfolio extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | Portfolio';

        // Setup Navigation
        $this->_navigation = [
			'level_0' => 'master_setup',
			'level_1' => 'portfolio',
			'level_2' => $this->router->class
		];
		$this->active_nav_primary($this->_navigation);

		// Load Model
		$this->load->model('portfolio_model');

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
		/**
		 * Normal Form Render
		 */
		$records = $this->portfolio_model->get_all();

		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Portfolio',
								'breadcrumbs' => ['Master Setup' => NULL, 'Portfolio' => NULL]
						])
						->partial('content', 'setup/portfolio/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Role
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->portfolio_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/portfolio/_form',
			[
				'form_elements' => $this->portfolio_model->validation_rules['basic'],
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Add a new Role
	 *
	 * @return void
	 */
	public function add()
	{
		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add');


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/portfolio/_form',
			[
				'form_elements' => $this->portfolio_model->validation_rules['basic'],
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Portfolio Specific Accounts
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function accounts($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->portfolio_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('accounts', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/portfolio/_form',
			[
				'form_elements' => $this->portfolio_model->validation_rules['accounts'],
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save($action, $record = NULL)
	{
		// Valid action?
		if( !in_array($action, array('add', 'edit', 'accounts')))
		{
			return [
				'status' => 'error',
				'message' => 'Invalid action!'
			];
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;

			$rules = $this->portfolio_model->validation_rules[ in_array($action, ['add', 'edit']) ? 'basic' : 'accounts'];
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				// Insert or Update?
				if($action === 'add')
				{
					// @NOTE: Activity Log will be automatically inserted
					$done = $this->portfolio_model->insert($data, TRUE); // No Validation on Model

					// Activity Log
					$done ? $this->portfolio_model->log_activity($done, 'C'): '';
				}
				else if ($action === 'accounts')
				{
					// Nullify Account ID if nothing supplied
					foreach($rules as $r)
					{
						$data[$r['field']] = $data[$r['field']] ? $data[$r['field']] : NULL;
					}

					// Now Update Data
					$done = $this->portfolio_model->save_accounts($record->id, $data) && $this->portfolio_model->log_activity($record->id, 'E');
				}
				else
				{
					// Now Update Data
					$done = $this->portfolio_model->update($record->id, $data, TRUE) && $this->portfolio_model->log_activity($record->id, 'E');
				}

				if(!$done)
				{
					$status = 'error';
					$message = 'Could not update.';
				}
				else
				{
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
			$success_html = '';
			if($status === 'success' )
			{
				if($action === 'add')
				{
					$records = $this->portfolio_model->get_all();
					$success_html = $this->load->view('setup/portfolio/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->portfolio_model->find($record->id);
					$success_html = $this->load->view('setup/portfolio/_single_row', ['record' => $record], TRUE);
				}
			}

			$return_data = [
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> $status === 'error',
				'hideBootbox' 	=> $status === 'success',
				'updateSection' => $status === 'success',
				'updateSectionData'	=> $status === 'success'
										? 	[
												'box' 	=> $action === 'add'
															? '#iqb-data-list'
															: '#_data-row-' . $record->id,
												'html' 	=> $success_html,

												//
												// How to Work with success html?
												// Jquery Method 	html|replaceWith|append|prepend etc.
												//
												'method' 	=> $action === 'add' ? 'html' : 'replaceWith'
											]
										: NULL,
				'form' 	  		=> $status === 'error'
									? 	$this->load->view('setup/portfolio/_form',
											[
												'form_elements' => $rules,
												'record' 		=> $record
											], TRUE)
									: 	null

			];
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

    /**
     * Flush Cache Data
     *
     * @return void
     */
    public function flush()
    {
        $this->portfolio_model->clear_cache();
        redirect($this->router->class);
    }

	// --------------------------------------------------------------------

	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->portfolio_model->find($id);

		if( !$record )
		{
			$this->template->render_404();
		}

		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];

		/**
		 * Safe to Delete?
		 */

		if( !safe_to_delete( 'portfolio_model', $id ) )
		{
			return $this->template->json($data);
		}

		// Admin Constraint?
		$done = $this->portfolio_model->delete($record->id);
		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-'.$record->id
			];
		}
		else
		{
			$data = [
				'status' 	=> 'error',
				'message' 	=> 'Could not be deleted. It might have references to other module(s)/component(s).'
			];
		}
		return $this->template->json($data);
	}

	// --------------------------------------------------------------------

	/**
     * Check Duplicate Callback
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function check_duplicate($code, $id=NULL)
    {

    	$code = strtoupper( $code ? $code : $this->input->post('code') );
    	$id   = $id ? (int)$id : (int)$this->input->post('id');

        if( $this->portfolio_model->check_duplicate(['code' => $code], $id))
        {
            $this->form_validation->set_message('check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }


    // --------------------------------------------------------------------
    // PORTFOLIO SETTINGS EXPLORE AND CRUD OPERATIONS
    // --------------------------------------------------------------------

    /**
     * Portfolio Settings
     *
     * List of all portfolio settings fiscal-year-wise
     * @return void
     */
    public function settings()
    {
    	// Site Meta
    	$this->data['site_title'] = 'Master Setup | Portfolio Settings';

    	$this->load->model('portfolio_setting_model');
    	/**
    	 * Update Nav Data
    	 */
    	$this->_navigation['level_2'] = 'settings';
    	$this->active_nav_primary($this->_navigation);

    	$records = $this->portfolio_setting_model->get_row_list();


		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Portfolio Settings',
								'breadcrumbs' => ['Master Setup' => NULL, 'Portfolio' => 'portfolio', 'Settings' => NULL]
						])
						->partial('content', 'setup/portfolio/_settings', compact('records'))
						->render($this->data);
  	}

  	// --------------------------------------------------------------------

	/**
	 * Add new Settings for a Specific Fiscal Year
	 *
	 * @return void
	 */
	public function add_settings()
	{
		$this->load->model('portfolio_setting_model');

		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save_settings('add');



		$portfolios_tree 		= $this->portfolio_model->dropdown_children_tree();
		$children_portfolios 	= $this->portfolio_model->dropdown_children();

		// Short term policy rate Validation Rules
		$spr_validation_rules = $this->__settings_stpr_validation_rules($children_portfolios, []);

		$json_data['form'] = $this->load->view('setup/portfolio/_form_settings',
			[
				'form_elements' 			=> $this->portfolio_setting_model->fy_validation_rules(),
				'sectioned_elements' 		=> $this->portfolio_setting_model->sectioned_validation_rules(),
				'spr_validation_rules' 		=> $spr_validation_rules,
				'portfolios_tree' 		=> $portfolios_tree,
				'action' 				=> 'add',
				'record' 				=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

		/**
		 * Get Short Term Policy Rate - Validation Rules
		 *
		 * @param array $child_portfolios Portfolio Dropdown (All Children)
		 * @return array
		 */
		private function __settings_stpr_validation_rules($child_portfolios, $settings=[], $formatted=false)
		{
			$spr_validation_rules = [];

			foreach($child_portfolios as $portfolio_id=>$portfolio_name)
			{
				/**
				 * If Formatted, We only return to Run Validation Rule
				 */
				if($formatted)
				{
					$spr_validation_rules = array_merge($spr_validation_rules, $this->__settings_stpr_validation_rule_single($portfolio_id));
				}
				else
				{
					$counter_spr = 0;
                    $stpr_post_data = $this->input->post("stpr[PORT_{$portfolio_id}]");
                    if($settings)
                    {
                    	foreach($settings as $t)
		                {
		                	if( $t->portfolio_id == $portfolio_id)
		                    {
	                    		// Format: [{"title":"One Week", "duration": 7, "rate": 10.00}]
	                            $short_term_policy_rate_list = $t->short_term_policy_rate ? json_decode($t->short_term_policy_rate) : [];
	                            foreach($short_term_policy_rate_list as $spr)
	                            {
	                            	$spr_data = [
	                            		'title' 	=> $stpr_post_data['title'][$counter_spr] ?? $spr->title,
	                            		'duration' 	=> $stpr_post_data['duration'][$counter_spr] ?? $spr->duration,
	                            		'rate' 		=> $stpr_post_data['rate'][$counter_spr] ?? $spr->rate
	                            	];

	                            	$spr_validation_rules["PORT_{$portfolio_id}"][] = $this->__settings_stpr_validation_rule_single($portfolio_id, $spr_data);

	                                $counter_spr++;
	                            }
		                    }
		                }
                    }


	                // Let's check if we have still more element per portfolio - On post request
	                // Or Post on Add
	                $counter_spr_post_total = count($stpr_post_data['title'] ?? []);
	                if($counter_spr_post_total > $counter_spr)
	                {
	                	for($i = $counter_spr; $i < $counter_spr_post_total; $i++)
	                	{
	                		$spr_data = [
                        		'title' 	=> $stpr_post_data['title'][$i] ?? '',
                        		'duration' 	=> $stpr_post_data['duration'][$i] ?? '',
                        		'rate' 		=> $stpr_post_data['rate'][$i] ?? ''
                        	];
                            $spr_validation_rules["PORT_{$portfolio_id}"][] = $this->__settings_stpr_validation_rule_single($portfolio_id, $spr_data);
	                	}
	                }

	                // If empty, get the default validation rules
	                if( !isset($spr_validation_rules["PORT_{$portfolio_id}"]) || empty($spr_validation_rules["PORT_{$portfolio_id}"]) )
	                {
	                	$spr_validation_rules["PORT_{$portfolio_id}"][] = $this->__settings_stpr_validation_rule_single($portfolio_id);
	                }
				}
			}

			// echo '<pre>'; print_r($spr_validation_rules);exit;
			return $spr_validation_rules;
		}

		private function __settings_stpr_validation_rule_single($portfolio_id, $data=[])
		{
			return [
                [
                    'field'     => "stpr[PORT_{$portfolio_id}][title][]",
                    'label'     => 'Title',
                    'rules' 	=> 'trim|required|max_length[50]',
                    'value'     => $data['title'] ?? '',
                    'type'      => 'text',
                    '_required' => true
                ],
                [
                    'field'     => "stpr[PORT_{$portfolio_id}][duration][]",
                    'label'     => 'Duration (Days)',
                    'rules' 	=> 'trim|required|integer|max_length[3]',
                    'value'     => $data['duration'] ?? '',
                    'type'      => 'text',
                    '_required' => true
                ],
                [
                    'field'     => "stpr[PORT_{$portfolio_id}][rate][]",
                    'label'     => 'Rate (%)',
                    'rules' 	=> 'trim|required|prep_decimal|decimal|max_length[5]',
                    'value'     => $data['rate'] ?? '',
                    'type'      => 'text',
                    '_required' => true
                ]
            ];
		}

	// --------------------------------------------------------------------

	/**
	 * Edit Portfolio Settings for Specific Fiscal Year
	 *
	 *
	 * @param integer $fiscal_yr_id
	 * @return void
	 */
	public function edit_settings($fiscal_yr_id)
	{
		$this->load->model('portfolio_setting_model');

		// Valid Record ?
		$fiscal_yr_id 	= (int)$fiscal_yr_id;
		$record 		= $this->portfolio_setting_model->get_row_single($fiscal_yr_id);
		$settings 		= $this->portfolio_setting_model->get_list_by_fiscal_year($fiscal_yr_id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save_settings('edit', $record, $settings, $settings);


		// No form Submitted?

		$portfolios_tree 		= $this->portfolio_model->dropdown_children_tree();
		$children_portfolios 	= $this->portfolio_model->dropdown_children();

		$rules = $this->portfolio_setting_model->validation_rules;
		$rules[0]['_data'] = IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown();

		// Short term policy rate Validation Rules
		$spr_validation_rules = $this->__settings_stpr_validation_rules($children_portfolios, $settings);

		$json_data['form'] = $this->load->view('setup/portfolio/_form_settings',
			[
				'form_elements' 			=> $this->portfolio_setting_model->fy_validation_rules(),
				'sectioned_elements' 		=> $this->portfolio_setting_model->sectioned_validation_rules(),
				'spr_validation_rules' 		=> $spr_validation_rules,
				'action' 			=> 'edit',
				'portfolios_tree' 	=> $portfolios_tree,
				'settings' 			=> $settings,
				'record' 			=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Save Targets for Specific Fiscal Year
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save_settings($action, $record = NULL, $settings = NULL)
	{

		// Valid action?
		if( !in_array($action, array('add', 'edit')))
		{
			return [
				'status' => 'error',
				'message' => 'Invalid action!'
			];
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{

			// $portfolios = $this->portfolio_model->dropdown_parent();

			$portfolios_tree 		= $this->portfolio_model->dropdown_children_tree();
			$children_portfolios 	= $this->portfolio_model->dropdown_children();

			$done = FALSE;

			$rules = $this->portfolio_setting_model->validation_rules;

			// Short term policy rate Validation Rules
			$spr_validation_rules = $this->__settings_stpr_validation_rules($children_portfolios, [], true);

			// Merge both rules
			$v_rules = array_merge($rules, $spr_validation_rules);

			$this->form_validation->set_rules($v_rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				$fiscal_yr_id 		= $this->input->post('fiscal_yr_id');
				$agent_commission 	= $this->input->post('agent_commission');
				$bs_service_charge 	= $this->input->post('bs_service_charge');
				$direct_discount 	= $this->input->post('direct_discount');
				$stamp_duty  		= $this->input->post('stamp_duty');
				$default_duration  	= $this->input->post('default_duration');
				$flag_short_term  	= $this->input->post('flag_short_term');



				// Insert or Update?
				if($action === 'add')
				{
					$i = 0;
					foreach($children_portfolios as $portfolio_id => $portfolio_name)
					{
						$data = [
							'fiscal_yr_id' 		=> $fiscal_yr_id,
							'portfolio_id'    	=> $portfolio_id,
							'agent_commission' 	=> $agent_commission[$i],
							'bs_service_charge' => $bs_service_charge[$i],
							'direct_discount' 	=> $direct_discount[$i],
							'stamp_duty' 		=> $stamp_duty[$i],
							'default_duration' 	=> $default_duration[$i],
							'flag_short_term' 	=> $flag_short_term[$i]
						];

						// Short Term Policy Rate
						$spr_post_data = $this->input->post("stpr[PORT_{$portfolio_id}]");
						$spr_count = count($spr_post_data['title']?? []);
						$spr_data = [];
						for($j=0; $j<$spr_count; $j++)
						{
							$spr_data[] = [
								'title' 	=> $spr_post_data['title'][$j],
								'duration' 	=> $spr_post_data['duration'][$j],
								'rate' 		=> $spr_post_data['rate'][$j],
							];
						}
						$json_spr_data = !empty($spr_data) ? json_encode($spr_data) : NULL;

						// Add to update data
						$data['short_term_policy_rate']  = $json_spr_data;

						$done = $this->portfolio_setting_model->insert($data, TRUE); // No Validation on Model

						// Activity Log
						$done ? $this->portfolio_setting_model->log_activity($done, 'C'): '';
						$i++;
					}
				}
				else
				{
					// Now Update Data
					$setting_ids = $this->input->post('setting_ids');
					$i = 0;
					foreach($children_portfolios as $portfolio_id => $portfolio_name)
					{
						$data = [
							'agent_commission' 	=> $agent_commission[$i],
							'bs_service_charge' => $bs_service_charge[$i],
							'direct_discount' 	=> $direct_discount[$i],
							'stamp_duty' 		=> $stamp_duty[$i],
							'default_duration' 	=> $default_duration[$i],
							'flag_short_term' 	=> $flag_short_term[$i]
						];
						$setting_id = $setting_ids[$i];

						// Short Term Policy Rate
						$spr_post_data = $this->input->post("stpr[PORT_{$portfolio_id}]");
						$spr_count = count($spr_post_data['title'] ?? []);
						$spr_data = [];
						for($j=0; $j<$spr_count; $j++)
						{
							$spr_data[] = [
								'title' 	=> $spr_post_data['title'][$j],
								'duration' 	=> $spr_post_data['duration'][$j],
								'rate' 		=> $spr_post_data['rate'][$j],
							];
						}
						$json_spr_data = !empty($spr_data) ? json_encode($spr_data) : NULL;

						// Add to update data
						$data['short_term_policy_rate'] = $json_spr_data;
						$done = $this->portfolio_setting_model->update($setting_id, $data, TRUE) && $this->portfolio_setting_model->log_activity($setting_id, 'E');

						$i++;
					}
				}

				if(!$done)
				{
					$status = 'error';
					$message = 'Could not update.';
				}
				else
				{
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
			$success_html = '';
			if($status === 'success' )
			{
				if($action === 'add')
				{
					$records = $this->portfolio_setting_model->get_row_list();
					$success_html = $this->load->view('setup/portfolio/_list_settings', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->portfolio_setting_model->get_row_single($fiscal_yr_id);
					$success_html = $this->load->view('setup/portfolio/_single_row_settings', ['record' => $record], TRUE);
				}
			}

			$rules 				= $this->portfolio_setting_model->validation_rules;
			$rules[0]['_data'] 	= IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown();
			$settings 			= $record ? $this->portfolio_setting_model->get_list_by_fiscal_year($record->fiscal_yr_id) : NULL;
			$spr_validation_rules = $this->__settings_stpr_validation_rules($children_portfolios, $settings);
			$return_data = [
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> $status === 'error',
				'hideBootbox' 	=> $status === 'success',
				'updateSection' => $status === 'success',
				'updateSectionData'	=> $status === 'success'
										? 	[
												'box' 	=> $action === 'add'
															? '#iqb-data-list'
															: '#_data-row-' . $record->fiscal_yr_id,
												'html' 	=> $success_html,

												//
												// How to Work with success html?
												// Jquery Method 	html|replaceWith|append|prepend etc.
												//
												'method' 	=> $action === 'add' ? 'html' : 'replaceWith'
											]
										: NULL,
				'form' 	  		=> $status === 'error'
									? 	$this->load->view('setup/portfolio/_form_settings',
											[
												'form_elements' 			=> $this->portfolio_setting_model->fy_validation_rules(),
												'sectioned_elements' 		=> $this->portfolio_setting_model->sectioned_validation_rules(),
												'record' 		=> $record,
												'action' 		=> $action,
												'portfolios_tree' 	=> $portfolios_tree,
												'settings' 		=> $settings,
												'spr_validation_rules' => $spr_validation_rules
											], TRUE)
									: 	null

			];
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
     * Callback - Check Setting Duplicate
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function _cb_settings_check_duplicate($fiscal_yr_id, $id=NULL)
    {
    	$this->load->model('portfolio_setting_model');
    	$fiscal_yr_id = strtoupper( $fiscal_yr_id ? $fiscal_yr_id : $this->input->post('fiscal_yr_id') );
    	$setting_ids = $this->input->post('setting_ids');

        if( $this->portfolio_setting_model->check_duplicate(['fiscal_yr_id' => $fiscal_yr_id], $setting_ids))
        {
            $this->form_validation->set_message('_cb_settings_check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

	/**
	 * Import Missing Portfolio Settings for Specific Fiscal Year
	 *
	 * 	We will simply create missing portfolios default entry.
	 *
	 * @param integer $fiscal_yr_id
	 * @return void
	 */
	public function import_missing_settings($fiscal_yr_id)
	{
		$this->load->model('portfolio_setting_model');

		// Valid Record ?
		$fiscal_yr_id 	= (int)$fiscal_yr_id;
		$existing_child_portfolios 	= $this->portfolio_setting_model->get_portfolios_by_fiscal_year($fiscal_yr_id);
		$all_child_portfolios 		= $this->portfolio_model->get_children();

		$existing = [];
		$all = [];
		foreach($existing_child_portfolios as $e)
		{
			$existing[] = $e->portfolio_id;
		}
		foreach($all_child_portfolios as $a)
		{
			$all[] = $a->id;
		}

		$existing = array_values($existing);
		$all = array_values($all);

		asort($existing);
		asort($all);

		$missing = array_diff($all, $existing);
		$count = count($missing);
		if( count($missing) )
		{
			$batch_data = [];

			foreach($missing as $portfolio_id)
			{
				$batch_data[] = [
					'fiscal_yr_id' => $fiscal_yr_id,
					'portfolio_id' => $portfolio_id,
				];
			}

			if ( !$this->portfolio_setting_model->insert_batch($batch_data, TRUE) )
			{
				return $this->template->json([
					'status' => 'error',
					'message' => 'Could not import missing portfolios.'
				]);
			}
		}
		return $this->template->json([
			'status' => 'success',
			'message' => "Successfully imported {$count} portfolios."
		]);

	}

	// --------------------------------------------------------------------

    /**
     * Duplicate Portfolio Settings form Old Fiscal Year Settings
     *
     * @return void
     */
    public function duplicate_settings($source_fiscal_year_id)
    {
    	$this->load->model('portfolio_setting_model');

		// Valid Record ?
		$source_fiscal_year_id 		= (int)$source_fiscal_year_id;
		$source_record 				= $this->portfolio_setting_model->get_row_single($source_fiscal_year_id);

        if(!$source_record)
        {
            $this->template->render_404();
        }

        $rules = [
            [
                'field' => 'fiscal_yr_id',
                'label' => 'Fiscal Year',
                'rules' => 'trim|required|integer|max_length[3]|callback__cb_settings_check_duplicate',
                '_type'     => 'dropdown',
                '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
                '_required' => true
            ]
        ];

        if( $this->input->post() )
        {
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $data = $this->input->post();

                $batch_data                 = [];
                $source_settings 			= $this->portfolio_setting_model->get_src_list_by_fiscal_year($source_fiscal_year_id);
                $destination_fiscal_year_id = $this->input->post('fiscal_yr_id');

                foreach($source_settings as $src)
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
                $done = $this->portfolio_setting_model->insert_batch($batch_data, TRUE);

                if(!$done)
                {
                    $status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                    // Clear Cache
                    $this->portfolio_setting_model->clear_cache();

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

                $records = $this->portfolio_setting_model->get_row_list();
				$html = $this->load->view('setup/portfolio/_list_settings', ['records' => $records], TRUE);

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
                    'form'          => $this->load->view('setup/portfolio/_form_duplicate', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view('setup/portfolio/_form_duplicate',
            [
                'form_elements'         => $rules,
                'record'                => null,
                'source_record'         => $source_record
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

}