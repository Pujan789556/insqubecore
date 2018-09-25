<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Addresses Lookup Controller
 *
 * @category 	Lookup
 */

// --------------------------------------------------------------------

class Addresses extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Addresses';

		// Load Model
		$this->load->model('address_model');

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
		$this->template->render_404();
	}

	// --------------------------------------------------------------------

	public function states($country_id)
	{
		$this->load->model('state_model');
		$dropdown = $this->state_model->dropdown((int)$country_id);
		$list = [];
		foreach($dropdown as $key=>$label)
		{
			$list[] = ['value' => $key, 'label' => $label];
		}

		echo json_encode($list);
		exit(0);
	}

	// --------------------------------------------------------------------

	public function address1($state_id)
	{
		$this->load->model('local_body_model');
		$dropdown = $this->local_body_model->dropdown_by_state((int)$state_id);

		$list = [];
		foreach($dropdown as $key=>$label)
		{
			$list[] = ['value' => $key, 'label' => $label];
		}

		echo json_encode($list);
		exit(0);
	}


}