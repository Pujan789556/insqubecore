<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Credit Notes Controller
 *
 *
 * @category 	Accounting
 */

// --------------------------------------------------------------------

class Ac_credit_notes extends MY_Controller
{
	/**
	 * Files Upload Path - Data
	 */
	public static $data_upload_path = INSQUBE_DATA_ROOT . 'credit_notes/';


	// --------------------------------------------------------------------

	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Credit Notes';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'accounting',
			'level_1' => 'ac_credit_notes'
		]);

		// Load Model
		$this->load->model('ac_credit_note_model');
		$this->load->model('ac_credit_note_detail_model');

	}

	// --------------------------------------------------------------------
	// SEARCH/LIST WIDGET FUNCTIONS
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
	public function page( $layout='f', $next_id = 0,  $do_filter = TRUE )
	{
		/**
		 * Check Permissions? OR Deny on Fail!
		 */
		$this->dx_auth->is_authorized('ac_credit_notes', 'explore.credit_note', TRUE);

		// dom data
		$dom_data = [
			'DOM_DataListBoxId'	=> '_iqb-data-list-box-ac-credit_note', 	// List box ID
			'DOM_FilterFormId'	=> '_iqb-filter-form-ac-credit_note', 		// Filter Form ID
		];

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

		$records 	= $this->ac_credit_note_model->rows($params);
		$records 	= $records ? $records : [];
		$total 		= count($records);


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
			'policy_id' => NULL,
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'ac_credit_notes/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'accounting/credit_notes/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('ac_credit_notes/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'accounting/credit_notes/_list';
		}
		else
		{
			$view = 'accounting/credit_notes/_rows';
		}

		if ( $this->input->is_ajax_request() )
		{
			$html = $this->load->view($view, $data, TRUE);
			$ajax_data = [
				'status' => 'success',
				'html'   => $html
			];
			$this->template->json($ajax_data);
		}

		$this->template
					->set_layout('layout-advanced-filters')
					->partial(
						'content_header',
						'accounting/credit_notes/_index_header',
						['content_header' => 'Manage Credit Notes'] + $data)
					->partial('content', 'accounting/credit_notes/_index', $data)
					->render($this->data);
	}


		private function _get_filter_elements()
		{
			$this->load->model('branch_model');
			$dropdown_branch 		 = $this->branch_model->dropdown();
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
		            'field' => 'filter_from_date',
		            'label' => 'From Date',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
		        [
		            'field' => 'filter_to_date',
		            'label' => 'To Date',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
		        ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Credit Note ID',
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
						'from_date' 	=> $this->input->post('filter_from_date') ?? NULL,
						'to_date' 		=> $this->input->post('filter_to_date') ?? NULL,
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


	// --------------------------------------------------------------------

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

	// --------------------------------------------------------------------

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
	 * Get all Credit Note for Supplied Policy
	 *
	 * @param int $policy_id
	 * @param bool $data_only Return Data Only
	 * @return JSON
	 */
	function by_policy($policy_id, $data_only = FALSE)
	{
		/**
		 * Check Permissions? OR Deny on Fail!
		 */
		$this->dx_auth->is_authorized('ac_credit_notes', 'explore.credit_note', TRUE);

		$policy_id 	= (int)$policy_id;
		$records = $this->ac_credit_note_model->rows_by_policy($policy_id);
		$data = [
			'records' 					=> $records,
			'policy_id' 				=> $policy_id,
			'next_id' 					=> NULL
		];
		// echo '<pre>'; print_r($data);exit;
		$html = $this->load->view('accounting/credit_notes/_policy/_list_widget', $data, TRUE);
		$ajax_data = [
			'status' => 'success',
			'html'   => $html
		];

		// Return if Ajax Data Only is Set
		if($data_only) return $ajax_data;

		$this->template->json($ajax_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Flush Cache - Per Policy
	 *
	 * @param type $policy_id
	 * @return type
	 */
	public function flush_by_policy($policy_id)
	{
		/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('ac_credit_notes', 'explore.credit_note', TRUE);

		$policy_id = $policy_id ? (int)$policy_id : NULL;
		$cache_var = $policy_id ? 'ac_credit_note_list_by_policy_' . $policy_id : NULL;

		$this->ac_credit_note_model->clear_cache($cache_var);

		$ajax_data = $this->by_policy($policy_id, TRUE);
		$json_data = [
			'status' 	=> 'success',
			'message' 	=> 'Successfully flushed the cache.',
			'reloadRow' => true,
			'rowId' 	=> '#list-widget-policy-credit_notes',
			'row' 		=> $ajax_data['html']
		];

		return $this->template->json($json_data);
	}

	// --------------------------------------------------------------------
	// CRUD FUNCTIONS
	// --------------------------------------------------------------------


	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add()
	{
		return $this->template->json([
				'title' => '@TODO - Do we need manual invoicing???',
				'status' => 'error',
				'message' => 'Talk with accounting personnels and implement this feature if needed.'
			], 404);

		/**
		 * Check Permissions? OR Deny on Fail
		 */
		$this->dx_auth->is_authorized('ac_credit_notes', 'add.credit_note', TRUE);

		$record = NULL;

		// Form Submitted? Save the data
		$json_data = $this->_save('add');


		// No form Submitted?
		$json_data['form'] = $this->load->view('accounting/credit_notes/_form',
			[
				'form_elements' 		=> $this->ac_credit_note_model->validation_rules,
				'record' 				=> $record,
				'voucher_detail_rows' 	=> NULL
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
		return false;


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

			$rules = $this->ac_credit_note_model->validation_rules_formatted();

			$this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();
        		// echo '<pre>'; print_r($data);exit;

        		// Insert or Update?
				if($action === 'add')
				{
					$data['flag_internal'] = IQB_FLAG_OFF;

					try {

						$done = $this->ac_credit_note_model->add($data);

					} catch (Exception $e) {

						return $this->template->json([
							'status' => $status,
							'message' => $e->getMessage()
						]);
					}
				}
				else
				{
					// Now Update Data
					$done = $this->ac_credit_note_model->edit($record->id, $data);
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
        		$message = validation_errors();

        	}

        	if($status === 'success' )
			{
				$ajax_data = [
					'message' => $message,
					'status'  => $status,
					'updateSection' => true,
					'hideBootbox' => true
				];

				$record 		= $this->ac_credit_note_model->row($action === 'add' ? $done : $record->id);
				$single_row 	=  'accounting/credit_notes/_single_row';
				$html = $this->load->view($single_row, ['record' => $record], TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $action === 'add' ? '#search-result-credit_note' : '#_data-row-credit_note-' . $record->id,
					'method' 	=> $action === 'add' ? 'prepend' : 'replaceWith',
					'html'		=> $html
				];

				return $this->template->json($ajax_data);
			}
			else
			{
				return $this->template->json([
					'status' => $status,
					'message' => $message
				]);
			}
		}
		return $return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Credit Note
	 *
	 * You can not delete any Credit Note.
	 *
	 * @param integer $id
	 * @return void
	 */
	public function delete($id)
	{
		return $this->template->json([
			'status' => 'error',
			'message' => 'You can not delete a credit_note!'
		], 404);
	}


	// --------------------------------------------------------------------
	//  DETAILS
	// --------------------------------------------------------------------


    /**
     * View Credit Note Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('ac_credit_notes', 'explore.credit_note', TRUE);

		/**
		 * Main Record
		 */
    	$id = (int)$id;
		$record = $this->ac_credit_note_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Check if Belongs to me?
		 */
		belongs_to_me( $record->branch_id );

		/**
		 * Credit Note Detail Rows
		 */
		$data = [
			'record' 	=> $record,
			'rows' 		=> $this->ac_credit_note_detail_model->rows_by_credit_note($record->id)
		];

		// echo '<pre>'; print_r($data);exit;

		$this->data['site_title'] = 'Credit Note Details | ' . $record->id;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Credit Note -' . $record->id,
								'breadcrumbs' => ['Credit Notes' => $this->router->class, 'Details' => NULL]
						])
						->partial('content', 'accounting/credit_notes/_details', $data)
						->render($this->data);

    }

    	private function _party_name($party_type, $party_id)
    	{
    		$party_name = '';
    		if( !$party_type || !$party_id ) return $party_name;

    		// Let's build party name
    		$party_model = '';
    		switch($party_type)
    		{
    			case IQB_AC_PARTY_TYPE_GENERAL:
    				$party_model = 'ac_party_model';
    				break;

				case IQB_AC_PARTY_TYPE_AGENT:
    				$party_model = 'agent_model';
    				break;

				case IQB_AC_PARTY_TYPE_CUSTOMER:
    				$party_model = 'customer_model';
    				break;

				case IQB_AC_PARTY_TYPE_COMPANY:
    				$party_model = 'company_model';
    				break;

				case IQB_AC_PARTY_TYPE_SURVEYOR:
    				$party_model = 'surveyor_model';
    				break;

				default:
					break;
    		}
    		if($party_model)
    		{
    			$this->load->model($party_model);
    			$party_name = $this->{$party_model}->name($party_id);
    		}
    		return $party_name;
    	}


	// --------------------------------------------------------------------
	//  PRINT CREDIT NOTE/RECEIPT
	// --------------------------------------------------------------------

	/**
	 * Print Credit Note
	 *
	 * @param string $type  credit_note|receipt
	 * @param integer $id  Credit Note ID
	 * @return void
	 */
    public function print($id)
    {
		/**
		 * Check Permissions
		 */
		$this->dx_auth->is_authorized('ac_credit_notes', 'print.credit_note', TRUE);


		/**
		 * Main Record (Complete Credit Note)
		 */
    	$id = (int)$id;
		$record = $this->ac_credit_note_model->get($id, IQB_FLAG_ON);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Check if Belongs to me?
		 */
		belongs_to_me( $record->branch_id );


		/**
		 * Download the physical copy if already exist
		 */
		$filename 	=  "credit_note-{$record->id}.pdf";
		$file 		= rtrim(self::$data_upload_path, '/') . '/' . $filename;
		if( file_exists($file) )
		{
			$this->load->helper('download');
			force_download($file, NULL);
			exit(0);
		}



		/**
		 * Credit Note Detail Rows
		 */
		$data = [
			'record' 	=> $record,
			'rows' 		=> $this->ac_credit_note_detail_model->rows_by_credit_note($record->id)
		];

		_CREDIT_NOTE__pdf($data, 'print');

    }

	// --------------------------------------------------------------------




	// --------------------------------------------------------------------
	//  FLAG AS PRINT
	// --------------------------------------------------------------------

    public function printed($type, $id, $policy_id = NULL)
    {
    	/**
		 * Valid Type?
		 */
    	if( !in_array($type, ['credit_note', 'receipt']) )
		{
			$this->template->render_404();
		}

		/**
		 * Check Permissions
		 */
    	$permission = "update.{$type}.print.flag";
		$this->dx_auth->is_authorized('ac_credit_notes', $permission, TRUE);


		/**
		 * Main Record (Complete Credit Note + Receipt Data)
		 */
    	$id = (int)$id;
		$record = $this->ac_credit_note_model->get($id, IQB_FLAG_ON);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
    	 * Already Printed?
    	 */
		$already_printed = FALSE;
		if( $type === 'credit_note' )
		{
			$already_printed = $record->flag_printed == IQB_FLAG_ON;
		}
		else
		{
			// Must have Receipt
			$already_printed = $record->receipt_id && $record->receipt_flag_printed == IQB_FLAG_ON;
		}
		if( $already_printed )
		{
			return $this->template->json([
				'title' 	=> 'Invalid Action!',
				'status' 	=> 'error',
				'message' 	=> 'It seems, you have already updated print flag!'
			], 404);
		}



		/**
		 * Call Individual Printed Method
		 */
		$method =  "_printed_{$type}";
		$record = $this->$method($record);

		if($record === FALSE )
		{
			return $this->template->json([
				'title' 	=> 'Could not Update',
				'status' 	=> 'error',
				'message' 	=> 'Could not update flag!'
			], 500);
		}

		/**
		 * Clear the Cache
		 */
		$this->ac_credit_note_model->clear_cache();

		/**
		 * Update The Row
		 */
		$row_html = $this->load->view('accounting/credit_notes/_single_row', ['record' => $record, 'policy_id' => $policy_id], TRUE);
		$ajax_data = [
			'message' => 'Successfully Updated',
			'status'  => 'success',
			'reloadRow' => true,
			'rowId' 	=> '#_data-row-credit_note-' . $record->id,
			'row' 		=> $row_html
		];
		return $this->template->json($ajax_data);
    }

	// --------------------------------------------------------------------

    	private function _printed_credit_note($record)
	    {
	    	if( $this->ac_credit_note_model->update_flag($record->id, 'flag_printed', IQB_FLAG_ON) )
	    	{
	    		$record->flag_printed = IQB_FLAG_ON;

	    		return $record;
	    	}
	    	return FALSE;
	    }

		// --------------------------------------------------------------------

	    private function _printed_receipt($record)
	    {
	    	$this->load->model('ac_receipt_model');
	    	if( $this->ac_receipt_model->update_flag($record->receipt_id, 'flag_printed', IQB_FLAG_ON) )
	    	{
	    		$record->receipt_flag_printed = IQB_FLAG_ON;

	    		return $record;
	    	}
	    	return FALSE;
	    }

	// --------------------------------------------------------------------


}















