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
	 * Activities related to Country
	 */
	'country' => [
		'_uri' => 'countries/',
		'_table' => 'master_countries',
		'_actions' => [
			'E' => 'edited' 
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
	 * Activities related to Setting
	 */
	'setting' => [
		'_uri' => 'settings/',
		'_table' => 'master_settings',
		'_actions' => [
			'E' => 'edited' 
	]],
	
];

