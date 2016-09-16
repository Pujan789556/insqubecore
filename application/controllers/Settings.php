<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller
{
	/**
	 * Validation Rules
	 * 
	 * @var array
	 */
	private $form_elements = [
		[
			'name' => 'organization',
	        'label' => 'Organization Name',
	        '_id' 	=> 'organization',
	        '_type' => 'text'
		],
		[
			'name' => 'address',
	        'label' => 'Headquarter Full Address',
	        '_id' 	=> 'address',
	        '_type'	=> 'textarea'
		],
		[
			'name' => 'per_page',
	        'label' => 'Pagination Limit',
	        '_id' 	=> 'per_page',
	        '_type'	=> 'dropdown',
	        '_data' => ['10' => '10', '20' => '20', '50' => '50', '100' => '100']
		],
		[
			'name' => 'flag_offline',
	        'label' => 'Set Offline',
	        '_id' 	=> 'flag_offline',
	        '_type' => 'switch',
	        '_data' => '1'
		],
		[
			'name' => 'offline_message',
	        'label' => 'Offline Message',
	        '_id' 	=> 'offline_message',
	        '_type'	=> 'textarea'
		],
		[
			'name' => 'admin_email',
	        'label' => 'Administrator Email',
	        '_id'	=> 'admin_email',
	        '_type'	=> 'email'
		],
		[
			'name' => 'from_email',
	        'label' => 'From Email',
	        '_id'	=> 'from_email',
	        '_type'	=> 'email'
		],
		[
			'name' => 'replyto_email',
	        'label' => 'Reply-to Email',
	        '_id'  	=> 'replyto_email',
	        '_type'	=> 'email'
		],
		[
			'name' => 'noreply_email',
	        'label' => 'No-reply Email',
	        '_id' 	=> 'noreply_email',
	        '_type'	=> 'email'
		],
		[
			'name' => 'website',
	        'label' => 'Website',
	        '_id' 	=> 'website',
	        '_type'	=> 'text'
		]	
	];

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
	        	$done = $this->setting_model->from_form(NULL, ['logo' => $new_logo ? $new_logo : $old_logo])->update(NULL, 1);	

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
								'form_elements' => $this->form_elements,
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
								'form_elements' => $this->form_elements,
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