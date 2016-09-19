<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Activity Class
 *
 * This class is used to save different module activities and to render the activity 
 * statements with proper anchoring to those module(s)
 *
 * @package		InsQube
 * @subpackage	Libraries
 * @category	Libraries
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link		
 */

class Activity {

	/**
	 * Current Activity Object
	 *
	 * @var object
	 */
	private $_activity = NULL;

	/**
	 * Activity Module Config
	 * @var array
	 */
	private $_module_config = [];

	/**
	 * Statement Method
	 * 
	 * @var string
	 */
	private $_statement_method = '';

	/**
	 * Activity Error String
	 * 
	 * @var string
	 */
	private $_activity_error = '';


	// --------------------------------------------------------------------

	/**
	 * CI Singleton
	 *
	 * @var object
	 */
	protected $ci;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @param	array	$config	Activity options
	 * @return	void
	 */
	public function __construct( $activity = NULL )
	{
		$this->ci =& get_instance();

		// Load activity configuration
		$this->ci->load->config('activities');

		// Initialize the activity
		$this->initialize($activity);

		log_message('info', 'Library: Activity Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize the activity params
	 *
	 * Accepts an associative array as input, containing activity preferences
	 *
	 * @param	array	config preferences
	 * @return	Activity
	 */
	public function initialize($activity = NULL)
	{

		$this->_activity = $activity;

		if( $this->_activity )
		{
			// Set statement method
			$this->_set_statement_method();

			// Set type config options from config file
			$this->set_module_config();
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Save an Activity to Database
	 * 
	 * @param array $data 
	 * @return bool
	 */
	public function save($data)
	{
		// Load Models
		$this->ci->load->model('activity_model');

		// Validate module and action
		if( $this->_before_save($data) )
		{
			return $this->ci->activity_model->insert($data);
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate Data before saving it
	 * 
	 * @param array $data 
	 * @return bool
	 */
	private function _before_save( $data )
	{
		$valid = FALSE;

		$configs = $this->ci->config->item('insqb_activity_types');

		$module = isset($data['module']) ? $data['module'] : FALSE;

		// Valid module?
		if( $module && array_key_exists($module, $configs))
		{
			$valid = TRUE;
		}

		// Valid module (action)
		$action = isset($data['action']) ? $data['action'] : NULL;

		if( !empty($action) && $valid && array_key_exists($action, $configs[$module]['_actions']))
		{
			$valid = TRUE;
		}

		if( !$valid )
		{
			$this->_activity_error = 'Invalid module or action';
		}

		return $valid;
	}	

	// --------------------------------------------------------------------

	/**
	 * Return activity statement
	 * 
	 * @return string
	 */
	public function statement()
	{
		if( !$this->_activity )
		{
			return '';
		}
		$method = $this->_get_statement_method();

		if( method_exists($this, $method) )
		{
			return $this->$method();
		}
		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * Return Common Statement
	 * 
	 * Common statement for all modules
	 * 
	 * 	Format: <action> module
	 * 
	 * @return string
	 */
	private function _statement_common( $fullstop = TRUE )
	{
		$action = $this->_module_config['_actions'][$this->_activity->action];
		$anchor = site_url(  $this->_module_config['_actions']['_uri'] . $this->_activity->module_id, 
							"{$this->activity->module} ( {$this->_activity->module_id} )" );

		$statement = "$action $anchor";

		$statement = $fullstop ? $statement . '.' : $statement;

		return $statement;
	}

	// --------------------------------------------------------------------

	/**
	 * Get User Activity Statement
	 * 
	 * 	Format: <action> user
	 * 
	 * @return string
	 */
	private function _statement_user(  )
	{		
		return $this->_statement_common();
	}

	// --------------------------------------------------------------------

	/**
	 * Get Role Activity Statement
	 * 
	 * 	Format: <action> role [ to <user> ]
	 * 
	 * @return string
	 */
	private function _statement_role(  )
	{		
		
		$statement = $this->_statement_common( FALSE );

		$extra_string = '.';

		// Check for extra
		// Assigned
		if($action == 'A')
		{
			// Statement:: assigned <role> to <user>
			// ref_anchor holds user_id			
			$user_id = $this->_activity->extra ? (int)$this->_activity->extra : '';
			if( $user_id )
			{
				$extra_string = ' to user ( ' . anchor('users/'. $user_id, $user_id ) . ' ).';
			}
		}
		$statement .= $extra_string;

		return $statement;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Permission Activity Statement
	 * 
	 * 	Format: <action> permission [ to <role> ]	
	 * 
	 * @return string
	 */
	private function _statement_permission(  )
	{		
		
		$statement = $this->_statement_common( FALSE );

		$extra_string = '.';

		// Check for extra
		// Assigned
		if($action == 'A')
		{
			// Statement:: assigned <permission> to <role>
			// ref_anchor holds role_id			
			$role_id = $this->_activity->extra ? (int)$this->_activity->extra : '';
			if( $role_id )
			{
				$extra_string = ' to role ( ' . anchor('roles/'. $role_id, $role_id ) . ' ).';
			}
		}
		$statement .= $extra_string;
		
		return $statement;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Activity Render Method
	 * 
	 * 	Set statement method according to current activity module.
	 * 	e.g. if user module is set, then it will set user statement method as 
	 *  _statement_user and so on...
	 * 
	 * @return $this
	 */
	private function _set_statement_method()
	{
		$this->_statement_method = '_statement_' . $this->_activity->module;

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Get activity statement method
	 * 
	 * @return string
	 */
	private function _get_statement_method()
	{
		return $this->_statement_method;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Activity Module Config
	 * 	
	 * 	If no module is supplied, it will set module config for current actvity object,
	 * 	else, it will set for specified module
	 * 
	 * @param string $module 
	 * @return Object
	 */
	public function set_module_config($module = '')
	{
		$configs = $this->ci->config->item('insqb_activity_types');

		if( $module && array_key_exists($module, $configs))
		{
			$this->_module_config = $configs[$module];
		}
		else if( !empty($this->_activity) && array_key_exists($this->_activity->module, $configs) )
		{
			$this->_module_config = $configs[$this->_activity->module];
		}
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Activity Module Config options
	 * 
	 * @return array
	 */
	public function get_module_config()
	{
		return $this->_module_config;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Activity Error
	 * 
	 * @return string
	 */
	public function get_error()
	{
		return $this->_activity_error;
	}

	// --------------------------------------------------------------------

}
