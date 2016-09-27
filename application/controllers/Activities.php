<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Activities Controller
 * 
 * This controller is used to explore user activities.
 *  
 * @category 	Activity
 */

// --------------------------------------------------------------------

class Activities extends MY_Controller
{
	/**
	 * Validation Rules
	 * 
	 * @var array
	 */
	private $form_elements = [];

	// --------------------------------------------------------------------

	function __construct()
	{
		parent::__construct();
		
		// Only Admin Can access this controller
		if( !$this->dx_auth->is_admin() )
		{
			$this->dx_auth->deny_access();
		}

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Activities';

       // Load Model
		$this->load->model('activity_model');
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
		 * Paginated Record List
		 */
		// $records = $this->activity_model->set_cache('all')->get_all();
		// $records = $this->activity_model->all();
		// $total = count($records);

		// if($total = $this->settings->per_page+1)
		// {
		// 	$next_id = $records[$total-1]->id;
		// }

		// // echo $this->db->last_query();
		// // echo '<pre>'; print_r($records);exit;

		// $records = $records ? $records : [];
		// $this->template->partial(
		// 					'content_header', 
		// 					'templates/_common/_content_header',
		// 					[
		// 						'content_header' => 'Explore Activities',
		// 						'breadcrumbs' => ['Master Setup' => NULL, 'Activities' => NULL]
		// 				])
		// 				->partial('content', 'activities/_index', compact('records'))
		// 				->render($this->data);

		$this->page();
	}

	function page( $next_id = 0 )
	{
		$next_id = (int)$next_id;

		// Prepare Params
		$params = array();
		if($next_id)
		{
			$params = ['id <=' => $next_id];
		}

		$records = $this->activity_model->all($params);
		$records = $records ? $records : [];
		$total = count($records);
		// echo $total;exit;

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
			'next_id' => $next_id
		];

		if ( $this->input->is_ajax_request() ) 
		{
			$html = $this->load->view('activities/_rows', $data, TRUE);
			$this->template->json([
				'status' => 'success',
				'html'   => $html
			]);
		}

		$this->template->partial(
							'content_header', 
							'templates/_common/_content_header',
							[
								'content_header' => 'Explore Activities',
								'breadcrumbs' => ['Master Setup' => NULL, 'Activities' => NULL]
						])
						->partial('content', 'activities/_index', $data)
						->render($this->data);
	}

	
}