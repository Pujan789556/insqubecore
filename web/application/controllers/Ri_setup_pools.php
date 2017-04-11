<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RI Setup - Pool Treaties Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 * @sub-category RI
 */

// --------------------------------------------------------------------

class Ri_setup_pools extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Only Admin Can access this controller
		if( !$this->dx_auth->is_admin() )
		{
			$this->dx_auth->deny_access();
		}

		// Helper
		$this->load->helper('ri');

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Master Setup | RI | Pools';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'ri',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('ri_setup_pool_model');
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
	 * Paginate Data List
	 *
	 * @param integer $next_id
	 * @return void
	 */
	function page( $layout='f', $next_id = 0,  $ajax_extra = [], $do_filter = TRUE )
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
		$filter_data = $this->_get_filter_data( $do_filter );
		if( $filter_data['status'] === 'success' )
		{
			$params = array_merge($params, $filter_data['data']);
		}

		$records = $this->ri_setup_pool_model->rows($params);
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
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-ri-setup-pool', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-ri-setup-treaty' 			// Filter Form ID
		];

		$data = [
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'ri_setup_pools/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'setup/ri/pools/_index';

			/**
			 * Filter Configurations
			 */
			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('ri_setup_pools/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/ri/pools/_list';
		}
		else
		{
			$view = 'setup/ri/pools/_rows';
		}


		if ( $this->input->is_ajax_request() )
		{


			// $view = $refresh === FALSE ? 'setup/ri/pools/_rows' : 'setup/ri/pools/_list';
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

		$this->template
						->set_layout('layout-advanced-filters')
						->partial(
							'content_header',
							'setup/ri/pools/_index_header',
							['content_header' => 'Manage Pools'] + $dom_data)
						->partial('content', 'setup/ri/pools/_index', $data)
						->render($this->data);
	}

	// --------------------------------------------------------------------

		private function _get_filter_elements()
		{
			$filters = [
				[
	                'field' => 'filter_fiscal_yr_id',
	                'label' => 'Fiscal Year',
	                'rules' => 'trim|integer|max_length[3]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
	                '_required' => false
	            ],
	            [
					'field' => 'filter_keywords',
			        'label' => 'Keywords <i class="fa fa-info-circle"></i>',
			        'rules' => 'trim|max_length[80]',
	                '_type'     => 'text',
	                '_label_extra' => 'data-toggle="tooltip" data-title="Type pool treaty name"'
				]
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
						'fiscal_yr_id' 	=> $this->input->post('filter_fiscal_yr_id') ?? NULL,
						'keywords' 		=> $this->input->post('filter_keywords') ?? NULL
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
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add()
	{
		$record = NULL;

		/**
		 * Prepare Form Data
		 */
		$form_data = [

			'form_elements' 	=> $this->ri_setup_pool_model->get_validation_rules(['basic', 'portfolios', 'reinsurers']),
			'record' 			=> $record,

			// Pool Portfolios
			'pool_portfolios' => [],

			// Pool Distribution
			'pool_distribution' 	=> [],
		];

		// Form Submitted? Save the data
		return $this->_save('add', $record, $form_data);
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
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ri_setup_pool_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Existing Reinsurers Distriution
		 */
		$pool_distribution = $this->ri_setup_pool_model->get_pool_distribution_by_pool($id);

		/**
		 * Existing Portfolios
		 */
		$pool_portfolios = $this->ri_setup_pool_model->get_portfolios_by_pool($id);


		/**
		 * Prepare Form Data
		 */
		$form_data = [

			'form_elements' 	=> $this->ri_setup_pool_model->get_validation_rules(['basic', 'portfolios', 'reinsurers']),
			'record' 			=> $record,

			// Reinsurer Companies
			'pool_distribution' => $pool_distribution,

			// Portfolios
			'pool_portfolios' => $pool_portfolios
		];

		// Form Submitted? Save the data
		return $this->_save('edit', $record, $form_data);
	}


	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save($action, $record = NULL, $form_data)
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
			$file = $record->file ?? NULL;

			$rules = $this->ri_setup_pool_model->get_validation_rules_formatted(['basic', 'portfolios', 'reinsurers']);
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
    			$data = $this->input->post();

        		// Insert or Update?
				if($action === 'add')
				{
					$done = $this->ri_setup_pool_model->add($data);
				}
				else
				{
					// Now Update Data
					$done = $this->ri_setup_pool_model->edit($record->id, $data);
				}

	        	if(!$done)
				{
					// Simply return error message
					return $this->template->json([
						'status' 	=> 'error',
						'message' 	=> 'Could not update.'
					]);
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
									'box' 		=> '#_iqb-data-list-box-ri-setup-pool',
									'method' 	=> 'html'
								]
							], FALSE);
					}
					else
					{
						// Get Updated Record
						$record = $this->ri_setup_pool_model->row($record->id);
						$success_html = $this->load->view('setup/ri/pools/_single_row', ['record' => $record], TRUE);
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
        	}
        	else
        	{
        		// Simply return validation error
				return $this->template->json([
					'status' 	=> 'error',
					'message' 	=> validation_errors()
				]);
        	}
		}

		// Prepare HTML Form
		$json_data['form'] = $this->load->view('setup/ri/pools/_form', $form_data, TRUE);

		// Return JSON
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Check if RI Distribution is 100%
		 *
		 * @param integer $treaty_type_id
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_distribution__complete($str)
		{
			$company_ids = $this->input->post('company_id');
			$distribution_percent = $this->input->post('distribution_percent');

			// Check duplicate Entries
			$unique_count = count( array_unique($company_ids) );
			if( $unique_count !== count($company_ids) )
			{
				$this->form_validation->set_message('_cb_distribution__complete', 'Reinsurer can not be duplicate.');
	            return FALSE;
			}

			// Lets do the math
			$percent = [];
			$i = 0;
			foreach ($company_ids as $rid)
			{
				$percent["$rid"] = $distribution_percent[$i++];
			}

			$total = 0;
			foreach($percent as $rid=>$dp)
			{
				$total += (float)$dp;
			}
			$total = (int)$total;

			// 100% ?
	        if( $total != 100 )
	        {
	            $this->form_validation->set_message('_cb_distribution__complete', 'The TOTAL of all %s must be equal to 100.');
	            return FALSE;
	        }
	        return TRUE;
		}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Check Duplicate - Portfolio
		 *
		 * Duplicate Condition: Portfolio Should be attached to only on Treay Per Fiscal Year
		 *
		 * @param integer $portfolio_id
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_portfolio__check_duplicate($portfolio_id, $id=NULL)
		{
			$portfolio_id = (int)$portfolio_id;
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');
	    	$fiscal_yr_id = (int)$this->input->post('fiscal_yr_id');

			// Check duplicate Entries
			$portfolio_ids 	= $this->input->post('portfolio_id');
			$unique_count 	= count( array_unique($portfolio_ids) );
			if( $unique_count !== count($portfolio_ids) )
			{
				$this->form_validation->set_message('_cb_portfolio__check_duplicate', 'Portfolio can not be duplicate.');
	            return FALSE;
			}

	    	// Check if Fiscal Year has not been selected yet?
	    	if( !$fiscal_yr_id )
	    	{
	    		$this->form_validation->set_message('_cb_portfolio__check_duplicate', 'The Fiscal Year must be supplied along with %s.');
	            return FALSE;
	    	}

	    	// Check Duplicate - Treaty Record Exist with given portfolio for given fiscal year other than supplied treaty id
	        if( $this->ri_setup_pool_model->_cb_portfolio__check_duplicate($fiscal_yr_id, $portfolio_id, $id) )
	        {
	            $this->form_validation->set_message('_cb_portfolio__check_duplicate', 'The %s already exists for supplied Fiscal Year in another Treaty.');
	            return FALSE;
	        }
	        return TRUE;
		}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Check Duplicate - Name
		 *
		 * Duplicate Condition: [Fiscal Year ID, Treaty Type] Should be Unique
		 *
		 * @param integer $name
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_name__check_duplicate($name, $id=NULL)
		{
			$name = strtoupper( $name ? $name : $this->input->post('name') );
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');

	    	// Check Duplicate
	        if( $this->ri_setup_pool_model->check_duplicate(['LOWER(`name`)=' => strtolower($name)], $id))
	        {
	            $this->form_validation->set_message('_cb_name__check_duplicate', 'The %s already exists.');
	            return FALSE;
	        }
	        return TRUE;
		}

	// --------------------------------------------------------------------

    /**
     * View Treaty Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('ri_setup_pools', 'explore.pool') )
		{
			$this->dx_auth->deny_access();
		}

    	$id = (int)$id;
    	$record = $this->ri_setup_pool_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Treaty Data
		 */
		$data = [
			'record' 				=> $record,
			'portfolios' 			=> $this->ri_setup_pool_model->get_portfolios_by_pool($id),
			'pool_distribution' 	=> $this->ri_setup_pool_model->get_pool_distribution_by_pool($id),
		];

		$this->data['site_title'] = 'Pool Details | ' . $record->name;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Pool Details <small>' . $record->name . '</small>',
								'breadcrumbs' => ['Pool Setup' => 'ri_setup_pools', 'Details' => NULL]
						])
						->partial('content', 'setup/ri/pools/_details', $data)
						->render($this->data);

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
		$record = $this->ri_setup_pool_model->find($id);
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
		if( !safe_to_delete( 'Ri_setup_pool_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->ri_setup_pool_model->delete($record->id);

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