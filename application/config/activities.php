<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| Activities Config
| -------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Activity Types
|--------------------------------------------------------------------------
|
| All the activity types are listed here.
| These types are closely related to permissions.
|
*/

$config['insqb_activity_types'] = [
	/**
	 * Activities related to Users
	 */
	'user' => [
		'_uri' => 'users/',
		'_table' => 'auth_users',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited', 
			'D' => 'deleted',
			'B' => 'banned'
	]],

	/**
	 * Activities related to Roles
	 */
	'role' => [
		'_uri' => 'roles/',
		'_table' => 'auth_roles',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited', 
			'D' => 'deleted',
			'A' => 'assigned'  // assigned <role> to <user>
	]],

	/**
	 * Activities related to Permissions
	 */
	'permission' => [
		'_uri' => 'permissions/',
		'_table' => 'auth_permissions',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited', 
			'D' => 'deleted',
			'A' => 'assigned'  // assigned <permission> to <role>
	]],
];

