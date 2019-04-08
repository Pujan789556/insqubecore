<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Property Tariff Controller
 *
 * This controller falls under "Application Settings" category.
 *
 * @category 	Application Settings
 * @sub-category Account
 */

// --------------------------------------------------------------------

class Tariff_property extends MY_Controller
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
        $this->data['site_title'] = 'Application Settings | Property Tariff';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'application_setup',
			'level_1' => 'portfolio',
			'level_2' => 'tariff',
			'level_3' => $this->router->class
		]);


		// Load Model
		$this->load->model('tariff_property_model');

		// URL Base
		$this->_url_base 		 = 'admin/' . $this->router->class;
		$this->data['_url_base'] = $this->_url_base; // for view to access
	}

	// --------------------------------------------------------------------

	/**
	 * Default Method
	 *
	 * List Fiscal-year wise
	 *
	 * @return type
	 */
	function index()
	{
		/**
		 * Normal Form Render
		 */
		// this will generate cache name: mc_master_departments_all
		$records = $this->tariff_property_model->get_index_rows();

		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Manage Property Tariff',
								'breadcrumbs' => ['Application Settings' => NULL, 'Property Tariff' => NULL]
						])
						->partial('content', 'setup/tariff/property/_index', compact('records'))
						->render($this->data);
	}

	// --------------------------------------------------------------------

    /**
     * List all tariff belonging to a single fiscal year
     *
     * @param integer $fiscal_year_id
     * @return void
     */
    public function details($fiscal_year_id)
    {

        $fiscal_year_id = (int)$fiscal_year_id;
        $records   = $this->tariff_property_model->get_list_by_fiscal_year_rows($fiscal_year_id);
        if(!$records)
        {
            $this->template->render_404();
        }

        $single = $records[0];
        $fiscal_year_text = $single->fy_code_np . "({$single->fy_code_en})";

        // Site Meta
        $this->data['site_title'] = 'Application Settings | Property Tariff - FY ' . $fiscal_year_text;



        $this->template->partial(
                            'content_header',
                            'templates/_common/_content_header',
                            [
                                'content_header' => 'Property Tariff - FY - ' . $fiscal_year_text,
                                'breadcrumbs' => ['Application Settings' => NULL, 'Tariff' => NULL, 'Property' => $this->_url_base, 'Details' => NULL]
                        ])
                        ->partial('content', 'setup/tariff/property/_index_by_fiscal_year', compact('records'))
                        ->render($this->data);
    }

    // --------------------------------------------------------------------


    /**
     * Add Risk Categories - For This Fiscal Year
     *
     * @return void
     */
    public function add_fy()
    {
        if( $this->input->post() )
        {
        	$rules = $this->tariff_property_model->v_rules_add_fy(TRUE);
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $data = $this->input->post();

                $fiscal_yr_id = $this->input->post('fiscal_yr_id');
                $codes = $data['code'];
                $count_categories = count($codes);
                $batch_data = [];
                for($i=0; $i< $count_categories; $i++)
                {
                	$batch_data[] = [
                		'fiscal_yr_id' 	=> $fiscal_yr_id,
                		'code' 			=> $data['code'][$i],
                		'name_en' 		=> $data['name_en'][$i],
                		'name_np' 		=> $data['name_np'][$i]
                	];
                }

                $batch_data = array_filter($batch_data);
                $done = $this->tariff_property_model->insert_batch($batch_data, TRUE);

                if(!$done)
                {
                    $this->template->json([
						'status' => 'error',
						'message' => 'Could not update.'
					]);
                }
                else
                {
                    // Clear Cache
                    $this->tariff_property_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                    $ajax_data = [
	                    'message' => $message,
	                    'status'  => $status,
	                    'updateSection' => true,
	                    'hideBootbox' => true
	                ];

	                $records    = $this->tariff_property_model->get_index_rows();
	                $html       = $this->load->view('setup/tariff/property/_list', ['records' => $records], TRUE);
	                $ajax_data['updateSectionData'] = [
	                    'box'       => '#iqb-data-list',
	                    'method'    => 'html',
	                    'html'      => $html
	                ];
	                return $this->template->json($ajax_data);
                }
            }
            else
            {
                $this->template->json([
					'status' => 'error',
					'title' => 'Validation Error!',
					'message' => validation_errors()
				]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view('setup/tariff/property/_form_add',
            [
                'form_elements'         => $this->tariff_property_model->v_rules_add_fy(),
                'record'                => null
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------


    /**
     * Add Default Tarrif Data
     *
     * @return void
     */
    public function edit_fy($fiscal_yr_id)
    {
    	$fiscal_yr_id = (int)$fiscal_yr_id;
        if( $this->input->post() )
        {
        	$rules = $this->tariff_property_model->v_rules_add_fy(TRUE);

        	// Fiscal Year validation is not required
        	array_shift($rules);
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $data = $this->input->post();
                $codes 				= $data['code'];
                $count_categories 	= count($codes);
                $batch_data 		= [];

                // OLD Records
                $old_records = $this->tariff_property_model->get_list_by_fiscal_year_rows($fiscal_yr_id);
                $old_ids = [];
                foreach ($old_records as $r) {
                	$old_ids[] = $r->id;
                }
                sort($old_ids);

            	// Current Records
                $current_ids = [];
                for($i=0; $i< $count_categories; $i++)
                {
                	if($data['ids'][$i])
                	{
                		$current_ids[] = $data['ids'][$i];
                	}
                }
                sort($current_ids);

                // To Dell IDS
                $to_del_ids = array_diff($old_ids, $current_ids);
                if($to_del_ids)
                {
                	$status = $this->tariff_property_model->delete_many($to_del_ids);
                	if(!$status)
                	{
                		$this->template->json([
							'status' => 'error',
							'title' 	=> 'Foreign key constraint',
							'message' => 'Some of removed record could not be delted.'
						]);
                	}
                }

                // Let's Update and Batch Insert
                $flag_updated = TRUE;
                for($i=0; $i< $count_categories; $i++)
                {
                	$id = (int)$data['ids'][$i];
                	if($id)
                	{
                		$update_data = [
	                		'code' 			=> $data['code'][$i],
	                		'name_en' 		=> $data['name_en'][$i],
	                		'name_np' 		=> $data['name_np'][$i]
	                	];
	                	$flag_updated = $this->tariff_property_model->update($id, $update_data, TRUE);
	                	if(!$flag_updated) break;
                	}
                	else
                	{
                		$batch_data[] = [
	                		'fiscal_yr_id' 	=> $fiscal_yr_id,
	                		'code' 			=> $data['code'][$i],
	                		'name_en' 		=> $data['name_en'][$i],
	                		'name_np' 		=> $data['name_np'][$i]
	                	];
                	}
                }

                if(!$flag_updated)
                {
                	$this->template->json([
						'status' => 'error',
						'title'  => 'Update Error',
						'message' => 'Some DB error occured while updated old records.'
					]);
                }

                $batch_data = array_filter($batch_data);
                $flag_insert = TRUE;
                if( $flag_updated && count($batch_data) > 0 )
                {
                	$flag_insert = $this->tariff_property_model->insert_batch($batch_data, TRUE);
                }


                if(!$flag_insert)
                {
                    $this->template->json([
						'status' => 'error',
						'title'  => 'Insert Error',
						'message' => 'Could not batch insert new records.'
					]);
                }
                else
                {
                    // Clear Cache
                    $this->tariff_property_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                    $ajax_data = [
	                    'message' => $message,
	                    'status'  => $status,
	                    'updateSection' => true,
	                    'hideBootbox' => true
	                ];

	                $records    = $this->tariff_property_model->get_index_rows();
	                $html       = $this->load->view('setup/tariff/property/_list', ['records' => $records], TRUE);
	                $ajax_data['updateSectionData'] = [
	                    'box'       => '#iqb-data-list',
	                    'method'    => 'html',
	                    'html'      => $html
	                ];
	                return $this->template->json($ajax_data);
                }
            }
            else
            {
                $this->template->json([
					'status' => 'error',
					'title' => 'Validation Error!',
					'message' => validation_errors()
				]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view('setup/tariff/property/_form_add',
            [
                'form_elements'         => $this->tariff_property_model->v_rules_add_fy(),
                'record'                => (object)['fiscal_yr_id' => $fiscal_yr_id],
                'risk_categories' 		=> $this->tariff_property_model->get_list_by_fiscal_year_rows($fiscal_yr_id)
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Duplicate  form Old Fiscal Year
     *
     * @return void
     */
    public function duplicate($source_fiscal_year_id)
    {
        // Valid Record ?
        $source_fiscal_year_id   = (int)$source_fiscal_year_id;
        $source_record             = $this->tariff_property_model->get_fiscal_year_row($source_fiscal_year_id);

        if(!$source_record)
        {
            $this->template->render_404();
        }

        $rules = $this->tariff_property_model->v_rules_duplicate_fy();
        if( $this->input->post() )
        {
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $data = $this->input->post();

                $batch_data                 = [];
                $source_tarrif              = $this->tariff_property_model->get_list_by_fiscal_year($source_fiscal_year_id);
                $destination_fiscal_year_id = $this->input->post('fiscal_yr_id');

                foreach($source_tarrif as $src)
                {
                    $source_record =(array)$src;

                    // Set Fiscal Year
                    $source_record['fiscal_yr_id'] = $destination_fiscal_year_id;

                    // Remoe Unnecessary Fields
                    unset($source_record['id']);
                    unset($source_record['created_at']);
                    unset($source_record['created_by']);
                    unset($source_record['updated_at']);
                    unset($source_record['updated_by']);

                    $batch_data[] = $source_record;
                }

                $batch_data = array_filter($batch_data);
                $done = $this->tariff_property_model->insert_batch($batch_data, TRUE);

                if(!$done)
                {
                    $this->template->json([
						'status' => 'error',
						'title'  => 'Insert Error',
						'message' => 'Could not batch insert new records.'
					]);
                }
                else
                {
                    // Clear Cache
                    $this->tariff_property_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                    $ajax_data = [
	                    'message' => $message,
	                    'status'  => $status,
	                    'updateSection' => true,
	                    'hideBootbox' => true
	                ];

	                $records    = $this->tariff_property_model->get_index_rows();
	                $html       = $this->load->view('setup/tariff/property/_list', ['records' => $records], TRUE);
	                $ajax_data['updateSectionData'] = [
	                    'box'       => '#iqb-data-list',
	                    'method'    => 'html',
	                    'html'      => $html
	                ];
	                return $this->template->json($ajax_data);
                }
            }
            else
            {
                $this->template->json([
					'status' => 'error',
					'title' => 'Validation Error!',
					'message' => validation_errors()
				]);
            }


        }

        // Let's render the form
        $json_data['form'] = $this->load->view('setup/tariff/property/_form_duplicate',
            [
                'form_elements'         => $rules,
                'record'                => null,
                'source_record'         => $source_record
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

	// --------------------------------------------------------------------

	/**
	 * Edit a Role
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->tariff_property_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$rules = $this->tariff_property_model->validation_rules;
		$json_data = $this->_save('edit', $record, $rules);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/tariff/property/_form',
			[
				'form_elements' => $rules,
				'record' 		=> $record
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Risks
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function risks($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->tariff_property_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Form Submitted? Save the data
		$rules = $this->tariff_property_model->v_rules_risks();
		$json_data = $this->_save('risks', $record, $rules);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/tariff/property/_form_risks',
			[
				'form_elements' => $rules,
				'record' 		=> $record,
				'risks' 		=> json_decode($record->risks ?? NULL)
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Tariff
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function tariff($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->tariff_property_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$this->load->model('portfolio_model');

		// Form Submitted? Save the data
		$rules = $this->tariff_property_model->v_rules_tariff();
		$json_data = $this->_save('tariff', $record, $rules);


		// No form Submitted?
		$json_data['form'] = $this->load->view('setup/tariff/property/_form_tariff',
			[
				'form_elements' => $rules,
				'record' 		=> $record,
				'tariff' 		=> json_decode($record->tariff ?? NULL),
				'portfolios' 	=> $this->portfolio_model->dropdown_children(IQB_MASTER_PORTFOLIO_PROPERTY_ID)
			], TRUE);

		// Return HTML
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save($action, $record = NULL, $rules)
	{
		// Valid action?
		if( !in_array($action, array('add', 'edit', 'risks', 'tariff')))
		{
			$this->template->json([
				'status' => 'error',
				'message' => 'Invalid action!'
			]);
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;
			$this->form_validation->set_rules($rules);
			if( $this->form_validation->run() === TRUE )
			{
				$data = $this->input->post();

				$done = FALSE;

				switch ($action)
				{
					case 'add':
						# code...
						break;

					case 'edit':
						$done = $this->tariff_property_model->update($record->id, $data, TRUE);
						break;

					case 'risks':
						$done = $this->_save_risks($record->id, $data);
						break;

					case 'tariff':
						$done = $this->_save_tariff($record->id, $data);
						break;

					default:
						# code...
						break;
				}


				if(!$done)
				{
					$status = 'error';
					$message = 'Could not update.';
				}
				else
				{
					$status = 'success';
					$message = 'Successfully Updated.';
				}
			}
			else
			{
				$this->template->json([
					'status' => 'error',
					'title' => 'Validation Error!',
					'message' => validation_errors()
				]);
			}

			// Success HTML
			$success_html = '';
			if($status === 'success' )
			{
				if($action === 'add')
				{
					$records = $this->tariff_property_model->get_all();
					$success_html = $this->load->view('setup/tariff/property/_list', ['records' => $records], TRUE);
				}
				else
				{
					// Get Updated Record
					$record = $this->tariff_property_model->get($record->id);
					$success_html = $this->load->view('setup/tariff/property/_single_row_by_fiscal_year', ['record' => $record], TRUE);
				}
			}

			$return_data = [
				'status' 		=> $status,
				'message' 		=> $message,
				'reloadForm' 	=> $status === 'error',
				'hideBootbox' 	=> $status === 'success',
				'updateSection' => $status === 'success',
				'updateSectionData'	=> $status === 'success'
										? 	[
												'box' 	=> $action === 'add'
															? '#iqb-data-list'
															: '#_data-row-' . $record->id,
												'html' 	=> $success_html,

												//
												// How to Work with success html?
												// Jquery Method 	html|replaceWith|append|prepend etc.
												//
												'method' 	=> $action === 'add' ? 'html' : 'replaceWith'
											]
										: NULL

			];
		}

		return $return_data;
	}

	// --------------------------------------------------------------------

	private function _save_risks($id, $post_data)
	{
		// Format Risks
		$rules = $this->tariff_property_model->v_rules_risks();
		$count = count($post_data['risks']['code']);

		$risk_data = [];
		for($i = 0; $i < $count; $i++)
		{
			$risk = [];
			foreach($rules as $single)
			{
				$key = $single['_key'];
				$risk[$key] = $post_data['risks'][$key][$i];
			}
			$risk_data[] = $risk;
		}

		$data = ['risks' => json_encode($risk_data)];

		return $this->tariff_property_model->update($id, $data, TRUE);
	}

	// --------------------------------------------------------------------

	private function _save_tariff($id, $post_data)
	{
		// Format Risks
		$rules = $this->tariff_property_model->v_rules_tariff();
		$count = count($post_data['tariff']['portfolio_id']);

		$risk_data = [];
		for($i = 0; $i < $count; $i++)
		{
			$risk = [];
			foreach($rules as $single)
			{
				$key = $single['_key'];
				$risk[$key] = $post_data['tariff'][$key][$i];
			}
			$risk_data[] = $risk;
		}

		$data = ['tariff' => json_encode($risk_data)];

		return $this->tariff_property_model->update($id, $data, TRUE);
	}

	// --------------------------------------------------------------------

	/**
     * Callback - Duplicate Risk Code
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function _cb_risk_duplicate($code){

    	$risks = $this->input->post('risks');
    	$codes = $risks['code'];
    	$count = count($risks['code']);
    	$unique_codes = [];

    	for($i=0; $i<$count; $i++)
    	{
    		$unique_codes[] = $codes[$i];
    	}
    	$unique_codes = array_unique($unique_codes);
    	$new_count = count($unique_codes);
        if( $count != $new_count )
        {
            $this->form_validation->set_message('_cb_risk_duplicate', 'The %s must be unique.');
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

	/**
     * Check Duplicate Callback - Fiscal Year
     *
     * @param string $fiscal_yr_id
     * @param integer|null $id
     * @return bool
     */
    public function check_duplicate_fiscal_year($fiscal_yr_id, $ids = NULL)
    {
    	$fiscal_yr_id 	= (int)$fiscal_yr_id;
    	$ids 			= $ids ? (int)$ids : $this->input->post('ids');
    	$ids 			= is_array($ids) ? array_values($ids) : [];
    	$ids  			= array_filter($ids);
    	// echo '<pre>'; print_r($ids); echo '</pre>'; exit;
	   	if( $this->tariff_property_model->check_duplicate_fiscal_year($fiscal_yr_id, $ids) )
        {
            $this->form_validation->set_message('check_duplicate_fiscal_year', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------

	/**
     * Check Duplicate Callback
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function check_duplicate_codes($code)
    {
    	$codes 		 = $this->input->post('code');
    	foreach ($codes as &$single)
    	{
    		$single = strtoupper($single);
    	}
    	$count_total = count($codes);

    	// remove duplicate
    	$unique 	 = array_unique($codes);

        if( $count_total != count($unique) )
        {
            $this->form_validation->set_message('check_duplicate_codes', 'The %s can not be duplicate for given fiscal year.');
            return FALSE;
        }
        return TRUE;
    }

    // --------------------------------------------------------------------


    /**
     * Flush Cache Data
     *
     * @return void
     */
    public function flush()
    {
        $this->tariff_property_model->clear_cache();
        redirect($this->_url_base);
    }

    // --------------------------------------------------------------------

}