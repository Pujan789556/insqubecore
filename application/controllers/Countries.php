<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * District Controller
 * 
 * This controller falls under "Master Setup" category.
 *  
 * @category 	Master Setup
 */

// --------------------------------------------------------------------

class Countries extends MY_Controller
{
	/**
	 * Validation Rules
	 * 
	 * @var array
	 */
	private $form_elements = [
		[
			'name' => 'name',
	        'label' => 'Country Name',
	        '_id' 	=> 'name',
	        '_type' => 'text',
	        '_required' => true
		],
        [
            'name' => 'alpha2',
            'label' => 'Country Code (alpha 2)',
            '_id' 	=> 'alpha2',
	        '_type' => 'text',
	        '_required' => true
        ],
        [
            'name' => 'alpha3',
            'label' => 'Country Code (alpha 3)',
            '_id' 	=> 'alpha3',
	        '_type' => 'text',
	        '_required' => true
        ],
        [
            'name' => 'dial_code',
            'label' => 'Dialing Code',
            '_id' 	=> 'dial_code',
	        '_type' => 'text',
	        '_required' => true
        ],
        [
            'name' => 'currency_code',
            'label' => 'Currency Code',
            '_id' 	=> 'dial_code',
	        '_type' => 'text',
	        '_required' => false
        ],
        [
            'name' => 'currency_name',
            'label' => 'Currency Name',
            '_id' 	=> 'currency_name',
	        '_type' => 'text',
	        '_required' => false
        ]
	];

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
        $this->data['site_title'] = 'Master Setup | Countries';

        // Setup Navigation        
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'general',
			'level_2' => $this->router->fetch_class()
		]);

		// Load Model
		$this->load->model('country_model');
    
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
		// this will generate cache name: mc_master_countries_all
		$records = $this->country_model->set_cache('all')->get_all();
	
		$this->template->partial(
							'content_header', 
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Countries',
								'breadcrumbs' => ['Master Setup' => NULL, 'Countries' => NULL]
						])
						->partial('content', 'setup/countries/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->country_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Form Submitted?
		 */
		if( $this->input->post() )
		{
			// Now Update Data
        	$done = $this->country_model->from_form()->update(NULL, $id);
        	$view = '';

        	if(!$done)
			{
				$status = 'error';
				$message = 'Validation Error.';				
			}
			else
			{
				$status = 'success';
				$message = 'Successfully Updated.';
				$record = $this->country_model->get($id);
			}	
			

			$row = $status === 'success' 
						? $this->load->view('setup/countries/_single_row', compact('record'), TRUE)
						: '';
			
			$this->template->json([
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> $status === 'error',
				'hideBootbox' 	=> $status === 'success',
				'updateSection' => $status === 'success',
				'updateSectionData'	=> $status === 'success' 
										? 	[
												'box' => '#_data-row-' . $record->id,
												'html' 		=> $row,
												// Jquery Method 	html|replaceWith|append|prepend etc.
												'method' 	=> 'replaceWith'
											]
										: NULL,
				'form' 	  		=> $status === 'error' 
									? 	$this->load->view('setup/countries/_form', 
											[
												'form_elements' => $this->form_elements,
												'record' 		=> $record
											], TRUE)
									: 	null

			]);
		}


		$form = $this->load->view('setup/countries/_form', 
			[
				'form_elements' => $this->form_elements,
				'record' 		=> $record
			], TRUE);

		// Return HTML 
		$this->template->json(compact('form'));
	}
}