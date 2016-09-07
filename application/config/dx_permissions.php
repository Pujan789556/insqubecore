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
$config['DX_permissions']['user'] = [
	'create.user',
	'edit.user',
	'delete.user',
	'ban.user'
];
$config['DX_permissions']['role'] = [
	'create.role',
	'edit.role',
	'delete.role',
	'assign.to.user'
];
$config['DX_permissions']['permission'] = [
	'create.permision',
	'edit.permission',
	'delete.permission',
	'assign.to.role'
];