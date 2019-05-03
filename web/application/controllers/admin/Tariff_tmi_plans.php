<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tariff TMI Plans Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Tariff_tmi_plans extends MY_Controller
{
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
        $this->data['site_title'] = 'Application Settings | TMI Plans';

        // Setup Navigation
        $this->_navigation = [
			'level_0' => 'application_setup',
			'level_1' => 'portfolio',
			'level_2' => 'tariff',
			'level_3' => $this->router->class
		];
		$this->active_nav_primary($this->_navigation);

		// Load Model
		$this->load->model('tmi_plan_model');

		// URL Base
        $this->_url_base        = 'admin/' . $this->router->class;
        $this->_view_base 		= 'setup/tariff/' . $this->router->class;

        $this->data['_url_base'] 	= $this->_url_base; // for view to access
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
		$records = $this->tmi_plan_model->get_all();

		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage TMI Plans',
								'breadcrumbs' => ['Application Settings' => NULL, 'TMI Plans' => NULL]
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
		$record = $this->tmi_plan_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view( $this->_view_base . '/_form',
			[
				'form_elements' => $this->tmi_plan_model->validation_rules['basic'],
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
		$json_data['form'] = $this->load->view($this->_view_base . '/_form',
			[
				'form_elements' => $this->tmi_plan_model->validation_rules['basic'],
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Tariff for the supplied Plan
	 *
	 *
	 * @param char $type 	m: medical, p: package policy
	 * @param integer $id
	 * @return void
	 */
	public function tariff($type, $id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->tmi_plan_model->find($id);
		if( !$record || !in_array($type, ['m', 'p']) )
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('tariff', $record, $type);

		// No form Submitted?
		$json_data['form'] = $this->load->view($this->_view_base . '/_form_tariff',
			[
				'form_elements' => $this->tmi_plan_model->validation_rules['tariff'],
				'record' 		=> $record,
				'type' 			=> $type
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Benefits for the supplied Plan
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function benefits($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->tmi_plan_model->find($id);
		if( !$record )
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('benefits', $record);

		// No form Submitted?
		$json_data['form'] = $this->load->view($this->_view_base . '/_form_benefits',
			[
				'form_elements' => $this->tmi_plan_model->validation_rules['benefits'],
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
	private function _save($action, $record = NULL, $tariff_type=NULL)
	{
		// Valid action?
		if( !in_array($action, array('add', 'edit', 'tariff', 'benefits')))
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

				if( in_array($action, ['add', 'edit']))
				{
					// Prepare Data
					$data['active'] 	= $data['active'] ?? 0; // If not set, set active to zero
					$data['parent_id'] 	= $data['parent_id'] ? $data['parent_id'] : NULL; // if not supplied, set to NULL
				}

				// Insert or Update?
				if($action === 'add')
				{
					$done = $this->tmi_plan_model->insert($data, TRUE); // No Validation on Model
				}
				else if($action === 'edit')
				{
					// Basic Information Edit Mode
					$done = $this->tmi_plan_model->update($record->id, $data, TRUE);
				}

				/**
				 * Tariff Save
				 */
				else if($action === 'tariff')
				{
					/**
					 * Build Tariff Structure
					 */
					$structured_tariff = $this->_build_tariff_data();
					$tariff_data = [];
					$tariff_data[ $tariff_type == 'm' ? 'tariff_medical' : 'tariff_package' ] = json_encode($structured_tariff);

					// Basic Information Edit Mode
					$done = $this->tmi_plan_model->update($record->id, $tariff_data, TRUE);
				}

				/**
				 * Benefits
				 */
				else
				{
					/**
					 * Build Benefit Structure
					 */
					$structured_benefits = $this->_build_benefit_data();
					$benefit_data = [
						'benefits' => json_encode($structured_benefits)
					];
					// Basic Information Edit Mode
					$done = $this->tmi_plan_model->update($record->id, $benefit_data, TRUE);
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
					$records = $this->tmi_plan_model->get_all();
					$success_html = $this->load->view($this->_view_base . '/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->tmi_plan_model->find($record->id);
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

		private function _v_rule($action)
		{
			$rules = [];
			switch($action)
			{
				case 'add':
				case 'edit':
					$rules = $this->tmi_plan_model->validation_rules['basic'];
					break;

				case 'tariff':
				case 'benefits':
					$rules = $this->tmi_plan_model->validation_rules[$action];
					break;

				default:
					break;
			}

			return $rules;
		}

		private function _build_tariff_data()
		{
			$post_tariff = $this->input->post('tariff');
			$item_count = count($post_tariff['day_min']);

			$structured_tariff = [];
			$form_elements = $this->tmi_plan_model->validation_rules['tariff'];
			for($i = 0; $i < $item_count; $i++)
			{
				$single_tariff = [];
				foreach($form_elements as $elem)
				{
					$key = $elem['_key'];

					$single_tariff[$key] = $post_tariff[$key][$i];
				}
				$structured_tariff[] = $single_tariff;
			}

			return $structured_tariff;
		}

		private function _build_benefit_data()
		{
			$post_benefits = $this->input->post('benefits');
			$item_count = count($post_benefits['section']);

			$structured_benefits = [];
			$form_elements = $this->tmi_plan_model->validation_rules['benefits'];
			for($i = 0; $i < $item_count; $i++)
			{
				$single_benefits = [];
				foreach($form_elements as $elem)
				{
					$key = $elem['_key'];

					$single_benefits[$key] = $post_benefits[$key][$i];
				}
				$structured_benefits[] = $single_benefits;
			}

			return $structured_benefits;
		}
	// --------------------------------------------------------------------

    /**
     * Flush Cache Data
     *
     * @return void
     */
    public function flush()
    {
        $this->tmi_plan_model->clear_cache();
        redirect($this->_url_base);
    }

	// --------------------------------------------------------------------

    // !!! WE do not delete any records
	public function delete($id)
	{
		show_404();
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

        if( $this->tmi_plan_model->check_duplicate(['code' => $code], $id))
        {
            $this->form_validation->set_message('check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }
}