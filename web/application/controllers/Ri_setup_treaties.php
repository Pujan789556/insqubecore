<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RI Setup - Treaties Controller
 *
 * This controller falls under "Master Setup" category.
 *
 * @category 	Master Setup
 * @sub-category RI
 */

// --------------------------------------------------------------------

class Ri_setup_treaties extends MY_Controller
{
	function __construct()
	{
		parent::__construct();

		// Only Admin Can access this controller
		if( !$this->dx_auth->is_admin() )
		{
			$this->dx_auth->deny_access();
		}

		// Helper
		$this->load->helper('ri');

		// Form Validation
		$this->load->library('Form_validation');

		// Set Template for this controller
        $this->template->set_template('dashboard');

        // Basic Data
        $this->data['site_title'] = 'Master Setup | RI | Treaties';

        // Setup Navigation
		$this->active_nav_primary([
			'level_0' => 'master_setup',
			'level_1' => 'ri',
			'level_2' => $this->router->class
		]);

		// Load Model
		$this->load->model('ri_setup_treaty_model');



		// Data Path
        $this->_upload_path = INSQUBE_DATA_PATH . 'treaties/';
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
		$this->page();
	}

	// --------------------------------------------------------------------

	/**
	 * Paginate Data List
	 *
	 * @param integer $next_id
	 * @return void
	 */
	function page( $layout='f', $next_id = 0,  $ajax_extra = [], $do_filter = TRUE )
	{

		// If request is coming from refresh method, reset nextid
		$next_id = (int)$next_id;

		$params = array();
		if( $next_id )
		{
			$params = ['next_id' => $next_id];
		}

		/**
		 * Extract Filter Elements
		 */
		$filter_data = $this->_get_filter_data( $do_filter );
		if( $filter_data['status'] === 'success' )
		{
			$params = array_merge($params, $filter_data['data']);
		}

		$records = $this->ri_setup_treaty_model->rows($params);
		$records = $records ? $records : [];
		$total = count($records);

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

		// DOM Data
		$dom_data = [
			'DOM_DataListBoxId' 		=> '_iqb-data-list-box-ri-setup-treaty', 		// List box ID
			'DOM_FilterFormId'		=> '_iqb-filter-form-ri-setup-treaty' 			// Filter Form ID
		];

		$data = [
			'records' => $records,
			'next_id' => $next_id,
			'next_url' => $next_id ? site_url( 'ri_setup_treaties/page/r/' . $next_id ) : NULL
		] + $dom_data;

		/**
		 * Find View
		 */
		if($layout === 'f') // Full Layout
		{
			$view = 'setup/ri/treaties/_index';

			/**
			 * Filter Configurations
			 */
			$data = array_merge($data, [
				'filters' 		=> $this->_get_filter_elements(),
				'filter_url' 	=> site_url('ri_setup_treaties/page/l/' )
			]);
		}
		else if($layout === 'l')
		{
			$view = 'setup/ri/treaties/_list';
		}
		else
		{
			$view = 'setup/ri/treaties/_rows';
		}


		if ( $this->input->is_ajax_request() )
		{


			// $view = $refresh === FALSE ? 'setup/ri/treaties/_rows' : 'setup/ri/treaties/_list';
			$html = $this->load->view($view, $data, TRUE);
			$ajax_data = [
				'status' => 'success',
				'html'   => $html
			];

			if( !empty($ajax_extra))
			{
				$ajax_data = array_merge($ajax_data, $ajax_extra);
			}
			$this->template->json($ajax_data);
		}

		$this->template
						->set_layout('layout-advanced-filters')
						->partial(
							'content_header',
							'setup/ri/treaties/_index_header',
							['content_header' => 'Manage Treaties'] + $dom_data)
						->partial('content', 'setup/ri/treaties/_index', $data)
						->render($this->data);
	}

	// --------------------------------------------------------------------

		private function _get_filter_elements()
		{
			$filters = [
				[
	                'field' => 'filter_fiscal_yr_id',
	                'label' => 'Fiscal Year',
	                'rules' => 'trim|integer|max_length[3]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $this->fiscal_year_model->dropdown(),
	                '_required' => false
	            ],
	            [
	                'field' => 'filter_treaty_type_id',
	                'label' => 'Treaty Type',
	                'rules' => 'trim|integer|max_length[3]',
	                '_type'     => 'dropdown',
	                '_data'     => IQB_BLANK_SELECT + $this->ri_setup_treaty_type_model->dropdown(),
	                '_required' => false
	            ],
			];
			return $filters;
		}

