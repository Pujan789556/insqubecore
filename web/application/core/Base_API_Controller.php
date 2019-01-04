<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base API Controller
 *
 * InsQuebe Base API Controller
 *
 * All the api controller will inherit this controller
 *
 * @package     InsQube
 * @author      IP Bastola
 * @link       	http://www.insqube.com
 */



// --------------------------------------------------------------------

class Base_API_Controller extends CI_Controller
{
	// Note: Only the widely used HTTP status codes are documented

    // Informational

    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518

    // Success

    /**
     * The request has succeeded
     */
    const HTTP_OK = 200;

    /**
     * The server successfully created a new resource
     */
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;

    /**
     * The server successfully processed the request, though no content is returned
     */
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229

    // Redirection

    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;

    /**
     * The resource has not been modified since the last request
     */
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238

    // Client Error

    /**
     * The request cannot be fulfilled due to multiple errors
     */
    const HTTP_BAD_REQUEST = 400;

    /**
     * The user is unauthorized to access the requested resource
     */
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;

    /**
     * The requested resource is unavailable at this present time
     */
    const HTTP_FORBIDDEN = 403;

    /**
     * The requested resource could not be found
     *
     * Note: This is sometimes used to mask if there was an UNAUTHORIZED (401) or
     * FORBIDDEN (403) error, for security reasons
     */
    const HTTP_NOT_FOUND = 404;

    /**
     * The request method is not supported by the following resource
     */
    const HTTP_METHOD_NOT_ALLOWED = 405;

    /**
     * The request was not acceptable
     */
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;

    /**
     * The request could not be completed due to a conflict with the current state
     * of the resource
     */
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585

    // Server Error

    /**
     * The server encountered an unexpected error
     *
     * Note: This is a generic error message when no specific message
     * is suitable
     */
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * The server does not recognise the request method
     */
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

	// --------------------------------------------------------------------

    /**
     * List all supported methods, the first will be the default format
     *
     * @var array
     */
    protected $_supported_formats = [
        'json' => 'application/json',
        'array' => 'application/json',
        'csv' => 'application/csv',
        'html' => 'text/html',
        'jsonp' => 'application/javascript',
        'php' => 'text/plain',
        'serialized' => 'application/vnd.php.serialized',
        'xml' => 'application/xml'
    ];

    /**
     * HTTP status codes and their respective description
     * Note: Only the widely used HTTP status codes are used
     *
     * @var array
     * @link http://www.restapitutorial.com/httpstatuscodes.html
     */
    protected $http_status_codes = [
        self::HTTP_OK => 'OK',
        self::HTTP_CREATED => 'CREATED',
        self::HTTP_NO_CONTENT => 'NO CONTENT',
        self::HTTP_NOT_MODIFIED => 'NOT MODIFIED',
        self::HTTP_BAD_REQUEST => 'BAD REQUEST',
        self::HTTP_UNAUTHORIZED => 'UNAUTHORIZED',
        self::HTTP_FORBIDDEN => 'FORBIDDEN',
        self::HTTP_NOT_FOUND => 'NOT FOUND',
        self::HTTP_METHOD_NOT_ALLOWED => 'METHOD NOT ALLOWED',
        self::HTTP_NOT_ACCEPTABLE => 'NOT ACCEPTABLE',
        self::HTTP_CONFLICT => 'CONFLICT',
        self::HTTP_INTERNAL_SERVER_ERROR => 'INTERNAL SERVER ERROR',
        self::HTTP_NOT_IMPLEMENTED => 'NOT IMPLEMENTED'
    ];

    /**
     * Contains details about the request
     * Fields: body, format, method, ssl
     * Note: This is a dynamic object (stdClass)
     *
     * @var object
     */
    protected $request = NULL;

    /**
     * Contains details about the response
     * Fields: format, lang
     * Note: This is a dynamic object (stdClass)
     *
     * @var object
     */
    protected $response = NULL;


	/**
	 * Data
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Application Settings from DB
	 *
	 * @var object
	 */
	public $settings;

	/**
	 * Current APP User Info
	 *
	 * @var object
	 */
	public $app_user = NULL;

	/**
	 * Application's Current Fiscal Year from DB
	 *
	 * @var object
	 */
	public $current_fiscal_year;

	/**
	 * Application's Current Quarter of Current Fiscal Year from DB
	 *
	 * @var object
	 */
	public $current_fy_quarter;

	/**
	 * Application's Current Month of Current Fiscal Year from DB
	 *
	 * @var object
	 */
	public $current_fy_month;


	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		// Call the CI_Model constructor
        parent::__construct();

        /**
         * Date/Time Zone
         */
        date_default_timezone_set('Asia/Katmandu');


        /**
         * Libraries
         */
        $this->load->library('api_auth');
        $this->load->library('format');

        /**
         * Format Support
         */
        $this->_supported_formats();

		// --------------------------------------------------------------------

