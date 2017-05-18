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
		 * Agent Module
		 */
		'agents'  => [
			'explore.agent',
			'add.agent',
			'edit.agent',
			'delete.agent'
		],

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
		 * Company Module
		 */
		'companies'  => [
			'explore.company',
			'add.company',
			'edit.company',
			'delete.company',

			// company branch
			'add.company.branch',
			'edit.company.branch',
			'delete.company.branch',
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

		/**
		 * Surveyor Module
		 */
		'surveyors'  => [
			'explore.surveyor',
			'add.surveyor',
			'edit.surveyor',
			'delete.surveyor'
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
			 * -------------------
			 * These CRUD permissions will apply homogeniously to all kind of policies ie.
			 * Fresh/Renewal/Endorsement
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
			'status.to.approved',
			'status.to.invoiced'
			'status.to.active', // issue policy
			'status.to.cancel',

			/**
			 * Accounting Permissions
			 * 	1. Generate Policy Voucher and Invoice
			 * 	2. Make payment and generate Policy Receipt
			 * 	3. Invoice Print, Update Print Flag
			 * 	4. Receipt Print, Update Print Flag
			 */
			'generate.policy.voucher.and.invoice',
			'make.policy.payment',
			'print.policy.invoice',
			'update.policy.invoice.print.flag',
			'print.policy.receipt',
			'update.policy.receipt.print.flag',

			/**
			 * Policy Schedule Generation
			 */
			'generate.policy.schedule',

			/**
			 * Followup Related Permissions
			 */
			'send.followup.notification',
		],

		/**
		 * Policy Transaction/Endorsement
		 */
		'policy_txn' => [

			/**
			 * CRUD Operation Permissions
			 */
			'explore.transaction',
			'add.transaction',
			'edit.transaction',
			'delete.transaction',

			/**
			 * Verify/Approval Permission
			 */
			'status.to.draft',
			'status.to.verified',
			'status.to.active', // Issue Endorsement (this will update approved_by, approved_at)

			/**
			 * RI-Approval Permission
			 */
			'ri.approval.on.transaction', // this will updated ri_approved_at/by & status to ri-approved(R)
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

	/**
	 * Group: Policy
	 */
	'Accounting Permissions' => [

		/**
		 * Accounts
		 */
		'ac_accounts'  => [
			'explore.account',
			'add.account',
			'edit.account',
			'delete.account'
		],

		/**
		 * Accounting Parties
		 */
		'ac_parties'  => [
			'explore.party',
			'add.party',
			'edit.party',
			'delete.party'
		],

		/**
		 * Vouchers
		 */
		'ac_vouchers'  => [
			'explore.voucher',
			'add.voucher',
			'edit.voucher',


			/**
			 * Reporting Permissions
			 * ---------------------
			 * @TODO -
			 */
		]
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
