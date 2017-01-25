<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| Policy Related Configurations
| -------------------------------------------------------------------
*/

/**
 * ========================================================================
 * 	MOTOR PORTFOLIO
 *  ========================================================================
 */

/*
|--------------------------------------------------------------------------
| Short Duration Policy Rate Table (३.४ छोटो अवधिको बीमाशुल्क दर)
|--------------------------------------------------------------------------
|
| Policy rate for short duration from the reference of annual rate
|
*/
$config['PO_motor__short_duration_rate'] = [

	'1w' 	=> ['title' => 'One Week', 		'rate' => 0.10, 'min_days' => 7],
	'1m'	=> ['title' => 'One Month', 	'rate' => 0.20, 'min_days' => 30],
	'2m'	=> ['title' => 'Two Months', 	'rate' => 0.30, 'min_days' => 60],
	'3m'	=> ['title' => 'Three Months', 	'rate' => 0.40, 'min_days' => 90],
	'4m'	=> ['title' => 'Four Months', 	'rate' => 0.50, 'min_days' => 120],
	'5m'	=> ['title' => 'Five Months', 	'rate' => 0.60, 'min_days' => 150],
	'6m'	=> ['title' => 'Six Months', 	'rate' => 0.70, 'min_days' => 180],
	'7m'	=> ['title' => 'Seven Months', 	'rate' => 0.80, 'min_days' => 210],
	'8m'	=> ['title' => 'Eight Months', 	'rate' => 0.90, 'min_days' => 240],
	'a8m'	=> ['title' => 'Above Eight Months', 'rate' => 1, 'min_days' => 241, 'max_days' => 365],
];

/*
|--------------------------------------------------------------------------
| NO Claim Discount Rate
|--------------------------------------------------------------------------
|
| Discount Rates for Non claim on previous years
|
*/
$config['PO_motor__no_claim_discount'] = [

	'1y' 	=> ['title' => 'One year before renewal', 		'rate' => 0.20],
	'2y' 	=> ['title' => 'Tow years before renewal', 		'rate' => 0.30],
	'3y' 	=> ['title' => 'Three years before renewal', 	'rate' => 0.40],
	'4y' 	=> ['title' => 'Four years before renewal', 	'rate' => 0.45],
	'5ya' 	=> ['title' => 'Five years before renewal', 	'rate' => 0.50],
];
