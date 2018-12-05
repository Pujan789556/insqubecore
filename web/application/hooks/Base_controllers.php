<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Base Controllers
 *
 * Hook file to load base controllers
 *
 * @package     InsQube
 * @author      IP Bastola
 * @link       	http://www.insqube.com
 */
function load_base_controllers()
{
	spl_autoload_register('insqube_autoload_base_controllers');
}

function insqube_autoload_base_controllers($class)
{
	if (strpos($class, 'CI_') !== 0)
	{
		$file = APPPATH . 'core/' . $class . '.php';
		if ( file_exists( $file ) && is_file( $file ) )
		{
            require_once($file);
        }
	}
}
