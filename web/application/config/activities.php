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
	 * Activities related to Account Heading Group
	 */
	'ac_account_groups' => [
		'_uri' => 'ac_account_groups/',
		'single' => 'account group',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Account
	 */
	'ac_accounts' => [
		'_uri' => 'ac_accounts/',
		'single' => 'account',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Account Invoice
	 */
	'ac_credit_notes' => [
		'_uri' => 'ac_credit_notes/',
		'single' => 'credit note',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],

	/**
	 * Activities related to Account Duties & Tax
	 */
	'ac_duties_and_tax' => [
		'_uri' => 'ac_duties_and_tax/',
		'single' => 'account duty and tax',
		'_actions' => [
			'U' => 'updated'
	]],

	/**
	 * Activities related to Account Invoice
	 */
	'ac_invoices' => [
		'_uri' => 'ac_invoices/',
		'single' => 'invoice',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],

	/**
	 * Activities related to Account Parties
	 */
	'ac_parties' => [
		'_uri' => 'ac_parties/',
		'single' => 'account party',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Account Invoice
	 */
	'ac_receipts' => [
		'_uri' => 'ac_receipts/',
		'single' => 'receipt',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],

	/**
	 * Activities related to Account Voucher
	 */
	'ac_vouchers' => [
		'_uri' => 'ac_vouchers/',
		'single' => 'voucher',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],

	/**
	 * Activities related to Account Voucher Type
	 */
	'ac_voucher_types' => [
		'_uri' => 'ac_voucher_types/',
		'single' => 'voucher type',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],


	/**
	 * Activities related to Agent
	 */
	'agents' => [
		'_uri' => 'agents/',
		'single' => 'agent',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Agriculture Tariff
	 */
	'agriculture_tariff' => [
		'_uri' => 'tariff/agriculture/',
		'single' => 'agriculture tariff',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
	]],

	/**
	 * Agriculture Tariff
	 */
	'bankers_blanket_tariff' => [
		'_uri' => 'tariff/misc_bb/',
		'single' => 'banker blanket tariff',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
	]],

	/**
	 * Activities related to Branches
	 */
	'branches' => [
		'_uri' => 'branches/',
		'single' => 'branch',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Branches
	 */
	'branch_targets' => [
		'_uri' => 'branches/targets/',
		'single' => 'branch target',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Departments
	 */
	'claim_schemes' => [
		'_uri' => 'claim_schemes/',
		'single' => 'claim scheme',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Departments
	 */
	'claims' => [
		'_uri' => 'claims/',
		'single' => 'claim',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Company
	 */
	'companies' => [
		'_uri' => 'companies/',
		'single' => 'company',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	// Company Branches
	'company_branches' => [
		'_uri' => 'companies/branches/',
		'single' => 'company branch',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Country
	 */
	'countries' => [
		'_uri' => 'countries/',
		'single' => 'country',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],

	/**
	 * Customers related to Agent
	 */
	'customers' => [
		'_uri' => 'customers/',
		'single' => 'customer',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Departments
	 */
	'departments' => [
		'_uri' => 'departments/',
		'single' => 'department',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],


	/**
	 * Activities related to District
	 */
	'districts' => [
		'_uri' => 'districts/',
		'single' => 'district',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],

	/**
	 * Activities related to Endorsement
	 */
	'endorsement_templates' => [
		'_uri' => 'endorsement_templates/',
		'single' => 'endorsement template',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Endorsement
	 */
	'endorsements' => [
		'_uri' => 'endorsements/',
		'single' => 'policy endorsement',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Agriculture Tariff
	 */
	'epa_tariff' => [
		'_uri' => 'tariff/misc_epa/',
		'single' => 'EPA tariff',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
	]],

	/**
	 * Agriculture Tariff
	 */
	'motor_tariff' => [
		'_uri' => 'tariff/motor/',
		'single' => 'motor tariff',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
	]],

	/**
	 * Activities related to Fiscal Years
	 */
	'fiscal_years' => [
		'_uri' => 'fiscal_years/',
		'single' => 'fiscal year',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],

	/**
	 * Activities related to Forex
	 */
	'forex' => [
		'_uri' => 'forex/',
		'single' => 'forex',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],

	/**
	 * Activities related to Fiscal Year Quarter
	 */
	'fy_quarters' => [
		'_uri' => 'fy_quarters/',
		'single' => 'fiscal year quarter',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],

	/**
	 * Activities related to Object
	 */
	'objects' => [
		'_uri' => 'objects/',
		'single' => 'policy object',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Policy
	 */
	'policies' => [
		'_uri' => 'policies/',
		'single' => 'policy',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Policy
	 */
	'policy_installments' => [
		'_uri' => 'policy_installments/',
		'single' => 'policy installment',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],



	/**
	 * Activities related to Portfolio
	 */
	'portfolio' => [
		'_uri' => 'portfolio/',
		'single' => 'portfolio',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Portfolio Settings
	 */
	'portfolio_settings' => [
		'_uri' => 'portfolio/settings/',
		'single' => 'portfolio setting',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Region
	 */
	'regions' => [
		'_uri' => 'regions/',
		'single' => 'region',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],


	/**
	 * RI Setup - Pools
	 */
	'ri_setup_pools' => [
		'_uri' => 'ri_setup_pools/',
		'single' => 'RI pool setup',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],


	/**
	 * RI Setup - Treaties
	 */
	'ri_setup_treaties' => [
		'_uri' => 'ri_setup_treaties/',
		'single' => 'RI treaty setup',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * RI Setup - Treaty Type
	 */
	'ri_setup_treaty_types' => [
		'_uri' => 'ri_setup_treaty_types/',
		'single' => 'RI treaty type',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],


	/**
	 * RI FAC Registration
	 */
	'ri_fac_registrations' => [
		'_uri' => 'ri_fac_registrations/',
		'single' => 'RI FAC Registration',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * RI Transaction
	 */
	'ri_transactions' => [
		'_uri' => 'ri_transactions/',
		'single' => 'RI Transaction',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],


	/**
	 * Activities related to Risks
	 */
	'risks' => [
		'_uri' => 'risks/',
		'single' => 'risk',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],


	/**
	 * Activities related to Roles
	 */
	'roles' => [
		'_uri' => 'roles/',
		'single' => 'role',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted',
			'A' => 'assigned role to',  // assigned <role> to <user>
			'P' => 'updated permissions to ',  // assigned permission to <role>
			'R' => 'revoked all permissions from all roles.'
	]],




	/**
	 * Activities related to Setting
	 */
	'settings' => [
		'_uri' => 'settings/',
		'single' => 'application settings',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],

	/**
	 * Activities related to State
	 */
	'states' => [
		'_uri' => 'states/',
		'single' => 'state',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated'
	]],

	/**
	 * Activities related to Surveyor Expertise
	 */
	'surveyor_expertise' => [
		'_uri' => 'surveyor_expertise/',
		'single' => 'surveyor expertise',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Surveyor
	 */
	'surveyors' => [
		'_uri' => 'surveyors/',
		'single' => 'surveyor',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted'
	]],


	/**
	 * Activities related to TMI Plans
	 */
	'tmi_plans' => [
		'_uri' => 'tmi_plans/',
		'single' => 'TMI Plan',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
	]],

	/**
	 * Activities related to User
	 */
	'users' => [
		'_uri' => 'users/',
		'single' => 'user',
		'_actions' => [
			'C' => 'added',
			'U' => 'updated',
			'D' => 'deleted',
			'A' => 'assigned role to',  // assigned <role> to <user>
			'B' => 'updated basdic information of', // of <user>
			'H' => 'changed password of', // of <user>
			'T' => 'updated contact of',  // updated contact of <user>
			'P' => 'updated profile of',  // updated profile of <user>
			'O' => 'uploaded document(s) of',  // uploaded documents of <user>
			'X' => 'banned', // banned <user>
			'Y' => 'unbanned' // unbanned <user>
	]],

];

