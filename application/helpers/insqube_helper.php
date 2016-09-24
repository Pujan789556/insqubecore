<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube General Helper Functions
 *
 * 	This will have general helper functions required
 * 
 * 
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link		
 */


// ------------------------------------------------------------------------

if ( ! function_exists('set_menu_active'))
{
	/**
	 * Set navigation menu active
	 * 
	 * @param string $nav_src supplied nav menu
	 * @param string $nav_dstn nav menu to compare against
	 * @param string $css_class CSS Class to return if active
	 * @return bool
	 */
	function set_menu_active( $nav_src, $nav_dstn, $css_class = 'active' )
	{
		return $nav_src === $nav_dstn ? $css_class : '';
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('str_to_nepdate'))
{
	/**
	 * String to Nepali Date
	 * 
	 * Converts into Date Structure from 8-char Nepali Date.
	 * 
	 *	Eg. 20500401  to 2050/04/01
	 * 
	 * @param string $str Nepali Date string
	 * @param string $separater seperator / or - or .
	 * @return string
	 */
	function str_to_nepdate( $str, $separater = '/' )
	{
		$date = [];
		$parts = str_split($str, 4);
		$date[] = $parts[0]; // year
		$mmdd = str_split($parts[1], 2);

		$date = array_merge($date, $mmdd);

		return implode($separater, $date);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_contact_form_fields'))
{
	/**
	 * Get Contact Form Fields
	 * 
	 * We are using contacts as JSON Column on corresponding tables.
	 * So, we need to have a common point to manage form fields, formatting
	 * and display of contact cards
	 * 
	 * @param string $css_class CSS Class to return if active
	 * @return array
	 */
	function get_contact_form_fields(  )
	{
		// Country Dropdown
		$CI =& get_instance();	
		$CI->load->model('country_model');
		$countries = $CI->country_model->dropdown();

		return [
			[
				'name' 		=> 'contacts[contact_name][]',
		        'label' 	=> 'Contact Name',
		        '_key' 		=> 'contact_name',
		        '_type' 	=> 'text',
		        '_required' => true
			],
			[
				'name' 		=> 'contacts[address1][]',
		        'label' 	=> 'Address 1',
		        '_key' 		=> 'address1',
		        '_type' 	=> 'text',
		        '_required' => true
			],
			[
				'name' 		=> 'contacts[address2][]',
		        'label' 	=> 'Address 2',
		        '_key' 		=> 'address1',
		        '_type' 	=> 'text',
		        '_required' => false
			],
			[
				'name' 		=> 'contacts[city][]',
		        'label' 	=> 'City',
		        '_key' 		=> 'city',
		        '_type' 	=> 'text',
		        '_required' => true
			],
			[
				'name' 		=> 'contacts[state][]',
		        'label' 	=> 'State/Province',
		        '_key' 		=> 'state',
		        '_type' 	=> 'text',
		        '_required' => false
			],
			[
				'name' 		=> 'contacts[zip][]',
		        'label' 	=> 'Zip/Postal Code',
		        '_key' 		=> 'zip',
		        '_type' 	=> 'text',
		        '_required' => false
			],
			[
				'name' 		=> 'contacts[country][]',
		        'label' 	=> 'Country',
		        '_key' 		=> 'country',
		        '_type'		=> 'dropdown',
		        '_data' 	=> $countries,
		        '_default'  => 'NP',
		        '_required' => true
			],
			[
				'name' 		=> 'contacts[phones][]',
		        'label' 	=> 'Phone(s)',
		        '_key' 		=> 'phones',
		        '_type' 	=> 'text',
		        '_required' => false,
		        '_help_text' => 'Comma separated list without std-code. eg. 1 4412345, 1 5512345' 
			],	
			[
				'name' 		=> 'contacts[fax][]',
		        'label' 	=> 'Fax(es)',
		        '_key' 		=> 'fax',
		        '_type' 	=> 'text',
		        '_required' => false,
		        '_help_text' => 'Comma separated list without std-code. eg. 1 4412345, 1 5512345' 
			],	
			[
				'name' 		=> 'contacts[mobile][]',
		        'label' 	=> 'Mobile',
		        '_key' 		=> 'mobile',
		        '_type' 	=> 'text',
		        '_required' => false
			],	
			[
				'name' 		=> 'contacts[email][]',
		        'label' 	=> 'Email',
		        '_key' 		=> 'email',
		        '_type' 	=> 'text',
		        '_required' => false
			],	
			[
				'name' 		=> 'contacts[web][]',
		        'label' 	=> 'Website',
		        '_key' 		=> 'web',
		        '_type' 	=> 'text',
		        '_required' => false
			]	
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_contact_form_validation_rules'))
{
	/**
	 * Get Contact Form Validation Rules
	 * 
	 * We are using contacts as JSON Column on corresponding tables.
	 * So, we need to have a common point to manage form fields, formatting
	 * and display of contact cards
	 * 
	 * @param void
	 * @return array
	 */
	function get_contact_form_validation_rules(  )
	{
		return [
			[
                'field'     => 'contacts[contact_name][]',
                'label'     => 'Contact Name',
                'rules'     => 'trim|required|max_length[100]'
            ],
            [
                'field'     => 'contacts[address1][]',
                'label'     => 'Address 1',
                'rules'     => 'trim|required|max_length[50]'
            ],
            [
                'field'     => 'contacts[address2][]',
                'label'     => 'Address 2',
                'rules'     => 'trim|max_length[50]'
            ],
            [
                'field'     => 'contacts[city][]',
                'label'     => 'City',
                'rules'     => 'trim|required|max_length[50]'
            ],
            [
                'field'     => 'contacts[state][]',
                'label'     => 'State/Province',
                'rules'     => 'trim|max_length[50]'
            ],
            [
                'field'     => 'contacts[zip][]',
                'label'     => 'Zip/Postal Code',
                'rules'     => 'trim|max_length[20]'
            ],
            [
                'field'     => 'contacts[country][]',
                'label'     => 'Country',
                'rules'     => 'trim|required|alpha|exact_length[2]'
            ],
            [
                'field'     => 'contacts[phones][]',
                'label'     => 'Phone(s)',
                'rules'     => 'trim|max_length[50]'
            ],  
            [
                'field'     => 'contacts[fax][]',
                'label'     => 'Fax(es)',
                'rules'     => 'trim|max_length[20]' 
            ],  
            [
                'field'     => 'contacts[mobile][]',
                'label'     => 'Mobile',
                'rules'     => 'trim|max_length[10]'
            ],  
            [
                'field'     => 'contacts[email][]',
                'label'     => 'Email',
                'rules'     => 'trim|valid_email|max_length[80]'
            ],  
            [
                'field'     => 'contacts[web][]',
                'label'     => 'Website',
                'rules'     => 'trim|valid_url|prep_url|max_length[255]'
            ]
		];
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_contact_data_from_form'))
{
	/**
	 * Get contact data from form post
	 * 
	 * Returns the multiple contact arrays if we have multiple contacts
	 * 
	 * @param bool 	$json 	Return JSON Data?
	 * @param bool  $single Is it single address format?
	 * @return array
	 */
	function get_contact_data_from_form( $json = TRUE, $single_address = TRUE )
	{
		$CI =& get_instance();	
		$contact_data = NULL;

		$contact_fields = ['contact_name', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'phones', 'fax', 'mobile', 'email', 'web'];

		$rules = get_contact_form_validation_rules();
		$CI->load->library('form_validation');
		$CI->form_validation->set_rules($rules);
        if($CI->form_validation->run())
        {
        	$post_data = $CI->input->post('contacts');
        	foreach($post_data as $key=>$values)
        	{
        		$i = 0;
        		foreach($values as $value)
        		{
        			if( in_array($key, $contact_fields))
        			{
        				$contact_data[$i][$key]  = $value;
	        			$i++;
        			}	        			
        		}
        	}
        }

        // If we are to store single contact as a JSON Object, Get the first Array
        if( $contact_data && $single_address)
        {
        	$contact_data = $contact_data[0];
        }

        return $json && !empty($contact_data) ? json_encode($contact_data) : $contact_data;
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_contact_widget'))
{
	/**
	 * Get Single Contact Widget Box
	 * 
	 * Get the contact widget html
	 * Format:
	 * 
	 * 		address1
	 * 		address2
	 * 		city, state, zip
	 * 		country
	 * 
	 * 		Tel:
	 * 		Fax:
	 * 		Mobile:
	 * 		Email:
	 * 		Web:
	 * 
	 * @param JSON $contact Single JSON Contact Object 
	 * @return html
	 */
	function get_contact_widget( $contact )
	{
		$CI =& get_instance();	
		$data = ['contact' => json_decode($contact) ];
		return $CI->load->view('templates/_common/_widget_contact', $data, TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_country_name'))
{
	/**
	 * Get country name from code (alpha2|alpha3)
	 * 
	 * @param string $code country code in alpha2 or alpha3 format
	 * @param string $column code column alpha2|alpha3
	 * @return string
	 */
	function get_country_name( $code, $column='alpha2' )
	{
		$CI =& get_instance();	
		$CI->load->model('country_model');
		$countries = $CI->country_model->dropdown($column);
		return array_key_exists($code, $countries) ? $countries[$code] : '';
	}
}

// ------------------------------------------------------------------------
