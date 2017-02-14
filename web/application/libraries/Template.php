<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Template Class
 *
 * This class enables the creation of calendars
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link		https://codeigniter.com/user_guide/libraries/calendar.html
 */

class Template {

	/**
	 * Template List from template configuration file
	 *
	 * @var string
	 */
	private $_templates;

	/**
	 * Http Status Code
	 *
	 * @var integer
	 */
	private $_http_code;

	/**
	 * Default Template Name
	 *
	 * @var string
	 */
	private $_default = 'default';

	/**
	 * Template Layout Name (To set dynamically)
	 *
	 * @var string
	 */
	private $_layout = null;


	/**
	 * Output Method
	 *
	 * @var string 	html | json
	 */
	public $method = 'html';

	/**
	 * Section Related Data
	 *
	 * This array holds the following information:
	 * 	Section Name
	 * 	Partial Name to Render
	 * 	Data To Supply to this Partial
	 *
	 * @var array data
	 */
	protected $_sections = [];

	/**
	 * Template Data
	 *
	 * All data passed through partial are stored in this array
	 * This is required if we need to return json
	 *
	 * @var array data
	 */
	protected $_data = [];


	// --------------------------------------------------------------------

	/**
	 * CI Singleton
	 *
	 * @var object
	 */
	protected $CI;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * Loads the template file and sets the default template.
	 *
	 * @param	array	$config	Calendar options
	 * @return	void
	 */
	public function __construct($config = array())
	{
		$this->CI =& get_instance();

		// Initialize the template
		$this->initialize($config);

		// Load template configuration
		$this->CI->config->load('template', TRUE);
		$this->_templates = $this->CI->config->item('templates', 'template');

		log_message('info', 'Template Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize the user preferences
	 *
	 * Accepts an associative array as input, containing display preferences
	 *
	 * @param	array	config preferences
	 * @return	Template
	 */
	public function initialize($config = array())
	{

		// Default Template
		if( isset($config['default']) )
		{
			$this->set_template($config['default']);
		}

		// Method
		$this->set_method( isset($config['method']) ? $config['method'] : '' );

		// Section Data Initialize
		$this->_data['__sections'] = [];

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the default template
	 *
	 * @param	string	template name
	 * @return	Template
	 */
	public function set_template($template = 'default')
	{
		if( array_key_exists($template, $this->_templates))
		{
			$this->_default = $template;
		}
		else
		{
			$this->_set_default();
		}
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Default Template Layout
	 * @return void
	 */
	private function _set_default()
	{
		$this->_default = 'default';
	}

	// --------------------------------------------------------------------


	/**
	 * Set the default output method
	 *
	 * @param	string	method name
	 * @return	Template
	 */
	public function set_method($method = '')
	{
		if(  $this->CI->input->is_ajax_request() )
		{
			$this->method = 'json';
		}
		else if( empty($method) )
		{
			$this->method = 'html';
		}
		else
		{
			$this->method = in_array($method, array('html', 'json')) ? $method : 'html';
		}
		return $this;
	}


	// --------------------------------------------------------------------

	/**
	 * Get the output method name
	 *
	 * @return	string
	 */
	public function get_method()
	{
		if( empty($this->method))
		{
			$this->set_method();
		}
		return $this->method;
	}

	// --------------------------------------------------------------------


	/**
	 * Set the layout of the template (Master - View)
	 *
	 * @param	string	layout name
	 * @return	Template
	 */
	public function set_layout($layout = '')
	{
		$this->_layout = $layout;

		return $this;
	}

	// --------------------------------------------------------------------


	/**
	 * Get the layout of the current template (Master - View)
	 *
	 * @param	void
	 * @return	String
	 */
	public function get_layout()
	{
		return $this->_layout ?? $this->_templates[$this->_default]['layout'];
	}

	// --------------------------------------------------------------------

	/**
	 * Set HTTP Status Code
	 * @param type|int $code
	 * @return void
	 */
	private function _set_http_code($code = 200)
	{
		$this->_http_code = $code;
		set_status_header($code); // using CI standard method
	}

	/**
	 * Renders a Templates
	 *
	 * @param array
	 * @param integer HTTP Response Code
	 * @return type
	 */
	public function render( $data = NULL, $http_code = 200 )
	{
		// Build Raw Data
		$this->_build_data($data);

		/**
		 *  Do we have an AJAX request?
		 */
		// $method = $this->get_method();
		// if( $method == 'json' )
		// {
		// 	$this->json([], $http_code);
		// }

		// Set HTTP status Code
		$this->_set_http_code($http_code);

		/**
		 * Render Layout and its Partials
		 */
		$layout 		= $this->get_layout();
		$master_view 	= rtrim($this->_templates[$this->_default]['path']) . DIRECTORY_SEPARATOR . $layout;
		$this->CI->load->view($master_view, $this->_data);
	}

	// --------------------------------------------------------------------

	/**
	 * Renders 404
	 *
	 * Renders 404 view on regular http request.
	 * If ajax request, send json string instead.
	 *
	 * @param string $view the 404 view
	 * @return void
	 */
	public function render_404( $view='', $message=null )
	{
		/**
		 *  Do we have an AJAX request?
		 */
		$method = $this->get_method();

		if( $method == 'json' )
		{
			$message = $message ?? $this->_404_message($method);
			$this->_output_json(['error' => 'not_found', 'message' => $message, 'title' => $this->_404_title()], 404);
		}
		else
		{
			show_404( $view );
		}
		exit(1);
	}

	private function _404_message( $method )
	{
		if($method == 'html')
		{
			$message = 	'साथी माफ गर्नुहोला। तपाईंले खोजेको कुरो पाइएन जस्तो छ । कताबाट एता आइपुग्नुभो कुन्नि ?' .  '<br/>' .
					'एक फेर IT को साथीहरुलाई यो कुरो पुर्याम न । '. '<br/>' .
					'तेसो गरे कसो होला ?' . '<br/>' . '<br/>' .
					'Dashboard मा जानु पर्ने हो भने ' . anchor('', 'यहाँँ क्लिक गरम् त') . ' !' . '<br/>' . '<br/>' .
					'धन्यवाद !';
		}
		else
		{
			$message = 	'साथी माफ गर्नुहोला। तपाईंले खोजेको कुरो पाइएन जस्तो छ ।' .  '<br/>' .
						'कताबाट एता आइपुग्नुभो कुन्नि ?' .  '<br/>' .
						'एक फेर IT को साथीहरुलाई यो कुरो पुर्याम न । ' .  '<br/>' .
						'तेसो गरे कसो होला ?' .  '<br/>' .
						'धन्यवाद !';
		}

		return $message;
	}

	private function _404_title()
	{
		$funny_headings = [
			'कुरो अलि मिलेन जस्तो छ !',
			'हैट कता पो आइपुगिएछ ?',
			'मजाक गर्नुको नि सीमा हुन्छ के !',
			'लु यो चैं अलि भएन ल !',
			'जे मन लाग्यो त्यै गर्ने अनि खोज्या काँ पाइन्छ त !',
			'खोज्या कुरो पाइएन भन्या के !'
		];
		$heading = $funny_headings[array_rand($funny_headings)];
		return $heading;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Partial
	 *
	 * @param string $section
	 * @param string $view
	 * @param array $data
	 * @return Object Template
	 */
	public function partial($section, $view, $data=[])
	{
		// Check if Valid section Name?
		if( $this->_valid_section($section))
		{
			// Build Section Data
			$this->_section_data($section, $view, $data);
		}

		// Build Raw Data
		$this->_build_data($data);

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate Section
	 * @param string $section
	 * @return boolean
	 */
	private function _valid_section($section)
	{
		return in_array($section, $this->_templates[$this->_default]['sections']) ? TRUE : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Output JSON Data
	 * @param array $data
	 * @return json
	 */
	public function json($data = [], $http_code = 200)
	{
		if( !empty($data))
		{
			// Build Raw Data
			$this->_build_data($data);
		}

		// Send Output as JSON
		$this->_output_json($this->_data, $http_code);
	}

	// --------------------------------------------------------------------

	public function _output_json($data = [], $http_code = 200)
	{
		// Send Output as JSON
		header('Content-Type: application/json', TRUE, $http_code);
		echo json_encode($data);
		exit(0);
	}

	// --------------------------------------------------------------------

	/**
	 * Build Raw Data Array
	 *
	 * @param array $data
	 * @return voic
	 */
	private function _build_data($data = [])
	{
		if( !empty($data) )
		{
			foreach( $data as $key=>$val)
			{
				$this->_data[$key] = $val;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Build Section Data Array
	 *
	 * @param array $data
	 * @return voic
	 */
	private function _section_data($section, $view, $data=[])
	{
		// Apply Section Prefix
		$prefix = $this->_templates[$this->_default]['prefix'];

		$this->_data['__sections'][$prefix. '_' . $section] = $this->CI->load->view($view, $data, TRUE);
	}

}