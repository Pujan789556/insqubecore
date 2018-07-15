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
	 * Files Upload Path
	 */
	public static $upload_path = INSQUBE_MEDIA_PATH . 'surveyors/';

	// --------------------------------------------------------------------

	/**
	 * Validation Rules
	 *
	 * @var array
	 */
	private $_rules = [

		/**
		 * Register New User
		 */
		'basic' => [
			[
				'field' => 'code',
		        'label' => 'Employee Code',
		        'rules' => 'trim|required|alpha_dash|max_length[20]|strtoupper|callback_code_check',
		        '_type' 	=> 'text',
		        '_required' => true
			],
			[
				'field' => 'username',
		        'label' => 'Username',
		        'rules' => 'trim|required|min_length[4]|max_length[20]|username_format|callback_username_check',
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
				'rules' => 'trim|required|min_length[4]|max_length[40]|matches[confirm_password]',
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
				'field' => 'code',
		        'label' => 'Employee Code',
		        'rules' => 'trim|required|alpha_dash|max_length[20]|strtoupper|callback_code_check',
		        '_type' 	=> 'text',
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
		 * Change Password
		 */
		'change-password' => [
			[
				'field' => 'password',
				'label' => 'Password',
				'rules' => 'trim|required|min_length[4]|max_length[40]|matches[confirm_password]',
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
				'rules' => 'trim|valid_date',
				'_key' 		=> 'dob', // Json Key Name
				'_type' 	=> 'date',
				'_extra_attributes' => 'data-provide="datepicker-inline"',
		        '_required' => false
			],
			[
				'field' => 'profile[salary]',
		        'label' => 'Salary',
		        'rules' => 'trim|prep_decimal|decimal|max_length[10]',
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
		$this->page();
	}

	/**
	 * Paginate Data List
	 *
	 * @param integer $next_id
	 * @return void
	 */
	function page( $layout='f', $next_id = 0,  $ajax_extra = [] )
	{
		// If request is coming from refresh method, reset nextid
		$next_id = (int)$next_id;

		$params = array();
		if( $next_id )
		{
			$params = ['next_id' => $next_id];
		}

		/**
		 * Extract Filter Elements
		 */
		$filter_data = $this->_get_filter_data( );
		if( $filter_data['status'] === 'success' )
		{
			$params = array_merge($params, $filter_data['data']);
		}

		$records = $this->user_model->rows($params);
		$records = $records ? $records : [];
		$total = count($records);

		/**
		 * Grab Next ID or Reset It
		 */
		if($total == $this->settings->per_page+1)
		{
			$next_id = $records[$total-1]->id;
			unset($records[$total-1]); // remove last record
		}
		else
		{
			$next_id = NULL;
		}

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-user', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-user' 			// Filter Form ID
		];

		$data = [
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'users/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'setup/users/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('users/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/users/_list';
		}
		else
		{
			$view = 'setup/users/_rows';
		}


		if ( $this->input->is_ajax_request() )
		{
			$html = $this->load->view($view, $data, TRUE);
			$this->template->json([
				'status' => 'success',
				'html'   => $html
			]);
		}


		$this->template
						->set_layout('layout-advanced-filters')
						->partial(
							'content_header',
							'setup/users/_index_header',
							['content_header' => 'Manage Users'] + $dom_data)
						->partial('content', 'setup/users/_index', $data)
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$this->load->model('role_model');
			$this->load->model('branch_model');
			$this->load->model('department_model');

			$select = ['' => 'Select ...'];
			$filters = [
				[
					'field' => 'filter_role',
			        'label' => 'Application Role',
			        'rules' => 'trim|integer|max_length[8]',
			        '_type' 	=> 'dropdown',
			        '_data' 	=> $select + $this->role_model->dropdown()
				],
				[
					'field' => 'filter_branch',
			        'label' => 'Branch',
			        'rules' => 'trim|integer|max_length[11]',
			        '_type' 	=> 'dropdown',
			        '_data' 	=> $select + $this->branch_model->dropdown()
				],
				[
					'field' => 'filter_department',
			        'label' => 'Department',
			        'rules' => 'trim|integer|max_length[11]',
			        '_type' 	=> 'dropdown',
			        '_data' 	=> $select + $this->department_model->dropdown()

				],
				[
					'field' => 'filter_keywords',
			        'label' => 'Name/Username',
			        'rules' => 'trim|max_length[80]',
			        '_type' 	=> 'text'
				]
			];

			return $filters;
		}

		private function _get_filter_data()
		{
			$data = ['status' => 'empty'];

			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$data['data'] = [
						'role_id' => $this->input->post('filter_role') ?? NULL,
						'branch_id' => $this->input->post('filter_branch') ?? NULL,
						'department_id' => $this->input->post('filter_department') ?? NULL,
						'keywords' 	=> $this->input->post('filter_keywords') ?? ''
					];
					$data['status'] = 'success';
				}
				else
				{
					$data = [
						'status' 	=> 'error',
						'message' 	=> validation_errors()
					];

					$this->template->json($data);
				}
			}
			return $data;
		}

	/**
	 * Refresh The Module
	 *
	 * Simply reload the first page
	 *
	 * @return type
	 */
	function refresh()
	{
		$this->page('l');
	}

	/**
	 * Filter the Data
	 *
	 * @return type
	 */
	function filter()
	{
		$this->page('l');
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
		$record = $this->user_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Validation RUles
		$rules = $this->_rules['edit-basic'];

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
				'_type' => ''
			];
		}

		$this->form_validation->set_rules($rules);
		if( $this->input->post() && $this->form_validation->run() )
		{
			$data = [
				'code' 			=> $this->input->post('code'),
				'role_id' 		=> $this->input->post('role_id'),
				'branch_id' 	=> $this->input->post('branch_id'),
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
		$record = $this->user_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Validation RUles
		$rules = $this->_rules['change-password'];
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


				/**
				 * Notify User Via Email
				 */
				$from = $this->settings->from_email;
				$subject = sprintf($this->lang->line('auth_password_changed_subject'), $this->settings->orgn_name_en);

				$profile = $record->profile ? json_decode($record->profile) : NULL;
				$profile_name = isset($profile) ? $profile->name : $record->username;

				// Trigger event and get email content
				$this->dx_auth->sending_password_changed_email([
					'user_profile_name' => $profile_name,
					'username' 			=> $record->username,
					'email' 			=> $record->email,
					'password'  		=> $password,
					'url'				=> site_url()
				], $message);

				$this->_email($record->email, $from, $subject, $message);

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
		$rules = $this->_rules['basic'];

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
				'form_elements' => $this->_rules['basic'],
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
		$record = $this->user_model->find($id);
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
			// in this case, we simply refresh the data table
			$first_time_data = [];
			if($first_time)
			{
				$records = $this->user_model->rows();
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
		$record = $this->user_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// If called from Previous Wizard Form, Load form
		if($next_wizard)
		{
			$this->_load_profile_form($record, TRUE);
		}

		/**
		 * Perform Validation
		 */
		$rules = $this->_rules['profile'];
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
	                'upload_path' => self::$upload_path,
	                'allowed_types' => 'gif|jpg|jpeg|png',
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
												'form_elements' => $this->_rules['profile'],
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
	 * Callback: Check Code
	 *
	 * @param string $code
	 * @return bool
	 */
	function code_check($code)
	{
		$id  = (int)$this->input->post('id');
		$id  = ($id !== 0) ? $id : NULL;

		$result = $this->user_model->is_code_available($code, $id);
		if ( ! $result )
		{
			$this->form_validation->set_message('code_check', 'Employee code already exist. Please choose another code.');
		}

		return $result;
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
		$record = $this->user_model->find($id);
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
			/**
			 * Delete Media if any
			 */
			$profile = $record->profile ? json_decode($record->profile) : NULL;
			if(isset($profile->picture) && $profile->picture != '' )
			{
				delete_insqube_document(self::$upload_path . $profile->picture);
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
	 * Ban a User
	 *
	 * @param integer $id
	 * @return json
	 */
	public function ban($id)
	{
		return $this->_ban_unban($id, 'ban');
	}

	/**
	 * UnBan a User
	 *
	 * @param integer $id
	 * @return json
	 */
	public function unban($id)
	{
		return $this->_ban_unban($id, 'unban');
	}

		private function _ban_unban($id, $action)
		{
			// Valid Record ?
			$id = (int)$id;
			$record = $this->user_model->find($id);
			if(!$record)
			{
				$this->template->render_404();
			}

			$data = [
				'status' 	=> 'error',
				'message' 	=> 'You cannot perform this action on the default records.'
			];
			/**
			 * Safe to Delete?
			 */
			if( !safe_to_delete( 'User_model', $id ) )
			{
				return $this->template->json($data);
			}

			if( $action === 'ban')
			{
				$done = $this->user_model->ban_user($record->id);
			}
			else
			{
				$done = $this->user_model->unban_user($record->id);
			}


			if($done)
			{
				$record = $this->user_model->row($id);
				$data = [
					'status' 	=> 'success',
					'message' 	=> "Successfully performed the action ($action)!",
					'reloadRow' => true,
					'row' 		=> $this->load->view('setup/users/_single_row', ['record' => $record], TRUE),
					'rowId'		=> '#_data-row-'.$record->id
				];
			}
			else
			{
				$data = [
					'status' 	=> 'error',
					'message' 	=> "Could not perform the action ($action)."
				];
			}

			return $this->template->json($data);
		}

	// --------------------------------------------------------------------

    /**
     * View User Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	$id = (int)$id;
		$record = $this->user_model->details($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$this->data['site_title'] = 'User Details | ' . $record->username;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'User Details <small>' . $record->username . '</small>',
								'breadcrumbs' => ['Users' => 'users', 'Details' => NULL]
						])
						->partial('content', 'setup/users/_details', compact('record'))
						->render($this->data);

    }

	// --------------------------------------------------------------------

    public function profile()
    {
    	return $this->details($this->dx_auth->get_user_id());
    }


	// --------------------------------------------------------------------
	//  USER SETTINGS
	// --------------------------------------------------------------------

    /**
     * Manage User Settings
     *
     * @param type $user_id
     * @return type
     */
    public function settings($user_id)
    {
        // Valid Record ?
        $this->load->model('dx_auth/user_setting_model', 'user_setting_model');
        $user_id = (int)$user_id;
        $record = $this->user_setting_model->get($user_id);
        if(!$record)
        {
            return $this->template->json([
                'status' => 'error',
                'message' => 'No user found!'
            ],404);
        }

        if( $this->input->post() )
        {
            $v_rules = $this->user_setting_model->validation_rules;
            $this->form_validation->set_rules($v_rules);
            if( $this->form_validation->run() )
            {
                // Validate Permissions
                $post_data = $this->input->post();

                // Check Flag One By One
                $data = [
                    'flag_re_login'     => $post_data['flag_re_login'] ?? 0,
                    'flag_back_date'    => $post_data['flag_back_date'] ?? 0,
                ];

                // Let's Update the Permissions
                if( $this->user_setting_model->update_settings($user_id, $data ) )
                {
                    $status = 'success';
                    $message = 'Successfully updated.';
                }
                else
                {
                    $status = 'error';
                    $message = 'Could not be updated.';
                }
            }
            else
            {
                $status = 'error';
                $message = 'Validation Failed!';
            }

            $this->template->json([
                'status' => $status,
                'message' => $message,
                'hideBootbox' => $status === 'success' // Hide bootbox on Success
            ]);
        }

        // Let's load the form
        $json_data['form'] = $this->load->view('setup/users/_form_user_setting',
        [
            'record'        => $record,
            'form_elements' => $this->user_setting_model->validation_rules
        ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

	// --------------------------------------------------------------------

    /**
     * Revoke All Back Date Settings
     *
     * Reset all back-date settings of all users
     *
     * @return json
     */
    public function revoke_all_backdate()
    {
    	$this->load->model('dx_auth/user_setting_model', 'user_setting_model');
    	if( $this->user_setting_model->update_flag_all('flag_back_date', IQB_STATUS_INACTIVE) )
    	{
    		$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully revoked all back-date settings!'
			];

			// @TODO: Log activity
    	}
    	else
    	{
    		$data = [
				'status' 	=> 'error',
				'message' 	=> 'Could not be updated!'
			];
		}
		return $this->template->json($data);
	}

	// --------------------------------------------------------------------

    /**
     * Rgenerate password for all users, set force-relogin
     * and send each users email regarding this changes
     *
     * @return json
     */
    public function renew_passwords()
    {
    	$users = $this->user_model->all_core();
    	$this->load->library('Token');

    	$total 		= count($users);
    	$success 	= 0;

    	foreach($users as $record)
    	{
    		// Skip Admin
    		if($record->id == 1) continue;


    		$password 	= $this->token->generate(20);
    		$hasher 		= new PasswordHash(
									$this->config->item('phpass_hash_strength'),
									$this->config->item('phpass_hash_portable'));

			// Hash new password using phpass
			$hashed_password = $hasher->HashPassword($password);


			$profile = $record->profile ? json_decode($record->profile) : NULL;
			$profile_name = isset($profile) ? $profile->name : $record->username;

			// Replace old password with new password
			if($this->user_model->change_password($record->id, $hashed_password))
			{
				// Trigger event (Let's send email with changed password)
				$this->dx_auth->user_changed_password($record->id, $hashed_password);

				$success++;

				/**
				 * Let's Send them Email
				 */
				$from = $this->settings->from_email;
				$subject = sprintf($this->lang->line('auth_password_changed_subject'), $this->settings->orgn_name_en);

				// Trigger event and get email content
				$this->dx_auth->sending_password_changed_email([
					'user_profile_name' => $profile_name,
					'username' 			=> $record->username,
					'email' 			=> $record->email,
					'password'  		=> $password,
					'url'				=> site_url()
				], $message);

				$this->_email($record->email, $from, $subject, $message);
			}

    	}

    	if( $success )
    	{
    		$data = [
				'status' 	=> 'success',
				'message' 	=> "{$success} out of {$total} user's password updated successfully."
			];

			// @TODO: Log activity
    	}
    	else
    	{
    		$data = [
				'status' 	=> 'error',
				'message' 	=> 'Could not be updated!'
			];
		}
		return $this->template->json($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Send email using email helper function
	 *
	 * Using email helper function lets check your email on log file if your
	 * development environment has not email sending facility
	 *
	 * @author 	IP Bastola
	 */
	function _email($to, $from, $subject, $message)
	{
		// Load helper
		$this->load->helper('email');
		/**
    	 * Prepare Email Data
    	 */
    	$email_data = [
    		'mailtype' 	=> 'html',

    		// From Email and From Name will be From Site Settings Data
   //  		'from' 		=> [
   //  			'email' => $from,
   //  			'name' => $this->settings->orgn_name_en
			// ],
    		'to' 		=> $to,
    		'subject' 	=> $subject,
    		'message' 	=> $message
    	];

    	// echo '<pre>'; print_r($email_data); echo '</pre>';
    	send_email($email_data);
	}
}