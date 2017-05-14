<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vouchers Controller
 *
 *
 * @category 	Accounting
 */

// --------------------------------------------------------------------

class Ac_vouchers extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Vouchers';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'accounting',
			'level_1' => 'ac_vouchers'
		]);

		// Load Model
		// $this->load->model('ac_account_group_model');
		$this->load->model('ac_voucher_model');
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
	public function page( $layout='f', $next_id = 0,  $ajax_extra = [], $do_filter = TRUE )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('ac_vouchers', 'explore.voucher') )
		{
			$this->dx_auth->deny_access();
		}

		/**
		 * NO AJAX ??
		 * 	Render the Explorer
		 */
		if ( !$this->input->is_ajax_request() )
		{
			return $this->_page_default();
		}

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
		$filter_data = $this->_get_filter_data( $do_filter );
		if( $filter_data['status'] === 'success' )
		{
			$params = array_merge($params, $filter_data['data']);
		}

		$records = $this->ac_voucher_model->rows($params);
		$records = $records ? $records : [];
		$total = count($records);

		/**
		 * Account Group Paths
		 */
		if($total)
		{
			foreach($records as &$record)
			{
				$path = $this->ac_account_group_model->get_path($record->account_group_id);
				$record->acg_path = $path;
			}
		}


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

		$data = [
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'ac_vouchers/page/r/' . $next_id ) : NULL
		];

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'accounting/vouchers/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('ac_vouchers/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'accounting/vouchers/_list';
		}
		else
		{
			$view = 'accounting/vouchers/_rows';
		}


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

		public function _page_default(  )
		{
			$data = [
				'DOM_DataListBoxId'	=> '_iqb-data-list-box-ac-voucher', 	// List box ID
				'DOM_FilterFormId'	=> '_iqb-filter-form-ac-voucher', 		// Filter Form ID
				'records' 			=> [],
				'next_id' 			=> NULL,
				'next_url' 			=> NULL,
				'filters' 			=> $this->_get_filter_elements(),
				'filter_url' 		=> site_url('ac_vouchers/page/l/' )
			];
			$this->template
							->set_layout('layout-advanced-filters')
							->partial(
								'content_header',
								'accounting/vouchers/_index_header',
								['content_header' => 'Manage Vouchers'] + $data)
							->partial('content', 'accounting/vouchers/_index', $data)
							->render($this->data);
		}

		private function _get_filter_elements()
		{
			$this->load->model('branch_model');
			$dropdown_branch 		 = $this->branch_model->dropdown();
			$dropdwon_account_groups = $this->ac_account_group_model->dropdown_tree();
			$filters = [
				[
	                'field' => 'filter_branch_id',
	                'label' => 'Branch',
	                'rules' => 'trim|integer|max_length[8]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $dropdown_branch,
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_fiscal_yr_id',
	                'label' => 'Fiscal Year',
	                'rules' => 'trim|integer|max_length[3]',
	                '_type'     => 'dropdown',
	                '_default' 	=> $this->current_fiscal_year->id,
	                '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_fy_quarter',
	                'label' => 'Quarter',
	                'rules' => 'trim|integer|exact_length[1]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + fiscal_year_quarters_dropdown(),
	                '_required' => false
	            ],
	            [
		            'field' => 'filter_start_date',
		            'label' => 'Voucher Start Date',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_end_date',
		            'label' => 'Voucher End Date',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Voucher Code',
			        'rules' => 'trim|max_length[20]',
	                '_type'     => 'text',
	                '_required' => false
				],
			];
			return $filters;
		}

		private function _get_filter_data( $do_filter=TRUE )
		{
			$data = ['status' => 'empty'];

			// Return Empty on do_filter = false (set 'false' by 'add' method)
			if( !$do_filter )
			{
				return $data;
			}
			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$data['data'] = [
						'branch_id' 	=> $this->input->post('filter_branch_id') ?? NULL,
						'fiscal_yr_id' 	=> $this->input->post('filter_fiscal_yr_id') ?? NULL,
						'fy_quarter' 	=> $this->input->post('filter_fy_quarter') ?? NULL,
						'start_date' 	=> $this->input->post('filter_start_date') ?? NULL,
						'end_date' 		=> $this->input->post('filter_end_date') ?? NULL,
						'keywords' 		=> $this->input->post('filter_keywords') ?? ''
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
		$record = $this->ac_voucher_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('accounting/vouchers/_form',
			[
				'form_elements' => $this->ac_voucher_model->validation_rules,
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
		$json_data = $this->_save('add');


		// No form Submitted?
		$json_data['form'] = $this->load->view('accounting/vouchers/_form',
			[
				'form_elements' => $this->ac_voucher_model->validation_rules,
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
			$this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			], 404);
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;

			$rules = $this->ac_voucher_model->validation_rules_formatted();

			$this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{

        		// @TODO - SAVE VOUCHER
        		$this->template->json([
					'status' => 'error',
					'message' => '@TODO - PLEASE UPDATE VOUCHER SAVE!'
				], 404);

        		$data = $this->input->post();

        		// Active/Inactive
        		$data['active'] = $data['active'] ?? 0;

        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->ac_voucher_model->insert($data, TRUE); // No Validation on Model

					// Activity Log
					$done ? $this->ac_voucher_model->log_activity($done, 'C'): '';
				}
				else
				{
					// Now Update Data
					$done = $this->ac_voucher_model->update($record->id, $data, TRUE) && $this->ac_voucher_model->log_activity($record->id, 'E');
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
				return $this->template->json([
					'status' => $status,
					'message' => validation_errors()
				]);
        	}

        	// Success HTML
			$success_html = '';
			$return_extra = [];
			if($status === 'success' )
			{
				if($action === 'add')
				{
					// Refresh the list page and close bootbox
					return $this->page('l', 0, [
							'message' => $message,
							'status'  => $status,
							'hideBootbox' => true,
							'updateSection' => true,
							'updateSectionData' => [
								'box' 		=> '#_iqb-data-list-box-ac-voucher',
								'method' 	=> 'html'
							]
						], FALSE);
				}
				else
				{
					// Get Updated Record
					$record = $this->ac_voucher_model->row($record->id);
					$record->acg_path = $this->ac_account_group_model->get_path($record->account_group_id);
					$success_html = $this->load->view('accounting/vouchers/_single_row', ['record' => $record], TRUE);
					$ajax_data = [
						'message' => $message,
						'status'  => $status,
						'updateSection' => true,
						'hideBootbox' => true
					];
					$ajax_data['updateSectionData'] = [
						'box' 		=> '#_data-row-' . $record->id,
						'method' 	=> 'replaceWith',
						'html'		=> $success_html
					];
					return $this->template->json($ajax_data);
				}
			}
			else
			{
				$return_data = [
					'status' 		=> $status,
					'message' 		=> $message,
					'reloadForm' 	=> true,
					'hideBootbox' 	=> false,
					'updateSection' => false,
					'updateSectionData'	=> NULL,
					'form' 	  		=> $this->load->view('accounting/vouchers/_form',
												[
													'form_elements' => $rules,
													'record' 		=> $record
												], TRUE)

				];
			}
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Callback: Valid Voucher Date?
	 *
	 * Voucher Date must be within the current Quarter.
	 *
	 * @param type $voucher_date
	 * @return type
	 */
	public function _valid_voucher_date($voucher_date)
	{
		$start 	= strtotime($this->current_fy_quarter->starts_at);
		$end 	= strtotime($this->current_fy_quarter->ends_at);
		$vdate 	= strtotime($voucher_date);

		if ($vdate >= $start && $vdate <= $end)
		{
			return TRUE;
		}

		$this->form_validation->set_message('_valid_voucher_date', "The \"Voucher Date\" does not fall under Current Quarter({$this->current_fy_quarter->starts_at} - {$this->current_fy_quarter->ends_at}).");

        return FALSE;
	}


	// --------------------------------------------------------------------

	/**
	 * Callback: Valid Voucher Amount
	 *
	 * Compute the Debit Sum and Credit Sum which should be equal.
	 *
	 * @param type $str
	 * @return type
	 */
	public function _valid_voucher_amount($str)
	{
		$amounts = $this->input->post('amount');
		$debits 	= $amounts['dr'];
		$credits 	= $amounts['cr'];

		$debit_total 	= 0.00;
		$credit_total 	= 0.00;

		// Compute Debit Total
		foreach($debits as $amount)
		{
			$debit_total += $amount;
		}

		// Compute Debit Total
		foreach($credits as $amount)
		{
			$credit_total += $amount;
		}

		$epsilon = 0.00001;
		if( abs($debit_total - $credit_total) < $epsilon )
		{
			return TRUE;
		}

		$this->form_validation->set_message('_valid_voucher_amount', '"Total Debit Amount" must be equal to "Total Credit Amount";');
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Agent
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ac_voucher_model->find($id);
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
		if( !safe_to_delete( 'Ac_chart_of_account_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->ac_voucher_model->delete($record->id);

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
}