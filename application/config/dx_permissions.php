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
			'explore.branch',
			'add.branch',
			'edit.branch',
			'delete.branch'
		],

		/**
		 * Country Module
		 */
		'countries'  => [
			'explore.country',
			'edit.country'
		],

		/**
		 * Department Module
		 */
		'departments'  => [
			'explore.department',
			'add.department',
			'edit.department',
			'delete.department'
		],

		/**
		 * District Module
		 */
		'districts' => [
			'explore.district',
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
			'explore.user',
			'add.user',
			'edit.user',
			'delete.user',
			'ban.user'
		],

		/**
		 * Role Module
		 */
		'roles' => [
			'explore.role',
			'add.role',
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
	],

	/**
	 * Group: Customers
	 */
	'Customer Permissions' => [
		/**
		 * Customer Module
		 */
		'customers'  => [
			'explore.customer',
			'add.customer',
			'edit.customer',
			'delete.customer'
		],
	],

	/**
	 * Group: Policy
	 */
	'Policy Permissions' => [
		/**
		 * Policy Module
		 */
		'policies'  => [
			'explore.policy',

			/**
			 * CRUD Permissions
			 */
			'add.policy',
			'edit.draft.policy',
			'edit.unverified.policy',
			'delete.draft.policy', // You can't delete other policy, It should be on draft status

			/**
			 * Status Upgrade/Downgrade Permissions
			 */
			'status.to.draft',
			'status.to.unverified',
			'status.to.verified',
			'status.to.paid',
			'status.to.active',
			'status.to.cancel',

			/**
			 * Policy Schedule Generation
			 */
			'generate.policy.schedule',

			/**
			 * Payment Related Permissions
			 */
			'make.policy.payment',
			'print.policy.payment.receipt',

			/**
			 * Invoice Related Permissions
			 */
			'generate.policy.invoice',
			'print.policy.invoice',

			/**
			 * Followup Related Permissions
			 */
			'send.followup.notification',

		],

		/**
		 * Object Module
		 */
		'objects' => [
			'explore.object',
			'add.object',
			'edit.object',
			'delete.object'
		],


	],
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
