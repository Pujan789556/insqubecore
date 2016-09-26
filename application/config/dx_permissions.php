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


/*
|--------------------------------------------------------------------------
| USER SCOPE
|--------------------------------------------------------------------------
|
|	### Definition
|	A way to extend user scope from local to multibranch to global.
|
|	### Why?
|		- For example, A user need to entry underwrite for nearby branch if that branch connectivity is lost. 
|		He will need a multi-branch scope i.e. this user will have a special permission (`scope.to.branch`) enabled. 
|		This scope has one more pre-requisite - one should have multiple branches assigned.
|
|		- For Department Head, he/she needs to veiw report app-wise rather than branch-wise. 
| 		In this case, the scope is 'global'.
|
|	### Structure
|		- Data Type: JSON
|		- Default Scope: local
|		- Data Structure
|			```json
|			{scope:local, default:primary_branch_code}  
|			{scope:global, default:primary_branch_code}
|			{scope:branch, default:primary_branch_code, branches:[branch_code1, branch_code2, ...]}
|			```
|		- Exception: Does not apply for Admin Role. Admin Role's default scope is 'global'
|
*/
$config['DX_user_scope'] = [
	/**
	 * Default Scope.
	 * 
	 * Role permissions shall apply to his only primary_branch
	 */
	'local',
	
	/**
	 * Scope to branch
	 * 
	 * When this permissions is assigned to a user, he/she will have all the permissions (inherited from his Role)
	 * available to all ASSIGNED BRANCH(es) including his primary branch.
	 */
	'branch',   	

	/**
	 * Scope to global
	 * 
	 * When this permissions is assigned to a user, he/she will have all the permissions (inherited from his Role)
	 * available to ALL BRANCH(es) including his primary branch.
	 */
	'global'
];
