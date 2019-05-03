<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Portfolio Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Portfolio extends MY_Controller
{
	/**
	 * Files Upload Path - Data
	 */
	public static $data_upload_path = INSQUBE_DATA_ROOT . 'portfolio/';

	// --------------------------------------------------------------------

	/**
	 * Controller URL
	 */
	private $_url_base;

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
        $this->data['site_title'] = 'Application Settings | Portfolio';

        // Setup Navigation
        $this->_navigation = [
			'level_0' => 'application_setup',
			'level_1' => 'portfolio',
			'level_2' => $this->router->class
		];
		$this->active_nav_primary($this->_navigation);

		// Load Model
		$this->load->model('portfolio_model');

		// Helper
		$this->load->helper('object');

		// URL Base
		$this->_url_base 		 = 'admin/' . $this->router->class;
		$this->_view_base 		 = 'setup/' . $this->router->class;

		$this->data['_url_base'] = $this->_url_base; // for view to access
		$this->data['_view_base'] 	= $this->_view_base;
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
								'breadcrumbs' => ['Application Settings' => NULL, 'Portfolio' => NULL]
						])
						->partial('content', $this->_view_base . '/_index', compact('records'))
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
		$json_data['form'] = $this->load->view($this->_view_base . '/_form',
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
		$json_data['form'] = $this->load->view($this->_view_base . '/_form',
			[
				'form_elements' => $this->portfolio_model->validation_rules['accounts'],
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


		// No form Submitted?
		$json_data['form'] = $this->load->view($this->_view_base . '/_form_risks',
			[
				'form_elements' => $rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Portfolio Specific Claim Docs - JSON
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function claim_docs($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->portfolio_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('claim_docs', $record);

		// Add already checked values
		$rules = $this->portfolio_model->validation_rules['claim_docs'];


		// No form Submitted?
		$json_data['form'] = $this->load->view($this->_view_base . '/_form_claim_docs',
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
		$json_data['form'] = $this->load->view($this->_view_base . '/_form',
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
		if( !in_array($action, array('edit', 'accounts', 'risks', 'claim_docs', 'bsrs_headings')))
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
					// Format JSON Data
					$risk_data['risks'] = $this->_format_risks_json($data);

					// // Now Update Data
					$done = $this->portfolio_model->update($record->id, $risk_data, TRUE);
				}
				else if ($action === 'claim_docs')
				{
					// Format JSON Data
					$claim_docs_data['claim_docs'] = $this->_format_claim_docs_json($data);

					// // Now Update Data
					$done = $this->portfolio_model->update($record->id, $claim_docs_data, TRUE);
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
					$success_html = $this->load->view($this->_view_base . '/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->portfolio_model->find($record->id);
					$success_html = $this->load->view($this->_view_base . '/_single_row', ['record' => $record], TRUE);
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
				case 'claim_docs':
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
		private function _format_risks_json ($post_data)
		{
			$risk_keys = ['code', 'name_en', 'name_np', 'type', 'default_min_premium'];
			$risks = $post_data['risks'];

			$json_data = [
				'default_premium_computation' => $risks['default_premium_computation']
			];
			for($i = 0; $i < count($risks['code']); $i++ )
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
		 * Format Claim Document JSON data from "Form Submission"
		 *
		 * @param array $post_data
		 * @return JSON
		 */
		private function _format_claim_docs_json ($post_data)
		{
			$json_keys = ['code', 'name_en', 'name_np'];
			$claim_docs = $post_data['claim_docs'];

			$json_data['claim_docs'] = [];
			for($i = 0; $i < count($claim_docs['code']); $i++ )
			{
				$single_object = [];
				foreach($json_keys as $key)
				{
					$single_object[$key] = $claim_docs[$key][$i] ?? NULL;
				}
				$json_data['claim_docs'][] = $single_object;
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
		 * Callback - Valid Claim Doc Code
		 * Must Be Unique
		 *
		 * @param type $code
		 * @return type
		 */
		public function _cb_claim_docs_check_duplicate($code)
		{
			$codes 	= $this->input->post()['claim_docs']['code'];
			$total 	= count($codes);
			$unique = array_unique(array_filter($codes));

	        if( $total !== count($unique) )
	        {
	            $this->form_validation->set_message('_cb_claim_docs_check_duplicate', 'The %s must be unique alphabetical characters.');
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
	                'upload_path' => self::$data_upload_path,
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
        redirect($this->_url_base);
    }

    // --------------------------------------------------------------------

    /**
     * Enable Portfolio
     *
     * @param int $id
     * @return json
     */
	public function enable($id)
	{
		// Valid Record ?
		$id 	= (int)$id;
		$record = $this->portfolio_model->find($id);
		if( !$record )
		{
			$this->template->render_404();
		}

		// Admin Constraint?
		$done = $this->portfolio_model->enable($record->id);
		if($done)
		{
			$record->active = IQB_FLAG_ON;
			$row = $this->load->view($this->_view_base . '/_single_row', ['record' => $record], TRUE);
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'reloadRow' => true,
				'rowId' 	=> '#_data-row-' . $record->id,
				'row' 		=> $row
			];
		}
		else
		{
			$data = [
				'status' 	=> 'error',
				'message' 	=> 'Could not be enabled.'
			];
		}
		return $this->template->json($data);
	}

	// --------------------------------------------------------------------

    /**
     * Enable Portfolio
     *
     * @param int $id
     * @return json
     */
	public function disable($id)
	{
		// Valid Record ?
		$id 	= (int)$id;
		$record = $this->portfolio_model->find($id);
		if( !$record )
		{
			$this->template->render_404();
		}

		// Admin Constraint?
		$done = $this->portfolio_model->disable($record->id);
		if($done)
		{
			$record->active = IQB_FLAG_OFF;
			$row = $this->load->view($this->_view_base . '/_single_row', ['record' => $record], TRUE);
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'reloadRow' => true,
				'rowId' 	=> '#_data-row-' . $record->id,
				'row' 		=> $row
			];
		}
		else
		{
			$data = [
				'status' 	=> 'error',
				'message' 	=> 'Could not be disabled.'
			];
		}
		return $this->template->json($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Portfolio
	 *
	 * @param inte $id Portfolio ID
	 * @return JSON
	 */
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
			/**
			 * Delete Data File If any
			 */
			if($record->file_toc)
			{
				delete_insqube_document(self::$data_upload_path . $record->file_toc);
			}

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
     * Download Surveyor Documents
     *
     * @param alphanumeric $doc_key document key|column that holds the name of the document
     * @param integer $id
     * @return void
     */
    public function download($doc_key, $id)
    {
    	$id = (int)$id;
		$record = $this->portfolio_model->find($id);
		if(!$record || !in_array($doc_key, ['file_toc']))
		{
			$this->template->render_404();
		}

		/**
		 * Download File
		 */
		$this->load->helper('download');
		$filename = $record->{$doc_key} ?? NULL;
		if($filename)
		{
			force_download( self::$data_upload_path . $filename, NULL, TRUE);
		}
		exit(1);

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
	    	$this->data['site_title'] = 'Application Settings | Portfolio Settings';


	    	$records = $this->portfolio_setting_model->get_row_list();
			$this->template->partial(
								'content_header',
								'templates/_common/_content_header',
								[
									'content_header' => 'Manage Portfolio Settings',
									'breadcrumbs' => ['Application Settings' => NULL, 'Portfolio' => $this->_url_base, 'Settings' => NULL]
							])
							->partial('content', $this->_view_base . '/_settings_default', compact('records'))
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
	    	$this->data['site_title'] = 'Application Settings | List of Portfolio Settings By Fiscal Year';

	    	$fiscal_yr_id 	= (int)$fiscal_yr_id;
	    	$records 		= $this->portfolio_setting_model->get_list_by_fiscal_year($fiscal_yr_id);

	    	$content_header = 'Manage Portfolio Settings - ' .  $records[0]->fy_code_np . '(' . $records[0]->fy_code_en . ')';


			$this->template->partial(
								'content_header',
								'templates/_common/_content_header',
								[
									'content_header' => $content_header,
									'breadcrumbs' => ['Application Settings' => NULL, 'Portfolio' => $this->_url_base . '/settings', 'Settings' => NULL]
							])
							->partial('content', $this->_view_base . '/_settings_fy', compact('records'))
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
        redirect($this->_url_base . '/settings' );
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

		$json_data['form'] 	= $this->load->view($this->_view_base . '/_form_settings',
			[
				'form_elements' 		=> $this->portfolio_setting_model->get_validation_rules('add'),
				'record' 				=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Portfolio Settings for Specific Portfolio for Specific Fiscal year
	 *
	 *
	 * @param integer $setting_id
	 * @return void
	 */
	public function edit_settings($setting_id)
	{
		$this->load->model('portfolio_setting_model');

		$record = $this->portfolio_setting_model->find($setting_id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save_settings('edit', $record);

		$json_data['form'] = $this->load->view($this->_view_base . '/_form_settings',
			[
				'form_elements' 	=> $this->portfolio_setting_model->get_validation_rules('edit'),
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
			$done 	= FALSE;
			$rules 	= $this->portfolio_setting_model->get_validation_rules($action);

			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				if($action === 'add')
				{
					$fiscal_yr_id = $this->input->post('fiscal_yr_id');
					$done = $this->portfolio_setting_model->add($fiscal_yr_id);
				}
				else
				{
					$post_data	 = $this->input->post();
					$update_data = [];

					foreach($rules as $r)
					{
						$key = $r['field'];
						$update_data[$key] = $post_data[$key] ?? NULL;
					}
					$done = $this->portfolio_setting_model->update($record->id, $update_data, TRUE);
				}

				if(!$done)
				{
					return $this->template->json([
						'status' 		=> 'error',
						'message' 		=> 'Could not be updated!'
					]);
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
					'status' 		=> 'error',
					'message' 		=> validation_errors()
				]);
			}

			if($status === 'success' )
			{
				$success_return = [
					'status' 		=> $status,
					'message' 		=> $message,
					'hideBootbox' 	=> true,
					'updateSection' => $action == 'add'
				];
				if($action === 'add')
				{
					$records 		= $this->portfolio_setting_model->get_row_list();
					$success_html 	= $this->load->view($this->_view_base . '/_list_settings_default', ['records' => $records], TRUE);
					$success_return = array_merge($success_return, [
						'updateSectionData'	=> [
							'box' 		=> '#iqb-data-list',
							'html' 		=> $success_html,
							'method' 	=> 'html'
						]
					]);
				}
				return $this->template->json($success_return);
			}
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
		if ( !$this->portfolio_setting_model->import_missing($fiscal_yr_id) )
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Could not import missing portfolios.'
			]);
		}

		return $this->template->json([
			'status' => 'success',
			'message' => "Successfully imported missing portfolios."
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
                $destination_fiscal_year_id = (int)$this->input->post('fiscal_yr_id');
                $done = $this->portfolio_setting_model->duplicate($source_fiscal_year_id, $destination_fiscal_year_id);

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
				$html = $this->load->view($this->_view_base . '/_list_settings_default', ['records' => $records], TRUE);

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
                    'form'          => $this->load->view($this->_view_base . '/_form_duplicate', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/_form_duplicate',
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

		$json_data['form'] = $this->load->view($this->_view_base . '/_form_settings_spr',
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