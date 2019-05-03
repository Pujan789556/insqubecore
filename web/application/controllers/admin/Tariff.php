<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tariff Controller
 *
 * Master Portfoliio Tariff Controller
 *
 * @category 	Application Settings
 */

// --------------------------------------------------------------------

class Tariff extends MY_Controller
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
        $this->data['site_title'] = 'Application Settings | Tariff';

        // Setup Navigation
        $this->_navigation = [
			'level_0' => 'application_setup',
			'level_1' => 'portfolio',
			'level_2' => $this->router->class
		];
		$this->active_nav_primary($this->_navigation);

		// Load Model
		// $this->load->model('portfolio_model');

		// Helper
		$this->load->helper('object');

        // URL Base
        $this->_url_base         = 'admin/' . $this->router->class;
        $this->_view_base        = 'setup/' . $this->router->class;

        $this->data['_url_base'] = $this->_url_base; // for view to access
        $this->data['_view_base']   = $this->_view_base;
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
		// Let's Render Motor Tariff as Default
        redirect('tariff/motor');

	}

    // --------------------------------------------------------------------
    // TARIFF (AGRICULTURE - CROP) - EXPLORE AND CRUD OPERATIONS
    // --------------------------------------------------------------------

    /**
     * Tariff - Agriculture
     *
     * List of all portfolio tariff fiscal-year-wise
     * @return void
     */
    public function agriculture( $method = "", $id_or_fy_id=null )
    {
        // Check if we have details Method?
        /**
         * Valid Method?
         *      Methods: add | edit | details | flush
         *
         * Urls
         *      /tariff/agriculture
         *      /tariff/agriculture/add
         *      /tariff/agriculture/details/<fiscal_year_id>
         *      /tariff/agriculture/duplicate/<fiscal_year_id>
         *      /tariff/agriculture/edit/<record_id>
         *      /tariff/agriculture/flush
         */
        if( !empty($method) && !in_array($method, ['add', 'edit', 'duplicate', 'details', 'flush']))
        {
            $this->template->render_404();
        }

        // Call the method other than "default"
        if( $method )
        {
            $method_name = '__agriculture__' . $method;
            return $this->{$method_name}($id_or_fy_id);
        }


        // Site Meta
        $this->data['site_title'] = 'Application Settings | Tariff - Agriculture';

        $this->load->model('tariff_agriculture_model');

        /**
         * Update Nav Data
         */
        $this->_navigation['level_3'] = 'agriculture';
        $this->active_nav_primary($this->_navigation);

        $records = $this->tariff_agriculture_model->get_index_rows();


        $this->template->partial(
                            'content_header',
                            'templates/_common/_content_header',
                            [
                                'content_header' => 'Tariff - Agriculture',
                                'breadcrumbs' => ['Application Settings' => NULL, 'Tariff' => NULL, 'Agriculture' => NULL]
                        ])
                        ->partial('content', $this->_view_base . '/agriculture/_index', compact('records'))
                        ->render($this->data);
    }

    // --------------------------------------------------------------------

    /**
     * List all tariff belonging to a single fiscal year
     *
     * @param integer $fiscal_year_id
     * @return void
     */
    private function __agriculture__details($fiscal_year_id)
    {

        $fiscal_year_id = (int)$fiscal_year_id;
        $this->load->model('tariff_agriculture_model');
        $records   = $this->tariff_agriculture_model->get_list_by_fiscal_year_rows($fiscal_year_id);
        if(!$records)
        {
            $this->template->render_404();
        }

        $single = $records[0];
        $fiscal_year_text = $single->fy_code_np . "({$single->fy_code_en})";

        // Site Meta
        $this->data['site_title'] = 'Application Settings | Agriculture Tariff - FY ' . $fiscal_year_text;


        /**
         * Update Nav Data
         */
        $this->_navigation['level_3'] = 'agriculture';
        $this->active_nav_primary($this->_navigation);

        // echo '<pre>'; print_r($this->_navigation);exit;

        $this->template->partial(
                            'content_header',
                            'templates/_common/_content_header',
                            [
                                'content_header' => 'Agriculture Tariff - FY - ' . $fiscal_year_text,
                                'breadcrumbs' => ['Application Settings' => NULL, 'Tariff' => NULL, 'Agriculture' => $this->_url_base . '/agriculture', 'Details' => NULL]
                        ])
                        ->partial('content', $this->_view_base . '/agriculture/_index_by_fiscal_year', compact('records'))
                        ->render($this->data);
    }

    // --------------------------------------------------------------------


    /**
     * Add Default Tarrif Data
     *
     * @return void
     */
    private function __agriculture__add()
    {
        $this->load->model('tariff_agriculture_model');
        $rules = $this->tariff_agriculture_model->insert_validate_rules;

        if( $this->input->post() )
        {
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $fiscal_yr_id = (int)$this->input->post('fiscal_yr_id');
                $done = $this->tariff_agriculture_model->add($fiscal_yr_id);

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
                $status = 'error';
                $message = 'Validation Error.';
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records    = $this->tariff_agriculture_model->get_index_rows();
                $html       = $this->load->view($this->_view_base . '/agriculture/_list', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                $form_data = [
                    'form_elements'         => $rules,
                    'record'                => null
                ];
                return $this->template->json([
                    'status'        => $status,
                    'message'       => $message,
                    'reloadForm'    => true,
                    'form'          => $this->load->view($this->_view_base . '/agriculture/_form_add', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/agriculture/_form_add',
            [
                'form_elements'         => $rules,
                'record'                => null
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Duplicate Agriculture Tariff form Old Fiscal Year
     *
     * @return void
     */
    private function __agriculture__duplicate($source_fiscal_year_id)
    {
        $this->load->model('tariff_agriculture_model');
        // Valid Record ?
        $source_fiscal_year_id   = (int)$source_fiscal_year_id;
        $source_record             = $this->tariff_agriculture_model->get_fiscal_year_row($source_fiscal_year_id);

        if(!$source_record)
        {
            $this->template->render_404();
        }

        $rules = $this->tariff_agriculture_model->insert_validate_rules;
        if( $this->input->post() )
        {
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $destination_fiscal_year_id = (int)$this->input->post('fiscal_yr_id');
                $done = $this->tariff_agriculture_model->duplicate($source_fiscal_year_id, $destination_fiscal_year_id);

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
                $status = 'error';
                $message = 'Validation Error.';
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records    = $this->tariff_agriculture_model->get_index_rows();
                $html       = $this->load->view($this->_view_base . '/agriculture/_list', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                $form_data = [
                    'form_elements'         => $rules,
                    'record'                => null,
                    'source_record'         => $source_record
                ];
                return $this->template->json([
                    'status'        => $status,
                    'message'       => $message,
                    'reloadForm'    => true,
                    'form'          => $this->load->view($this->_view_base . '/agriculture/_form_duplicate', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/agriculture/_form_duplicate',
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
     * Add Default Tarrif Data
     *
     * @return void
     */
    private function __agriculture__edit($id)
    {
        $this->load->model('tariff_agriculture_model');

        // Valid Record ?
        $id = (int)$id;
        $record = $this->tariff_agriculture_model->get($id);
        if(!$record)
        {
            $this->template->render_404();
        }


        // $this->load->model('bs_agro_category_model');
        // $bs_agro_categories = $this->bs_agro_category_model->dropdown_by_portfolio($record->portfolio_id);

        $this->load->model('bs_agro_breed_model');
        $bs_agro_breeds = $this->bs_agro_breed_model->dropdown_by_portfolio($record->portfolio_id);
        if( !$bs_agro_breeds )
        {
            $this->template->json([
                'status' => 'error',
                'title'     => 'Data has not been setup yet.',
                'message'   =>  'Please add the "Beema Samiti Agriculture Category & Breeds" for this portfolio first.<br><br>' .
                                'Application Settings >> Beema Samiti >> Agriculture Categories >> Details >> Manage Breeds'
            ], 422);
        }

        $rules = $this->tariff_agriculture_model->validation_rules;

        if( $this->input->post() )
        {
            /**
             * Forma Validation Rule
             */
            $v_rules = [];
            foreach($rules as $group_name => $rule)
            {
                $v_rules = array_merge($v_rules, $rule);
            }
            $this->form_validation->set_rules($v_rules);

            $data = $this->input->post();

            // echo '<pre>'; print_r($data);exit;

            if( $this->form_validation->run() === TRUE )
            {
                $data   = $this->input->post();
                $tariff = $data['tariff'];

                $post_data = [];

                /**
                 * Prepare Tariff
                 */
                $tariff_count   = count($tariff['bs_agro_breed_id']);
                $tariff_data    = [];
                for($i = 0; $i < $tariff_count; $i++)
                {
                    $single_tarrif = [
                        'bs_agro_breed_id'   => $tariff['bs_agro_breed_id'][$i],
                        'rate'               => $tariff['rate'][$i],
                    ];

                    $tariff_data[] = $single_tarrif;
                }

                $post_data['tariff'] = json_encode($tariff_data);

                // Activate Tariff
                $post_data['active'] = $data['active'] ?? 0;

                $done = $this->tariff_agriculture_model->update($record->id, $post_data, TRUE);

                if(!$done)
                {

                    $status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                    // Clear Cache
                    $this->tariff_agriculture_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                }
            }
            else
            {
                $status = 'error';
                $message = 'Validation Error.';
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records   = $this->tariff_agriculture_model->get_list_by_fiscal_year_rows($record->fiscal_yr_id);
                $html       = $this->load->view($this->_view_base . '/agriculture/_list_by_fiscal_year', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                $form_data = [
                    'form_elements'         => $rules,
                    'record'                => $record
                ];
                return $this->template->json([
                    'status'        => $status,
                    // 'message'       => $message,
                    'message'       => validation_errors(),
                    // 'reloadForm'    => true,
                    // 'form'          => $this->load->view('setup/tariff/agriculture/_form_edit', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/agriculture/_form_edit',
            [
                'form_elements'         => $rules,
                'record'                => $record,
                'bs_agro_breeds'        => $bs_agro_breeds
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Flush Cache Data
     *
     * @return void
     */
    private function __agriculture__flush($id)
    {
        $this->load->model('tariff_agriculture_model');

        $this->tariff_agriculture_model->clear_cache();
        redirect($this->_url_base . '/agriculture');
    }

    // --------------------------------------------------------------------

    /**
     * Callback - Check Agriculture Tariff Duplicate
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function _cb_tariff_agriculture_check_duplicate($fiscal_yr_id, $id=NULL)
    {
        $this->load->model('portfolio_setting_model');
        $fiscal_yr_id = strtoupper( $fiscal_yr_id ? $fiscal_yr_id : $this->input->post('fiscal_yr_id') );
        $tariff_ids = $this->input->post('tariff_ids');

        if( $this->tariff_agriculture_model->check_duplicate(['fiscal_yr_id' => $fiscal_yr_id], $tariff_ids))
        {
            $this->form_validation->set_message('_cb_tariff_agriculture_check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }


    // --------------------------------------------------------------------
    // TARIFF (MISC - BANKER'S BLANKET) - EXPLORE AND CRUD OPERATIONS
    // --------------------------------------------------------------------

    /**
     * Tariff - MISC - BANKER'S BLANKET
     *
     * List of all portfolio tariff fiscal-year-wise
     * @return void
     */
    public function misc_bb( $method = "", $id_or_fy_id=null )
    {
        // Check if we have details Method?
        /**
         * Valid Method?
         *      Methods: add | edit | details | flush
         *
         * Urls
         *      /tariff/misc_bb
         *      /tariff/misc_bb/add
         *      /tariff/misc_bb/duplicate/<record_id>
         *      /tariff/misc_bb/edit/<record_id>
         *      /tariff/misc_bb/flush
         */
        if( !empty($method) && !in_array($method, ['add', 'edit', 'duplicate', 'flush']))
        {
            $this->template->render_404();
        }

        // Call the method other than "default"
        if( $method )
        {
            $method_name = '__misc_bb__' . $method;
            return $this->{$method_name}($id_or_fy_id);
        }


        // Site Meta
        $this->data['site_title'] = 'Application Settings | Tariff - Misc (Banker\'s Blanket)';

        $this->load->model('tariff_misc_bb_model');

        /**
         * Update Nav Data
         */
        $this->_navigation['level_3'] = 'misc_bb';
        $this->active_nav_primary($this->_navigation);

        $records = $this->tariff_misc_bb_model->get_index_rows();


        $this->template->partial(
                            'content_header',
                            'templates/_common/_content_header',
                            [
                                'content_header' => 'Tariff - Misc (Banker\'s Blanket)',
                                'breadcrumbs' => ['Application Settings' => NULL, 'Tariff' => NULL, 'Misc (Banker\'s Blanket)' => NULL]
                        ])
                        ->partial('content', $this->_view_base . '/misc_bb/_index', compact('records'))
                        ->render($this->data);
    }

    // --------------------------------------------------------------------

    /**
     * Flush Cache Data
     *
     * @return void
     */
    private function __misc_bb__flush($id)
    {
        $this->load->model('tariff_misc_bb_model');

        $this->tariff_misc_bb_model->clear_cache();
        redirect($this->_url_base . '/misc_bb');
    }

     // --------------------------------------------------------------------


    /**
     * Add Tarrif Data
     *
     * @return void
     */
    private function __misc_bb__add()
    {
        $this->load->model('tariff_misc_bb_model');
        $this->load->model('portfolio_model');

        $portfolio_record = $this->portfolio_model->find(IQB_SUB_PORTFOLIO_MISC_BB_ID);

        /**
         * Form Posted? Let's Save it
         */
        $this->__misc_bb__save('add');

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/misc_bb/_form',
            [
                'form_elements'         => $this->tariff_misc_bb_model->validation_rules('add'),
                'record'                => NULL,
                'portfolio_record'     => $portfolio_record
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Edit Tarrif Data
     *
     * @param int $id
     * @return void
     */
    private function __misc_bb__edit($id)
    {
        $this->load->model('tariff_misc_bb_model');
        $this->load->model('portfolio_model');

        // Valid Record ?
        $id = (int)$id;
        $record = $this->tariff_misc_bb_model->get($id);
        if(!$record)
        {
            $this->template->render_404();
        }

        $portfolio_record = $this->portfolio_model->find(IQB_SUB_PORTFOLIO_MISC_BB_ID);

        /**
         * Form Posted? Let's Save it
         */
        $this->__misc_bb__save('edit', $record);

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/misc_bb/_form',
            [
                'form_elements'         => $this->tariff_misc_bb_model->validation_rules('edit'),
                'record'                => $record,
                'portfolio_record'     => $portfolio_record
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Save Tarrif Data
     *
     * @param string    $action
     * @param object    $record
     * @return void
     */
    private function __misc_bb__save($action, $record=NULL)
    {
        if( $this->input->post() )
        {
            /**
             * Forma Validation Rule
             */
            $v_rules = $this->tariff_misc_bb_model->validation_rules($action, true);
            $this->form_validation->set_rules($v_rules);

            $data = $this->input->post();

            if( $this->form_validation->run() === TRUE )
            {
                $data   = $this->input->post();
                $tariff = $data['tariff'];



                $post_data = [
                    'tariff' => json_encode($tariff),
                    'active' => $data['active'] ?? 0
                ];


                /**
                 * Add or Edit
                 */

                if($action == 'add')
                {
                    // Add Portfolio ID
                    $post_data['portfolio_id'] = IQB_SUB_PORTFOLIO_MISC_BB_ID;
                    $post_data['fiscal_yr_id'] = $data['fiscal_yr_id'];
                    $done = $this->tariff_misc_bb_model->insert($post_data, TRUE);
                }
                else
                {
                    $done = $this->tariff_misc_bb_model->update($record->id, $post_data, TRUE);
                }

                if(!$done)
                {
                    $status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                    // Clear Cache
                    $this->tariff_misc_bb_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                }
            }
            else
            {
                $status = 'error';
                $message = validation_errors();
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records   = $this->tariff_misc_bb_model->get_index_rows();
                $html       = $this->load->view($this->_view_base . '/misc_bb/_list', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                return $this->template->json([
                    'status'        => $status,
                    'message'       => $message,
                ]);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Duplicate Misc (Banker\'s Blanket) Tariff form Old Fiscal Year
     *
     * @return void
     */
    private function __misc_bb__duplicate($source_id)
    {
        $this->load->model('tariff_misc_bb_model');
        // Valid Record ?
        $source_id      = (int)$source_id;
        $source_record  = $this->tariff_misc_bb_model->get($source_id);

        if(!$source_record)
        {
            $this->template->render_404();
        }

        $rules = $this->tariff_misc_bb_model->duplicate_validation_rules();

        if( $this->input->post() )
        {
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $data = $this->input->post();

                $batch_data                 = [];
                $destination_fiscal_year_id = $this->input->post('fiscal_yr_id');


                // Only Data Row from Traiff Table
                $src  = $this->tariff_misc_bb_model->find($source_record->id);
                $post_data =(array)$src;

                // Set Fiscal Year with Newly supplied value
                $post_data['fiscal_yr_id'] = $destination_fiscal_year_id;

                // Remoe Unnecessary Fields
                unset($post_data['id']);
                unset($post_data['created_at']);
                unset($post_data['created_by']);
                unset($post_data['updated_at']);
                unset($post_data['updated_by']);

                $done = $this->tariff_misc_bb_model->insert($post_data, TRUE);

                if(!$done)
                {
                    $status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                    // Clear Cache
                    $this->tariff_misc_bb_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                }
            }
            else
            {
                $status = 'error';
                $message = validation_errors();
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records    = $this->tariff_misc_bb_model->get_index_rows();
                $html       = $this->load->view($this->_view_base . '/misc_bb/_list', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                $form_data = [
                    'form_elements'         => $rules,
                    'record'                => null,
                    'source_record'         => $source_record
                ];
                return $this->template->json([
                    'status'        => $status,
                    'message'       => $message,
                    'reloadForm'    => true,
                    'form'          => $this->load->view($this->_view_base . '/misc_bb/_form_duplicate', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/misc_bb/_form_duplicate',
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
     * Callback - Check Misc (Banker\'s Blanket) Tariff Duplicate
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function _cb_tariff_misc_bb_check_duplicate($fiscal_yr_id, $id=NULL)
    {
        $fiscal_yr_id = intval( $fiscal_yr_id ? $fiscal_yr_id : $this->input->post('fiscal_yr_id') );

        $where = [
            'fiscal_yr_id' => $fiscal_yr_id,
            'portfolio_id' => IQB_SUB_PORTFOLIO_MISC_BB_ID
        ];
        if( $this->tariff_misc_bb_model->check_duplicate($where))
        {
            $this->form_validation->set_message('_cb_tariff_misc_bb_check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }


    // --------------------------------------------------------------------
    // TARIFF (MISC - EXPEDITION PERSONNEL ACCIDENT) - EXPLORE AND CRUD OPERATIONS
    // --------------------------------------------------------------------

    /**
     * Tariff - MISC - EXPEDITION PERSONNEL ACCIDENT
     *
     * List of all portfolio tariff fiscal-year-wise
     * @return void
     */
    public function misc_epa( $method = "", $id_or_fy_id=null )
    {
        // Check if we have details Method?
        /**
         * Valid Method?
         *      Methods: add | edit | details | flush
         *
         * Urls
         *      /tariff/misc_epa
         *      /tariff/misc_epa/add
         *      /tariff/misc_epa/duplicate/<record_id>
         *      /tariff/misc_epa/edit/<record_id>
         *      /tariff/misc_epa/flush
         */
        if( !empty($method) && !in_array($method, ['add', 'edit', 'duplicate', 'flush']))
        {
            $this->template->render_404();
        }

        // Call the method other than "default"
        if( $method )
        {
            $method_name = '__misc_epa__' . $method;
            return $this->{$method_name}($id_or_fy_id);
        }


        // Site Meta
        $this->data['site_title'] = 'Application Settings | Tariff - Misc (Expedition Personnel Accident)';

        $this->load->model('tariff_misc_epa_model');

        /**
         * Update Nav Data
         */
        $this->_navigation['level_3'] = 'misc_epa';
        $this->active_nav_primary($this->_navigation);

        $records = $this->tariff_misc_epa_model->get_index_rows();


        $this->template->partial(
                            'content_header',
                            'templates/_common/_content_header',
                            [
                                'content_header' => 'Tariff - Misc (Expedition Personnel Accident)',
                                'breadcrumbs' => ['Application Settings' => NULL, 'Tariff' => NULL, 'Misc (Expedition Personnel Accident)' => NULL]
                        ])
                        ->partial('content', $this->_view_base . '/misc_epa/_index', compact('records'))
                        ->render($this->data);
    }

    // --------------------------------------------------------------------

    /**
     * Flush Cache Data
     *
     * @return void
     */
    private function __misc_epa__flush($id)
    {
        $this->load->model('tariff_misc_epa_model');

        $this->tariff_misc_epa_model->clear_cache();
        redirect($this->_url_base . '/misc_epa');
    }

     // --------------------------------------------------------------------


    /**
     * Add Tarrif Data
     *
     * @return void
     */
    private function __misc_epa__add()
    {
        $this->load->model('tariff_misc_epa_model');
        $this->load->model('portfolio_model');

        $portfolio_record = $this->portfolio_model->find(IQB_SUB_PORTFOLIO_MISC_EPA_ID);

        /**
         * Form Posted? Let's Save it
         */
        $this->__misc_epa__save('add');

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/misc_epa/_form',
            [
                'form_elements'         => $this->tariff_misc_epa_model->validation_rules('add'),
                'record'                => NULL,
                'portfolio_record'     => $portfolio_record
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Edit Tarrif Data
     *
     * @param int $id
     * @return void
     */
    private function __misc_epa__edit($id)
    {
        $this->load->model('tariff_misc_epa_model');
        $this->load->model('portfolio_model');

        // Valid Record ?
        $id = (int)$id;
        $record = $this->tariff_misc_epa_model->get($id);
        if(!$record)
        {
            $this->template->render_404();
        }

        $portfolio_record = $this->portfolio_model->find(IQB_SUB_PORTFOLIO_MISC_EPA_ID);

        /**
         * Form Posted? Let's Save it
         */
        $this->__misc_epa__save('edit', $record);

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/misc_epa/_form',
            [
                'form_elements'         => $this->tariff_misc_epa_model->validation_rules('edit'),
                'record'                => $record,
                'portfolio_record'     => $portfolio_record
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Save Tarrif Data
     *
     * @param string    $action
     * @param object    $record
     * @return void
     */
    private function __misc_epa__save($action, $record=NULL)
    {
        if( $this->input->post() )
        {
            /**
             * Forma Validation Rule
             */
            $v_rules = $this->tariff_misc_epa_model->validation_rules($action, true);
            $this->form_validation->set_rules($v_rules);

            $data = $this->input->post();

            if( $this->form_validation->run() === TRUE )
            {
                $data   = $this->input->post();
                $tariff = $data['tariff'];



                $post_data = [
                    'tariff' => json_encode($tariff),
                    'active' => $data['active'] ?? 0
                ];


                /**
                 * Add or Edit
                 */
                if($action == 'add')
                {
                    // Add Portfolio ID
                    $post_data['portfolio_id'] = IQB_SUB_PORTFOLIO_MISC_EPA_ID;
                    $post_data['fiscal_yr_id'] = $data['fiscal_yr_id'];
                    $done = $this->tariff_misc_epa_model->insert($post_data, TRUE);
                }
                else
                {
                    $done = $this->tariff_misc_epa_model->update($record->id, $post_data, TRUE);
                }

                if(!$done)
                {
                    $status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                    // Clear Cache
                    $this->tariff_misc_epa_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                }
            }
            else
            {
                $status = 'error';
                $message = validation_errors();
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records   = $this->tariff_misc_epa_model->get_index_rows();
                $html       = $this->load->view($this->_view_base . '/misc_epa/_list', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                return $this->template->json([
                    'status'        => $status,
                    'message'       => $message,
                ]);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Duplicate Misc (Expedition Personnel Accident) Tariff form Old Fiscal Year
     *
     * @return void
     */
    private function __misc_epa__duplicate($source_id)
    {
        $this->load->model('tariff_misc_epa_model');
        // Valid Record ?
        $source_id      = (int)$source_id;
        $source_record  = $this->tariff_misc_epa_model->get($source_id);

        if(!$source_record)
        {
            $this->template->render_404();
        }

        $rules = $this->tariff_misc_epa_model->duplicate_validation_rules();

        if( $this->input->post() )
        {
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $data = $this->input->post();

                $batch_data                 = [];
                $destination_fiscal_year_id = $this->input->post('fiscal_yr_id');


                // Only Data Row from Traiff Table
                $src  = $this->tariff_misc_epa_model->find($source_record->id);
                $post_data =(array)$src;

                // Set Fiscal Year with Newly supplied value
                $post_data['fiscal_yr_id'] = $destination_fiscal_year_id;

                // Remoe Unnecessary Fields
                unset($post_data['id']);
                unset($post_data['created_at']);
                unset($post_data['created_by']);
                unset($post_data['updated_at']);
                unset($post_data['updated_by']);

                $done = $this->tariff_misc_epa_model->insert($post_data, TRUE);

                if(!$done)
                {
                    $status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                    // Clear Cache
                    $this->tariff_misc_epa_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                }
            }
            else
            {
                $status = 'error';
                $message = validation_errors();
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records    = $this->tariff_misc_epa_model->get_index_rows();
                $html       = $this->load->view($this->_view_base . '/misc_epa/_list', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                $form_data = [
                    'form_elements'         => $rules,
                    'record'                => null,
                    'source_record'         => $source_record
                ];
                return $this->template->json([
                    'status'        => $status,
                    'message'       => $message,
                    'reloadForm'    => true,
                    'form'          => $this->load->view($this->_view_base . '/misc_epa/_form_duplicate', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/misc_epa/_form_duplicate',
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
     * Callback - Check Misc (Expedition Personnel Accident) Tariff Duplicate
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function _cb_tariff_misc_epa_check_duplicate($fiscal_yr_id, $id=NULL)
    {
        $fiscal_yr_id = intval( $fiscal_yr_id ? $fiscal_yr_id : $this->input->post('fiscal_yr_id') );

        $where = [
            'fiscal_yr_id' => $fiscal_yr_id,
            'portfolio_id' => IQB_SUB_PORTFOLIO_MISC_EPA_ID
        ];
        if( $this->tariff_misc_epa_model->check_duplicate($where))
        {
            $this->form_validation->set_message('_cb_tariff_misc_epa_check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }





    // --------------------------------------------------------------------
    // TARIFF (MOTOR) - EXPLORE AND CRUD OPERATIONS
    // --------------------------------------------------------------------

    /**
     * Tariff - Motor
     *
     * List of all portfolio tariff fiscal-year-wise
     * @return void
     */
    public function motor( $method = "", $id_or_fy_id=null )
    {

    	// Check if we have details Method?
    	// tarrif_motor
    	/**
    	 * Valid Method?
    	 * 		Methods: add | edit | details | flush
    	 *
    	 * Urls
    	 * 		/tariff/motor
    	 * 		/tariff/motor/add
    	 * 		/tariff/motor/details/<fiscal_year_id>
    	 * 		/tariff/motor/duplicate/<fiscal_year_id>
         *      /tariff/motor/edit/<record_id>
    	 * 		/tariff/motor/flush
    	 */
    	if( !empty($method) && !in_array($method, ['add', 'edit', 'duplicate', 'details', 'flush']))
    	{
    		$this->template->render_404();
    	}

    	// Call the method other than "default"
    	if( $method )
    	{
    		$method_name = '__motor__' . $method;
    		return $this->{$method_name}($id_or_fy_id);
    	}


        // Site Meta
        $this->data['site_title'] = 'Application Settings | Tariff - Motor';

        $this->load->model('tariff_motor_model');

        /**
         * Update Nav Data
         */
        $this->_navigation['level_3'] = 'motor';
        $this->active_nav_primary($this->_navigation);

        $records = $this->tariff_motor_model->get_index_rows();


        $this->template->partial(
                            'content_header',
                            'templates/_common/_content_header',
                            [
                                'content_header' => 'Tariff - Motor',
                                'breadcrumbs' => ['Application Settings' => NULL, 'Tariff' => 'tariff', 'Motor' => NULL]
                        ])
                        ->partial('content', $this->_view_base . '/motor/_index', compact('records'))
                        ->render($this->data);
    }

    // --------------------------------------------------------------------

    /**
     * List all tariff belonging to a single fiscal year
     *
     * @param integer $fiscal_year_id
     * @return void
     */
    private function __motor__details($fiscal_year_id)
    {

        $fiscal_year_id = (int)$fiscal_year_id;
        $this->load->model('tariff_motor_model');
        $records   = $this->tariff_motor_model->get_list_by_fiscal_year_rows($fiscal_year_id);
        if(!$records)
        {
            $this->template->render_404();
        }

        $single = $records[0];
        $fiscal_year_text = $single->fy_code_np . "({$single->fy_code_en})";

        // Site Meta
        $this->data['site_title'] = 'Application Settings | Motor Tariff - FY ' . $fiscal_year_text;

        $this->load->model('tariff_motor_model');


        /**
         * Update Nav Data
         */
        $this->_navigation['level_3'] = 'motor';
        $this->active_nav_primary($this->_navigation);


        $this->template->partial(
                            'content_header',
                            'templates/_common/_content_header',
                            [
                                'content_header' => 'Motor Tariff - FY - ' . $fiscal_year_text,
                                'breadcrumbs' => ['Application Settings' => NULL, 'Tariff' => NULL, 'Motor' => $this->_url_base . '/motor', 'Details' => NULL]
                        ])
                        ->partial('content', $this->_view_base . '/motor/_index_by_fiscal_year', compact('records'))
                        ->render($this->data);
    }

    // --------------------------------------------------------------------


    /**
     * Add Default Tarrif Data
     *
     * @return void
     */
    private function __motor__add()
    {
        $this->load->model('tariff_motor_model');
        $rules = $this->tariff_motor_model->insert_validate_rules;

        if( $this->input->post() )
        {
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $fiscal_yr_id   = (int)$this->input->post('fiscal_yr_id');
                $done           = $this->tariff_motor_model->add($fiscal_yr_id);

                if(!$done)
                {

                	$status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                	// Clear Cache
                	$this->tariff_motor_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                }
            }
            else
            {
                $status = 'error';
                $message = 'Validation Error.';
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records    = $this->tariff_motor_model->get_index_rows();
                $html       = $this->load->view($this->_view_base . '/motor/_list', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                $form_data = [
                    'form_elements'         => $rules,
                    'record'                => null
                ];
                return $this->template->json([
                    'status'        => $status,
                    'message'       => $message,
                    'reloadForm'    => true,
                    'form'          => $this->load->view($this->_view_base . '/motor/_form_add', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/motor/_form_add',
            [
                'form_elements'         => $rules,
                'record'                => null
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    /**
     * Duplicate Motor Tariff form Old Fiscal Year
     *
     * @return void
     */
    private function __motor__duplicate($source_fiscal_year_id)
    {
        $this->load->model('tariff_motor_model');
        // Valid Record ?
        $source_fiscal_year_id   = (int)$source_fiscal_year_id;
        $source_record             = $this->tariff_motor_model->get_fiscal_year_row($source_fiscal_year_id);

        if(!$source_record)
        {
            $this->template->render_404();
        }

        $rules = $this->tariff_motor_model->insert_validate_rules;

        if( $this->input->post() )
        {
            $this->form_validation->set_rules($rules);
            if( $this->form_validation->run() === TRUE )
            {
                $destination_fiscal_year_id = (int)$this->input->post('fiscal_yr_id');
                $done = $this->tariff_motor_model->duplicate($source_fiscal_year_id, $destination_fiscal_year_id);
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
                $status = 'error';
                $message = 'Validation Error.';
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records    = $this->tariff_motor_model->get_index_rows();
                $html       = $this->load->view($this->_view_base . '/motor/_list', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                $form_data = [
                    'form_elements'         => $rules,
                    'record'                => null,
                    'source_record'         => $source_record
                ];
                return $this->template->json([
                    'status'        => $status,
                    'message'       => $message,
                    'reloadForm'    => true,
                    'form'          => $this->load->view($this->_view_base . '/motor/_form_duplicate', $form_data, TRUE)
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/motor/_form_duplicate',
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
     * Add Default Tarrif Data
     *
     * @return void
     */
    private function __motor__edit($id)
    {
    	$this->load->model('tariff_motor_model');

    	// Valid Record ?
		$id = (int)$id;
		$record = $this->tariff_motor_model->get($id);
		if(!$record)
		{
			$this->template->render_404();
		}

        if( $this->input->post() )
        {
        	/**
        	 * Forma Validation Rule
        	 */
        	$v_rules = $this->__motor_v_rules($record, TRUE);
            $this->form_validation->set_rules($v_rules);

            $data = $this->input->post();

            if( $this->form_validation->run() === TRUE )
            {
                $data = $this->input->post();

                $post_data = [];
                /**
                 * Prepare Tariff
                 */
                $tariff = $data['tariff'];
                $tariff_count = count($tariff['ec_min']);
                $tariff_data = [];
                for($i = 0; $i < $tariff_count; $i++)
                {
                	$single_tarrif = [
                        'ec_type'           => $tariff['ec_type'][$i],
                        'ec_min'            => $tariff['ec_min'][$i],
                        'ec_max'            => $tariff['ec_max'][$i],
	               		'default_age_max'    => $tariff['default_age_max'][$i],

                        /**
                         * Motorcycle/CVC Specific Field
                         */
                        'default_rate'      => $tariff['default_rate'][$i] ?? NULL,


                        /**
                         * Private Vechile Specific Fields
                         */
                        'default_si_amount' => $tariff['default_si_amount'][$i] ?? NULL,
                        'default_si_rate'   => $tariff['default_si_rate'][$i] ?? NULL,
                        'remaining_si_rate' => $tariff['remaining_si_rate'][$i] ?? NULL,
                        'minus_amount'      => $tariff['minus_amount'][$i] ?? NULL,

                        /**
                         * Commercial Vechile Specific Fields
                         */
                        'plus_amount'       => $tariff['plus_amount'][$i] ?? NULL,
                        'ec_threshold'      => $tariff['ec_threshold'][$i] ?? NULL,
                        'cost_per_ec_above' => $tariff['cost_per_ec_above'][$i] ?? NULL,

                        /**
                         * Common Fields
                         */
                        'age1_min'              => $tariff['age1_min'][$i],
                        'age1_max'              => $tariff['age1_max'][$i],
                        'rate1'                 => $tariff['rate1'][$i],
                        'age2_min'              => $tariff['age2_min'][$i],
                        'rate2'                 => $tariff['rate2'][$i],
                        'third_party'           => $tariff['third_party'][$i]
                	];

                	$tariff_data[] = $single_tarrif;
                }

                $post_data['tariff'] = json_encode($tariff_data);

                // Disabled Friendly Discount Rate
                $post_data['dr_mcy_disabled_friendly'] = $data['dr_mcy_disabled_friendly'] ?? NULL;

                // Private Hire - Private Vehicle
                $post_data['rate_pvc_on_hire'] = $data['rate_pvc_on_hire'] ?? NULL;

                // Commercial Vehicle : Personal Use
                $post_data['dr_cvc_on_personal_use'] = $data['dr_cvc_on_personal_use'] ?? NULL;

                // Towing Premium Amount
                $post_data['pramt_towing'] = $data['pramt_towing'] ?? NULL;

                /**
                 * No Claim Discount
                 */
                $no_claim_discount = $data['no_claim_discount'];
                $count = count($no_claim_discount['years']);
                $no_claim_discount_data = [];
                for($i=0; $i< $count; $i++)
                {
                	$no_claim_discount_data[] = [
                		'years'   => $no_claim_discount['years'][$i],
                        'rate'    => $no_claim_discount['rate'][$i],
                        'label_en'    => $no_claim_discount['label_en'][$i],
                		'label_np'    => $no_claim_discount['label_np'][$i]
                	];
                }
                $post_data['no_claim_discount'] = json_encode($no_claim_discount_data);

                /**
                 * Discount Rate Voluntary Excess
                 */
                $dr_voluntary_excess = $data['dr_voluntary_excess'];
                $count = count($dr_voluntary_excess['amount']);
                $dr_voluntary_excess_data = [];
                for($i=0; $i< $count; $i++)
                {
                	$dr_voluntary_excess_data[] = [
                		'amount'  => $dr_voluntary_excess['amount'][$i],
                		'rate'    => $dr_voluntary_excess['rate'][$i]
                	];
                }
                $post_data['dr_voluntary_excess'] = json_encode($dr_voluntary_excess_data);

                /**
                 * Permium Amount: Compulsory Excess
                 */
                $pramt_compulsory_excess = $data['pramt_compulsory_excess'];
                $count = count($pramt_compulsory_excess['min_age']);
                $pramt_compulsory_excess_data = [];
                for($i=0; $i< $count; $i++)
                {
                	$pramt_compulsory_excess_data[] = [
                		'min_age' => $pramt_compulsory_excess['min_age'][$i],
                		'max_age' => $pramt_compulsory_excess['max_age'][$i],
                		'amount'  => $pramt_compulsory_excess['amount'][$i]
                	];
                }
                $post_data['pramt_compulsory_excess'] = json_encode($pramt_compulsory_excess_data);

                /**
                 * Motor Accident Premium
                 */
                $post_data['accident_premium'] = json_encode($data['accident_premium']);

                /**
                 * Risk Group
                 */
                $post_data['riks_group'] = json_encode($data['riks_group']);

                /**
                 * Insured Value Tariff (Coverred Amount)
                 */
                $post_data['insured_value_tariff'] = json_encode($data['insured_value_tariff']);

                /**
                 * Trailer/Trolly Tarrif
                 */
                $post_data['trolly_tariff'] = json_encode($data['trolly_tariff'] ?? NULL);

                // Activate Tariff
	            $post_data['active'] = $data['active'] ?? 0;

                // Default Premium
                $post_data['default_premium'] = $data['default_premium'];


                $done = $this->tariff_motor_model->update($record->id, $post_data, TRUE);

                if(!$done)
                {

                	$status = 'error';
                    $message = 'Could not update.';
                }
                else
                {
                	// Clear Cache
                	$this->tariff_motor_model->clear_cache();

                    $status = 'success';
                    $message = 'Successfully Updated.';
                }
            }
            else
            {
                $status = 'error';
                $message = 'Validation Error.';
            }

            // Success HTML
            if($status === 'success' )
            {
                $ajax_data = [
                    'message' => $message,
                    'status'  => $status,
                    'updateSection' => true,
                    'hideBootbox' => true
                ];

                $records   = $this->tariff_motor_model->get_list_by_fiscal_year_rows($record->fiscal_yr_id);
                $html       = $this->load->view($this->_view_base . '/motor/_list_by_fiscal_year', ['records' => $records], TRUE);

                $ajax_data['updateSectionData'] = [
                    'box'       => '#iqb-data-list',
                    'method'    => 'html',
                    'html'      => $html
                ];
                return $this->template->json($ajax_data);
            }
            else
            {
                return $this->template->json([
                    'status'        => $status,
                    'message'       => validation_errors(),
                ]);
            }
        }

        // Let's render the form
        $json_data['form'] = $this->load->view($this->_view_base . '/motor/_form_edit',
            [
                'form_elements'         => $this->__motor_v_rules($record),
                'record'                => $record
            ], TRUE);

        // Return HTML
        $this->template->json($json_data);
    }

    // --------------------------------------------------------------------

    private function __motor_v_rules($record, $formatted = FALSE)
    {
        $v_rules        = [];
        $portfolio_id   = (int)$record->portfolio_id;

        switch ($portfolio_id)
        {
            case IQB_SUB_PORTFOLIO_MOTORCYCLE_ID:
                $v_rules = $this->tariff_motor_model->motorcycle_validation_rules($formatted);
                break;

            case IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID:
                $v_rules = $this->tariff_motor_model->private_vehicle_validation_rules($formatted);
                break;

            case IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID:
                $v_rules = $this->tariff_motor_model->commercial_vehicle_validation_rules($formatted);
                break;

            default:
                break;
        }

        return $v_rules;
    }


    // --------------------------------------------------------------------

    /**
     * Flush Cache Data
     *
     * @return void
     */
    private function __motor__flush($id)
    {
        $this->load->model('tariff_motor_model');

        $this->tariff_motor_model->clear_cache();
        redirect($this->_url_base . '/motor');
    }

    // --------------------------------------------------------------------

    /**
     * Callback - Check Motor Tariff Duplicate
     *
     * @param string $code
     * @param integer|null $id
     * @return bool
     */
    public function _cb_tariff_motor_check_duplicate($fiscal_yr_id, $id=NULL)
    {
    	$this->load->model('portfolio_setting_model');
    	$fiscal_yr_id = strtoupper( $fiscal_yr_id ? $fiscal_yr_id : $this->input->post('fiscal_yr_id') );
    	$tariff_ids = $this->input->post('tariff_ids');

        if( $this->tariff_motor_model->check_duplicate(['fiscal_yr_id' => $fiscal_yr_id], $tariff_ids))
        {
            $this->form_validation->set_message('_cb_tariff_motor_check_duplicate', 'The %s already exists.');
            return FALSE;
        }
        return TRUE;
    }


}