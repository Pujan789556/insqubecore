<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Policy Premium Controller
 *
 * We use this controller to work with the policy premium.
 *
 * This controller falls under "Policy" category.
 *
 * @category 	Policy
 */

// --------------------------------------------------------------------

class Premium extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Premium';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'policies',
		]);

		// Load Model
		$this->load->model('policy_model');
		$this->load->model('premium_model');
		$this->load->model('object_model');

		// Policy Configuration/Helper
		$this->load->config('policy');
		$this->load->helper('policy');
		$this->load->helper('object');

		// Media Helper
		$this->load->helper('insqube_media');

		// Image Path
        $this->_upload_path = INSQUBE_MEDIA_PATH . 'policies/';
	}

	// --------------------------------------------------------------------
	// SEARCH OPERATIONS
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
		$this->template->render_404();
	}


	// --------------------------------------------------------------------
	// CRUD OPERATIONS
	// --------------------------------------------------------------------

	/**
	 * Edit a Policy Premium Record
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($policy_id)
	{
		// Valid Record ?
		$policy_id = (int)$policy_id;
		$policy_record = $this->policy_model->get($policy_id);
		if(!$policy_record)
		{
			$this->template->render_404();
		}

		/**
		 * Belongs to Me? i.e. My Branch? OR Terminate
		 */
		belongs_to_me($policy_record->branch_id);


		/**
		 * Check Editable?
		 */
		is_policy_editable($policy_record->status);


		// Post? Save it
		$this->__save($policy_record);

		// Render Form
		$this->__render_form($policy_record);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save($action, $form_data, $from_widget = 'n')
	{

		// Valid action?
		if( !in_array($action, array('add', 'edit')))
		{
			return [
				'status' => 'error',
				'message' => 'Invalid action!'
			];
		}

		// Valid "from" ?
		if( !in_array($from_widget, array('y', 'n')))
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
		$record = $form_data['record'];

		if( $this->input->post() )
		{
			$done = FALSE;

			// These Rules are Sectioned, We need to merge Together
			$this->policy_model->set_validation_rules($action); // set rules according to action
			$v_rules = $this->policy_model->get_validation_rule($action);

            $this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();

        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->policy_model->insert($data, TRUE); // No Validation on Model

					// Activity Log
					$done ? $this->policy_model->log_activity($done, 'C'): '';
				}
				else
				{
					// Now Update Data
					$done = $this->policy_model->update($record->id, $data, TRUE) && $this->policy_model->log_activity($record->id, 'E');
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


			if($status === 'success' )
			{

				$ajax_data = [
					'message' => $message,
					'status'  => $status,
					'updateSection' => true,
					'hideBootbox' => true
				];

				if($action === 'add')
				{
					$record = $this->policy_model->row($done);
					$html = $this->load->view('policies/_single_row', ['record' => $record], TRUE);

					$ajax_data['updateSectionData'] = [
						'box' 		=> '#search-result-policy',
						'method' 	=> 'prepend',
						'html'		=> $html
					];
				}
				else
				{
					/**
					 * Widget or Row?
					 */
					$record = $from_widget === 'n'
								? $this->policy_model->row($record->id)
								: $this->policy_model->get($record->id);

					$view = $from_widget === 'n'
									? 'policies/_single_row'
									: 'policies/tabs/_tab_overview';

					$html = $this->load->view($view, ['record' => $record], TRUE);
					$ajax_data['updateSectionData']  = [
						'box' 		=> $from_widget === 'n' ? '#_data-row-policy-' . $record->id : '#tab-policy-overview-inner',
						'method' 	=> 'replaceWith',
						'html'		=> $html
					];
				}
				return $this->template->json($ajax_data);
			}
			else
			{

				// Policy Package of Portfolio if supplied
				$portfolio_id = (int)$this->input->post('portfolio_id');
				if($portfolio_id )
				{
					$form_data['form_elements']['portfolio'][1]['_data'] = _PO_policy_package_dropdown($portfolio_id);
				}

				return $this->template->json([
					'status' 		=> $status,
					'message' 		=> $message,
					'reloadForm' 	=> true,
					'form' 			=> $this->load->view('policies/_form', $form_data, TRUE)
				]);
			}
		}

		/**
		 * Render The Form
		 */
		$json_data = [
			'form' => $this->load->view('policies/_form_box', $form_data, TRUE)
		];
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Policy
	 *
	 * Only Draft Version of a Policy can be deleted.
	 *
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		// // Valid Record ?
		// $id = (int)$id;
		// $record = $this->policy_model->find($id);
		// if(!$record)
		// {
		// 	$this->template->render_404();
		// }

		// /**
		//  * Check Permissions
		//  *
		//  * Deletable Status
		//  * 		draft
		//  *
		//  * Deletable Permission
		//  * 		delete.draft.policy
		//  */
		// $editable_status 		= [IQB_POLICY_STATUS_DRAFT, IQB_POLICY_STATUS_UNVERIFIED];

		// // Deletable Status?
		// if( $record->status !== IQB_POLICY_STATUS_DRAFT )
		// {
		// 	$this->dx_auth->deny_access();
		// }

		// // Deletable Permission ?
		// $__flag_authorized 		= FALSE;
		// if(
		// 	$this->dx_auth->is_admin()

		// 	||

		// 	$this->dx_auth->is_authorized('policies', 'delete.draft.policy')
		// )
		// {
		// 	$__flag_authorized = TRUE;
		// }

		// if( !$__flag_authorized )
		// {
		// 	$this->dx_auth->deny_access();
		// }


		// $data = [
		// 	'status' 	=> 'error',
		// 	'message' 	=> 'You cannot delete the default records.'
		// ];
		// /**
		//  * Safe to Delete?
		//  */
		// if( !safe_to_delete( 'Policy_model', $id ) )
		// {
		// 	return $this->template->json($data);
		// }

		// $done = $this->policy_model->delete($record->id);

		// if($done)
		// {
		// 	/**
		// 	 * @TODO: Delete all related media
		// 	 */
		// 	// if($record->picture)
		// 	// {
		// 	// 	// Load media helper
		// 	// 	$this->load->helper('insqube_media');

		// 	// 	delete_insqube_document($this->_upload_path . $record->picture);
		// 	// }

		// 	$data = [
		// 		'status' 	=> 'success',
		// 		'message' 	=> 'Successfully deleted!',
		// 		'removeRow' => true,
		// 		'rowId'		=> '#_data-row-policy-'.$record->id
		// 	];
		// }
		// else
		// {
		// 	$data = [
		// 		'status' 	=> 'error',
		// 		'message' 	=> 'Could not be deleted. It might have references to other module(s)/component(s).'
		// 	];
		// }
		// return $this->template->json($data);
	}


	// --------------------------------------------------------------------

		/**
		 * Get Premium Form View
		 *
		 * @param object $record Policy Record
		 * @return string
		 */
		private function __get_form($record)
		{
			$form_view = 'premium/_form_' . $record->portfolio_code;
			return $form_view;
		}

	// --------------------------------------------------------------------

		/**
		 * Get Policy Object
		 *
		 * @param object $record Policy Record
		 * @return object 	Policy Object
		 */
		private function __get_policy_object($record)
		{
			$object = new StdClass();
			$object->attributes 	= $record->object_attributes;
			$object->id 			= $record->object_id;
			$object->portfolio_id 	= $record->portfolio_id;

			return $object;
		}

	// --------------------------------------------------------------------

		/**
		 * Save/Update Premium
		 *
		 * @param type $record 	Policy Record
		 * @return type
		 */
		private function __save($record)
		{
			if( $this->input->post() )
			{
				switch ($record->portfolio_id)
				{
					// Motor
					case IQB_MASTER_PORTFOLIO_MOTOR_ID:
							return $this->__save_MOTOR($record);
						break;

					default:
						# code...
						break;
				}
			}
		}

	// --------------------------------------------------------------------


		/**
		 * Render Premium Form
		 *
		 * @param type $record 	Policy Record
		 * @return type
		 */
		private function __render_form($record)
		{
			/**
			 *  Let's Load The Premium Form For this Record
			 */
			$object = $this->__get_policy_object($record);

			// Let's get the premium goodies for given portfolio
			$premium_goodies = _PO_premium_goodies($object, $record->fiscal_yr_id);

			// Premium Form
			$form_view = $this->__get_form($record);

			// Let's render the form
	        $json_data['form'] = $this->load->view($form_view,
	            [
	                'form_elements'         => $premium_goodies['validation_rules'],
	                'record'                => $record,
	                'object' 				=> $object,
	                'tariff_record' 		=> $premium_goodies['tariff_record']
	            ], TRUE);

	        // Return HTML
	        $this->template->json($json_data);
		}

	// --------------------------------------------------------------------
}