        // Initialise the response, request and rest objects
        $this->request = new stdClass();
        $this->response = new stdClass();

        // Which format should the data be returned in?
        $this->response->format = $this->_detect_output_format();

        // Determine whether the connection is HTTPS
        $this->request->ssl = is_https();

        // Should we answer if not over SSL?
        $this->_https_check();

		// --------------------------------------------------------------------
        /**
         * App Settings
         */
        $this->_app_settings();


        /**
         * Check if system if offline
         */
        $this->_check_offline();


		/**
		 * Other App Settings
         *  - Fiscal Year
         *  - App User
		 */
        $this->_app_fiscal_year();
		$this->_app_user();
	}

    // --------------------------------------------------------------------

    private function _https_check()
    {
        // Should we answer if not over SSL?
        if ($this->config->item('force_https') && $this->request->ssl === FALSE)
        {
            $this->response([
                    $this->config->item('rest_status_field') => FALSE,
                    $this->config->item('rest_message_field') => $this->lang->line('text_rest_unsupported')
                ], self::HTTP_FORBIDDEN);
        }
    }

    // --------------------------------------------------------------------

    private function _supported_formats()
    {
        // Determine supported output formats from configuration
        $supported_formats = $this->config->item('api_supported_formats');

        // Validate the configuration setting output formats
        if (empty($supported_formats))
        {
            $supported_formats = [];
        }

        if ( ! is_array($supported_formats))
        {
            $supported_formats = [$supported_formats];
        }

        // Add silently the default output format if it is missing
        $default_format = $this->_get_default_output_format();
        if (!in_array($default_format, $supported_formats))
        {
            $supported_formats[] = $default_format;
        }

        // Now update $this->_supported_formats
        $this->_supported_formats = array_intersect_key($this->_supported_formats, array_flip($supported_formats));
    }

	// --------------------------------------------------------------------

	/**
	 * Set Application Settings from DB
	 *
	 * @return void
	 */
	private function _app_settings()
	{
		/**
         * Get Cached Result, If no, cache the query result
         */
        $this->settings = $this->setting_model->get(['id' => 1]);
	}

	// --------------------------------------------------------------------

	/**
	 * Set Loggedin User from DB
	 *
	 * @return void
	 */
	private function _app_user()
	{
		$this->app_user = new stdClass();

		if( $this->api_auth->is_authorized() )
		{
            $payload = $this->api_auth->validated_token();

			$this->app_user->api_key 	    = $payload->data->api_key;
            $this->app_user->auth_type 		= $payload->data->auth_type;
            $this->app_user->auth_type_id 	= $payload->data->auth_type_id;
		}
	}

    // --------------------------------------------------------------------

    /**
     * Validate Token
     *
     * @return bool
     */
    public function check_authorized()
    {
        $result = $this->api_auth->validated_token();
        $status = $result[$this->api_auth->status_field] ?? NULL;
        if( $status === FALSE )
        {
            $this->response($result, self::HTTP_UNAUTHORIZED);
        }

        return TRUE;
    }

	// --------------------------------------------------------------------

	/**
	 * Set Current Fiscal Year From DB
	 *
	 * @return void
	 */
	private function _app_fiscal_year()
	{
		/**
         * Get Cached Result, If no, cache the query result
         */
		$today = date('Y-m-d');
        $this->current_fiscal_year = $this->fiscal_year_model->get_fiscal_year($today);

        /**
         * Current Quarter
         */
        $this->current_fy_quarter = $this->fy_quarter_model->get_quarter_by_date($today);

        /**
         * Current Month
         */
        $this->current_fy_month = $this->fy_month_model->get_month_by_date($today);
	}

	// --------------------------------------------------------------------


	/**
	 * Check Offline
	 *
	 * Show offline message and exit if the sytem is set to be offline
	 *
	 * @return void
	 */
	private function _check_offline()
	{
		if( $this->settings->flag_offline == IQB_FLAG_ON )
		{
			$this->response([
                    $this->config->item('rest_status_field') => FALSE,
                    $this->config->item('rest_message_field') => $this->settings->offline_message
                ], self::HTTP_SERVICE_UNAVAILABLE);
		}
	}

	// --------------------------------------------------------------------


	/**
     * Takes mixed data and optionally a status code, then creates the response
     *
     * @access public
     * @param array|NULL $data Data to output to the user
     * @param int|NULL $http_code HTTP status code
     * @param bool $continue TRUE to flush the response to the client and continue
     * running the script; otherwise, exit
     */
    public function response($data = NULL, $http_code = NULL, $continue = FALSE)
    {
        // If the HTTP status is not NULL, then cast as an integer
        if ($http_code !== NULL)
        {
            // So as to be safe later on in the process
            $http_code = (int) $http_code;
        }

        // Set the output as NULL by default
        $output = NULL;

        // If data is NULL and no HTTP status code provided, then display, error and exit
        if ($data === NULL && $http_code === NULL)
        {
            $http_code = self::HTTP_NOT_FOUND;
        }

        // If data is not NULL and a HTTP status code provided, then continue
        elseif ($data !== NULL)
        {
            // If the format method exists, call and return the output in that format
            if (method_exists($this->format, 'to_' . $this->response->format))
            {
                // Set the format header
                $this->output->set_content_type($this->_supported_formats[$this->response->format], strtolower($this->config->item('charset')));
                $output = $this->format->factory($data)->{'to_' . $this->response->format}();

                // An array must be parsed as a string, so as not to cause an array to string error
                // Json is the most appropriate form for such a datatype
                if ($this->response->format === 'array')
                {
                    $output = $this->format->factory($output)->{'to_json'}();
                }
            }
            else
            {
                // If an array or object, then parse as a json, so as to be a 'string'
                if (is_array($data) || is_object($data))
                {
                    $data = $this->format->factory($data)->{'to_json'}();
                }

                // Format is not supported, so output the raw data as a string
                $output = $data;
            }
        }

        // If not greater than zero, then set the HTTP status code as 200 by default
        // Though perhaps 500 should be set instead, for the developer not passing a
        // correct HTTP status code
        $http_code > 0 || $http_code = self::HTTP_OK;

        $this->output->set_status_header($http_code);

        // JC: Log response code only if rest logging enabled
        // if ($this->config->item('rest_enable_logging') === TRUE)
        // {
        //     $this->_log_response_code($http_code);
        // }

        // Output the data
        $this->output->set_output($output);

        if ($continue === FALSE)
        {
            // Display the data and exit execution
            $this->output->_display();
            exit;
        }

        // Otherwise dump the output automatically
    }

	// --------------------------------------------------------------------

    /**
     * Takes mixed data and optionally a status code, then creates the response
     * within the buffers of the Output class. The response is sent to the client
     * lately by the framework, after the current controller's method termination.
     * All the hooks after the controller's method termination are executable
     *
     * @access public
     * @param array|NULL $data Data to output to the user
     * @param int|NULL $http_code HTTP status code
     */
    public function set_response($data = NULL, $http_code = NULL)
    {
        $this->response($data, $http_code, TRUE);
    }

	// --------------------------------------------------------------------

    /**
     * Gets the default format from the configuration. Fallbacks to 'json'
     * if the corresponding configuration option $config['rest_default_format']
     * is missing or is empty
     *
     * @access protected
     * @return string The default supported input format
     */
    protected function _get_default_output_format()
    {
        $default_format = (string) $this->config->item('rest_default_format');
        return $default_format === '' ? 'json' : $default_format;
    }

	// --------------------------------------------------------------------


    /**
     * Detect which format should be used to output the data
     *
     * @access protected
     * @return mixed|NULL|string Output format
     */
    protected function _detect_output_format()
    {
        // Concatenate formats to a regex pattern e.g. \.(csv|json|xml)
        $pattern = '/\.('.implode('|', array_keys($this->_supported_formats)).')($|\/)/';
        $matches = [];

        // Check if a file extension is used e.g. http://example.com/api/index.json?param1=param2
        if (preg_match($pattern, $this->uri->uri_string(), $matches))
        {
            return $matches[1];
        }

        // Get the format parameter named as 'format'
        if (isset($this->_get_args['format']))
        {
            $format = strtolower($this->_get_args['format']);

            if (isset($this->_supported_formats[$format]) === TRUE)
            {
                return $format;
            }
        }

        // Get the HTTP_ACCEPT server variable
        $http_accept = $this->input->server('HTTP_ACCEPT');

        // Otherwise, check the HTTP_ACCEPT server variable
        if ($this->config->item('rest_ignore_http_accept') === FALSE && $http_accept !== NULL)
        {
            // Check all formats against the HTTP_ACCEPT header
            foreach (array_keys($this->_supported_formats) as $format)
            {
                // Has this format been requested?
                if (strpos($http_accept, $format) !== FALSE)
                {
                    if ($format !== 'html' && $format !== 'xml')
                    {
                        // If not HTML or XML assume it's correct
                        return $format;
                    }
                    elseif ($format === 'html' && strpos($http_accept, 'xml') === FALSE)
                    {
                        // HTML or XML have shown up as a match
                        // If it is truly HTML, it wont want any XML
                        return $format;
                    }
                    else if ($format === 'xml' && strpos($http_accept, 'html') === FALSE)
                    {
                        // If it is truly XML, it wont want any HTML
                        return $format;
                    }
                }
            }
        }

        // Check if the controller has a default format
        if (empty($this->rest_format) === FALSE)
        {
            return $this->rest_format;
        }

        // Obtain the default format from the configuration
        return $this->_get_default_output_format();
    }
}

/* End of file Base_API_Controller.php */
/* Location: /core/Base_API_Controller.php */