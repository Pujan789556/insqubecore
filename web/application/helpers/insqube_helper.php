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

if ( ! function_exists('safe_to_delete'))
{
    /**
     * Prevent Default Data Deletion
     *
     * The application might have different tables coming up with default data installed
     * to work properly. So we have to prevent these data from accidental deletion.
     *
     * @param string $model Model Name
     * @param integer|null $del_id Record ID to delete
     * @return bool
     */
    function safe_to_delete( string $model, int $del_id = 0 )
    {
    	$model = ucfirst($model);
    	if( ! class_exists($model) )
    	{
    		return FALSE;
    	}

    	$flag 	= $model::$protect_default ?? FALSE;
    	$max_id = $model::$protect_max_id ?? 0;

        $safe = TRUE;
        if( $flag == TRUE AND $max_id != 0 AND $del_id != 0 AND $max_id >= $del_id )
        {
            $safe = FALSE;
        }
        return $safe;
    }
}

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

if ( ! function_exists('is_assoc'))
{
    /**
     * Is Associative Array?
     *
     * Check if the supplied array is an associative array.
     *
     * @param array $array
     * @return bool
     */
    function is_assoc(array $array)
    {
        // Keys of the array
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
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
				'name' 		=> 'contacts[address1]',
		        'label' 	=> 'Address 1',
		        '_key' 		=> 'address1',
		        '_type' 	=> 'text',
		        '_required' => true
			],
			[
				'name' 		=> 'contacts[address2]',
		        'label' 	=> 'Address 2',
		        '_key' 		=> 'address2',
		        '_type' 	=> 'text',
		        '_required' => false
			],
			[
				'name' 		=> 'contacts[city]',
		        'label' 	=> 'City',
		        '_key' 		=> 'city',
		        '_type' 	=> 'text',
		        '_required' => true
			],
			[
				'name' 		=> 'contacts[state]',
		        'label' 	=> 'State/Province',
		        '_key' 		=> 'state',
		        '_type' 	=> 'text',
		        '_required' => false
			],
			[
				'name' 		=> 'contacts[zip]',
		        'label' 	=> 'Zip/Postal Code',
		        '_key' 		=> 'zip',
		        '_type' 	=> 'text',
		        '_required' => false
			],
			[
				'name' 		=> 'contacts[country]',
		        'label' 	=> 'Country',
		        '_key' 		=> 'country',
		        '_type'		=> 'dropdown',
		        '_data' 	=> $countries,
		        '_default'  => 'NP',
		        '_required' => true
			],
			[
				'name' 		=> 'contacts[phones]',
		        'label' 	=> 'Phone(s)',
		        '_key' 		=> 'phones',
		        '_type' 	=> 'text',
		        '_required' => false,
		        '_help_text' => 'Comma separated list without std-code. eg. 1 4412345, 1 5512345'
			],
			[
				'name' 		=> 'contacts[fax]',
		        'label' 	=> 'Fax(es)',
		        '_key' 		=> 'fax',
		        '_type' 	=> 'text',
		        '_required' => false,
		        '_help_text' => 'Comma separated list without std-code. eg. 1 4412345, 1 5512345'
			],
			[
				'name' 		=> 'contacts[mobile]',
		        'label' 	=> 'Mobile',
		        '_key' 		=> 'mobile',
		        '_type' 	=> 'text',
		        '_required' => false
			],
			[
				'name' 		=> 'contacts[email]',
		        'label' 	=> 'Email',
		        '_key' 		=> 'email',
		        '_type' 	=> 'text',
		        '_required' => false
			],
			[
				'name' 		=> 'contacts[web]',
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
                'field'     => 'contacts[address1]',
                'label'     => 'Address 1',
                'rules'     => 'trim|required|max_length[50]'
            ],
            [
                'field'     => 'contacts[address2]',
                'label'     => 'Address 2',
                'rules'     => 'trim|max_length[50]'
            ],
            [
                'field'     => 'contacts[city]',
                'label'     => 'City',
                'rules'     => 'trim|required|max_length[50]'
            ],
            [
                'field'     => 'contacts[state]',
                'label'     => 'State/Province',
                'rules'     => 'trim|max_length[50]'
            ],
            [
                'field'     => 'contacts[zip]',
                'label'     => 'Zip/Postal Code',
                'rules'     => 'trim|max_length[20]'
            ],
            [
                'field'     => 'contacts[country]',
                'label'     => 'Country',
                'rules'     => 'trim|required|alpha|exact_length[2]'
            ],
            [
                'field'     => 'contacts[phones]',
                'label'     => 'Phone(s)',
                'rules'     => 'trim|max_length[50]'
            ],
            [
                'field'     => 'contacts[fax]',
                'label'     => 'Fax(es)',
                'rules'     => 'trim|max_length[20]'
            ],
            [
                'field'     => 'contacts[mobile]',
                'label'     => 'Mobile',
                'rules'     => 'trim|max_length[10]'
            ],
            [
                'field'     => 'contacts[email]',
                'label'     => 'Email',
                'rules'     => 'trim|valid_email|max_length[80]'
            ],
            [
                'field'     => 'contacts[web]',
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
	function get_contact_data_from_form( $json = TRUE)
	{
		$CI =& get_instance();
		$contact_data = NULL;

		$contact_fields = ['contact_name', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'phones', 'fax', 'mobile', 'email', 'web'];

		$rules = get_contact_form_validation_rules();
		$CI->load->library('form_validation');
		$CI->form_validation->set_rules($rules);
        if($CI->form_validation->run())
        {
        	$contact_data = $CI->input->post('contacts');
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
	 * @param bool $snippet_only Return Only Snippet Text
	 * @param bool $plain_text  Return only Plain Text(No link on email,mobile,website, No HR)
	 * @return html
	 */
	function get_contact_widget( $contact, $snippet_only = false, $plain_text = false )
	{
		$CI =& get_instance();
		$data = ['contact' => json_decode($contact), 'plain_text' => $plain_text ];

        $view = $snippet_only ? 'templates/_common/_widget_contact_snippet' : 'templates/_common/_widget_contact';
		return $CI->load->view( $view, $data, TRUE);
	}
}

// ------------------------------------------------------------------------

if ( ! function_exists('get_contact_widget_two_lines'))
{
	/**
	 * Get Single Line Contact
	 *
	 * Get the contact widget html
	 * Format:
	 *
	 * 		Address1,  address2,  city, state, zip, country
	 * 		Tel: ..., Fax: ..., Mobile: ..., Email: ..., Web: ....
	 *
	 * @param JSON $contact Single JSON Contact Object
	 * @param string $prefix Prefix text if any
	 * @param bool $plain_text  Return only Plain Text(No link on email,mobile,website, No HR)
	 * @return html
	 */
	function get_contact_widget_two_lines( $contact, $prefix = '' )
	{
		$CI =& get_instance();
		$data = ['contact' => json_decode($contact), 'prefix' => $prefix ];
		$view ='templates/_common/_widget_contact_snippet_two_lines';
		return $CI->load->view( $view, $data, TRUE);
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

if ( ! function_exists('belongs_to_me'))
{
    /**
     * Belongs to Me?
     *
     * Check if this record belongs to branch Specified?
     *
     * @param integer $branch_id branch ID
     * @return mixed
     */
    function belongs_to_me( $branch_id, $terminate_on_fail = TRUE  )
    {
        $CI =& get_instance();

        $__flag_authorized = FALSE;
        if( $CI->dx_auth->belongs_to_me($branch_id) )
        {
            $__flag_authorized = TRUE;
        }

        // Terminate on Exit?
        if( $__flag_authorized === FALSE && $terminate_on_fail == TRUE)
        {
            $CI->dx_auth->deny_access();
            exit(1);
        }

        return $__flag_authorized;
    }
}


// ------------------------------------------------------------------------
if ( ! function_exists('_COMPANY_type_dropdown'))
{
    /**
     * Get Company Type Dropdown
     *
     *
     * @param bool $flag_blank_select   Whether to append blank select
     * @return  bool
     */
    function _COMPANY_type_dropdown( $flag_blank_select = true)
    {
        $dropdown = [

            IQB_COMPANY_TYPE_BANK           => 'Bank or Financial Institution',
            IQB_COMPANY_TYPE_BROKER         => 'Broker Company',
            IQB_COMPANY_TYPE_INSURANCE      => 'Insurance Company',
            IQB_COMPANY_TYPE_RE_INSURANCE   => 'Re-insurance Company',
            IQB_COMPANY_TYPE_GENERAL 		=> 'General Company'
        ];

        if($flag_blank_select)
        {
            $dropdown = IQB_BLANK_SELECT + $dropdown;
        }
        return $dropdown;
    }
}

// ------------------------------------------------------------------------
if ( ! function_exists('_FLAG_yes_no_dropdwon'))
{
    /**
     * Get YES/NO DROPDOWN
     *
     *
     * @param bool $flag_blank_select   Whether to append blank select
     * @return  bool
     */
    function _FLAG_yes_no_dropdwon( $flag_blank_select = true)
    {
        $dropdown = [
            IQB_FLAG_YES    => 'Yes',
            IQB_FLAG_NO		=> 'No'
        ];

        if($flag_blank_select)
        {
            $dropdown = IQB_BLANK_SELECT + $dropdown;
        }
        return $dropdown;
    }
}

// ------------------------------------------------------------------------