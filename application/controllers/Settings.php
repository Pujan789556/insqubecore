<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller
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
        $this->data['site_title'] = 'Settings';

        // Image Path
        $this->_upload_path = MEDIAPATH . 'settings/';
	}

	/**
	 * Default Method
	 *
	 * Render the settings
	 *
	 * @return type
	 */
	function index()
	{
		// Image Helper
		$this->load->helper('insqube_media');

		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{

			/**
			 * Validate Post before uploading any Media
			 */
			$rules = $this->setting_model->validation_rules;
			$this->form_validation->set_rules($rules);

			if( $this->form_validation->run() === TRUE )
			{
				/**
				 * Upload Image If any?
				 */
				$upload_result 	= $this->_upload_logo();
				$status 		= $upload_result['status'];
				$message 		= $upload_result['message'];
				$files 			= $upload_result['files'];

	            /**
	             * Update Data
	             */
	            if( $status === 'success' || $status === 'no_file_selected')
	            {
	            	// Get New Logo
	            	$new_logo = $status === 'success' ? $files[0] : $this->settings->logo;

	            	// Now Update Data
		        	// $done = $this->setting_model->from_form(NULL, ['logo' => $new_logo])->update(NULL, 1) && $this->setting_model->log_activity(1, 'E');
	            	$data = $this->input->post();
	            	$data['logo'] = $new_logo;

		        	$done = $this->setting_model->update(1, $data, TRUE) && $this->setting_model->log_activity(1, 'E');


					// Validation Error?
					if( !$done )
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
			}
			else
			{
				$status = 'error';
				$message = 'Validation Error.';
			}

			$record = $status === 'success'
									? $this->setting_model->get(['id' => 1])
									: $this->settings;

			$view = $this->load->view('settings/_form_general', [
								'form_elements' => $this->setting_model->validation_rules,
								'record' 		=> $record
							], TRUE);

			$this->template->json([
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> true,
				'form' 	  		=> $view
			]);
		}


		/**
		 * Normal Form Render
		 */
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Settings',
								'breadcrumbs' => ['Settings' => NULL]
						])
						->partial('content', 'settings/_index',
							[
								'form_elements' => $this->setting_model->validation_rules,
								'record' 		=> $this->settings
							])
						->render($this->data);
	}

	function _upload_logo( )
	{
		$options = [
			'config' => [
				'encrypt_name' => TRUE,
                'upload_path' => $this->_upload_path,
                'allowed_types' => 'gif|jpg|png',
                'max_size' => '2048'
			],
			'form_field' => 'logo',

			'create_thumb' => TRUE,

			// Delete Old file
			'old_files' => [$this->settings->logo],
			'delete_old' => TRUE
		];
		return upload_insqube_media($options);
	}

}