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
		$this->load->helper('image');

		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{
			/**
			 * Upload Image If any?
			 */
            // Upload Status and Related Variables
			list($old_logo, $new_logo, $status, $message) = $this->_upload_logo();
            
            /**
             * Update Data
             */
            if( $status === 'success')
            {
            	// Now Update Data
	        	$done = $this->setting_model->from_form(NULL, ['logo' => $new_logo ? $new_logo : $old_logo])->update(NULL, 1) && $this->setting_model->log_activity(1, 'E');	

				// Validation Error?
				if(!$done)
				{
					$status = 'error';
					$message = 'Validation Error.';		

					// Delete New Upload If we have any Validation Error
					if( $new_logo )
					{
						delete_image($this->_upload_path . $new_logo);
					}						
				}
				else
				{
					// Delete Old Logo if New Logo Uploaded & Updated on Database?
					if( $new_logo )
					{
						$old_image = $this->_upload_path . $old_logo;						
						// Delete image and its thumbnails as well
						delete_image($old_image);
					}
					$status = 'success';
					$message = 'Successfully Updated.';
				}
            }		

			$record = $status === 'success' 
									? $this->setting_model->get(['id' => 1]) 
									: $this->settings;

			$view = $this->load->view('settings/_form_general', [
								'form_elements' => $this->setting_model->rules['insert'],
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
								'form_elements' => $this->setting_model->rules['insert'],
								'record' 		=> $this->settings
							])
						->render($this->data);
	}

	private function _upload_logo()
	{
		/**
		 * Upload Image If any?
		 */
        $old_logo = $this->settings->logo;
        $new_logo = '';
        $status = 'success';
        $message = '';
        if( isset($_FILES['logo']['name']) && !empty($_FILES['logo']['name']) )
        {
        	$config = array(
                'encrypt_name' => TRUE,
                'upload_path' => $this->_upload_path,
                'allowed_types' => 'gif|jpg|png',
                'max_size' => '2048'
            );
            $this->load->library('upload', $config);

        	if( $this->upload->do_upload('logo'))
            {
            	$uploaded = $this->upload->data();	  
            	$new_logo =  $uploaded['file_name']; 

            	/**
            	 * Generate Thumbnail
            	 */
            	$this->load->library('image_lib');	            	
            	create_thumbnail( $uploaded['full_path'] );   	
            }
            else
            {
            	$status = 'error';
            	$message = $this->upload->display_errors();	            	
            }            	
        }

        return [$old_logo, $new_logo, $status, $message];
	}

}