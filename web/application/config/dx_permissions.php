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
			'status.to.verified',
			'status.to.active',
			'status.to.cancel',

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
		 * Policy Installments
		 */
		'policy_installments' => [

			/**
			 * CRUD Operation Permissions
			 */
			'explore.installment',

			/**
			 * Accounting Permissions
			 * 	1. Generate Policy Voucher
			 * 	1. Generate Policy Invoice
			 * 	2. Make payment agains each installment
			 */
			'generate.policy.voucher',
			'generate.policy.invoice',
			'generate.policy.credit_note',
			'make.policy.payment',
			'make.policy.refund',
		],

		/**
		 * Endorsement
		 */
		'endorsements' => [

			/**
			 * CRUD Operation Permissions
			 */
			'explore.endorsement',
			'add.endorsement',
			'edit.draft.endorsement',
			'delete.draft.endorsement',

			/**
			 * Verify/Approval Permission
			 */
			'status.to.draft',
			'status.to.verified',
			'status.to.ri.approved', // Permission to RI Approve
			'status.to.vouchered',
			'status.to.invoiced',
			'status.to.active', // Issue Endorsement

			/**
			 * Endorsement Print
			 */
			'print.endorsement'
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
		 * Ledgers
		 */
		'ac_ledgers'  => [
			'explore.ledger',
		],

		/**
		 * Trial Balance
		 */
		'ac_trial_balance'  => [
			'explore.trial.balance',
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
			'print.voucher',


			/**
			 * Reporting Permissions
			 * ---------------------
			 * @TODO -
			 */
		],

		/**
		 * Credit Notes
		 */
		'ac_credit_notes'  => [
			'explore.credit_note',
			'add.credit_note',
			'print.credit_note',
			'update.credit_note.print.flag',

			/**
			 * Reporting Permissions
			 * ---------------------
			 * @TODO -
			 */
		],

		/**
		 * Invoices
		 */
		'ac_invoices'  => [
			'explore.invoice',
			'add.invoice',
			'print.invoice',
			'print.receipt',
			'update.invoice.print.flag',
			'update.receipt.print.flag',

			/**
			 * Reporting Permissions
			 * ---------------------
			 * @TODO -
			 */
		]
	],

	/**
	 * Group: RI
	 */
	'RI Permissions' => [

		/**
		 * RI Transactions
		 */
		'ri_transactions'  => [
			'explore.endorsement',
			'add.endorsement',
			'edit.transaction',
			'delete.transaction',

			// FAC related permissions
			'register.fac'
		],
	],

	/**
	 * Group: Claim
	 */
	'Claim Permissions' => [

		/**
		 * RI Transactions
		 */
		'claims'  => [
			'explore.claim',
			'add.claim',
			'edit.claim.draft',
			'delete.claim.draft',

			// After verify activities
			'assign.claim.surveyors',
			'update.claim.settlement',
			'update.claim.assessment',
			'update.claim.scheme',
			'assign.beema.samiti.report.heading',
			'update.claim.progress', // Claim progress description

			// Status Permission
			'status.to.draft',
			'status.to.verified',
			'status.to.approved',
			'status.to.settled',
			'status.to.closed',
			'status.to.withdrawn',

			// Voucher on closed/withdrawn claim (for surveyor settlement)
			'generate.claim.voucher'
		],
	],

	/**
	 * Group: Reports
	 */
	'Report Permissions' => [
		/**
		 * Beema Samiti Report Module
		 */
		'bs_reports'  => [
			'explore.bs.reports',
			'download.bs.reports',
			'edit.bs.report',
			'add.bs.report',
			'delete.bs.report'
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
