<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DX Permissions Configuration
| -------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Object Wise: Functional Permissions
|--------------------------------------------------------------------------
|
| The permissions are defined as per Objects/Module
| Example: User Object -> Create, Edit, Delete, Activate, Ban etc
|
| All Permissions Objects will be diplayed to Form a complex
| permission matrix assigned to Roles
|
*/
$config['DX_permissions'] = [

	/**
	 * User Module
	 */
	'users'  => [
		'create.user',
		'edit.user',
		'delete.user',
		'ban.user'
	],

	/**
	 * Role Module
	 */
	'roles' => [
		'create.role',
		'edit.role',
		'delete.role',
		'assign.to.user'
	],

	/**
	 * District Module
	 */
	'districts' => [
		'edit.district'
	],

	/**
	 * Setting Module
	 */
	'settings' => [
		'edit.setting'
	]
];