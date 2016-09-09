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

		// echo '<pre>';print_r($this->data);exit;
		// $sess_data = $this->session->userdata();

		$this->data['site_title'] = 'Welcome';

		$this->template->render($this->data);
	}

}