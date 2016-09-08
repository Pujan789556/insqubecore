<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
	
	function __construct()
	{
		parent::__construct();
		
		$this->load->library('Form_validation');		

		// Check logged in?
		if ( !$this->dx_auth->is_logged_in())
		{
			$this->dx_auth->deny_access('login');
		}
	
		// Set Template for this controller
        $this->template->set_template('dashboard');
	}
	
	function index()
	{

		$sess_data = $this->session->userdata();

		$this->template->render([
				        	'site_title' => 'Welcome',
				        	'sess_data' => $sess_data
				    	]);
	}

}