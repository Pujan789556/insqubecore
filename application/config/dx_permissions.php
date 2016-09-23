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
	 * Group: General Components
	 */
	'General Permissions' => [
		/**
		 * Branch Module
		 */
		'branches'  => [
			'create.branch',
			'edit.branch',
			'delete.branch'
		],

		/**
		 * Country Module
		 */
		'countries'  => [
			'edit.country'
		],

		/**
		 * Department Module
		 */
		'departments'  => [
			'create.department',
			'edit.department',
			'delete.department'
		],

		/**
		 * District Module
		 */
		'districts' => [
			'edit.district'
		],
	],

	/**
	 * Group: Security Components
	 */
	'Security Permissions' =>[
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
		 * Setting Module
		 */
		'settings' => [
			'edit.setting'
		]
	]		
];