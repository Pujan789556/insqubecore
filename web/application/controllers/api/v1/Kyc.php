<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * KYCs API Controller
 *
 * @category 	API
 * @version 	v1
 */

// --------------------------------------------------------------------

class Kyc extends Base_API_Controller
{

	function __construct()
	{
		parent::__construct();

		// Only authorized request can access this API
		$this->check_authorized();

		// Valid Auth Type?
		$this->_check_valid_auth_type();

		// Model
		$this->load->model('customer_model');
		$this->load->model('address_model');

		// Library
		$this->load->library('form_validation');

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
		/**
		 * Customer Record & Address Record
		 */
		$customer_id 	= (int)$this->api_auth->get_token_data('auth_type_id');
		$customer 	 	= $this->customer_model->find($customer_id);
		if(!$customer)
		{
			$this->response_404();
		}

		// Address
		$address 		= $this->address_model->get_by_type(IQB_ADDRESS_TYPE_CUSTOMER, $customer->id);
		$data = [
			'customer' => $this->_trim_customer($customer),
			'address'  => $this->_trim_address($address),
			'flag_kyc_verified' => $customer->flag_kyc_verified == IQB_FLAG_ON ? TRUE : FALSE
		];
		$this->response([
                    $this->config->item('api_status_field') 	=> TRUE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_request_ok'),
                    $this->config->item('api_data_field') 		=> $data,
                ], self::HTTP_OK);
	}

	// --------------------------------------------------------------------

	private function _trim_customer($customer)
	{
		$customer_trimmed = new stdClass();
		foreach(Customer_model::$editable_fields as $key)
		{
			$customer_trimmed->{$key} = $customer->{$key} ?? NULL;
		}
		return $customer_trimmed;
	}

	// --------------------------------------------------------------------

	private function _trim_address($address)
	{
		$address_trimmed = new stdClass();
		foreach(Address_model::$editable_fields as $key)
		{
			$address_trimmed->{$key} = $address->{$key} ?? NULL;
		}
		return $address_trimmed;
	}

	// --------------------------------------------------------------------

	/**
	 * Is Valid Auth Type?
	 *
	 * Only Customer User can get their KYC
	 * @return type
	 */
	private function _check_valid_auth_type()
	{
		$auth_type = (int)$this->api_auth->get_token_data('auth_type');
		if($auth_type !== IQB_API_AUTH_TYPE_CUSTOMER)
		{
			$this->__err_invalid_auth_type();
		}
		return TRUE;
	}

	// --------------------------------------------------------------------

	private function __err_invalid_auth_type($http_code)
	{
		$this->response([
                    $this->config->item('api_status_field') 	=> FALSE,
                    $this->config->item('api_message_field') 	=> $this->lang->line('api_text_invalid_auth_type'),
                ], self::HTTP_BAD_REQUEST);
	}

	// --------------------------------------------------------------------

	private function __err_validation()
	{
		$this->response([
                    $this->config->item('api_status_field') 	=> FALSE,
                    $this->api_auth->err_code_field 			=> IQB_API_ERR_CODE__VALIDATION_ERROR,
                    $this->config->item('api_message_field') 	=> strip_tags( validation_errors() ),
                ], self::HTTP_BAD_REQUEST);
	}

}