<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Endorsement_templates Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Endorsement_templates extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | Endorsement Templates';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'portfolio',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('endorsement_template_model');
		$this->load->model('portfolio_model');
        $this->load->helper('policy');
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

		$records = $this->endorsement_template_model->rows($params);
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
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-endorsement_template', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-endorsement_template' 			// Filter Form ID
		];

		$data = [
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'endorsement_templates/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'setup/endorsement_templates/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('endorsement_templates/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/endorsement_templates/_list';
		}
		else
		{
			$view = 'setup/endorsement_templates/_rows';
		}


		if ( $this->input->is_ajax_request() )
		{

			// $view = $refresh === FALSE ? 'setup/endorsement_templates/_rows' : 'setup/endorsement_templates/_list';
			$html = $this->load->view($view, $data, TRUE);
			$ajax_data = [
				'status' => 'success',
				'html'   => $html
			];

			if( !empty($ajax_extra))
			{
				$ajax_data = array_merge($ajax_data, $ajax_extra);
			}
			$this->template->json($ajax_data);
		}

		/**
		 * Filter Configurations
		 */
		// $data['filters'] = $this->_get_filter_elements();
		// $data['filter_url'] = site_url('endorsement_templates/filter/');

		$this->template
						->set_layout('layout-advanced-filters')
						->partial(
							'content_header',
							'setup/endorsement_templates/_index_header',
							['content_header' => 'Manage Endorsement Templates'] + $dom_data)
						->partial('content', 'setup/endorsement_templates/_index', $data)
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$e_type_dropdown = _ENDORSEMENT_type_dropdown(false);
			$select = ['' => 'Select ...'];
			$filters = [
				[
	                'field' => 'filter_portfolio',
	                'label' => 'Portfolio',
	                'label' => 'Portfolio',
	                'rules' => 'trim|integer|max_length[11]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $this->portfolio_model->dropdown_children_tree(),
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_endorsement_type',
	                'label' => 'Endorsement Type',
	                'rules' => 'trim|integer|max_length[2]|in_list[' . implode(',', array_keys($e_type_dropdown) ) . ']',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $e_type_dropdown,
	                '_required' => false
	            ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Keywords <i class="fa fa-info-circle"></i>',
			        'rules' => 'trim|max_length[250]',
	                '_type'     => 'text',
	                '_label_extra' => 'data-toggle="tooltip" data-title="Endorsement title"'
				],
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
						'portfolio_id' 		=> $this->input->post('filter_portfolio') ?? NULL,
						'endorsement_type' 	=> $this->input->post('filter_endorsement_type') ?? NULL,
						'keywords' 			=> $this->input->post('filter_keywords') ?? ''
					];
					$data['status'] = 'success';
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

	// --------------------------------------------------------------------

	/**
	 * Edit a Recrod
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->endorsement_template_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/endorsement_templates/_form',
			[
				'form_elements' => $this->endorsement_template_model->validation_rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add()
	{
		$record = NULL;

		// Form Submitted? Save the data
		$this->_save('add');


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/endorsement_templates/_form',
			[
				'form_elements' => $this->endorsement_template_model->validation_rules,
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
			return $this->template->json(['status' => 'error', 'message' => 'Invalid Action!']);
		}

		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{
			$done = FALSE;

			$this->form_validation->set_rules($this->endorsement_template_model->validation_rules);
			if($this->form_validation->run() === TRUE )
        	{
				$data = $this->input->post();

        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->endorsement_template_model->insert($data, TRUE); // No Validation on Model
				}
				else
				{
					// Now Update Data
					$done = $this->endorsement_template_model->update($record->id, $data, TRUE);
				}

	        	if(!$done)
				{
					return $this->template->json(['status' => 'error', 'message' => 'Could not update.']);
				}
				else
				{
					$status = 'success';
					$message = 'Successfully Updated.';

					if($action === 'add')
					{
						// Refresh the list page and close bootbox
						return $this->page('l', 0, [
								'message' => $message,
								'status'  => $status,
								'hideBootbox' => true,
								'updateSection' => true,
								'updateSectionData' => [
									'box' 		=> '#_iqb-data-list-box-endorsement_template',
									'method' 	=> 'html'
								],
							]);
					}
					else
					{
						// Get Updated Record
						$record = $this->endorsement_template_model->row($record->id);
						$success_html = $this->load->view('setup/endorsement_templates/_single_row', ['record' => $record], TRUE);

						return $this->template->json([
							'status' 		=> $status,
							'message' 		=> $message,
							'reloadForm' 	=> false,
							'hideBootbox' 	=> true,
							'updateSection' => true,
							'updateSectionData' => [
								'box' 		=> '#_data-row-' . $record->id,
								'method' 	=> 'replaceWith',
								'html'		=> $success_html
							]
						]);
					}
				}
        	}
        	else
        	{
        		return $this->template->json(['status' => 'error', 'message' => validation_errors()]);
        	}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Endorsement Templates
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->endorsement_template_model->find($id);
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
		if( !safe_to_delete( 'Endorsement_template_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->endorsement_template_model->delete($record->id);

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
     * View Endorsement Templates Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	$id = (int)$id;
		$record = $this->endorsement_template_model->row($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$this->data['site_title'] = 'Endorsement Templates Details | ' . $record->portfolio_name_en;
		$header_title = 'Endorsement Templates Details <small>' . $record->portfolio_name_en . '</small> - <small>' . _ENDORSEMENT_type_text($record->endorsement_type) . '</small>';
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => $header_title,
								'breadcrumbs' => ['Endorsement Templates' => 'endorsement_templates', 'Details' => NULL]
						])
						->partial('content', 'setup/endorsement_templates/_details', compact('record'))
						->render($this->data);

    }
}