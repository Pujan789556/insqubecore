<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Forex Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Forex extends MY_Controller
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
        $this->data['site_title'] = 'Master Setup | Forex';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('forex_model');
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

		$records = $this->forex_model->rows($params);
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
			'DOM_DataListBoxId' 	=> '_iqb-data-list-box-forex', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-forex' 			// Filter Form ID
		];

		$data = [
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'forex/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'setup/forex/_index';

			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('forex/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/forex/_list';
		}
		else
		{
			$view = 'setup/forex/_rows';
		}


		if ( $this->input->is_ajax_request() )
		{

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
							'setup/forex/_index_header',
							['content_header' => 'Manage Forex'] + $dom_data)
						->partial('content', 'setup/forex/_index', $data)
						->render($this->data);
	}

		private function _get_filter_elements()
		{
			$filters = [
				[
		            'field' => 'filter_exchange_date',
		            'label' => 'Exchange Date',
		            'rules' => 'trim|valid_date',
		            '_type'     => 'date',
		            '_required' => false
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
						'exchange_date' 		=> $this->input->post('filter_exchange_date') ?? NULL,
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

	// --------------------------------------------------------------------

    /**
     * Duplicate forex to specific date
     *
     * @return void
     */
    public function duplicate($id)
    {
        $this->load->model('forex_model');

        // Valid Record ?
        $id   	= (int)$id;
        $source_record = $this->forex_model->row($id);
        if(!$source_record)
        {
            $this->template->render_404();
        }

        $rules = $this->forex_model->duplicate_v_rules();

        if( $this->input->post() )
        {
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $exchange_date = $this->input->post('exchange_date');

                $data = [
                	'exchange_date' 	=> $exchange_date,
                	'exchange_rates' 	=> $source_record->exchange_rates
                ];

                $done = $this->forex_model->insert($data, TRUE);

                if(!$done)
                {

                    $status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                    // Clear Cache
                    $this->forex_model->clear_cache();

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
            if($status === 'success' )
            {
            	// Refresh the list page and close bootbox
				return $this->page('l', 0, [
						'message' => $message,
						'status'  => $status,
						'hideBootbox' => true,
						'updateSection' => true,
						'updateSectionData' => [
							'box' 		=> '#_iqb-data-list-box-forex',
							'method' 	=> 'html'
						],
					]);
            }
            else
            {
                $form_data = [
                    'form_elements'         => $rules,
                    'record'                => null,
                    'source_record'         => $source_record
                ];
                return $this->template->json([
                    'status'        => $status,
                    'message'       => $message,
                    'reloadForm'    => true,
                    'form'          => $this->load->view('setup/forex/_form_duplicate', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view('setup/forex/_form_duplicate',
            [
                'form_elements'         => $rules,
                'record'                => null,
                'source_record'         => $source_record
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    	/**
	     * Callback : Valid Exchange Date
	     *
	     * Case I:
	     * 		No Future Date
	     *
	     * Case II:
	     * 		No Duplicate
	     *
	     * @param date $exchange_date
	     * @return bool
	     */
	    public function _cb_valid_exchange_date($exchange_date)
	    {
	    	/**
	    	 * Case I: No future Date
	    	 */
	    	if( strtotime($exchange_date) > strtotime(date('Y-m-d')) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_exchange_date', '"Exchange Date" can not be future date.');
	            return FALSE;
	    	}

	    	/**
	    	 * Case II: Duplicate
	    	 */
	    	if( $this->forex_model->check_duplicate(['exchange_date'=> $exchange_date]) )
	    	{
	    		$this->form_validation->set_message('_cb_valid_exchange_date', '"Exchange Date" already exists.');
	            return FALSE;
	    	}
	        return TRUE;
	    }

	// --------------------------------------------------------------------

	/**
	 * Delete a Forex
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		return $this->template->json([
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the forex records.'
		]);

		// // Valid Record ?
		// $id = (int)$id;
		// $record = $this->forex_model->find($id);
		// if(!$record)
		// {
		// 	$this->template->render_404();
		// }

		// $data = [
		// 	'status' 	=> 'error',
		// 	'message' 	=> 'You cannot delete the default records.'
		// ];
		// /**
		//  * Safe to Delete?
		//  */
		// if( !safe_to_delete( 'Endorsement_template_model', $id ) )
		// {
		// 	return $this->template->json($data);
		// }

		// $done = $this->forex_model->delete($record->id);

		// if($done)
		// {
		// 	$data = [
		// 		'status' 	=> 'success',
		// 		'message' 	=> 'Successfully deleted!',
		// 		'removeRow' => true,
		// 		'rowId'		=> '#_data-row-'.$record->id
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
     * View Forex Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	$id = (int)$id;
		$record = $this->forex_model->row($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$data = [
			'record' 	=> $record
		];

		$html = $this->load->view('setup/forex/_details', $data, TRUE);
		$this->template->json([
			'html' 	=> $this->load->view('setup/forex/_details', $data, TRUE),
			'title' => 'Forex Details - ' .  $record->exchange_date
		]);
    }
}