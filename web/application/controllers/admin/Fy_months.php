<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Fy_months Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Fy_months extends MY_Controller
{
	/**
	 * Controller URL
	 */
	private $_url_base;

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
        $this->data['site_title'] = 'Application Settings | Fiscal Year Months';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'application_setup',
			'level_1' => 'general',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('fy_month_model');

		// URL Base
		$this->_url_base 		 = 'admin/' . $this->router->class;
		$this->data['_url_base'] = $this->_url_base; // for view to access
	}

	// --------------------------------------------------------------------

	/**
	 * Default Method
	 *
	 * Render the list
	 *
	 * @return type
	 */
	function index()
	{
		/**
		 * Normal Form Render
		 */
		$data = [
			'fy_records' 	=> $this->fiscal_year_model->get_all()
		];

		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Fiscal Year Months',
								'breadcrumbs' => ['Application Settings' => NULL, 'Fiscal Year Months' => NULL]
						])
						->partial('content', 'setup/fy_months/_index', $data)
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Fiscal Year Months - By Fiscal Year
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($fiscal_yr_id)
	{
		// Valid Record ?
		$fiscal_yr_id 	= (int)$fiscal_yr_id;
		$fy_record 		= $this->fiscal_year_model->get($fiscal_yr_id);

		if(!$fy_record )
		{
			$this->template->json([
				'status' => 'error',
				'message' => 'Fiscal Year Not Found!'
			], 404);
		}


		/**
		 * Existing Categories for this Portfolio and heading type
		 */
		$records 		= $this->fy_month_model->by_fiscal_year($fiscal_yr_id);
		$months = $this->month_model->dropdown_fy();

		/**
		 * Format Records according to month id
		 */
		$records_formattted = [];
		foreach($records as $single)
		{
			$records_formattted[$single->month_id] = $single;
		}

		$rules = $this->fy_month_model->validation_rules;
		if( $this->input->post() )
		{
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();
				$batch_data = [];
				for($i = 0; $i < count($data['month_id']); $i++ )
				{
					$batch_data[] = [
						'fiscal_yr_id' 	=> $fiscal_yr_id,
						'month_id' 		=> $data['month_id'][$i],
						'starts_at' 	=> $data['starts_at'][$i],
						'ends_at' 		=> $data['ends_at'][$i]
					];
				}
				$done = $this->fy_month_model->save($batch_data);

				if($done)
				{
					$return_data = [
						'status' => 'success',
						'message' => "Successfully updated.",
						'hideBootbox' => true
					];
				}
				else
				{
					$return_data = [
						'status' => 'error',
						'message' => "Could not update data.",
						'hideBootbox' => true
					];
				}

				$this->template->json($return_data);
			}
			else
			{
				$this->template->json([
					'status' 	=> 'error',
					'title' 	=> 'Validation Error!',
					'message' 	=> validation_errors()
				], 422);
			}
		}

		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/fy_months/_form',
			[
				'form_elements' => $this->fy_month_model->validation_rules,
				'fy_record' 	=> $fy_record,
				'records' 		=> $records_formattted,
				'months' 		=> $months
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Validaton Callback - Valid Dates?
	 */
	function _cb_valid_dates($ends_at)
	{
		$ends_at 		= $this->input->post('ends_at');
    	$starts_at 		= $this->input->post('starts_at');
    	$fiscal_yr_id 	= (int)$this->input->post('fiscal_yr_id');

    	/**
    	 * Start < End
    	 */
    	for($i=0; $i < 11; $i++ )
    	{
    		$st = $starts_at[$i];
    		$en = $ends_at[$i];

    		if(strtotime($st) >= strtotime($en))
	    	{
	    		$this->form_validation->set_message('_cb_valid_dates', 'The Start Date must be less than End Date.');
	            return FALSE;
	    	}
    	}


    	/**
    	 * Fall Under Fiscal Year Rnage?
    	 */
    	$fy_record = $this->fiscal_year_model->get($fiscal_yr_id);

    	if(!$fy_record)
    	{
			$this->form_validation->set_message('_cb_valid_dates', 'No fiscal year record found.');
            return FALSE;
    	}

    	if(
			strtotime($starts_at[0]) != strtotime($fy_record->starts_at_en) // Shrawan Start
    			||
    		strtotime($ends_at[11]) != strtotime($fy_record->ends_at_en) // Ashar Ends
		){

    		$this->form_validation->set_message('_cb_valid_dates', 'The Start/End Dates must fall under selected Fiscal Year Range.');
            return FALSE;
    	}

        return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Validaton Callback - Valid Months?
	 */
	function _cb_valid_month($month)
	{
		$month_ids = $this->input->post('month_id');
		$total = count(array_unique($month_ids));
		if($total != 12 )
    	{
			$this->form_validation->set_message('_cb_valid_month', 'Month Reference Mismatch.');
            return FALSE;
    	}

    	$months = array_keys($this->month_model->dropdown());
    	foreach($month_ids as $month_id)
    	{
    		if(!in_array($month_id, $months))
    		{
    			$this->form_validation->set_message('_cb_valid_month', 'Invalid Month References.');
            	return FALSE;
    		}
    	}

    	return TRUE;
	}

    // --------------------------------------------------------------------

    /**
     * View Category Details by Portfolio
     *
     * @param integer $fiscal_yr_id
     * @return void
     */
    public function details($fiscal_yr_id)
    {
    	// Valid Record ?
		$fiscal_yr_id 	= (int)$fiscal_yr_id;
		$fy_record 		= $this->fiscal_year_model->get($fiscal_yr_id);

		if(!$fy_record )
		{
			$this->template->json([
				'status' => 'error',
				'message' => 'Fiscal Year Not Found!'
			], 404);
		}


		/**
		 * Existing Categories for this Portfolio and heading type
		 */
		$records 	= $this->fy_month_model->by_fiscal_year($fiscal_yr_id);
		$months 	= $this->month_model->dropdown_fy();


		/**
		 * Portfolio and its headings
		 */
		$data = [
			'records' 	=> $records,
			'months' 	=> $months
		];

		$page_header = "Fiscal Year Months - {$fy_record->code_np}";

		$this->template->json([
			'html' 	=> $this->load->view('setup/fy_months/_details', $data, TRUE),
			'title' => "Fiscal Year Months - {$fy_record->code_np}({$fy_record->code_en})"
		]);
    }

    // --------------------------------------------------------------------


    /**
     * Flush Cache Data
     *
     * @return void
     */
    public function flush()
    {
        $this->fy_month_model->clear_cache();
        redirect($this->_url_base);
    }

}
