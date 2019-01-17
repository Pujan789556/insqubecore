<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bsrs_headings Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Bsrs_headings extends MY_Controller
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
        $this->data['site_title'] = 'Application Settings | Beema Samiti Report Setup - Headings';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'application_setup',
			'level_1' => 'beema_samiti',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('bsrs_heading_model');
		$this->load->model('bsrs_heading_type_model');
		$this->load->model('portfolio_model');

		// URL Base
		$this->_url_base 		 = 'admin/' . $this->router->class;
		$this->data['_url_base'] = $this->_url_base; // for view to access
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

		$portfolios = $this->portfolio_model->get_children();
		$sectioned_portfolio = [];
		foreach($portfolios as $single)
		{
			/**
			 * NOTE: Escape Agriculture Portfolios
			 *
			 * This is because Agriculture has different reporting format
			 */
			if( $single->parent_id != IQB_MASTER_PORTFOLIO_AGR_ID )
			{
				$sectioned_portfolio[$single->parent_name_en][] = $single;
			}
		}

		$data = [
			'portfolios' 	=> $sectioned_portfolio,
			'heading_types' => $this->bsrs_heading_type_model->dropdown()
		];

		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Beema Samiti Report Setup - Headings',
								'breadcrumbs' => ['Application Settings' => NULL, 'Beema Samiti Report Setup - Headings' => NULL]
						])
						->partial('content', 'setup/bsrs_headings/_index', $data)
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Heading - By Portfolio, Heading Type
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($portfolio_id, $heading_type_id)
	{
		// Valid Record ?
		$portfolio_id 	 = (int)$portfolio_id;
		$heading_type_id = (int)$heading_type_id;

		$portfolio 		= $this->portfolio_model->find($portfolio_id);
		$heading_type 	= $this->bsrs_heading_type_model->find($heading_type_id);

		if(!$portfolio || !$heading_type)
		{
			$this->template->json([
				'status' => 'error',
				'message' => 'Portfolio and/or Heading Type Not Found!'
			], 404);
		}

		/**
		 * NOTE: Agriculture Portfolio? Do nothing
		 */
		if($portfolio->parent_id == IQB_MASTER_PORTFOLIO_AGR_ID)
		{
			$this->template->json([
				'status' => 'error',
				'message' => 'Agriculture Portfolios do not required this setup!'
			], 403);
		}


		/**
		 * Existing Headings for this Portfolio and heading type
		 */
		$records 		= $this->bsrs_heading_model->by_portfolio_heading_type($portfolio_id, $heading_type_id);
		$old_heading_ids = [];
		foreach($records as $r)
		{
			$old_heading_ids[] = $r->id;
		}

		$rules = $this->bsrs_heading_model->validation_rules;
		if( $this->input->post() )
		{
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				/**
				 * Tasks:
				 *
				 * 1. Add New Headings
				 * 2. Update Old Headings
				 * 3. Delete Removed Headings
				 */
				$total_insert = 0;
				$total_update = 0;
				$total_delete = 0;

				$to_del_ids 	= [];
				$to_update_ids  = [];

				$data = $this->input->post();
				$batch_data = [];
				for($i = 0; $i < count($data['code']); $i++ )
				{
					$heading_id = $data['heading_id'][$i];
					if( !$heading_id )
					{
						$batch_data[] = [
							'portfolio_id' => $portfolio_id,
							'heading_type_id' => $heading_type_id,
							'code' 	=> $data['code'][$i],
							'name' 	=> $data['name'][$i]
						];
					}
					else if( in_array($heading_id, $old_heading_ids))
					{
						/**
						 * Update Existing Records
						 */
						$done = $this->bsrs_heading_model->update($heading_id, [
							'code' 	=> $data['code'][$i],
							'name' 	=> $data['name'][$i]
						], TRUE);

						$done ? $total_update++ : '';
						$to_update_ids[] = $heading_id;

					}
				}

				/**
				 * Batch Insert New
				 */
				if( $batch_data )
				{
					$done = $this->bsrs_heading_model->insert_batch($batch_data, TRUE);

					$done ? $total_insert = count($batch_data) : '';
				}


				/**
				 * Delete If any
				 */
				$to_del_ids = array_diff($old_heading_ids, $to_update_ids);
				foreach($to_del_ids as $heading_id)
				{
					$done = $this->bsrs_heading_model->delete($heading_id);
					$done ? $total_delete++ : '';
				}




				$success = $total_insert || $total_update || $total_delete;

				if($success)
				{
					$return_data = [
						'status' => 'success',
						'message' => "Success. New Insert({$total_insert}), Update({$total_update}), Delete({$total_delete})",
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

		// Form Submitted? Save the data
		// $json_data = $this->_save('edit', $record);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/bsrs_headings/_form',
			[
				'form_elements' => $this->bsrs_heading_model->validation_rules,
				'portfolio' 	=> $portfolio,
				'heading_type' 	=> $heading_type,
				'records' 		=> $records
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}


	// --------------------------------------------------------------------

	/**
     * Check Duplicate Heading Code Callback
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function check_duplicate_code($code)
    {
    	$codes = $this->input->post('code');
    	$total_count = count($codes);
    	$unique_count = count ( array_unique($codes));

    	if( $total_count !== $unique_count )
    	{
    		$this->form_validation->set_message('check_duplicate_code', 'Duplicate Heading Code.');
            return FALSE;
    	}
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * View Invoice Details
     *
     * @param integer $portfolio_id
     * @return void
     */
    public function details($portfolio_id)
    {
    	// Valid Record ?
		$portfolio_id 	 = (int)$portfolio_id;

		$portfolio 		= $this->portfolio_model->find($portfolio_id);

		if(!$portfolio )
		{
			$this->template->json([
				'status' => 'error',
				'message' => 'Portfolio Not Found!'
			], 404);
		}


		/**
		 * Portfolio and its headings
		 */
		$data = [
			'portfolio' 	=> $portfolio,
			'headings' 		=> $this->bsrs_heading_model->by_portfolio($portfolio_id),
		];

		$page_header = "Beema Samiti Report Setup - Headings - {$portfolio->name_en}";

		$this->data['site_title'] = 'Beema Samiti Report Setup - Headings | ' . $portfolio->name_en;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => $page_header,
								'breadcrumbs' => ['Application Settings' => NULL, 'BS Report Setup - Headings' => $this->_url_base, 'Details' => NULL]
						])
						->partial('content', 'setup/bsrs_headings/_details', $data)
						->render($this->data);
    }

    // --------------------------------------------------------------------


    /**
     * Flush Cache Data
     *
     * @return void
     */
    public function flush()
    {
        $this->bsrs_heading_type_model->clear_cache();
        redirect($this->_url_base);
    }

}