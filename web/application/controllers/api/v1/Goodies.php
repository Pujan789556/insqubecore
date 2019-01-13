<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Goodies API Controller
 *
 * This is to provide additional resources to the clients
 * such as Country dropdown, state dropdown, address_1 dropdown etc
 *
 * @category 	API
 * @version 	v1
 */

// --------------------------------------------------------------------

class Goodies extends Base_API_Controller
{

	function __construct()
	{
		parent::__construct();

		// Only authorized request can access this API
		$this->check_authorized();
	}

	// --------------------------------------------------------------------

	/**
	 * Default Method
	 *
	 * Return KYC
	 *
	 * @return type
	 */
	function index()
	{
		$this->response_404();
	}

	// --------------------------------------------------------------------

	/**
	 * Countries
	 * By Specific Columns: id, alpha2, alpha3
	 *
	 * @param string $col Column Name [id|alpha2|alpha3]
	 * @return json
	 */
	function countries($col="id")
	{
		$this->load->model('country_model');
		$dropdown = $this->country_model->dropdown($col, 'api');
		$this->response([
                    $this->config->item('api_status_field') 	=> TRUE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_request_ok'),
                    $this->config->item('api_data_field') 		=> $dropdown,
                ], self::HTTP_OK);
	}

	// --------------------------------------------------------------------

	/**
	 * States by Country
	 *
	 * @param int $country_id Country ID
	 * @return json
	 */
	function states($country_id)
	{
		$country_id = intval($country_id);
		$this->load->model('state_model');
		$dropdown = $this->state_model->dropdown($country_id, 'api');
		$this->response([
                    $this->config->item('api_status_field') 	=> TRUE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_request_ok'),
                    $this->config->item('api_data_field') 		=> $dropdown,
                ], self::HTTP_OK);
	}

	// --------------------------------------------------------------------

	/**
	 * Address_1 by State ID
	 *
	 * @param string $ref [state|district]
	 * @param int $ref_id State or District ID based on $ref
	 * @return json
	 */
	function address1($ref, $ref_id)
	{
		if(!in_array($ref, ['state', 'district']))
		{
			$this->response_404();
		}

		$ref_id = intval($ref_id);
		$this->load->model('local_body_model');
		if($ref == 'state')
		{
			$dropdown = $this->local_body_model->dropdown_by_state($ref_id, 'api');
		}
		else
		{
			$dropdown = $this->local_body_model->dropdown_by_district($ref_id, 'both', 'api');
		}



		$this->response([
                    $this->config->item('api_status_field') 	=> TRUE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_request_ok'),
                    $this->config->item('api_data_field') 		=> $dropdown,
                ], self::HTTP_OK);
	}


}