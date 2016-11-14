<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Branches Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Branches extends MY_Controller
{
	private $_navigation = [];

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
        $this->data['site_title'] = 'Master Setup | Branches';

        // Setup Navigation
        $this->_navigation = [
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class,
			'level_3' => 'index'
		];
		$this->active_nav_primary($this->_navigation);

		// Load Model
		$this->load->model('branch_model');
	}

	// --------------------------------------------------------------------

	/**
	 * Default Method
	 *
	 * Render the list
	 *
	 * @return type
	 */
	function index()
	{
		/**
		 * Normal Form Render
		 */
		// this will generate cache name: mc_master_departments_all
		$records = $this->branch_model->get_all();
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Branches',
								'breadcrumbs' => ['Master Setup' => NULL, 'Branches' => NULL]
						])
						->partial('content', 'setup/branches/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Branch
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->branch_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/branches/_form',
			[
				'form_elements' => $this->branch_model->validation_rules,
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
		$json_data['form'] = $this->load->view('setup/branches/_form',
			[
				'form_elements' => $this->branch_model->validation_rules,
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
			$done = FALSE;

			$rules = array_merge($this->branch_model->validation_rules, get_contact_form_validation_rules());
			if($action === 'edit')
			{
				// Update Validation Rule on Update
				$rules[1]['rules'] = 'trim|required|max_length[5]|callback_check_duplicate';
			}
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				// Insert or Update?
				if($action === 'add')
				{
					// @NOTE: Activity Log will be automatically inserted
					$done = $this->branch_model->insert($data, TRUE); // No Validation on Model

					// Activity Log
					$done ? $this->branch_model->log_activity($done, 'C'): '';
				}
				else
				{
					// Now Update Data
					$done = $this->branch_model->update($record->id, $data, TRUE) && $this->branch_model->log_activity($record->id, 'E');
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
					$records = $this->branch_model->get_all();
					$success_html = $this->load->view('setup/branches/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->branch_model->find($record->id);
					$success_html = $this->load->view('setup/branches/_single_row', ['record' => $record], TRUE);
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
									? 	$this->load->view('setup/branches/_form',
											[
												'form_elements' => $this->branch_model->validation_rules,
												'record' 		=> $record
											], TRUE)
									: 	null

			];
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Branch
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->branch_model->find($id);
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
		if( !safe_to_delete( 'Branch_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->branch_model->delete($record->id);

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
    public function check_duplicate($code, $id=NULL){

    	$code = strtoupper( $code ? $code : $this->input->post('code') );
    	$id   = $id ? (int)$id : (int)$this->input->post('id');

        if( $this->branch_model->check_duplicate(['code' => $code], $id))
        {
            $this->form_validation->set_message('check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }


    // --------------------------------------------------------------------

    /**
     * View Branch Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	$id = (int)$id;
		$record = $this->branch_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}
		$this->data['site_title'] = 'Branch Details | ' . $record->name;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Branch Details <small>' . $record->name . '</small>',
								'breadcrumbs' => ['Branches' => 'branches', 'Details' => NULL]
						])
						->partial('content', 'setup/branches/_details', compact('record'))
						->render($this->data);

    }

    // --------------------------------------------------------------------


    // --------------------------------------------------------------------
    // MANAGE TARGETS
    // --------------------------------------------------------------------


    public function targets()
    {
    	// Site Meta
    	$this->data['site_title'] = 'Master Setup | Branch Targets';

    	$this->load->model('branch_target_model');

    	/**
    	 * Update Nav Data
    	 */
    	$this->_navigation['level_3'] = 'targets';
    	$this->active_nav_primary($this->_navigation);

    	$records = $this->branch_target_model->get_row_list();


		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Branch Targets',
								'breadcrumbs' => ['Master Setup' => NULL, 'Branches' => 'branches', 'Targets' => NULL]
						])
						->partial('content', 'setup/branches/_targets', compact('records'))
						->render($this->data);
  	}

  	// --------------------------------------------------------------------

  	// --------------------------------------------------------------------

	/**
	 * Add new Targets for a Specific Fiscal Year
	 *
	 * @return void
	 */
	public function add_targets()
	{
		$this->load->model('branch_target_model');

		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save_targets('add');


		// No form Submitted?
		$rules = $this->branch_target_model->validation_rules;
		$rules[0]['_data'] = ['' => 'Select...'] + $this->fiscal_year_model->dropdown();

		$branches = $this->branch_model->dropdown();

		$json_data['form'] = $this->load->view('setup/branches/_form_targets',
			[
				'form_elements' => $rules,
				'branches' 		=> $branches,
				'action' 		=> 'add',
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Branch Targets for Specific Fiscal Year
	 *
	 *
	 * @param integer $fiscal_yr_id
	 * @return void
	 */
	public function edit_targets($fiscal_yr_id)
	{
		$this->load->model('branch_target_model');

		// Valid Record ?
		$fiscal_yr_id 	= (int)$fiscal_yr_id;
		$record 		= $this->branch_target_model->get_row_single($fiscal_yr_id);
		$targets 		= $this->branch_target_model->get_list_by_fiscal_year($fiscal_yr_id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save_targets('edit', $record);


		// No form Submitted?
		$branches = $this->branch_model->dropdown();

		$rules = $this->branch_target_model->validation_rules;
		$rules[0]['_data'] = ['' => 'Select...'] + $this->fiscal_year_model->dropdown();
		$json_data['form'] = $this->load->view('setup/branches/_form_targets',
			[
				'form_elements' => $rules,
				'action' 		=> 'edit',
				'branches' 		=> $branches,
				'targets' 		=> $targets,
				'record' 		=> $record
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
	private function _save_targets($action, $record = NULL)
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
			$done = FALSE;

			$rules = $this->branch_target_model->validation_rules;


			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				$fiscal_yr_id = $this->input->post('fiscal_yr_id');
				$target_total = $this->input->post('target_total');
				$branches = $this->branch_model->dropdown();

				// Insert or Update?
				if($action === 'add')
				{
					$i = 0;
					foreach($branches as $branch_id => $branch_name)
					{
						$data = [
							'fiscal_yr_id' 	=> $fiscal_yr_id,
							'branch_id'    	=> $branch_id,
							'target_total' 	=> $target_total[$i]
						];

						$done = $this->branch_target_model->insert($data, TRUE); // No Validation on Model

						// Activity Log
						$done ? $this->branch_target_model->log_activity($done, 'C'): '';
						$i++;
					}
				}
				else
				{
					// Now Update Data
					$target_ids = $this->input->post('target_ids');
					$i = 0;
					foreach($branches as $branch_id => $branch_name)
					{
						$data = [
							'target_total' 	=> $target_total[$i]
						];
						$target_id = $target_ids[$i];

						$done = $this->branch_target_model->update($target_id, $data, TRUE) && $this->branch_target_model->log_activity($target_id, 'E');

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
					$records = $this->branch_target_model->get_row_list();
					$success_html = $this->load->view('setup/branches/_list_targets', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->branch_target_model->get_row_single($fiscal_yr_id);
					$success_html = $this->load->view('setup/branches/_single_row_targets', ['record' => $record], TRUE);
				}
			}

			$rules 				= $this->branch_target_model->validation_rules;
			$rules[0]['_data'] 	= ['' => 'Select...'] + $this->fiscal_year_model->dropdown();
			$targets 			= $record ? $this->branch_target_model->get_list_by_fiscal_year($record->fiscal_yr_id) : NULL;

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
									? 	$this->load->view('setup/branches/_form_targets',
											[
												'form_elements' => $rules,
												'record' 		=> $record,
												'action' 		=> $action,
												'targets' 		=> $targets
											], TRUE)
									: 	null

			];
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Branch Targets for Specific Fiscal Year
	 *
	 *
	 * @param integer $fiscal_yr_id
	 * @return void
	 */
	public function target_details($fiscal_yr_id)
	{
		$this->load->model('branch_target_model');
		$this->load->model('portfolio_model');

		// Valid Record ?
		$fiscal_yr_id 	= (int)$fiscal_yr_id;
		$record 		= $this->branch_target_model->get_row_single($fiscal_yr_id);
		$targets 		= $this->branch_target_model->get_list_by_fiscal_year($fiscal_yr_id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$this->data['site_title'] = 'Branch Target Details | Fiscal Year ' . $record->code_np;

		/**
		 * Master Portfolio Array Structure
		 *
		 * 	[
		 * 		'12' => [
		 * 			'id' => '12',
		 * 			'name' => 'Agriculture',
		 * 			'parent_id' => '0',
		 * 			'children' = [
		 * 				'22' => ['id' => '22', 'name' => 'Cattle', 'parent_id' => '12'],
		 * 				'29' => ['id' => '29', 'name' => 'Fish', 'parent_id' => '12']
		 * 			]
		 * 		],
		 * 		'13' => [
		 * 			'id' => '13',
		 * 			'name' => 'Aviation',
		 * 			'parent_id' => '0'
		 * 		]
		 * 	]
		 */
		$portfolio_list = $this->portfolio_model->get_all();
		$portfolio = [];
		foreach($portfolio_list as $p)
		{
			$single = [
				'id' 			=> $p->id,
				'name' 			=> $p->name_en,
				'parent_id' 	=> $p->parent_id
			];
			if($p->parent_id == '0')
			{
				$portfolio["{$p->id}"] = $single;
			}
			else
			{
				$portfolio["{$p->parent_id}"]['children']["{$p->id}"] = $single;
			}
		}

		$branches = $this->branch_model->dropdown();
		$partial_data = [
			'branches' 		=> $branches,
			'targets' 		=> $targets,
			'record' 		=> $record,
			'portfolio'		=> $portfolio
		];
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Branch Target Details <small> Fiscal Year ' . $record->code_np . '</small>',
								'breadcrumbs' => ['Master Setup' => NULL, 'Branch Targets' => 'branches/targets', 'Target Details' => NULL]
						])
						->partial('content', 'setup/branches/_target_details', $partial_data)
						->partial('dynamic_js', 'setup/branches/_target_js')
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Save Target details for Specific Branch for Specific Fiscal Year
	 *
	 * @param integer $target_id
	 * @return json
	 */
	public function save_target_details($target_id)
	{
		$this->load->model('branch_target_model');
		$this->load->model('portfolio_model');

		// Valid Record ?
		$target_id = (int)$target_id;
		$record = $this->branch_target_model->find($target_id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;

			$rules = [
				[
		            'field' => 'target_total',
		            'label' => 'Total Target',
		            'rules' => 'trim|required|prep_decimal|decimal|max_length[14]|callback_check_target_math',
		            '_type'     => 'text',
		            '_required' => true
		        ],
		        [
		            'field' => 'portfolio_ids[]',
		            'label' => 'Portfolio',
		            'rules' => 'trim|required|integer|max_length[11]',
		            '_type'     => 'text',
		            '_required' => true
		        ],
		        [
		            'field' => 'portfolio_target[]',
		            'label' => 'Portfolio-wise Target',
		            'rules' => 'trim|required|prep_decimal|decimal|max_length[14]',
		            '_type'     => 'text',
		            '_required' => true
		        ],
		        [
		            'field' => 'child_portfolio_ids[]',
		            'label' => 'Sub Portfolio',
		            'rules' => 'trim|required|integer|max_length[11]',
		            '_type'     => 'text',
		            '_required' => true
		        ],
		        [
		            'field' => 'parent_ids[]',
		            'label' => 'Parent Portfolio',
		            'rules' => 'trim|required|integer|max_length[11]',
		            '_type'     => 'text',
		            '_required' => true
		        ],
		        [
		            'field' => 'child_portfolio_target[]',
		            'label' => 'Sub-Portfolio-wise Target',
		            'rules' => 'trim|required|prep_decimal|decimal|max_length[14]',
		            '_type'     => 'text',
		            '_required' => true
		        ]
		    ];


			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				/**
				 * JSON Structure in Database (Target Details)
				 *
				 * 	[{
				 * 		"portfolio" : "12",
				 * 		"target" : "500.98",
				 * 		"children" : [{"portfolio":"22", "target": "300"}, {"portfolio":"29", "target": "200"}]
				 * 	},{
				 * 		"portfolio" : "13",
				 * 		"target" : "479"
				 * }]
				 */
				$post_data = [
					'target_total'   	=> $this->input->post('target_total'),
					'target_details' 	=> $this->_get_target_details_formatted(TRUE)
				];

				$done = $this->branch_target_model->update($target_id, $post_data, TRUE) && $this->branch_target_model->log_activity($target_id, 'E');


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
		}

		$return_data = [
			'status' => $status,
			'message' => validation_errors() ? validation_errors() : $message
		];

		$this->template->json($return_data);
	}
		/**
		 * Check Target Math
		 *
		 * Check if Target Total = SUM(portfolio_targets) && Portfolio Target = SUM(sub_portfolio_targets)
		 * @return type
		 */
		public function check_target_math()
		{
			$target_total = $this->input->post('target_total');

			$target_details = $this->_get_target_details_formatted();

			// Check Target Total ( = SUM(parent portfolio target) )
			$parent_total = 0;
			foreach($target_details as $t)
			{
				$parent_total += $t['target'];

				// Check Portfolio Total ( = SUM(child portfolio target) )
				$children = $t['children'] ?? [];
				$child_total = 0;
				$portfolio_total = $t['target'];
				foreach($children as $c)
				{
					$child_total += $c['target'];
				}

				if( !empty($children) && $child_total != $portfolio_total)
				{
					$this->form_validation->set_message('check_target_math', 'The target distribution is invalid (portfolio total != sum(children_portfolio) ).');
		            return FALSE;
				}

			}
			if( $target_total != $parent_total)
			{
				$this->form_validation->set_message('check_target_math', 'The target distribution is invalid (target total != sum(parent_portfolio) ).');
	            return FALSE;
			}
	        return TRUE;
		}

		/**
		 * Get Formatted Target Details Data
		 *
		 * @param bool $json Should we return on JSON Format or Array?
		 * @return json|array
		 */
		private function _get_target_details_formatted($json = FALSE)
		{
			/**
			 * JSON Structure in Database (Target Details)
			 *
			 * 	[{
			 * 		"portfolio" : "12",
			 * 		"target" : "500.98",
			 * 		"children" : [{"portfolio":"22", "target": "300"}, {"portfolio":"29", "target": "200"}]
			 * 	},{
			 * 		"portfolio" : "13",
			 * 		"target" : "479"
			 * }]
			 */

			$portfolio_ids = $this->input->post('portfolio_ids');
			$portfolio_target = $this->input->post('portfolio_target');

			$child_portfolio_ids 	= $this->input->post('child_portfolio_ids');
			$parent_ids 			= $this->input->post('parent_ids');
			$child_portfolio_target = $this->input->post('child_portfolio_target');


			$data = [];

			$i = 0;
			foreach( $portfolio_ids as $portfolio_id)
			{
				$data[] = ['portfolio' => $portfolio_id, 'target' => $portfolio_target[$i]];

				// Let's Check if it has children
				$j = 0;
				foreach($parent_ids as $parent_id)
				{
					if( $parent_id == $portfolio_id )
					{
						$data[$i]['children'][] = ['portfolio' => $child_portfolio_ids[$j], 'target' => $child_portfolio_target[$j]];
					}
					$j++;
				}

				$i++;
			}

			return $json ? json_encode($data) : $data;
		}

	// --------------------------------------------------------------------

	/**
	 * Delete Targets of a Specific Fiscal Year
	 * @param integer $id
	 * @return json
	 */
	public function delete_targets($fiscal_yr_id)
	{
		$this->load->model('branch_target_model');

		// Valid Record ?
		$fiscal_yr_id = (int)$fiscal_yr_id;
		$targets 		= $this->branch_target_model->get_list_by_fiscal_year($fiscal_yr_id);
		$record 		= $this->branch_target_model->get_row_single($fiscal_yr_id);
		if(!$record || !$targets)
		{
			$this->template->render_404();
		}

		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];

		foreach ($targets as $target)
		{
			/**
			 * Safe to Delete?
			 */
			if( !safe_to_delete( 'Branch_target_model', $target->id ) )
			{
				return $this->template->json($data);
			}

			$done = $this->branch_target_model->delete($target->id);
		}



		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-'.$record->fiscal_yr_id
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
}