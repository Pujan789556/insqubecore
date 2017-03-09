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
	 * Activities related to Account Heading
	 */
	'ac_heading' => [
		'_uri' => 'ac_headings/',
		'_table' => 'ac_account_headings',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Account Heading Group
	 */
	'ac_heading_group' => [
		'_uri' => 'ac_heading_groups/',
		'_table' => 'ac_account_heading_groups',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited'
	]],

	/**
	 * Activities related to Agent
	 */
	'agent' => [
		'_uri' => 'agents/',
		'_table' => 'master_agents',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Branches
	 */
	'branch' => [
		'_uri' => 'branches/',
		'_table' => 'master_branches',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Branches
	 */
	'branch_target' => [
		'_uri' => 'branches/targets/',
		'_table' => 'master_branch_targets',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Company
	 */
	'company' => [
		'_uri' => 'companies/',
		'_table' => 'master_companies',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	// Company Branches
	'company_branch' => [
		'_uri' => 'companies/branch/',
		'_table' => 'master_company_branches',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Country
	 */
	'country' => [
		'_uri' => 'countries/',
		'_table' => 'master_countries',
		'_actions' => [
			'E' => 'edited'
	]],

	/**
	 * Customers related to Agent
	 */
	'customer' => [
		'_uri' => 'customers/',
		'_table' => 'dt_customers',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Departments
	 */
	'department' => [
		'_uri' => 'departments/',
		'_table' => 'master_departments',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to District
	 */
	'district' => [
		'_uri' => 'districts/',
		'_table' => 'master_districts',
		'_actions' => [
			'E' => 'edited'
	]],

	/**
	 * Activities related to Object
	 */
	'object' => [
		'_uri' => 'objects/',
		'_table' => 'dt_policy_objects',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Policy
	 */
	'policy' => [
		'_uri' => 'policies/',
		'_table' => 'dt_policies',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Portfolio
	 */
	'portfolio' => [
		'_uri' => 'portfolio/',
		'_table' => 'master_portfolio',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
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
			'A' => 'assigned',  // assigned <role> to <user>
			'P' => 'updated permissions to ',  // assigned permission to <role>
			'R' => 'revoked all permissions from all roles.'
	]],


	/**
	 * Activities related to Portfolio Settings
	 */
	'portfolio_setting' => [
		'_uri' => 'portfolio/settings/',
		'_table' => 'master_portfolio_settings',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to Setting
	 */
	'setting' => [
		'_uri' => 'settings/',
		'_table' => 'master_settings',
		'_actions' => [
			'E' => 'edited'
	]],

	/**
	 * Activities related to Surveyor
	 */
	'surveyor' => [
		'_uri' => 'surveyors/',
		'_table' => 'master_surveyors',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted'
	]],

	/**
	 * Activities related to User
	 */
	'user' => [
		'_uri' => 'users/',
		'_table' => 'auth_users',
		'_actions' => [
			'C' => 'added',
			'E' => 'edited',
			'D' => 'deleted',
			'A' => 'assigned',  // assigned <role> to <user>
			'B' => 'updated basdic information', // of <user>
			'H' => 'changed password', // of <user>
			'T' => 'updated contact',  // updated contact of <user>
			'P' => 'updated profile',  // updated profile of <user>
			'O' => 'uploaded document(s)',  // uploaded documents of <user>
			'X' => 'banned', // banned <user>
			'U' => 'unbanned' // unbanned <user>
	]],

];

