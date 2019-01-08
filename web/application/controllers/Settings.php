<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller
{
	/**
	 * Files Upload Path - Media
	 */
	public static $media_upload_path = INSQUBE_MEDIA_ROOT . 'media/settings/';

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
        $this->data['site_title'] = 'Settings';
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
		try{

			$rules = $this->setting_model->get_validation_rules('general');

		} catch (Exception $e){

			return $this->template->json([
				'status' 	=> 'error',
				'message' 	=> 'Exception: ' . $e->getMessage()
			], 404);
		}

		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{

			/**
			 * Validate Post before uploading any Media
			 */
			// $rules = $this->setting_model->get_validation_rules('general');
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


	            	$data = $this->input->post();
	            	$data['logo'] = $new_logo;

	            	// Offline checkbox
	            	$data['flag_offline'] = $data['flag_offline'] ?? 0;

		        	$done = $this->setting_model->update(1, $data, TRUE);


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

			$view = $this->load->view('setup/settings/_form_general', [
								'form_elements' => $rules,
								'record' 		=> $record
							], TRUE);

			$this->template->json([
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> true,
				'reloadPage' 	=> $status == 'success' && $this->settings->flag_offline != $data['flag_offline'],
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
						->partial('content', 'setup/settings/_index',
							[
								'rules' 	=> $this->setting_model->validation_rules,
								'record'	=> $this->settings
							])
						->render($this->data);
	}

    // --------------------------------------------------------------------

	function _upload_logo( )
	{
		$options = [
			'config' => [
				'encrypt_name' => TRUE,
                'upload_path' => self::$media_upload_path,
                'allowed_types' => 'gif|jpg|jpeg|png',
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

    // --------------------------------------------------------------------

	/**
     * Setting Sections
     *
     * Section specific settings are displayed/updated here
     *
     * @param string $section
     * @return json
     */
    function section($section)
    {
        /**
         * Valid Section?
         */
        if( !$this->setting_model->valid_section($section) )
        {
            return $this->template->json([
                'status'    => 'error',
                'message'   => 'No information submitted.'
            ], 404);
        }

        /**
         * Form Submitted?
         */
        if( $this->input->post() )
        {
            try{

                $rules = $this->setting_model->get_validation_rules($section);

            } catch (Exception $e){

                return $this->template->json([
                    'status'    => 'error',
                    'message'   => 'Exception: ' . $e->getMessage()
                ], 404);
            }
            /**
             * Validate Post before uploading any Media
             */
            $this->form_validation->set_rules($rules);

            if( $this->form_validation->run() === TRUE )
            {
                $post_data = $this->input->post();
                $section_data = [];
                foreach($rules as $element)
                {
                	$field_value = $this->input->post($element['field']) ? $this->input->post($element['field']) : NULL;
                    $section_data[$element['field']] = $field_value;
                }

                $done = $this->setting_model->update(1, $section_data, TRUE);
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
            else
            {
                $status = 'error';
                $message = 'Validation Error.';
            }

            $record = $status === 'success'
                                    ? $this->setting_model->get(['id' => 1])
                                    : $this->settings;

            /**
             *  IMPORTANT!
             * ---------------
             *  $action_url & $dom_parent_container should match exactly while rendering form
             *  from Index Method
             *
             *  $action_url             = "settings/section/{$section}"
             *  $dom_parent_container   = "tab-{$section}-settings"
             */
            $action_url             = site_url("settings/section/{$section}");
            $dom_parent_container   = "#tab-{$section}-settings";
            $view = $this->load->view('setup/settings/_form_section', [
                                'form_elements'         => $rules,
                                'record'                => $record,
                                'action_url'            => $action_url,
                                'dom_parent_container'  => $dom_parent_container
                            ], TRUE);

            $this->template->json([
                'status'        => $status,
                'message'       => $message,
                'reloadForm'    => true,
                'form'          => $view
            ]);
        }

        return $this->template->json([
            'status'    => 'error',
            'message'   => 'No information submitted.'
        ], 404);
    }

}