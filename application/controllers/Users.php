<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Users Controller
 * 
 * This controller falls under "Master Setup" category.
 *  
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Users extends MY_Controller
{
	/**
	 * Validation Rules
	 * 
	 * @var array
	 */
	private $rules = [

		/**
		 * Register New User
		 */
		'basic' => [
			[
				'field' => 'username',
		        'label' => 'Username',
		        'rules' => 'trim|required|min_length[4]|max_length[20]|alpha_dash|callback_username_check',
		        '_type' 	=> 'text',
		        '_required' => true
			],
			[
				'field' => 'email',
				'label' => 'Email',
				'rules' => 'trim|required|valid_email|callback_email_check',
				'_type' 	=> 'text',
		        '_required' => true
			],
			[
				'field' => 'password',
				'label' => 'Password',
				'rules' => 'trim|required|min_length[4]|max_length[20]|matches[confirm_password]',
				'_type' 	=> 'password',
		        '_required' => true
			],
			[
				'field' => 'confirm_password',
				'label' => 'Confirm Password',
				'rules' => 'trim|required',
				'_type' 	=> 'password',
		        '_required' => true
			],
			[
				'field' => 'role_id',
		        'label' => 'Application Role',
		        'rules' => 'trim|required|integer|max_length[8]',
		        '_type' 	=> 'dropdown',
		        '_required' => true
			],
			[
				'field' => 'branch_id',
		        'label' => 'Branch',
		        'rules' => 'trim|required|integer|max_length[11]',
		        '_type' 	=> 'dropdown',
		        '_required' => true
			],
			[
				'field' => 'department_id',
		        'label' => 'Department',
		        'rules' => 'trim|required|integer|max_length[11]',
		        '_type' 	=> 'dropdown',
		        '_required' => true
			],

			/**
			 * Scope: local/branch/global
			 */
			[
				'field' => 'scope[scope]',
				'label' => 'Scope',
				'rules' => 'trim|required|alpha|in_list[local,branch,global]',
				'_type' 	=> 'dropdown',
				'_data' 	=> ['local'=>'local', 'branch' => 'branch', 'global' => 'global'],
		        '_required' => true
			]
		],

		/**
		 * Edit Basic Information
		 */
		'edit-basic' => [			
			[
				'field' => 'role_id',
		        'label' => 'Application Role',
		        'rules' => 'trim|required|integer|max_length[8]',
		        '_type' 	=> 'dropdown',
		        '_required' => true
			],
			[
				'field' => 'branch_id',
		        'label' => 'Branch',
		        'rules' => 'trim|required|integer|max_length[11]',
		        '_type' 	=> 'dropdown',
		        '_required' => true
			],
			[
				'field' => 'department_id',
		        'label' => 'Department',
		        'rules' => 'trim|required|integer|max_length[11]',
		        '_type' 	=> 'dropdown',
		        '_required' => true
			],

			/**
			 * Scope: local/branch/global
			 */
			[
				'field' => 'scope[scope]',
				'label' => 'Scope',
				'rules' => 'trim|required|alpha|in_list[local,branch,global]',
				'_type' 	=> 'dropdown',
				'_data' 	=> ['local'=>'local', 'branch' => 'branch', 'global' => 'global'],
		        '_required' => true
			]
		],

		/**
		 * Change Password
		 */
		'change-password' => [
			[
				'field' => 'password',
				'label' => 'Password',
				'rules' => 'trim|required|min_length[4]|max_length[20]|matches[confirm_password]',
				'_type' 	=> 'password',
		        '_required' => true
			],
			[
				'field' => 'confirm_password',
				'label' => 'Confirm Password',
				'rules' => 'trim|required',
				'_type' 	=> 'password',
		        '_required' => true
			]
		],

		/**
		 * User Profile
		 */
		'profile' => [
			[
				'field' => 'profile[name]',
		        'label' => 'Full Name',
		        'rules' => 'trim|required|max_length[100]',
		        '_key' 		=> 'name', // Json Key Name
		        '_type' 	=> 'text',
		        '_required' => true
			],
			[
				'field' => 'profile[gender]',
		        'label' => 'Gender',
		        'rules' => 'trim|required|alpha|in_list[male,female,other]',
		        '_key' 		=> 'gender', // Json Key Name
		        '_type' 	=> 'dropdown',
		        '_data'		=> ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'],
		        '_required' => true
			],
			[
				'field' => 'profile[designation]',
		        'label' => 'Designation',
		        'rules' => 'trim|required|max_length[100]',
		        '_key' 		=> 'designation', // Json Key Name
		        '_type' 	=> 'text',
		        '_required' => true
			],
			[
				'field' => 'profile[dob]',
				'label' => 'Date of Birth',
				'rules' => 'trim|required|valid_date',
				'_key' 		=> 'dob', // Json Key Name
				'_type' 	=> 'date',
		        '_required' => true
			],
			[
				'field' => 'profile[salary]',
		        'label' => 'Salary',
		        'rules' => 'trim|decimal|max_length[10]',
		        '_key' 		=> 'salary', // Json Key Name
		        '_type' 	=> 'text',
		        '_required' => false
			]
		]	
	];

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
        $this->data['site_title'] = 'Master Setup | Users';

        // Setup Navigation        
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'security',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('user_model');

		// Image Path
        $this->_upload_path = MEDIAPATH . 'users/';  
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
		// this will generate cache name: mc_auth_roles_all
		// $records = $this->user_model->set_cache('all')->get_all();
		$records = $this->user_model->all();
		$next_id = NULL;
		$data = [
			'records' => $records,
			'next_id' => $next_id
		];
		if ( $this->input->is_ajax_request() ) 
		{
			$html = $this->load->view('setup/users/_list', $data, TRUE);
			$this->template->json([
				'status' => 'success',
				'html'   => $html
			]);
		}

		$this->template->partial(
							'content_header', 
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Users',
								'breadcrumbs' => ['Master Setup' => NULL, 'Users' => NULL]
						])
						->partial('content', 'setup/users/_index', $data)
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit User's Basic Information
	 * 
	 * @param integer $id 
	 * @return void
	 */
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->user_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Validation RUles
		$rules = $this->rules['edit-basic'];

		/**
		 * Update Validation Rule if Scope is branch
		 */
		$scope = $this->input->post('scope');
		if($scope['scope'] === 'branch')
		{
			$rules[] = [
				'field' => 'scope[list][]',
				'label' => 'Branches',
				'rules' => 'trim|required|integer',
			];
		}

		$this->form_validation->set_rules($rules);
		if( $this->input->post() && $this->form_validation->run() )
		{
			$data = $this->input->post();

			$data = [
				'role_id' => $this->input->post('role_id'),
				'branch_id' => $this->input->post('branch_id'),
				'department_id' => $this->input->post('department_id')
			];

			// Scope
			$scope = $this->input->post('scope');
			if($scope['scope'] !== 'branch')
			{
				// Reset scope list
				unset($scope['list']);
			}
			$data['scope'] = json_encode($scope);

			// Update Basic Information
			$r = [];
			if($this->user_model->update_basic($id, $data))
			{
				$status = 'success';
				$message = "User's contact updated successfully.";

				// Reload Row: Get updated record
				$record = $this->user_model->row($id);
				$r = [
					'updateSection' => true,
					'updateSectionData'	=> [
						'box' 	=> '#_data-row-' . $record->id,
						'html' 	=> $this->load->view('setup/users/_single_row', ['record' => $record], TRUE),
						//
						// How to Work with success html?
						// Jquery Method 	html|replaceWith|append|prepend etc.
						// 
						'method' 	=> 'replaceWith'
					]
				];
			}
			else
			{
				$status = 'error';
				$message = "Could not update user's contact.";
			}

			$return_data = $r + [
				'status' 		=> $status,
				'message' 		=> $message,
				'hideBootbox' 	=> $status === 'success'
			];
			return $this->template->json($return_data);
		}		

		// required models
		$this->load->model('role_model');
		$this->load->model('branch_model');
		$this->load->model('department_model');

		// No form Submitted?
		$json_data = [
			'reloadForm' => true
		];
		$json_data['form'] = $this->load->view('setup/users/_form', 
			[
				'form_title' 	=> 'Basic Information',
				'action_url'	=> site_url('users/edit/'. $record->id),
				'form_elements' => $rules,
				'record' 		=> $record,
				'form_record'   => $record,
				'roles' 		=> $this->role_model->dropdown(),
				'branches' 		=> $this->branch_model->dropdown(),
				'departments'	=> $this->department_model->dropdown()

			], TRUE);

		// Return HTML 
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Change User's Password
	 * 
	 * @param integer $id 
	 * @return void
	 */
	public function change_password($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->user_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Validation RUles
		$rules = $this->rules['change-password'];

		/**
		 * Update Validation Rule if Scope is branch
		 */
		$scope = $this->input->post('scope');
		if($scope['scope'] === 'branch')
		{
			$rules[] = [
				'field' => 'scope[list][]',
				'label' => 'Branches',
				'rules' => 'trim|required|integer',
			];
		}

		$this->form_validation->set_rules($rules);
		if( $this->input->post() && $this->form_validation->run() )
		{
			
			// Success
			$password = $this->input->post('password');

			$hasher = new PasswordHash(
					$this->config->item('phpass_hash_strength'),
					$this->config->item('phpass_hash_portable'));

			// Hash new password using phpass
			$hashed_password = $hasher->HashPassword($password);

			// Replace old password with new password
			if($this->user_model->change_password($id, $hashed_password))
			{
				// Trigger event
				$this->dx_auth->user_changed_password($id, $hashed_password);

				$status = 'success';
				$message = "User's password updated successfully.";
			}
			else
			{
				$status = 'error';
				$message = "Could not change user's password.";
			}

			$return_data = [
				'status' 		=> $status,
				'message' 		=> $message,
				'hideBootbox' 	=> $status === 'success'
			];
			return $this->template->json($return_data);
		}		

		// No form Submitted?
		$json_data = [
			'reloadForm' => true
		];
		$json_data['form'] = $this->load->view('setup/users/_form', 
			[
				'form_title' 	=> 'Change Password - ' . $record->username,
				'action_url'	=> site_url('users/change_password/'. $record->id),
				'form_elements' => $rules,
				'record' 		=> $record,
				'form_record'   => $record

			], TRUE);

		// Return HTML 
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Add a User
	 * 
	 * 	Wizard Flow:
	 * 		basic -> contact -> profile -> docs
	 * 
	 * This is the first wizard method to add user.
	 * 
	 * @return void
	 */
	public function add()
	{
		$record = NULL;
		$json_data = [
			'reloadForm' => true
		];

		// Validation RUles
		$rules = $this->rules['basic'];

		/**
		 * Update Validation Rule if Scope is branch
		 */
		$scope = $this->input->post('scope');
		if($scope['scope'] === 'branch')
		{
			$rules[] = [
				'field' => 'scope[list][]',
				'label' => 'Branches',
				'rules' => 'trim|required|integer',
			];
		}

		$this->form_validation->set_rules($rules);
		if( $this->input->post() && $this->form_validation->run() )
		{
			$data = $this->input->post();

			// Extract Vitals
			$username 	= $data['username'];
			$email 		= $data['email'];
			$password 	= $data['password'];
			unset($data['username'], $data['email'], $data['password']);

			// Create Scope
			if($data['scope']['scope'] !== 'branch')
			{
				// Reset scope list
				unset($data['scope']['list']);
			}
			$data['scope'] = json_encode($data['scope']);
			
			// Let's Create/Register the User
			$user_id = $this->dx_auth->register($username, $password, $email, $data);

			/**
			 * Load Next Wizard: Contact Form
			 */
			if($user_id)
			{
				return $this->update_contact($user_id, TRUE, TRUE);
			}
			else
			{
				$json_data['status'] = 'error';
				$json_data['message'] = 'Could not create user.';
			}
		}

		
		// No form Submitted?	

		// required models
		$this->load->model('role_model');	
		$this->load->model('branch_model');
		$this->load->model('department_model');
		$json_data['form'] = $this->load->view('setup/users/_form', 
			[
				'form_title' 	=> 'Basic Information',
				'action_url'	=> site_url('users/add/'),
				'form_elements' => $this->rules['basic'],
				'record' 		=> $record,
				'form_record'   => NULL,
				'roles' 		=> $this->role_model->dropdown(),
				'branches' 		=> $this->branch_model->dropdown(),
				'departments'	=> $this->department_model->dropdown()
			], TRUE);

		// Return HTML 
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Update User's Contact
	 * 
	 * Supports wizard on User Creation. When user is created first time and 
	 * contact wizard is loaded for the first time, we show some toastr message
	 * and reload the user list on the background
	 * 
	 * @param integer $id 
	 * @param bool $next_wizard 
	 * @param bool $first_time 
	 * @return void
	 */
	public function update_contact($id, $next_wizard = FALSE, $first_time = FALSE)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->user_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// If called from Previous Wizard Form, Load form
		if($next_wizard)
		{
			return $this->_load_contact_form($record, TRUE, $first_time);
		}		

		/**
		 * Perform Validation
		 */
		$rules = get_contact_form_validation_rules();
		$this->form_validation->set_rules($rules);
		if( $this->input->post() && $this->form_validation->run() )
		{
			// Check Next Wizard
			$next_wizard = $this->input->post('next_wizard');

			// Update Contact
			if($this->user_model->update_contact($id, [
				'contact' => get_contact_data_from_form()
			]))
			{
				if($next_wizard)
				{
					return $this->update_profile($id, $next_wizard);
				}
				else
				{
					$status = 'success';
					$message = "User's contact updated successfully.";
				}
			}
			else
			{
				$status = 'error';
				$message = "Could not update user's contact.";
			}

			$return_data = [
				'status' 		=> $status,
				'message' 		=> $message,
				'hideBootbox' 	=> $status === 'success'
			];
			return $this->template->json($return_data);
		}
		

		// Update next_wizard if we have form validation failed
		$next_wizard = $this->input->post('next_wizard');
		
		// Load Form		
		$this->_load_contact_form($record, $next_wizard);
		
	}
		/**
		 * Sub-function to load Contact Form for update_contact function
		 * 
		 * @param object $record 
		 * @param bool $next_wizard 
		 * @param tbool $first_time 
		 * @return void
		 */
		private function _load_contact_form($record, $next_wizard = FALSE, $first_time = FALSE)
		{
			// If it is loaded for the first time, its coming from add function
			// in this case, we have to insert the user list in our list table
			$first_time_data = [];
			if($first_time)
			{
				$records = $this->user_model->all();
				$list_html = $this->load->view('setup/users/_list', 
					['records' => $records, 'next_id' => NULL], TRUE);

				$first_time_data = [
					'status' 		=> 'success',
					'message' 		=> "User created successfully. Let's update contact for him/her",
					'updateSection' => true,
					'updateSectionData'	=> [
						'box' 		=> '#iqb-data-list',
						'html' 		=> $list_html,
						'method' 	=> 'html'
					]
				];
			}
			// Contact Record
			$contact_record = $record->contact ? json_decode($record->contact) : NULL;

			$json_data = $first_time_data + [
				'reloadForm' => true,
				'form' => $this->load->view('setup/users/_form_contact', 
											[
												'action_url'	=> site_url('users/update_contact/' . $record->id),
												'record' => $record,
												'next_wizard' 	=> $next_wizard
											], TRUE)
			];
			
			// Return HTML 
			$this->template->json($json_data);
		}

	// --------------------------------------------------------------------

	/**
	 * Update User's Profile
	 * 
	 * Supports wizard on user creation.
	 * 
	 * @param integer $id 
	 * @param bool $next_wizard 
	 * @return void
	 */	
	public function update_profile($id, $next_wizard = FALSE)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->user_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Load media helper
		$this->load->helper('insqube_media');  
		
		// If called from Previous Wizard Form, Load form
		if($next_wizard)
		{
			$this->_load_profile_form($record, TRUE);
		}		

		/**
		 * Perform Validation
		 */
		$rules = $this->rules['profile'];
		$this->form_validation->set_rules($rules);
		if( $this->input->post() && $this->form_validation->run() )
		{
			// Extract Old Profile Picture if any
			$profile = $record->profile ? json_decode($record->profile) : NULL;
			$picture = $profile->picture ?? NULL;

			/**
			 * Upload Image If any?
			 */   
			$upload_result 	= $this->_upload_profile_picture($picture);   
			$status 		= $upload_result['status'];
			$message 		= $upload_result['message'];
			$files 			= $upload_result['files'];

			if( $status === 'success' || $status === 'no_file_selected')
            {
            	// Let's Update Rest of the Data
				$data = [];

				$data['profile'] = $this->input->post('profile');
				// Get New Profile Picture
            	$picture = $status === 'success' ? $files[0] : $picture;
            	$data['profile']['picture'] = $picture;

				$data['profile'] = json_encode($data['profile']);
				
				// Let's update profile
				if($this->user_model->update_profile($id, $data))
				{
					$status = 'success';
					$message = "User's profile updated successfully.";
				}
				else
				{
					$status = 'error';
					$message = "Could not update user's profile.";
				}
			}
			$return_data = [
				'status' 		=> $status,
				'message' 		=> $message,
				'hideBootbox' 	=> $status === 'success'
			];
			return $this->template->json($return_data);
		}

		// Update next_wizard if we have form validation failed
		$next_wizard = $this->input->post('next_wizard');
		
		// Load Form		
		$this->_load_profile_form($record, $next_wizard);
		
	}
		/**
		 * Sub-function: Upload Profile Picture
		 * 
		 * @param string|null $old_picture 
		 * @return array
		 */
		private function _upload_profile_picture( $old_picture = NULL )
		{
			$options = [
				'config' => [
					'encrypt_name' => TRUE,
	                'upload_path' => $this->_upload_path,
	                'allowed_types' => 'gif|jpg|png',
	                'max_size' => '2048'
				],
				'form_field' => 'picture',

				'create_thumb' => TRUE,

				// Delete Old file
				'old_files' => $old_picture ? [$old_picture] : [],
				'delete_old' => TRUE
			];
			return upload_insqube_media($options);
		}

		/**
		 * Sub-function to load Profile Form for update_profile function
		 * 
		 * @param object $record 
		 * @param bool $next_wizard 
		 * @return void
		 */
		private function _load_profile_form($record, $next_wizard = FALSE)
		{
			// Contact Record
			$profile_record = $record->profile ? json_decode($record->profile) : NULL;

			$json_data = [
				'reloadForm' => true,
				'form' => $this->load->view('setup/users/_form_profile', 
											[
												'form_title' 	=> 'User Profile',
												'action_url'	=> site_url('users/update_profile/' . $record->id),
												'form_elements' => $this->rules['profile'],
												'record' 		=> $record,
												'form_record'   => $profile_record,
												'next_wizard' 	=> $next_wizard
											], TRUE)
			];
			
			// Return HTML 
			$this->template->json($json_data);
		}

	// --------------------------------------------------------------------
	
	/**
	 * Callback: Check Username
	 * 
	 * @param string $username 
	 * @return bool
	 */
	function username_check($username)
	{
		$id  = (int)$this->input->post('id');
		$id  = ($id !== 0) ? $id : NULL;

		$result = $this->dx_auth->is_username_available($username, $id);
		if ( ! $result)
		{
			$this->form_validation->set_message('username_check', 'Username already exist. Please choose another username.');
		}
				
		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Callback: Check Email
	 * 
	 * @param email $email 
	 * @return bool
	 */
	function email_check($email)
	{
		$id  = (int)$this->input->post('id');
		$id  = ($id !== 0) ? $id : NULL;

		$result = $this->dx_auth->is_email_available($email, $id);
		if ( ! $result)
		{
			$this->form_validation->set_message('email_check', 'Email is already used by another user. Please choose another email address.');
		}
				
		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a User
	 * 
	 * @param integer $id 
	 * @return json
	 */
	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->user_model->get($id);
		if(!$record)
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
		if( !safe_to_delete( 'User_model', $id ) )
		{
			return $this->template->json($data);
		}


		$done = $this->user_model->delete_user($record->id);
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
}