<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Template Configurations
|--------------------------------------------------------------------------
|
| A basic template configuration has the following:
| 	- Template Name
| 	- Template Path (relative to views folder)
| 	- Template Sections
|  	- Section prefix
|
*/

/*
|--------------------------------------------------------------------------
| Default Template
|--------------------------------------------------------------------------
|
| Default Template Configuration
|
*/
$config['templates']['default'] = array(

	// Template Path (relative to views folder)
	'path' => 'layouts/default',

	// Sections
	'sections' => [
		'header',
		'body',
		'footer'
	],

	// Section Prefix
	// The template variable will have this prefix
	// E.g. for above sections, you will have the following variables available:
	// 	$__section_header, $__section_body, & $__section_footer
	'prefix' => '__section'
);


/*
|--------------------------------------------------------------------------
| Login Template
|--------------------------------------------------------------------------
|
| Login template will be used for logging in & password recovery
|
*/
$config['templates']['login'] = array(

	// Template Path (relative to views folder)
	'path' => 'templates/login',

	// Default Layout Name
	'layout' => 'layout',

	// Sections
	'sections' => [
		'header',
		'body',
		'footer'
	],

	// Section Prefix
	// The template variable will have this prefix
	// E.g. for above sections, you will have the following variables available:
	// 	$__section_header, $__section_body, & $__section_footer
	'prefix' => '__section'
);

/*
|--------------------------------------------------------------------------
| Dashboard Template
|--------------------------------------------------------------------------
|
| Dashboard Template
|
*/
$config['templates']['dashboard'] = array(

	// Template Path (relative to views folder)
	'path' => 'templates/dashboard',

	// Default Layout Name
	'layout' => 'layout',

	// Sections
	'sections' => [
		'header',
		'sidebar',
		'content_header',
		'content',
		'footer',
		'control_sidebar',
		'dynamic_js'
	],

	// Section Prefix
	'prefix' => '__section'
);