<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bs_agro_categories Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Bs_agro_categories extends MY_Controller
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
        $this->data['site_title'] = 'Application Settings | Beema Samiti - Agriculture Categories';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'application_setup',
			'level_1' => 'beema_samiti',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('bs_agro_category_model');
		$this->load->model('bs_agro_breed_model');
		$this->load->model('portfolio_model');

		// URL Base
		$this->_url_base 		 = 'admin/' . $this->router->class;
		$this->_view_base 		 = 'setup/' . $this->router->class;

		$this->data['_url_base'] = $this->_url_base; // for view to access
		$this->data['_view_base'] 	= $this->_view_base;
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
		$portfolios = $this->portfolio_model->get_children(IQB_MASTER_PORTFOLIO_AGR_ID);
		$data = [
			'portfolios' 	=> $portfolios
		];

		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Beema Samiti - Agriculture Categories',
								'breadcrumbs' => ['Application Settings' => NULL, 'Beema Samiti - Agriculture Categories' => NULL]
						])
						->partial('content', $this->_view_base . '/_index', $data)
						->render($this->data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Categories - By Portfolio
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($portfolio_id)
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
		 * Existing Categories for this Portfolio and heading type
		 */
		$records 		= $this->bs_agro_category_model->by_portfolio($portfolio_id);
		$existing_ids = [];
		foreach($records as $r)
		{
			$existing_ids[] = $r->id;
		}

		$rules = $this->bs_agro_category_model->validation_rules;
		if( $this->input->post() )
		{
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				/**
				 * Tasks:
				 *
				 * 1. Add New Categories
				 * 2. Update Old Categories
				 * 3. Delete Removed Categories
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
					$category_id = $data['category_id'][$i];
					if( !$category_id )
					{
						$single_data = [
							'portfolio_id' => $portfolio_id,
							'code' 		=> $data['code'][$i],
							'name_en' 	=> $data['name_en'][$i],
							'name_np' 	=> $data['name_np'][$i]
						];
						$done = $this->bs_agro_category_model->insert($single_data, TRUE);
						$done ? $total_insert++ : '';
					}
					else if( in_array($category_id, $existing_ids))
					{
						/**
						 * Update Existing Records
						 */
						$done = $this->bs_agro_category_model->update($category_id, [
							'code' 	=> $data['code'][$i],
							'name_en' 	=> $data['name_en'][$i],
							'name_np' 	=> $data['name_np'][$i]
						], TRUE);

						$done ? $total_update++ : '';
						$to_update_ids[] = $category_id;

					}
				}


				/**
				 * Delete If any
				 */
				$to_del_ids = array_diff($existing_ids, $to_update_ids);
				foreach($to_del_ids as $category_id)
				{
					$done = $this->bs_agro_category_model->delete($category_id);
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
		$json_data['form'] = $this->load->view($this->_view_base . '/_form',
			[
				'form_elements' => $this->bs_agro_category_model->validation_rules,
				'portfolio' 	=> $portfolio,
				'records' 		=> $records
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit Breeds - By Category
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit_breed($category_id)
	{
		// Valid Record ?
		$category_id 	 = (int)$category_id;

		$category 		= $this->bs_agro_category_model->find($category_id);

		if(!$category )
		{
			$this->template->json([
				'status' => 'error',
				'message' => 'Portfolio Not Found!'
			], 404);
		}


		/**
		 * Existing Categories for this Portfolio and heading type
		 */
		$records 		= $this->bs_agro_breed_model->by_category($category_id);
		$existing_ids = [];
		foreach($records as $r)
		{
			$existing_ids[] = $r->id;
		}

		$rules = $this->bs_agro_breed_model->validation_rules;
		if( $this->input->post() )
		{
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				/**
				 * Tasks:
				 *
				 * 1. Add New Categories
				 * 2. Update Old Categories
				 * 3. Delete Removed Categories
				 */
				$total_insert = 0;
				$total_update = 0;
				$total_delete = 0;

				$to_del_ids 	= [];
				$to_update_ids  = [];

				$data = $this->input->post();
				for($i = 0; $i < count($data['code']); $i++ )
				{
					$breed_id = $data['breed_id'][$i];
					if( !$breed_id )
					{
						$single_data = [
							'category_id' 	=> $category_id,
							'code' 			=> $data['code'][$i],
							'name_en' 		=> $data['name_en'][$i],
							'name_np' 		=> $data['name_np'][$i]
						];

						$done = $this->bs_agro_breed_model->insert($single_data, TRUE);
						$done ? $total_insert++ : '';
					}
					else if( in_array($breed_id, $existing_ids))
					{
						/**
						 * Update Existing Records
						 */
						$done = $this->bs_agro_breed_model->update($breed_id, [
							'code' 	=> $data['code'][$i],
							'name_en' 	=> $data['name_en'][$i],
							'name_np' 	=> $data['name_np'][$i]
						], TRUE);

						$done ? $total_update++ : '';
						$to_update_ids[] = $breed_id;

					}
				}


				/**
				 * Delete If any
				 */
				$to_del_ids = array_diff($existing_ids, $to_update_ids);
				foreach($to_del_ids as $breed_id)
				{
					$done = $this->bs_agro_breed_model->delete($breed_id);
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
		$json_data['form'] = $this->load->view($this->_view_base . '/_form',
			[
				'form_elements' => $this->bs_agro_breed_model->validation_rules,
				'category' 		=> $category,
				'records' 		=> $records
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

    // --------------------------------------------------------------------

    /**
     * View Category Details by Portfolio
     *
     * @param integer $portfolio_id
     * @return void
     */
    public function details($portfolio_id)
    {
    	// Valid Record ?
		$portfolio_id 	 = (int)$portfolio_id;
		$portfolio 		 = $this->portfolio_model->find($portfolio_id);
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
			'records' 		=> $this->bs_agro_category_model->by_portfolio($portfolio_id),
		];

		$page_header = "Beema Samiti - Agriculture Categories - {$portfolio->name_en}";

		$this->data['site_title'] = 'Beema Samiti - Agriculture Categories | ' . $portfolio->name_en;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => $page_header,
								'breadcrumbs' => ['Application Settings' => NULL, 'BS Agriculture - Categories' => $this->_url_base, 'Details' => NULL]
						])
						->partial('content', $this->_view_base . '/_details', $data)
						->render($this->data);
    }

    // --------------------------------------------------------------------

	/**
     * View breeds list by category - Preview
     *
     * @param integer $category_id
     * @return void
     */
    public function breeds($category_id)
    {

		// Valid Record ?
		$category_id 	= (int)$category_id;
		$category 		= $this->bs_agro_category_model->find($category_id);

		if(!$category )
		{
			$this->template->json([
				'status' => 'error',
				'message' => 'Portfolio Not Found!'
			], 404);
		}

		/**
		 * Records
		 */
		$records 		= $this->bs_agro_breed_model->by_category($category_id);


		/**
		 * Breeds Data
		 */
		$data = [
			'category' 	=> $category,
			'records' 	=> $records
		];

		$this->template->json([
			'html' 	=> $this->load->view($this->_view_base . '/_breed_preview', $data, TRUE),
			'title' => "Breed List of {$category->name_np}({$category->name_en})"
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
        $this->bs_agro_category_model->clear_cache();
        redirect($this->_url_base);
    }

    /**
     * Flush Breed Cache Data
     *
     * @return void
     */
    public function flush_breed($portoflio_id)
    {
        $this->bs_agro_breed_model->clear_cache();
        redirect($this->_url_base . '/details/' . $portoflio_id);
    }



}
