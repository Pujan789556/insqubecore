<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');
	}

	function index()
	{
		$this->data['site_title'] = 'Welcome';

		$this->load->model('policy_model');
		// $this->load->helper('policy');
		$policies 	= $this->policy_model->rows([]);

		$data = ['policies' => $policies];
		$this->template
				->partial('content', 'dashboard/_index', $data)
				->partial('dynamic_js', 'dashboard/_js')
				->render($this->data);
	}

}