<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bs_reports Controller
 *
 * This controller falls under "Reports" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Bs_reports extends MY_Controller
{
	/**
	 * Files Upload Path
	 */
	public static $upload_path = INSQUBE_MEDIA_PATH . 'reports/bs/';

	// --------------------------------------------------------------------

	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Reports | Beema Samiti';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'reports',
			'level_1' => $this->router->class
		]);

		// Load Model
		$this->load->model('bs_report_model');
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

	// --------------------------------------------------------------------

	/**
	 * Underwriting - Report List
	 *
	 * @param integer $next_id
	 * @return void
	 */
	function page( $layout='f', $next_id = 0 )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('reports', 'explore.bs.reports') )
		{
			$this->dx_auth->deny_access();
		}


		// If request is coming from refresh method, reset nextid
		$next_id 		= (int)$next_id;
		$next_url_base 	= $this->router->class . '/page/r/';

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 	=> '_iqb-data-list-box-reports', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-reports' 			// Filter Form ID
		];

		/**
		 * Get Search Result
		 */
		$data = $this->_get_filter_data( $next_url_base, $next_id);
		$data = array_merge($data, $dom_data);


		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'reports/bs/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url($this->router->class . '/page/l/0')
			]);
		}
		else if($layout === 'l')
		{
			$view = 'reports/bs/_list';
		}
		else
		{
			$view = 'reports/bs/_rows';
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
							'reports/bs/_index_header',
							['content_header' => 'Beema Samiti Reports'] + $dom_data)
						->partial('content', 'reports/bs/_index', $data)
						->partial('dynamic_js', 'reports/bs/_js')
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$fy_dropdown = $this->fiscal_year_model->dropdown();
			$filters = [
	            [
	                'field' => 'filter_category',
	                'label' => 'Report Category',
	                'rules' => 'trim|alpha|exact_length[2]|in_list[' . implode(',', array_keys(IQB_BS_REPORT_CATEGORIES)) . ']',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + IQB_BS_REPORT_CATEGORIES,
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_type',
	                'label' => 'Report Type',
	                'rules' => 'trim|alpha|exact_length[1]|in_list[' . implode(',', array_keys(IQB_BS_REPORT_TYPES)) . ']',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + IQB_BS_REPORT_TYPES,
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_fiscal_yr_id',
	                'label' => 'Fiscal Year',
	                'rules' => 'trim|integer|max_length[3]|in_list[' . implode(',', array_keys($fy_dropdown)) . ']',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $fy_dropdown,
	                '_default'  => $this->current_fiscal_year->id,
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_fy_quarter_month',
	                'label' => 'Quarter/Month',
	                'rules' => 'trim|integer|max_length[8]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT,
	                '_required' => false
	            ],
			];
			return $filters;
		}

		private function _get_filter_data( $next_url_base, $next_id = 0)
		{
			$params = [];

			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$params = [
						'category' 			=> $this->input->post('filter_category') ?? NULL,
						'type' 				=> $this->input->post('filter_type') ?? NULL,
						'fiscal_yr_id' 		=> $this->input->post('filter_fiscal_yr_id') ?? NULL,
						'fy_quarter_month' 	=> $this->input->post('filter_fy_quarter_month') ?? NULL
					];
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

			$next_id = (int)$next_id;
			if( $next_id )
			{
				$params['next_id'] = $next_id;
			}

			/**
			 * Get Search Result
			 */
			$records = $this->bs_report_model->rows($params);
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

			$data = [
				'records'  => $records,
				'next_id'  => $next_id,
				'next_url' => $next_id ? site_url( rtrim($next_url_base, '/\\') . '/' . $next_id ) : NULL
			];
			return $data;
		}

	// --------------------------------------------------------------------

	public function download($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_admin() && !$this->dx_auth->is_authorized('reports', 'download.bs.reports') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->bs_report_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Let's Download
		$this->load->helper('download');
        $download_file = self::$upload_path . '/' . $record->filename;
        if( file_exists($download_file) )
        {
            force_download($download_file, NULL, true);
        }
        else
        {
        	$this->template->render_404('', "Sorry! File Not Found.");
        }
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
	 * Edit a Recrod
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('reports', 'edit.bs.report') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->bs_report_model->find($id);
		if(!$record || !$this->_is_editable($record) )
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$this->_save('edit', $record);

		$rules = $this->bs_report_model->validation_rules;
		// Find type
		if($record->type === IQB_BS_REPORT_TYPE_QUARTELRY )
		{
			$fy_quarter_month_dd = fiscal_year_quarters_dropdown();
		}
		else
		{
			$fy_quarter_month_dd = nepali_month_dropdown();
		}
		$rules[3]['_data'] = $fy_quarter_month_dd;


		// No form Submitted?
		$json_data['form'] = $this->load->view('reports/bs/_form_box',
			[
				'form_elements' => $rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

		private function _is_editable($record)
		{
			return $record->status != IQB_FLAG_ON;
		}

	// --------------------------------------------------------------------

	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add( )
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('reports', 'add.bs.report') )
		{
			$this->dx_auth->deny_access();
		}

		$record = NULL;

		// Form Submitted? Save the data
		$this->_save('add', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('reports/bs/_form_box',
			[
				'form_elements' => $this->bs_report_model->validation_rules,
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
	 * @param char 	$from_widget
	 * @param string $widget_reference
	 * @return array
	 */
	private function _save($action, $record = NULL)
	{
		// Valid action? Valid from_widget
		if( !in_array($action, array('add', 'edit'))  )
		{
			return $this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			], 404);
		}

		/**
		 * Form Submitted?
		 */

		if( $this->input->post() )
		{
			$done = FALSE;

			// Extract Old Profile Picture if any
			$picture = $record->picture ?? NULL;

			$rules = $this->bs_report_model->validation_rules;
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();

        		$formatted_data = [
        			'category'          => $data['category'],
		            'type'              => $data['type'],
		            'fiscal_yr_id'      => $data['fiscal_yr_id'],
		            'fy_quarter_month'  => $data['fy_quarter_month']
        		];
        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->bs_report_model->insert($formatted_data, TRUE); // No Validation on Model
				}
				else
				{
					// Now Update Data
					$done = $this->bs_report_model->update($record->id, $formatted_data, TRUE);
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

				$record 			= $this->bs_report_model->get( $action === 'add' ? $done : $record->id );
				$single_row 		=  'reports/bs/_single_row';

				$html = $this->load->view($single_row, ['record' => $record], TRUE);
				$ajax_data['updateSectionData'] = [
					'box' 		=> $action === 'add' ? '#search-result-reports' : '#_data-row-' . $record->id,
					'method' 	=> $action === 'add' ? 'prepend' : 'replaceWith',
					'html'		=> $html
				];
				return $this->template->json($ajax_data);
			}

			// Form
			return $this->template->json([
				'status' 		=> $status,
				'message' 		=> $message], 422);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Callback - Check duplicate
	 *
	 * @param int $fy_quarter_month
	 * @return bool
	 */
	public function check_duplicate($fy_quarter_month, $id=NULL)
	{
    	$id   = $id ? (int)$id : (int)$this->input->post('id');
    	$data = $this->input->post();
    	$where = [
    		'category'          => $data['category'],
            'type'              => $data['type'],
            'fiscal_yr_id'      => $data['fiscal_yr_id'],
            'fy_quarter_month'  => $data['fy_quarter_month'],
    	];

        if( $this->bs_report_model->check_duplicate($where, $id))
        {
            $this->form_validation->set_message('check_duplicate', 'The Record already exists.');
            return FALSE;
        }


        /**
         * Must be Past Fiscal Year Month or Quarter
         */
        $fiscal_yr_id 		= $data['fiscal_yr_id'];
        $type 				= $data['type'];
        $fy_quarter_month 	= $data['fy_quarter_month'];
        if($fiscal_yr_id > $this->current_fiscal_year->id)
        {
	    	$this->form_validation->set_message('check_duplicate', 'You can not generate report for Future Fiscal Year.');
	        return FALSE;
        }

        if($type == IQB_BS_REPORT_TYPE_QUARTELRY && $fy_quarter_month > $this->current_fy_quarter->quarter )
        {
        	$this->form_validation->set_message('check_duplicate', 'You can not generate report for Future Quarter.');
        	return FALSE;

        }
        else if($type == IQB_BS_REPORT_TYPE_MONTHLY && $fy_quarter_month > $this->current_fy_month->month_id )
        {
        	$this->form_validation->set_message('check_duplicate', 'You can not generate report for Future Month.');
        	return FALSE;
        }

        return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a Company
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('reports', 'delete.bs.report') )
		{
			$this->dx_auth->deny_access();
		}

		// Valid Record ?
		$id = (int)$id;
		$record = $this->bs_report_model->find($id);
		if(!$record || !$this->_is_editable($record))
		{
			return $this->template->json([
				'title' => 'Permission Denied',
				'status' => 'error',
				'message' => 'You can not delete this record.'
			], 403);
		}

		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];
		/**
		 * Safe to Delete?
		 */
		if( !safe_to_delete( 'Bs_report_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->bs_report_model->delete($record->id);

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