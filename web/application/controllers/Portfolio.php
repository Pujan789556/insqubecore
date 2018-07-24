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
	/**
	 * Files Upload Path
	 */
	public static $upload_path = INSQUBE_MEDIA_PATH . 'portfolio/';

	// --------------------------------------------------------------------

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
	 * Edit a Portfolio Specific Risks
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function risks($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->portfolio_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('risks', $record);

		// Add already checked values
		$rules = $this->portfolio_model->validation_rules['risks'];
		$rules[0]['_checkbox_value'] = $record->risk_ids ? explode(',', $record->risk_ids) : [];

		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/portfolio/_form',
			[
				'form_elements' => $rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Portfolio Specific Risks - JSON
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function risks_json($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->portfolio_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('risks_json', $record);

		// Add already checked values
		$rules = $this->portfolio_model->validation_rules['risks_json'];


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/portfolio/_form_risks',
			[
				'form_elements' => $rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Portfolio Specific Beema Samiti Report Setup - Heading Types
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function bsrs_headings($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->portfolio_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('bsrs_headings', $record);

		// Add already checked values
		$rules = $this->portfolio_model->validation_rules['bsrs_headings'];
		$rules[0]['_checkbox_value'] = $record->bsrs_heading_type_ids ? explode(',', $record->bsrs_heading_type_ids) : [];

		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/portfolio/_form',
			[
				'form_elements' => $rules,
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
		if( !in_array($action, array('edit', 'accounts', 'risks', 'risks_json', 'bsrs_headings')))
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			],404);
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;

			$rules = $this->_v_rule($action);
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				/**
				 * File upload in edit mode
				 */
				$file_toc = $record->file_toc ?? NULL;
				if( $action === 'edit' )
				{
					/**
					 * Upload toc file if any?
					 */
					$upload_result 	= $this->_upload_file($file_toc);
					$status 		= $upload_result['status'];
					$message 		= $upload_result['message'];

					if( $status === 'error')
					{
						return $this->template->json([
							'title'  	=> 'Upload Failed!',
							'status' 	=> $status,
							'message' 	=> $message
						]);
					}

					$files 			= $upload_result['files'];
					$file_toc = $status === 'success' ? $files[0] : $file_toc;
					$data['file_toc'] = $file_toc;
				}

				if ($action === 'accounts')
				{
					// Nullify Account ID if nothing supplied
					$account_data = [];
					foreach($rules as $r)
					{
						$account_data[$r['field']] = $data[$r['field']] ? $data[$r['field']] : NULL;
					}

					// Now Update Data
					$done = $this->portfolio_model->update($record->id, $account_data, TRUE);
				}
				else if ($action === 'risks')
				{
					$risk_ids = $data['risks'] ?? NULL;
					$risk_data = [
						'risk_ids' => $risk_ids ? implode(',', $risk_ids) : NULL
					];

					// Now Update Data
					$done = $this->portfolio_model->update($record->id, $risk_data, TRUE);
				}
				else if ($action === 'risks_json')
				{
					// Format JSON Data
					$risk_data['risks'] = $this->_format_risk_json($data);

					// // Now Update Data
					$done = $this->portfolio_model->update($record->id, $risk_data, TRUE);
				}
				else if ($action === 'bsrs_headings')
				{
					// Nullify Account ID if nothing supplied
					$bsrs_headings = $data['bsrs_headings'] ?? NULL;
					$bsrs_heading_data = [
						'bsrs_heading_type_ids' => $bsrs_headings ? implode(',', $bsrs_headings) : NULL
					];

					// Now Update Data
					$done = $this->portfolio_model->update($record->id, $bsrs_heading_data, TRUE);
				}
				else
				{
					// Basic Information Edit Mode
					$done = $this->portfolio_model->update($record->id, $data, TRUE);
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
				return $this->template->json([
					'title'  => 'Validation Failed!',
					'status' => 'error',
					'message' => validation_errors()
				]);
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
				'reloadForm' 	=> false,
				'hideBootbox' 	=> true,
				'updateSection' => true,
				'updateSectionData'	=> [
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
			];
		}

		return $return_data;
	}
		/**
		 * Validation rules
		 *
		 * @param string $action
		 * @return array
		 */
		private function _v_rule($action)
		{
			$rules = [];
			switch($action)
			{
				case 'add':
				case 'edit':
					$rules = $this->portfolio_model->validation_rules['basic'];
					break;

				case 'accounts':
				case 'risks':
				case 'risks_json':
				case 'bsrs_headings':
					$rules = $this->portfolio_model->validation_rules[$action];
					break;

				default:
					break;
			}

			return $rules;
		}

		// --------------------------------------------------------------------

		/**
		 * Format Risk JSON data from "Form Submission"
		 *
		 * @param array $post_data
		 * @return JSON
		 */
		private function _format_risk_json ($post_data)
		{
			$risk_keys = ['code', 'name', 'type', 'default_min_premium'];
			$risks = $post_data['risks'];

			$json_data = [
				'default_premium_computation' => $risks['default_premium_computation']
			];
			for($i = 0; $i < count($risks['name']); $i++ )
			{
				$single_object = [];
				foreach($risk_keys as $key)
				{
					$single_object[$key] = $risks[$key][$i] ?? NULL;
				}
				$json_data['risks'][] = $single_object;
			}

			return json_encode($json_data);
		}

		// --------------------------------------------------------------------

		/**
		 * Callback - Valid Risk Code
		 * Must Be Unique
		 *
		 * @param type $code
		 * @return type
		 */
		public function _cb_risks_check_duplicate($code)
		{
			$codes 	= $this->input->post()['risks']['code'];
			$total 	= count($codes);
			$unique = array_unique(array_filter($codes));

	        if( $total !== count($unique) )
	        {
	            $this->form_validation->set_message('_cb_risks_check_duplicate', 'The %s must be unique alphabetical characters.');
	            return FALSE;
	        }
	        return TRUE;
		}

		// --------------------------------------------------------------------

		/**
		 * Sub-function: Upload Company Profile Picture
		 *
		 * @param string|null $old_document
		 * @return array
		 */
		private function _upload_file( $old_document = NULL )
		{
			$options = [
				'config' => [
					'encrypt_name' => TRUE,
	                'upload_path' => self::$upload_path,
	                'allowed_types' => 'doc|docx|pdf',
	                'max_size' => '2048'
				],
				'form_field' => 'file_toc',

				'create_thumb' => FALSE,

				// Delete Old file
				'old_files' => $old_document ? [$old_document] : [],
				'delete_old' => TRUE
			];
			return upload_insqube_media($options);
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
    public function settings( $mode="default", $fiscal_yr_id = NULL)
    {

    	if( !in_array($mode, ['default', 'fy']) )
    	{
    		return $this->template->json(['status' => 'error', 'message' => 'Invalid modes!'], 404);
    	}

    	$this->load->model('portfolio_setting_model');

    	/**
    	 * Update Nav Data
    	 */
    	$this->_navigation['level_2'] = 'settings';
    	$this->active_nav_primary($this->_navigation);

    	/**
    	 * Based on Mode, We Either List Index or All portfolios Per Fiscal year
    	 */
    	if($mode == 'default')
    	{
    		return $this->_settings_default();
    	}
    	else
    	{
    		return $this->_settings_fy($fiscal_yr_id);
    	}
  	}

  	// --------------------------------------------------------------------

  		/**
  		 * Default Portfolio Setting List Function
  		 *
  		 * This function list all settings by Fiscal Year as a default listing method.
  		 *
  		 * @return void
  		 */
  		private function _settings_default()
  		{
  			// Site Meta
	    	$this->data['site_title'] = 'Master Setup | Portfolio Settings';


	    	$records = $this->portfolio_setting_model->get_row_list();
			$this->template->partial(
								'content_header',
								'templates/_common/_content_header',
								[
									'content_header' => 'Manage Portfolio Settings',
									'breadcrumbs' => ['Master Setup' => NULL, 'Portfolio' => 'portfolio', 'Settings' => NULL]
							])
							->partial('content', 'setup/portfolio/_settings_default', compact('records'))
							->render($this->data);
  		}

	// --------------------------------------------------------------------

  		/**
  		 * List all portfolio settings for given fiscal year
  		 *
  		 * This function list all settings by Fiscal Year as a default listing method.
  		 *
  		 * @return void
  		 */
  		private function _settings_fy($fiscal_yr_id)
  		{
  			// Site Meta
	    	$this->data['site_title'] = 'Master Setup | List of Portfolio Settings By Fiscal Year';

	    	$fiscal_yr_id 	= (int)$fiscal_yr_id;
	    	$records 		= $this->portfolio_setting_model->get_list_by_fiscal_year($fiscal_yr_id);

	    	$content_header = 'Manage Portfolio Settings - ' .  $records[0]->fy_code_np . '(' . $records[0]->fy_code_en . ')';


			$this->template->partial(
								'content_header',
								'templates/_common/_content_header',
								[
									'content_header' => $content_header,
									'breadcrumbs' => ['Master Setup' => NULL, 'Portfolio' => 'portfolio', 'Settings' => NULL]
							])
							->partial('content', 'setup/portfolio/_settings_fy', compact('records'))
							->render($this->data);
  		}

  	// --------------------------------------------------------------------

    /**
     * Flush Cache Data - Portfolio Settings
     *
     * @return void
     */
    public function flush_settings()
    {
    	$this->load->model('portfolio_setting_model');
        $this->portfolio_setting_model->clear_cache();
        redirect($this->router->class . '/settings' );
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

		$json_data['form'] 	= $this->load->view('setup/portfolio/_form_settings',
			[

				'form_elements' 		=> $this->portfolio_setting_model->validation_rules,
				'portfolios_tree' 		=> $portfolios_tree,
				'settings' 				=> [],
				'action' 				=> 'add',
				'record' 				=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
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
		$json_data = $this->_save_settings('edit', $record, $settings);

		// No form Submitted?
		$portfolios_tree 		= $this->portfolio_model->dropdown_children_tree();
		$children_portfolios 	= $this->portfolio_model->dropdown_children();

		$json_data['form'] = $this->load->view('setup/portfolio/_form_settings',
			[
				'form_elements' 			=> $this->portfolio_setting_model->validation_rules,
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

			$rules = $this->portfolio_setting_model->get_validation_rules(['fiscal_yr', 'basic'], TRUE);

			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				$fiscal_yr_id 			= $this->input->post('fiscal_yr_id');
				$agent_commission 		= $this->input->post('agent_commission');
				$bs_service_charge 		= $this->input->post('bs_service_charge');
				$direct_discount 		= $this->input->post('direct_discount');
				$pool_premium 			= $this->input->post('pool_premium');
				$stamp_duty  			= $this->input->post('stamp_duty');
				$amt_default_basic_premium  	= $this->input->post('amt_default_basic_premium');
				$amt_default_pool_premium  		= $this->input->post('amt_default_pool_premium');
				$flag_default_duration 	= $this->input->post('flag_default_duration');
				$default_duration  		= $this->input->post('default_duration');
				$flag_short_term  		= $this->input->post('flag_short_term');
				$flag_installment  		= $this->input->post('flag_installment');


				/**
				 * Prepare Data
				 */
				if($action === 'add')
				{
					$batch_data = [];
					$i = 0;
					foreach($children_portfolios as $portfolio_id => $portfolio_name)
					{
						$batch_data[] = [
							'fiscal_yr_id' 				=> $fiscal_yr_id,
							'portfolio_id'    			=> $portfolio_id,
							'agent_commission' 			=> $agent_commission[$i],
							'bs_service_charge' 		=> $bs_service_charge[$i],
							'direct_discount' 			=> $direct_discount[$i],
							'pool_premium' 				=> $pool_premium[$i],
							'stamp_duty' 				=> $stamp_duty[$i],
							'amt_default_basic_premium' 	=> $amt_default_basic_premium[$i],
							'amt_default_pool_premium' 		=> $amt_default_pool_premium[$i],
							'flag_default_duration' 	=> $flag_default_duration[$i],
							'default_duration' 			=> $default_duration[$i],
							'flag_short_term' 			=> $flag_short_term[$i],
							'flag_installment' 			=> $flag_installment[$i]
						];
						$i++;
					}

					$done = $this->portfolio_setting_model->insert_batch($batch_data, TRUE); // No Validation on Model
				}
				else
				{
					$i = 0;
					$setting_ids = $this->input->post('setting_id');
					foreach($children_portfolios as $portfolio_id => $portfolio_name)
					{
						$single_data = [
							'agent_commission' 			=> $agent_commission[$i],
							'bs_service_charge' 		=> $bs_service_charge[$i],
							'direct_discount' 			=> $direct_discount[$i],
							'pool_premium' 				=> $pool_premium[$i],
							'stamp_duty' 				=> $stamp_duty[$i],
							'amt_default_basic_premium' 	=> $amt_default_basic_premium[$i],
							'amt_default_pool_premium' 		=> $amt_default_pool_premium[$i],
							'flag_default_duration' 	=> $flag_default_duration[$i],
							'default_duration' 			=> $default_duration[$i],
							'flag_short_term' 			=> $flag_short_term[$i],
							'flag_installment' 			=> $flag_installment[$i]
						];

						$setting_id = $setting_ids[$i];
						$done = $this->portfolio_setting_model->update($setting_id, $single_data, TRUE);

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
				$message = validation_errors();
			}

			// Success HTML
			$success_html = '';
			if($status === 'success' )
			{
				if($action === 'add')
				{
					$records = $this->portfolio_setting_model->get_row_list();
					$success_html = $this->load->view('setup/portfolio/_list_settings_default', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->portfolio_setting_model->get_row_single($fiscal_yr_id);
					$success_html = $this->load->view('setup/portfolio/_single_row_settings_default', ['record' => $record], TRUE);
				}
			}

			$return_data = [
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> false,
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
										: NULL
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
    	$setting_id = $this->input->post('setting_id');

        if( $this->portfolio_setting_model->check_duplicate(['fiscal_yr_id' => $fiscal_yr_id], $setting_id))
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
				$html = $this->load->view('setup/portfolio/_list_settings_default', ['records' => $records], TRUE);

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

    // --------------------------------------------------------------------

	/**
	 * Configure Short-term Policy Rate - Per Portfolio Settings
	 *
	 * @param integer $id
	 * @return void
	 */
	public function configure_settings_spr($id)
	{
		$this->load->model('portfolio_setting_model');

		// Valid Record ?
		$id 	= (int)$id;
		$record = $this->portfolio_setting_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Validation Rules
		$v_rules = $this->__settings_spr_validation_rules();
		if( $this->input->post() )
		{
			$this->form_validation->set_rules($v_rules);
			if( $this->form_validation->run() === TRUE )
			{
				$spr_post = $this->input->post('spr');

				$count = count($spr_post['title']);

				$spr_data = [];

				for($i = 0; $i < $count; $i++)
				{
					$spr_data[] = [
						'title' 	=> $spr_post['title'][$i],
						'duration' 	=> $spr_post['duration'][$i],
						'rate' 		=> $spr_post['rate'][$i],
					];
				}

				$data = [ 'short_term_policy_rate' => json_encode($spr_data) ];
				$done = $this->portfolio_setting_model->update($id, $data, TRUE);

				if($done)
				{
					$status 	= 'success';
					$message 	= 'Successfully updated.';
				}
				else
				{
					$status 	= 'error';
					$message 	= 'Could not be updated.';
				}
			}
			else
			{
				$status 	= 'error';
				$message 	= validation_errors();
			}

			$return_data = [
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> false,
				'hideBootbox' 	=> $status === 'success',
				'updateSection' => false
			];

			return $this->template->json($return_data);
		}

		$json_data['form'] = $this->load->view('setup/portfolio/_form_settings_spr',
								[
									'form_elements' 	=> $v_rules,
									'record' 			=> $record
								], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

		/**
		 * Get Short Term Policy Rate - Validation Rules
		 *
		 * @return array
		 */
		private function __settings_spr_validation_rules()
		{
			return [
                [
                    'field'     => "spr[title][]",
                    'label'     => 'Title',
                    'rules' 	=> 'trim|required|max_length[50]',
                    '_type'      => 'text',
                    '_key' 		=> 'title',
                    '_required' => true
                ],
                [
                    'field'     => "spr[duration][]",
                    'label'     => 'Duration (Days)',
                    'rules' 	=> 'trim|required|integer|max_length[3]',
                    '_type'      => 'text',
                    '_key' 		=> 'duration',
                    '_required' => true
                ],
                [
                    'field'     => "spr[rate][]",
                    'label'     => 'Rate (%)',
                    'rules' 	=> 'trim|required|prep_decimal|decimal|max_length[5]',
                    '_type'      => 'text',
                    '_key' 		=> 'rate',
                    '_required' => true
                ]
            ];
		}

}