		private function _get_filter_data( $do_filter=TRUE )
		{
			$data = ['status' => 'empty'];

			// Return Empty on do_filter = false (set 'false' by 'add' method)
			if( !$do_filter )
			{
				return $data;
			}
			if( $this->input->post() )
			{
				$rules = $this->_get_filter_elements();
				$this->form_validation->set_rules($rules);
				if( $this->form_validation->run() )
				{
					$data['data'] = [
						'fiscal_yr_id' 		=> $this->input->post('filter_fiscal_yr_id') ?? NULL,
						'treaty_type_id' 	=> $this->input->post('filter_treaty_type_id') ?? NULL
					];
					$data['status'] = 'success';
				}
				else
				{
					$data = [
						'status' 	=> 'error',
						'message' 	=> validation_errors()
					];

					$this->template->json($data);
				}
			}
			return $data;
		}

	// --------------------------------------------------------------------

	/**
	 * Refresh The Module
	 *
	 * Simply reload the first page
	 *
	 * @return type
	 */
	function refresh()
	{
		$this->page('l');
	}

	/**
	 * Filter the Data
	 *
	 * @return type
	 */
	function filter()
	{
		$this->page('l');
	}

	// --------------------------------------------------------------------

	/**
	 * Add a new Record
	 *
	 * @return void
	 */
	public function add()
	{
		$record = NULL;

		/**
		 * Prepare Form Data
		 */
		$form_data = [
			'form_elements' 	=> $this->ri_setup_treaty_model->get_validation_rules(['basic', 'brokers', 'portfolios']),
			'record' 			=> $record,

			// Broker Companies
			'brokers' 			=> $this->company_model->dropdown_brokers(),
			'treaty_borkers' 	=> [],

			// Portfolios
			'portfolios' 		=> $this->portfolio_model->dropdown_children(),
			'treaty_portfolios' => [],

			// // Reinsurer Companies
			// 'reinsurers' 			=> $this->company_model->dropdown_reinsurers(),
			// 'treaty_distribution' 	=> [],
		];

		// Form Submitted? Save the data
		return $this->_save('add', $record, $form_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Edit a Recrod
	 *
	 *
	 * @param integer $id
	 * @return void
	 */
	public function edit($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ri_setup_treaty_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Existing Brokers
		 */
		$treaty_borkers = $this->ri_setup_treaty_model->get_brokers_by_treaty_dropdown($id);

		/**
		 * Existing Portfolios
		 */
		$treaty_portfolios = $this->ri_setup_treaty_model->get_portfolios_by_treaty_dropdown($id);


		/**
		 * Prepare Form Data
		 */
		$form_data = [
			'form_elements' 	=> $this->ri_setup_treaty_model->get_validation_rules(['basic', 'brokers', 'portfolios']),
			'record' 			=> $record,

			// Brokers
			'brokers' 			=> $this->company_model->dropdown_brokers(),
			'treaty_borkers' 	=> array_keys($treaty_borkers),

			// Portfolios
			'portfolios' 		=> $this->portfolio_model->dropdown_children(),
			'treaty_portfolios' => array_keys($treaty_portfolios)
		];

		// Form Submitted? Save the data
		return $this->_save('edit', $record, $form_data);
	}


	// --------------------------------------------------------------------

	/**
	 * Save a Record
	 *
	 * @param string $action [add|edit]
	 * @param object|null $record Record Object or NULL
	 * @return array
	 */
	private function _save($action, $record = NULL, $form_data)
	{

		// Valid action?
		if( !in_array($action, array('add', 'edit')))
		{
			return [
				'status' => 'error',
				'message' => 'Invalid action!'
			];
		}

		/**
		 * Form Submitted?
		 */
		$return_data = [];

		if( $this->input->post() )
		{
			$done = FALSE;
			$file = $record->file ?? NULL;

			$rules = $this->ri_setup_treaty_model->get_validation_rules_formatted(['basic', 'brokers', 'portfolios']);
            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		/**
				 * Upload Document If any?
				 */
				$upload_result 	= $this->_upload_treaty_document($file);
				$status 		= $upload_result['status'];
				$message 		= $upload_result['message'];
				$files 			= $upload_result['files'];
				$file = $status === 'success' ? $files[0] : $file;

				if( $status === 'success' || $status === 'no_file_selected')
	            {
	            	$data = $this->input->post();
	            	$data['file'] = $file;

	            	// if no estimated premium income, null it.
	            	$data['estimated_premium_income'] = $data['estimated_premium_income'] ? $data['estimated_premium_income'] : NULL;

	        		// Insert or Update?
					if($action === 'add')
					{
						$done = $this->ri_setup_treaty_model->add($data);
					}
					else
					{
						// Now Update Data
						// Get old treaty portfolio
						$old_data['old_portfolios'] = $form_data['treaty_portfolios'];
						$done = $this->ri_setup_treaty_model->edit($record->id, $data, $old_data);
					}

		        	if(!$done)
					{
						// Simply return error message
						return $this->template->json([
							'status' 	=> 'error',
							'message' 	=> 'Could not update.'
						]);
					}
					else
					{
						$status = 'success';
						$message = 'Successfully Updated.';

						if($action === 'add')
						{
							// Refresh the list page and close bootbox
							return $this->page('l', 0, [
									'message' => $message,
									'status'  => $status,
									'hideBootbox' => true,
									'updateSection' => true,
									'updateSectionData' => [
										'box' 		=> '#_iqb-data-list-box-ri-setup-treaty',
										'method' 	=> 'html'
									]
								], FALSE);
						}
						else
						{
							// Get Updated Record
							$record = $this->ri_setup_treaty_model->row($record->id);
							$success_html = $this->load->view('setup/ri/treaties/_single_row', ['record' => $record], TRUE);
							$ajax_data = [
								'message' => $message,
								'status'  => $status,
								'updateSection' => true,
								'hideBootbox' => true
							];
							$ajax_data['updateSectionData'] = [
								'box' 		=> '#_data-row-' . $record->id,
								'method' 	=> 'replaceWith',
								'html'		=> $success_html
							];
							return $this->template->json($ajax_data);
						}
					}
	            }
        	}
        	else
        	{
        		// Simply return validation error
				return $this->template->json([
					'status' 	=> 'error',
					'message' 	=> validation_errors()
				]);
        	}
		}

		// Prepare HTML Form
		$json_data['form'] = $this->load->view('setup/ri/treaties/_form', $form_data, TRUE);

		// Return JSON
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Manage Tax & Commission
	 *
	 * @param integer $id Treaty ID
	 * @return void
	 */
	public function tnc($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ri_setup_treaty_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		if( $this->input->post() )
		{
			$done 	= FALSE;

            $this->form_validation->set_rules($this->ri_setup_treaty_model->get_tnc_validation_rules($record->treaty_type_id, true));
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();
        		$done = $this->ri_setup_treaty_model->save_treaty_tnc($record->id, $data);

        		if($done)
        		{
        			// Update the Portfolio Table
					$record = $this->ri_setup_treaty_model->get($id);
					$success_html = $this->load->view('setup/ri/treaties/snippets/_ri_tnc_data', ['record' => $record], TRUE);

					$ajax_data = [
						'message' => 'Successfully Updated',
						'status'  => 'success',
						'updateSection' => true,
						'hideBootbox' => true
					];
					$ajax_data['updateSectionData'] = [
						'box' 		=> '#ri-tnc-data',
						'method' 	=> 'html',
						'html'		=> $success_html
					];
					return $this->template->json($ajax_data);
        		}
        		else
        		{
        			// Simply return could not update message. Might be some logical error or db error.
	        		return $this->template->json([
	                    'status'        => 'error',
	                    'message'       => 'Could not update!'
	                ]);
        		}
        	}
        	else
        	{
    			// Simply Return Validation Error
        		return $this->template->json([
                    'status'        => 'error',
                    'message'       => validation_errors()
                ]);
        	}
		}

		/**
		 * Prepare Form Data
		 */
		$form_data = [
			'form_elements' 	=> $this->ri_setup_treaty_model->get_tnc_validation_rules($record->treaty_type_id),
			'record' 			=> $record
		];

		// Prepare HTML Form
		$json_data['form'] = $this->load->view('setup/ri/treaties/_form_tnc', $form_data, TRUE);


		// Return JSON
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Manage Commission Scales
	 *
	 * @param integer $id Treaty ID
	 * @return void
	 */
	public function commission_scales($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ri_setup_treaty_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		if( $this->input->post() )
		{
			$done 	= FALSE;
			$rules 	= $this->ri_setup_treaty_model->get_validation_rules_formatted(['commission_scale']);
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();
        		$done = $this->ri_setup_treaty_model->save_treaty_commission_scale($record->id, $data);

        		if($done)
        		{
        			// Update the Portfolio Table
					$record 		= $this->ri_setup_treaty_model->get($id);
					$success_html 	= $this->load->view('setup/ri/treaties/snippets/_ri_commission_scale_data', ['record' => $record], TRUE);

					$ajax_data = [
						'message' => 'Successfully Updated',
						'status'  => 'success',
						'updateSection' => true,
						'hideBootbox' => true
					];
					$ajax_data['updateSectionData'] = [
						'box' 		=> '#ri-commission-scale-data',
						'method' 	=> 'html',
						'html'		=> $success_html
					];
					return $this->template->json($ajax_data);
        		}
        		else
        		{
        			// Simply return could not update message. Might be some logical error or db error.
	        		return $this->template->json([
	                    'status'        => 'error',
	                    'message'       => 'Could not update!'
	                ]);
        		}
        	}
        	else
        	{
    			// Simply Return Validation Error
        		return $this->template->json([
                    'status'        => 'error',
                    'message'       => validation_errors()
                ]);
        	}
		}

		/**
		 * Prepare Form Data
		 */
		$form_data = [
			'form_elements' 	=> $this->ri_setup_treaty_model->get_validation_rules(['commission_scale']),
			'record' 			=> $record
		];

		// Prepare HTML Form
		$json_data['form'] = $this->load->view('setup/ri/treaties/_form_commission_scale', $form_data, TRUE);

		// Return JSON
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Manage Distribution
	 *
	 * @param integer $id Treaty ID
	 * @return void
	 */
	public function distribution($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ri_setup_treaty_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Treaty Distribution
		 */
		$treaty_distribution = $this->ri_setup_treaty_model->get_treaty_distribution_by_treaty($id);

		/**
		 * Prepare Form Data
		 */
		$form_data = [
			'form_elements' 	=> $this->ri_setup_treaty_model->get_validation_rules(['reinsurers']),
			'record' 			=> $record,

			// Treaty Distribution
			'reinsurers' 			=> $this->company_model->dropdown_reinsurers(),
			'treaty_distribution' 	=> $treaty_distribution,
		];

		$return_data = [];
		if( $this->input->post() )
		{
			$done 	= FALSE;
			$rules 	= $this->ri_setup_treaty_model->get_validation_rules_formatted(['reinsurers']);

            $this->form_validation->set_rules($rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();
        		$done = $this->ri_setup_treaty_model->save_treaty_distribution($record->id, $data);

        		if($done)
        		{
        			// Update the Distribution Table
					$treaty_distribution = $this->ri_setup_treaty_model->get_treaty_distribution_by_treaty($id);
					$success_html = $this->load->view('setup/ri/treaties/snippets/_ri_distribution_data', ['treaty_distribution' => $treaty_distribution], TRUE);

					$ajax_data = [
						'message' => 'Successfully Updated',
						'status'  => 'success',
						'updateSection' => true,
						'hideBootbox' => true
					];
					$ajax_data['updateSectionData'] = [
						'box' 		=> '#ri-distribution-data',
						'method' 	=> 'html',
						'html'		=> $success_html
					];
					return $this->template->json($ajax_data);
        		}
        		else
        		{
        			// Simply return could not update message. Might be some logical error or db error.
	        		return $this->template->json([
	                    'status'        => 'error',
	                    'message'       => 'Could not update!'
	                ]);
        		}
        	}
        	else
        	{
    			// Simply Return Validation Error
        		return $this->template->json([
                    'status'        => 'error',
                    'message'       => validation_errors()
                ]);
        	}
		}

		// Prepare HTML Form
		$json_data['form'] = $this->load->view('setup/ri/treaties/_form_distribution', $form_data, TRUE);

		// Merge Return Data with Form Data
		$json_data = array_merge($json_data, $return_data);

		// Return JSON
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Check if RI Distribution is 100%
		 *
		 * @param integer $treaty_type_id
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_distribution__complete($str)
		{
			$reinsurer_ids = $this->input->post('reinsurer_ids');
			$distribution_percent = $this->input->post('distribution_percent');

			// Check duplicate Entries
			$unique_count = count( array_unique($reinsurer_ids) );
			if( $unique_count !== count($reinsurer_ids) )
			{
				$this->form_validation->set_message('_cb_distribution__complete', 'Reinsurer can not be duplicate.');
	            return FALSE;
			}

			// Lets do the math
			$percent = [];
			$i = 0;
			foreach ($reinsurer_ids as $rid)
			{
				$percent["$rid"] = $distribution_percent[$i++];
			}

			$total = 0;
			foreach($percent as $rid=>$dp)
			{
				$total += (float)$dp;
			}
			$total = (int)$total;

			// 100% ?
	        if( $total != 100 )
	        {
	            $this->form_validation->set_message('_cb_distribution__complete', 'The TOTAL of all %s must be equal to 100.');
	            return FALSE;
	        }
	        return TRUE;
		}

	// --------------------------------------------------------------------

	/**
	 * Manage Portfolios
	 *
	 * @param integer $id Treaty ID
	 * @return void
	 */
	public function portfolios($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ri_setup_treaty_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Treaty Portfolios
		 */
		$portfolios = $this->ri_setup_treaty_model->get_portfolios_by_treaty($id);

		/**
		 * Validation Rules/Form Elements Based on the Treaty Type
		 */
		$v_rules = $this->_portfolio_validation_rules_by_treaty_type($record);


		/**
		 * Prepare Form Data
		 */
		$form_data = [
			'form_elements' 	=> $v_rules,
			'record' 			=> $record,

			// Treaty Portfolio
			'portfolios' 	=> $portfolios,
		];

		$return_data = [];
		if( $this->input->post() )
		{
			$done 	= FALSE;

            $this->form_validation->set_rules($v_rules);
			if($this->form_validation->run() === TRUE )
        	{
        		$data = $this->input->post();
        		$done = $this->ri_setup_treaty_model->save_treaty_portfolios($record->id, $data);

        		if($done)
        		{
        			// Update the Portfolio Table
					$portfolios = $this->ri_setup_treaty_model->get_portfolios_by_treaty($id);
					$success_html = $this->load->view('setup/ri/treaties/snippets/_ri_portfolio_data', ['portfolios' => $portfolios], TRUE);

					$ajax_data = [
						'message' => 'Successfully Updated',
						'status'  => 'success',
						'updateSection' => true,
						'hideBootbox' => true
					];
					$ajax_data['updateSectionData'] = [
						'box' 		=> '#ri-portfolio-data',
						'method' 	=> 'html',
						'html'		=> $success_html
					];
					return $this->template->json($ajax_data);
        		}
        		else
        		{
        			// Simply return could not update message. Might be some logical error or db error.
	        		return $this->template->json([
	                    'status'        => 'error',
	                    'message'       => 'Could not update!'
	                ]);
        		}
        	}
        	else
        	{
    			// Simply Return Validation Error
        		return $this->template->json([
                    'status'        => 'error',
                    'message'       => validation_errors()
                ]);
        	}
		}

		// Prepare HTML Form
		$json_data['form'] = $this->load->view('setup/ri/treaties/_form_portfolios', $form_data, TRUE);

		// Merge Return Data with Form Data
		$json_data = array_merge($json_data, $return_data);

		// Return JSON
		$this->template->json($json_data);
	}

	// --------------------------------------------------------------------

		private function _portfolio_validation_rules_by_treaty_type($record)
		{

			$portfolio_dropdown = $this->ri_setup_treaty_model->get_portfolios_by_treaty_dropdown($record->id);
			$v_rules = $this->ri_setup_treaty_model->get_validation_rules_formatted(['portfolios_common']);

			// First rule is 'portfolio_ids[]', update validation rule
			$v_rules[0]['rules'] = 'trim|required|integer|max_length[8]|in_list['.implode(',',array_keys($portfolio_dropdown)).']';


			if( (int)$record->treaty_type_id === IQB_RI_TREATY_TYPE_SP )
			{
				$v_rules = array_merge($v_rules, $this->ri_setup_treaty_model->get_validation_rules_formatted(['portfolios_sp']));
			}
			else if( (int)$record->treaty_type_id === IQB_RI_TREATY_TYPE_QT )
			{
				$v_rules = array_merge($v_rules, $this->ri_setup_treaty_model->get_validation_rules_formatted(['portfolios_qt']));
			}
			else if( (int)$record->treaty_type_id === IQB_RI_TREATY_TYPE_QS )
			{
				$v_rules = array_merge($v_rules, $this->ri_setup_treaty_model->get_validation_rules_formatted(['portfolios_qs']));
			}
			else if( (int)$record->treaty_type_id === IQB_RI_TREATY_TYPE_EOL )
			{
				$v_rules = array_merge($v_rules, $this->ri_setup_treaty_model->get_validation_rules_formatted(['portfolios_eol']));
			}

			return $v_rules;
		}

	// --------------------------------------------------------------------

		/**
		 * Sub-function: Upload Company Profile Picture
		 *
		 * @param string|null $old_file
		 * @return array
		 */
		private function _upload_treaty_document( $old_file = NULL )
		{
			$options = [
				'config' => [
					'encrypt_name' => TRUE,
	                'upload_path' => $this->_upload_path,
	                'allowed_types' => 'pdf',
	                'max_size' => '4096'
				],
				'form_field' => 'file',

				'create_thumb' => FALSE,

				// Delete Old file
				'old_files' => $old_file ? [$old_file] : [],
				'delete_old' => TRUE
			];
			return upload_insqube_media($options);
		}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Check Duplicate - [Fiscal Year ID, Treaty Type]
		 *
		 * Duplicate Condition: [Fiscal Year ID, Treaty Type] Should be Unique
		 *
		 * @param integer $treaty_type_id
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_treaty_type__check_duplicate($treaty_type_id, $id=NULL)
		{
			$treaty_type_id = strtoupper( $treaty_type_id ? $treaty_type_id : $this->input->post('treaty_type_id') );
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');
	    	$fiscal_yr_id = (int)$this->input->post('fiscal_yr_id');

	    	// Check if Fiscal Year has not been selected yet?
	    	if( !$fiscal_yr_id )
	    	{
	    		$this->form_validation->set_message('_cb_treaty_type__check_duplicate', 'The Fiscal Year must be supplied along with %s.');
	            return FALSE;
	    	}

	    	// Check Duplicate
	        if( $this->ri_setup_treaty_model->check_duplicate(['fiscal_yr_id' => $fiscal_yr_id, 'treaty_type_id' => $treaty_type_id], $id))
	        {
	            $this->form_validation->set_message('_cb_treaty_type__check_duplicate', 'The %s already exists for supplied Fiscal Year.');
	            return FALSE;
	        }
	        return TRUE;
		}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Check Duplicate - Portfolio
		 *
		 * Duplicate Condition: Portfolio Should be attached to only on Treay Per Fiscal Year
		 *
		 * @param integer $portfolio_id
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_portfolio__check_duplicate($portfolio_id, $id=NULL)
		{
			$portfolio_id = strtoupper( $portfolio_id ? $portfolio_id : $this->input->post('portfolio_id') );
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');
	    	$fiscal_yr_id = (int)$this->input->post('fiscal_yr_id');

	    	// Check if Fiscal Year has not been selected yet?
	    	if( !$fiscal_yr_id )
	    	{
	    		$this->form_validation->set_message('_cb_portfolio__check_duplicate', 'The Fiscal Year must be supplied along with %s.');
	            return FALSE;
	    	}

	    	// Check Duplicate - Treaty Record Exist with given portfolio for given fiscal year other than supplied treaty id
	        if( $this->ri_setup_treaty_model->_cb_portfolio__check_duplicate($fiscal_yr_id, $portfolio_id, $id) )
	        {
	            $this->form_validation->set_message('_cb_portfolio__check_duplicate', 'The %s already exists for supplied Fiscal Year in another Treaty.');
	            return FALSE;
	        }
	        return TRUE;
		}

	// --------------------------------------------------------------------

		/**
		 * Callback Validation Function - Check Duplicate - Name
		 *
		 * Duplicate Condition: [Fiscal Year ID, Treaty Type] Should be Unique
		 *
		 * @param integer $name
		 * @param integer|null $id
		 * @return bool
		 */
		public function _cb_name__check_duplicate($name, $id=NULL)
		{
			$name = strtoupper( $name ? $name : $this->input->post('name') );
	    	$id   = $id ? (int)$id : (int)$this->input->post('id');

	    	// Check Duplicate
	        if( $this->ri_setup_treaty_model->check_duplicate(['LOWER(`name`)=' => strtolower($name)], $id))
	        {
	            $this->form_validation->set_message('_cb_name__check_duplicate', 'The %s already exists.');
	            return FALSE;
	        }
	        return TRUE;
		}

	// --------------------------------------------------------------------

    /**
     * View Treaty Details
     *
     * @param integer $id
     * @return void
     */
    public function details($id)
    {
    	/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('ri_setup_treaties', 'explore.treaty') )
		{
			$this->dx_auth->deny_access();
		}

    	$id = (int)$id;
    	$record = $this->ri_setup_treaty_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		/**
		 * Treaty Data
		 */
		$data = [
			'record' 				=> $record,
			'brokers' 				=> $this->ri_setup_treaty_model->get_brokers_by_treaty($id),
			'portfolios' 			=> $this->ri_setup_treaty_model->get_portfolios_by_treaty($id),
			'treaty_distribution' 	=> $this->ri_setup_treaty_model->get_treaty_distribution_by_treaty($id),
		];

		$this->data['site_title'] = 'Treaty Details | ' . $record->name;
		$this->template->partial(
							'content_header',
							'templates/_common/_content_header',
							[
								'content_header' => 'Treaty Details <small>' . $record->name . '</small>',
								'breadcrumbs' => ['Treaty Setup' => 'ri_setup_treaties', 'Details' => NULL]
						])
						->partial('content', 'setup/ri/treaties/_details', $data)
						->render($this->data);

    }

	// --------------------------------------------------------------------

	/**
	 * Delete a Agent
	 * @param integer $id
	 * @return json
	 */
	public function delete($id)
	{
		// Valid Record ?
		$id = (int)$id;
		$record = $this->ri_setup_treaty_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		$data = [
			'status' 	=> 'error',
			'message' 	=> 'You cannot delete the default records.'
		];
		/**
		 * Safe to Delete?
		 */
		if( !safe_to_delete( 'Ri_setup_treaty_model', $id ) )
		{
			return $this->template->json($data);
		}

		$done = $this->ri_setup_treaty_model->delete($record->id);

		if($done)
		{
			$data = [
				'status' 	=> 'success',
				'message' 	=> 'Successfully deleted!',
				'removeRow' => true,
				'rowId'		=> '#_data-row-'.$record->id
			];
		}
		else
		{
			$data = [
				'status' 	=> 'error',
				'message' 	=> 'Could not be deleted. It might have references to other module(s)/component(s).'
			];
		}
		return $this->template->json($data);
	}

	// --------------------------------------------------------------------

	public function download($id)
	{
		/**
		 * Check Permissions
		 */
		if( !$this->dx_auth->is_authorized('ri_setup_treaties', 'download.treaty') )
		{
			$this->dx_auth->deny_access();
		}

		$record = $this->ri_setup_treaty_model->find($id);
		if(!$record)
		{
			$this->template->render_404();
		}

		// Let's Download
		$this->load->helper('download');
        $download_file = $record->file ? $this->_upload_path . $record->file : NULL;
        if( $download_file && file_exists($download_file) )
        {
            force_download($download_file, NULL, true);
        }
        else
        {
        	$this->template->render_404('', "Sorry! File Not Found.");
        }
	}

	// --------------------------------------------------------------------


}