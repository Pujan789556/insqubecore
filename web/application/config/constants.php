<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


/*
|--------------------------------------------------------------------------
| CACHE DURATIONS
|--------------------------------------------------------------------------
|
| Cache durations:
| 		1/2 hour 	= 1 * 30 * 60 	= 1800
| 		1 hour 	= 1 * 60 * 60 	= 3600
| 		1 day  	= 24 * 60 * 60 	= 86400 (default)
| 		1 week 	= 86400 * 7  	= 604800
| 		1 month = 86400 * 30 	= 2592000
|
*/
define('CACHE_DURATION_HALF_HR', 	1800);
define('CACHE_DURATION_HR', 		3600);
define('CACHE_DURATION_6HRS', 		21600);
define('CACHE_DURATION_HALF_DAY', 	43200);
define('CACHE_DURATION_DAY', 		86400);
define('CACHE_DURATION_WEEK', 		604800);
define('CACHE_DURATION_MONTH', 		2592000);

/*
|--------------------------------------------------------------------------
| APPLICATION SPECIFIC GENERAL CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_BLANK_SELECT', 	[''  => 'Select...']);
define('IQB_ZERO_SELECT', 	['0' => 'Select...']);


/*
|--------------------------------------------------------------------------
| ACTIVE/INACTIVE STATUS CONSTANT
|--------------------------------------------------------------------------
|
*/
define('IQB_STATUS_ACTIVE',       1);
define('IQB_STATUS_INACTIVE',     0);


/*
|--------------------------------------------------------------------------
| YES/NO FLAG, ON/OFF FLAG, LOCKED/UNLOCKED FLAG
|--------------------------------------------------------------------------
|
*/
define('IQB_FLAG_YES',    	'Y');
define('IQB_FLAG_NO',     	'N');

define('IQB_FLAG_OFF',				0);
define('IQB_FLAG_ON',    			1);

define('IQB_FLAG_LOCKED',	1);
define('IQB_FLAG_UNLOCKED',	0);


/*
|--------------------------------------------------------------------------
| USER SCOPE CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_USER_SCOPE_LOCAL', 		'local');
define('IQB_USER_SCOPE_BRANCH', 	'branch');
define('IQB_USER_SCOPE_GLOBAL', 	'global');

/*
|--------------------------------------------------------------------------
| ADDRESS TYPE CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_ADDRESS_TYPE_AGENT',       		1);
define('IQB_ADDRESS_TYPE_COMPANY_BRANCH',   2);
define('IQB_ADDRESS_TYPE_CUSTOMER',      	3);
define('IQB_ADDRESS_TYPE_GENERAL_PARTY',    4);
define('IQB_ADDRESS_TYPE_SURVEYOR',      	5);
define('IQB_ADDRESS_TYPE_BRANCH',      		6); // Master Branches
define('IQB_ADDRESS_TYPES',  [
	IQB_ADDRESS_TYPE_AGENT 			=> 'Agent',
	IQB_ADDRESS_TYPE_COMPANY_BRANCH => 'Company Branch',
	IQB_ADDRESS_TYPE_CUSTOMER 		=> 'Customer',
	IQB_ADDRESS_TYPE_GENERAL_PARTY 	=> 'General Party',
	IQB_ADDRESS_TYPE_SURVEYOR 		=> 'Surveyor',
	IQB_ADDRESS_TYPE_BRANCH 		=> 'Branch'
]);

/*
|--------------------------------------------------------------------------
| RISK TYPE CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_RISK_TYPE_BASIC',       1);
define('IQB_RISK_TYPE_POOL',        2);


/*
|--------------------------------------------------------------------------
| COMPANY TYPE CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_COMPANY_TYPE_BROKER',       'B');
define('IQB_COMPANY_TYPE_BANK',         'F');
define('IQB_COMPANY_TYPE_RE_INSURANCE', 'R');
define('IQB_COMPANY_TYPE_INSURANCE',    'I');
define('IQB_COMPANY_TYPE_GENERAL',      'G');

/*
|--------------------------------------------------------------------------
| COMPANY ID CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_COMPANY_ID_BEEMA_SAMITI',  1);


/*
|--------------------------------------------------------------------------
| BEEMA SAMITI REPORT SETUP - HEADING TYPE ID
|--------------------------------------------------------------------------
|
*/
define('IQB_BSRS_HEADING_TYPE_ID_CLAIM',  3);


/*
|--------------------------------------------------------------------------
| SURVEYOR TYPE CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_SURVEYOR_TYPE_INDIVIDUAL',  1);
define('IQB_SURVEYOR_TYPE_COMPANY',    	2);

// SUB PORTFOLIO LIST
define('IQB_SURVEYOR_TYPES',  [
	IQB_SURVEYOR_TYPE_INDIVIDUAL 	=> 'Individual',
	IQB_SURVEYOR_TYPE_COMPANY 		=> 'Company'
]);


/*
|--------------------------------------------------------------------------
| PARENT PORTFOLIO CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_MASTER_PORTFOLIO_AGR_ID',  		1);
define('IQB_MASTER_PORTFOLIO_AVIATION_ID',  2);
define('IQB_MASTER_PORTFOLIO_ENG_ID',  		3);
define('IQB_MASTER_PORTFOLIO_FIRE_ID',      4);
define('IQB_MASTER_PORTFOLIO_MARINE_ID',    5);
define('IQB_MASTER_PORTFOLIO_MICRO_ID',     6);
define('IQB_MASTER_PORTFOLIO_MISC_ID',      7);
define('IQB_MASTER_PORTFOLIO_MOTOR_ID',     8);


/*
|--------------------------------------------------------------------------
| AGRICULTURE PORTFOLIO - SUB-PORTFOLIOS IDS
|--------------------------------------------------------------------------
|
| WARNING: Should be exactly same as in database, add here if you add in db
|
*/
define('IQB_SUB_PORTFOLIO_AGR_CROP_ID', 	101); 	// Crop
define('IQB_SUB_PORTFOLIO_AGR_CATTLE_ID', 	102); 	// Cattle
define('IQB_SUB_PORTFOLIO_AGR_POULTRY_ID', 	103); 	// Poultry
define('IQB_SUB_PORTFOLIO_AGR_FISH_ID', 	104); 	// Fish (Pisciculture)
define('IQB_SUB_PORTFOLIO_AGR_BEE_ID', 		105); 	// Bee (Apiculture)

// SUB PORTFOLIO LIST - AGRICULTURE - CROP PORTFOLIOS
define('IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__AGR',  [

	IQB_SUB_PORTFOLIO_AGR_CROP_ID 		=> 'Crop',
	IQB_SUB_PORTFOLIO_AGR_CATTLE_ID 	=> 'Cattle',
	IQB_SUB_PORTFOLIO_AGR_POULTRY_ID 	=> 'Poultry',
	IQB_SUB_PORTFOLIO_AGR_FISH_ID 		=> 'Fish',
	IQB_SUB_PORTFOLIO_AGR_BEE_ID 		=> 'Bee'
]);



/*
|--------------------------------------------------------------------------
| ENGINEERING PORTFOLIO - SUB-PORTFOLIOS IDS
|--------------------------------------------------------------------------
|
| WARNING: Should be exactly same as in database, add here if you add in db
|
*/
define('IQB_SUB_PORTFOLIO_ENG_BL_ID', 	301); 	// Boiler Explosion
define('IQB_SUB_PORTFOLIO_ENG_CAR_ID', 	302);	// Contractor All Risks
define('IQB_SUB_PORTFOLIO_ENG_CPM_ID', 	303); 	// Contractor P & M
define('IQB_SUB_PORTFOLIO_ENG_EEI_ID', 	304);	// Electronic Equipment Insurance
define('IQB_SUB_PORTFOLIO_ENG_EAR_ID', 	305); 	// Erection All Risks
define('IQB_SUB_PORTFOLIO_ENG_MB_ID',  	306); 	// Machine Breakdown
define('IQB_SUB_PORTFOLIO_ENG_LOP_ID', 	307); 	// Loss of Profit


// SUB PORTFOLIO LIST
define('IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__ENG',  [

	IQB_SUB_PORTFOLIO_ENG_BL_ID 	=> 'Boiler Explosion',
	IQB_SUB_PORTFOLIO_ENG_CAR_ID 	=> "Contractor's All Risks",
	IQB_SUB_PORTFOLIO_ENG_CPM_ID 	=> 'Contractor Plant & Machinery',
	IQB_SUB_PORTFOLIO_ENG_EEI_ID 	=> 'Electronic Equipment Insurance',
	IQB_SUB_PORTFOLIO_ENG_EAR_ID 	=> 'Erection All Risks',
	IQB_SUB_PORTFOLIO_ENG_MB_ID 	=> 'Machinery Breakdown',
	IQB_SUB_PORTFOLIO_ENG_LOP_ID 	=> 'Loss of Profit(ENG)'
]);

/*
|--------------------------------------------------------------------------
| FIRE PORTFOLIO - SUB-PORTFOLIOS ID
|--------------------------------------------------------------------------
|
| WARNING: Should be exactly same as in database, add here if you add in db
|
*/
define('IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID', 		401); 	// Fire General
define('IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID', 	402);	// Householder Policy
define('IQB_SUB_PORTFOLIO_FIRE_LOP_ID', 			403); 	// Loss of Profit - Fire

// SUB PORTFOLIO LIST
define('IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__FIRE',  [
	IQB_SUB_PORTFOLIO_FIRE_GENERAL_ID 				=> 'Fire General',
	IQB_SUB_PORTFOLIO_FIRE_HOUSEHOLDER_ID 			=> "Householder's Policy (FIRE)",
	IQB_SUB_PORTFOLIO_FIRE_LOP_ID 					=> "Loss of Profit (FIRE)"
]);


/*
|--------------------------------------------------------------------------
| MARINE PORTFOLIO - SUB-PORTFOLIOS ID
|--------------------------------------------------------------------------
|
| WARNING: Should be exactly same as in database, add here if you add in db
|
*/
define('IQB_SUB_PORTFOLIO_MARINE_AIR_TRANSIT_ID', 		501); 	// Air Transit
define('IQB_SUB_PORTFOLIO_MARINE_MARINE_TRANSIT_ID', 	502); 	// Marine Transit
define('IQB_SUB_PORTFOLIO_MARINE_OPEN_MARINE_ID', 		503);	// Open Marine
define('IQB_SUB_PORTFOLIO_MARINE_ROAD_AIR_TRANSIT_ID', 	504);	// Road Air Transit
define('IQB_SUB_PORTFOLIO_MARINE_ROAD_TANSIT_ID', 		505); 	// Marine Roat Transit

// SUB PORTFOLIO LIST
define('IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE',  [
	IQB_SUB_PORTFOLIO_MARINE_AIR_TRANSIT_ID 		=> 'Air Transit',
	IQB_SUB_PORTFOLIO_MARINE_MARINE_TRANSIT_ID 		=> 'Marine Transit',
	IQB_SUB_PORTFOLIO_MARINE_OPEN_MARINE_ID 		=> 'Open Marine',
	IQB_SUB_PORTFOLIO_MARINE_ROAD_AIR_TRANSIT_ID 	=> 'Road/Air Transit',
	IQB_SUB_PORTFOLIO_MARINE_ROAD_TANSIT_ID			=>  'Road Transit'
]);


/*
|--------------------------------------------------------------------------
| MISCELLANEOUS PORTFOLIO - SUB-PORTFOLIOS IDS
|--------------------------------------------------------------------------
|
| WARNING: Should be exactly same as in database, add here if you add in db
|
*/
define('IQB_SUB_PORTFOLIO_MISC_BB_ID', 		701); 	// Banker's Blanket
define('IQB_SUB_PORTFOLIO_MISC_BRGJWL_ID', 	702); 	// Burglary - Jewelry
define('IQB_SUB_PORTFOLIO_MISC_BRGHB_ID', 	703); 	// Burglary - Housebreaking
define('IQB_SUB_PORTFOLIO_MISC_CT_ID', 		704); 	// Cash in Transit
define('IQB_SUB_PORTFOLIO_MISC_CS_ID', 		705); 	// Cash in Safe
define('IQB_SUB_PORTFOLIO_MISC_EPA_ID', 	706); 	// Expedition Personnel Accident
define('IQB_SUB_PORTFOLIO_MISC_FG_ID', 		708); 	// Fidelity Guarantee
define('IQB_SUB_PORTFOLIO_MISC_GPA_ID', 	709); 	// Group Personnel Accident
define('IQB_SUB_PORTFOLIO_MISC_HI_ID', 		710); 	// Health Insurance (MISC)
define('IQB_SUB_PORTFOLIO_MISC_PA_ID', 		714); 	// Personnel Accident
define('IQB_SUB_PORTFOLIO_MISC_PL_ID', 		717); 	// Public Liability
define('IQB_SUB_PORTFOLIO_MISC_TMI_ID', 	718); 	// Travel Medical Insurance
define('IQB_SUB_PORTFOLIO_MISC_BRGCS_ID', 	720); 	// Burglary - Cash in Safe
define('IQB_SUB_PORTFOLIO_MISC_CC_ID', 		721); 	// Cash in Counter


// SUB PORTFOLIO LIST Burglary (Jewelry, Housebreaking, Cash in Safe)
define('IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG',  [
	IQB_SUB_PORTFOLIO_MISC_BRGJWL_ID 		=> 'Burglary - Jewelry',
	IQB_SUB_PORTFOLIO_MISC_BRGHB_ID 		=> 'Burglary - Housebreaking',
	IQB_SUB_PORTFOLIO_MISC_BRGCS_ID 		=> 'Burglary - Cash in Safe'
]);


/*
|--------------------------------------------------------------------------
| MOTOR PORTFOLIO - SUB-PORTFOLIOS ID
|--------------------------------------------------------------------------
|
| WARNING: Should be exactly same as in database, add here if you add in db
|
*/
define('IQB_SUB_PORTFOLIO_MOTORCYCLE_ID',          		801); 	// Motorcycle
define('IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID',     		802); 	// Private Vehicle
define('IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID',  		803);	// Commercial Vehicle

// SUB PORTFOLIO LIST
define('IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR',  [
	IQB_SUB_PORTFOLIO_MOTORCYCLE_ID 		=> 'Motorcycle',
	IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID 	=> 'Private Vehicle',
	IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID => 'Commercial Vehicle'
]);

/*
|--------------------------------------------------------------------------
| MOTOR PORTFOLIO - "COMMERCIAL VEHICLE" SUB-PORTFOLIO - TYPES
|--------------------------------------------------------------------------
*/
define('IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_GENERAL',      'GCG');     // Goods Carrier - Truck
define('IQB_MOTOR_CVC_TYPE_GOODS_CARRIER_TANKER',       'GCT');     // Goods Carrier - Tanker
define('IQB_MOTOR_CVC_TYPE_PASSENGER_CARRIER',          'PC');      // Passenger Carrier
define('IQB_MOTOR_CVC_TYPE_TAXI',                       'TX');      // TAXI
define('IQB_MOTOR_CVC_TYPE_TEMPO',                      'TM');      // Tempo (e-rikshaw, safa tempo, tricycle)
define('IQB_MOTOR_CVC_TYPE_AGRO_FORESTRY',              'AF');      // Agriculture & Forestry Vehicle
define('IQB_MOTOR_CVC_TYPE_TRACTOR_POWER_TRILLER',      'TRPT');    // Tractor & Power Triller'
define('IQB_MOTOR_CVC_TYPE_CONSTRUCTION_EQUIPMENT',     'CE');      // Construction Equipment Vehicle


/*
|--------------------------------------------------------------------------
| POLICY STATUS CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_POLICY_STATUS_DRAFT')          OR define('IQB_POLICY_STATUS_DRAFT',        'D');
defined('IQB_POLICY_STATUS_VERIFIED')       OR define('IQB_POLICY_STATUS_VERIFIED',     'V');
defined('IQB_POLICY_STATUS_ACTIVE')         OR define('IQB_POLICY_STATUS_ACTIVE',       'A');
defined('IQB_POLICY_STATUS_CANCELED')       OR define('IQB_POLICY_STATUS_CANCELED',     'C');
defined('IQB_POLICY_STATUS_EXPIRED')        OR define('IQB_POLICY_STATUS_EXPIRED',      'E');


/*
|--------------------------------------------------------------------------
| POLICY FLAG DIRECT DISCOUNT, AGENT COMMISSION CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_POLICY_FLAG_DC_AGENT_COMMISSION')  OR define('IQB_POLICY_FLAG_DC_AGENT_COMMISSION',    'C');
defined('IQB_POLICY_FLAG_DC_DIRECT')    		OR define('IQB_POLICY_FLAG_DC_DIRECT',      		'D');
defined('IQB_POLICY_FLAG_DC_NONE')    			OR define('IQB_POLICY_FLAG_DC_NONE',      			'N');


/*
|--------------------------------------------------------------------------
| POLICY ENDORSEMENT STATUS CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_POLICY_ENDORSEMENT_STATUS_DRAFT')          OR define('IQB_POLICY_ENDORSEMENT_STATUS_DRAFT',        'D');
defined('IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED')       OR define('IQB_POLICY_ENDORSEMENT_STATUS_VERIFIED',     'V');
defined('IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED')    OR define('IQB_POLICY_ENDORSEMENT_STATUS_RI_APPROVED',  'R');
defined('IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED')    	OR define('IQB_POLICY_ENDORSEMENT_STATUS_VOUCHERED',  	'H');
defined('IQB_POLICY_ENDORSEMENT_STATUS_INVOICED')    	OR define('IQB_POLICY_ENDORSEMENT_STATUS_INVOICED',  	'I');
defined('IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE')         OR define('IQB_POLICY_ENDORSEMENT_STATUS_ACTIVE',       'A');


/*
|--------------------------------------------------------------------------
| POLICY ENDORSEMENT TYPES CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_POLICY_ENDORSEMENT_TYPE_FRESH')    OR define('IQB_POLICY_ENDORSEMENT_TYPE_FRESH',      1);
defined('IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL')  OR define('IQB_POLICY_ENDORSEMENT_TYPE_RENEWAL',    2);

// OTHER ENDORSEMENTS
defined('IQB_POLICY_ENDORSEMENT_TYPE_GENERAL')  			OR define('IQB_POLICY_ENDORSEMENT_TYPE_GENERAL',    		3);
defined('IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER')	OR define('IQB_POLICY_ENDORSEMENT_TYPE_OWNERSHIP_TRANSFER', 4);
defined('IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE')  	OR define('IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_UPGRADE',    5);
defined('IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND')  		OR define('IQB_POLICY_ENDORSEMENT_TYPE_PREMIUM_REFUND',    	6);
defined('IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE')  			OR define('IQB_POLICY_ENDORSEMENT_TYPE_TERMINATE',    		7);


// @TODO : TO DEL
defined('IQB_POLICY_TXN_TYPE_ET')       OR define('IQB_POLICY_TXN_TYPE_ET',     	3); // ENDORSEMENT TRANSACTIONAL
defined('IQB_POLICY_TXN_TYPE_EG')       OR define('IQB_POLICY_TXN_TYPE_EG',     	4); // ENDORSEMENT GENERAL
defined('IQB_POLICY_TXN_TYPE_EC')       OR define('IQB_POLICY_TXN_TYPE_EC',     	5); // ENDORSEMENT CANCEL/REFUND

/*
|--------------------------------------------------------------------------
| POLICY ENDORSEMENT COMPUTATION BASIS CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_POLICY_ENDORSEMENT_CB_ANNUAL')     OR define('IQB_POLICY_ENDORSEMENT_CB_ANNUAL',   1);
defined('IQB_POLICY_ENDORSEMENT_CB_STR')    	OR define('IQB_POLICY_ENDORSEMENT_CB_STR',  	2); // STR = SHORT TERM RATE
defined('IQB_POLICY_ENDORSEMENT_CB_PRORATA')    OR define('IQB_POLICY_ENDORSEMENT_CB_PRORATA', 	3);


/*
|--------------------------------------------------------------------------
| POLICY ENDORSEMENT - SHORT TERM COMPUTATION CONFIG
|--------------------------------------------------------------------------
|
*/
define('IQB_POLICY_ENDORSEMENT_SPR_CONFIG_BOTH',       1);
define('IQB_POLICY_ENDORSEMENT_SPR_CONFIG_BASIC',      2);


/*
|--------------------------------------------------------------------------
| POLICY INSTALLMENT STATUS CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_POLICY_INSTALLMENT_STATUS_DRAFT')          OR define('IQB_POLICY_INSTALLMENT_STATUS_DRAFT',        'D');
defined('IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED')    	OR define('IQB_POLICY_INSTALLMENT_STATUS_VOUCHERED',  	'V');
defined('IQB_POLICY_INSTALLMENT_STATUS_INVOICED')    	OR define('IQB_POLICY_INSTALLMENT_STATUS_INVOICED',  	'I');
defined('IQB_POLICY_INSTALLMENT_STATUS_PAID')         	OR define('IQB_POLICY_INSTALLMENT_STATUS_PAID',       	'P');

/*
|--------------------------------------------------------------------------
| POLICY INSTALLMENT TYPE CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_POLICY_INSTALLMENT_TYPE_INVOICE_TO_CUSTOMER') OR define('IQB_POLICY_INSTALLMENT_TYPE_INVOICE_TO_CUSTOMER', 'I');
defined('IQB_POLICY_INSTALLMENT_TYPE_REFUND_TO_CUSTOMER') OR define('IQB_POLICY_INSTALLMENT_TYPE_REFUND_TO_CUSTOMER',  	'R');



/*
|--------------------------------------------------------------------------
| POLICY OBJECT CONSTANTS - MOTOR
|--------------------------------------------------------------------------
*/

// OBJECT OWNERSHIP
defined('IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_GOVT')       OR define('IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_GOVT',      'G');
defined('IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT')   OR define('IQB_POLICY_OBJECT_MOTOR_OWNERSHIP_NON_GOVT',   'N');

// POLICY PACKAGES - Third Party | Comprehensive
defined('IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE')       OR define('IQB_POLICY_PACKAGE_MOTOR_COMPREHENSIVE',    'CP');
defined('IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY')         OR define('IQB_POLICY_PACKAGE_MOTOR_THIRD_PARTY',      'TP');

// POLICY PACKAGE - NOT APPLICABLE
defined('IQB_POLICY_PACKAGE_NOT_APPLICABLE')         OR define('IQB_POLICY_PACKAGE_NOT_APPLICABLE',      'NA');


/*
|--------------------------------------------------------------------------
| RI CONSTANTS
|--------------------------------------------------------------------------
*/

// ACCOUNT BASIC TYPES
defined('IQB_RI_SETUP_AC_BASIC_TYPE_AY')    OR define('IQB_RI_SETUP_AC_BASIC_TYPE_AY',	1);
defined('IQB_RI_SETUP_AC_BASIC_TYPE_LOY')   OR define('IQB_RI_SETUP_AC_BASIC_TYPE_LOY', 2);
defined('IQB_RI_SETUP_AC_BASIC_TYPE_UWY')	OR define('IQB_RI_SETUP_AC_BASIC_TYPE_UWY', 3);
defined('IQB_RI_SETUP_AC_BASIC_TYPES')    	OR define('IQB_RI_SETUP_AC_BASIC_TYPES', 	[
	IQB_RI_SETUP_AC_BASIC_TYPE_AY 	=> 'Accounting Year (Clean cut basis)',
	IQB_RI_SETUP_AC_BASIC_TYPE_LOY 	=> 'Loss Occuring Year',
	IQB_RI_SETUP_AC_BASIC_TYPE_UWY 	=> 'Under Writing Year'
]);

// TREATY TYPES (Must Match with Database)
defined('IQB_RI_TREATY_TYPE_SP')    OR define('IQB_RI_TREATY_TYPE_SP',	1);
defined('IQB_RI_TREATY_TYPE_QT')    OR define('IQB_RI_TREATY_TYPE_QT',	2);
defined('IQB_RI_TREATY_TYPE_QS')    OR define('IQB_RI_TREATY_TYPE_QS',	3);
defined('IQB_RI_TREATY_TYPE_EOL')   OR define('IQB_RI_TREATY_TYPE_EOL',	4);
defined('IQB_RI_TREATY_TYPES')    	OR define('IQB_RI_TREATY_TYPES', 	[
	IQB_RI_TREATY_TYPE_SP 	=> 'Surplus',
	IQB_RI_TREATY_TYPE_QT 	=> 'Quota Share',
	IQB_RI_TREATY_TYPE_QS 	=> 'Quota Share & Surplus',
	IQB_RI_TREATY_TYPE_EOL 	=> 'Excess of Loss',
]);

// TREATY TYPES FOR POOL TREATIES
defined('IQB_RI_TREATY_TYPES_POOL')    	OR define('IQB_RI_TREATY_TYPES_POOL', 	[
	IQB_RI_TREATY_TYPE_SP 	=> 'Surplus',
	IQB_RI_TREATY_TYPE_QT 	=> 'Quota Share',
	IQB_RI_TREATY_TYPE_QS 	=> 'Quota Share & Surplus'
]);

// RI TRANSACTIONS - PREMIUM TYPE CONSTATNS
defined('IQB_RI_TRANSACTION_PREMIUM_TYPE_BASIC')  OR define('IQB_RI_TRANSACTION_PREMIUM_TYPE_BASIC', 1);
defined('IQB_RI_TRANSACTION_PREMIUM_TYPE_POOL')   OR define('IQB_RI_TRANSACTION_PREMIUM_TYPE_POOL',	 2);
defined('IQB_RI_TRANSACTION_PREMIUM_TYPES')  OR define('IQB_RI_TRANSACTION_PREMIUM_TYPES', 	[
	IQB_RI_TRANSACTION_PREMIUM_TYPE_BASIC 	=> 'Basic',
	IQB_RI_TRANSACTION_PREMIUM_TYPE_POOL 	=> 'Pool'
]);


/*
|--------------------------------------------------------------------------
| CLAIM STATUS CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_CLAIM_STATUS_DRAFT')           OR define('IQB_CLAIM_STATUS_DRAFT',        	'D');
defined('IQB_CLAIM_STATUS_VERIFIED')    	OR define('IQB_CLAIM_STATUS_VERIFIED',  	'V');
defined('IQB_CLAIM_STATUS_APPROVED')    	OR define('IQB_CLAIM_STATUS_APPROVED',  	'A');
defined('IQB_CLAIM_STATUS_SETTLED')    		OR define('IQB_CLAIM_STATUS_SETTLED',  		'S');
defined('IQB_CLAIM_STATUS_WITHDRAWN')       OR define('IQB_CLAIM_STATUS_WITHDRAWN',     'W');
defined('IQB_CLAIM_STATUS_CLOSED')         	OR define('IQB_CLAIM_STATUS_CLOSED',       	'C');

// CLAIM - SURVEYOR VOUCHER FLAG
defined('IQB_CLAIM_FLAG_SRV_VOUCHER_NOT_REQUIRED')  OR define('IQB_CLAIM_FLAG_SRV_VOUCHER_NOT_REQUIRED', 	0);
defined('IQB_CLAIM_FLAG_SRV_VOUCHER_REQUIRED')  	OR define('IQB_CLAIM_FLAG_SRV_VOUCHER_REQUIRED', 		1);
defined('IQB_CLAIM_FLAG_SRV_VOUCHER_VOUCHERED')  	OR define('IQB_CLAIM_FLAG_SRV_VOUCHER_VOUCHERED', 		2);





/*
|--------------------------------------------------------------------------
| VOUCHER TYPE CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_AC_VOUCHER_TYPE_PRI')    			OR define('IQB_AC_VOUCHER_TYPE_PRI',	1);
defined('IQB_AC_VOUCHER_TYPE_RCPT')    			OR define('IQB_AC_VOUCHER_TYPE_RCPT',	2);
defined('IQB_AC_VOUCHER_TYPE_PMNT')    			OR define('IQB_AC_VOUCHER_TYPE_PMNT',	3);
defined('IQB_AC_VOUCHER_TYPE_JRNL')    			OR define('IQB_AC_VOUCHER_TYPE_JRNL',	4);
defined('IQB_AC_VOUCHER_TYPE_CNTR')    			OR define('IQB_AC_VOUCHER_TYPE_CNTR',	5);
defined('IQB_AC_VOUCHER_TYPE_CRDN')    			OR define('IQB_AC_VOUCHER_TYPE_CRDN',	6);
defined('IQB_AC_VOUCHER_TYPE_GINV')    			OR define('IQB_AC_VOUCHER_TYPE_GINV',	7);
defined('IQB_AC_VOUCHER_TYPE_PUR')    			OR define('IQB_AC_VOUCHER_TYPE_PUR',	8);
defined('IQB_AC_VOUCHER_TYPE_PURRTN')    		OR define('IQB_AC_VOUCHER_TYPE_PURRTN',	9);


/*
|--------------------------------------------------------------------------
| POLICY-VOUCHER-RELATION REFERENCE CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_REL_POLICY_VOUCHER_REF_PI') OR define('IQB_REL_POLICY_VOUCHER_REF_PI',	'PI'); // Policy Installment
defined('IQB_REL_POLICY_VOUCHER_REF_CLM') OR define('IQB_REL_POLICY_VOUCHER_REF_CLM',	'CLM'); // Claim
defined('IQB_REL_POLICY_VOUCHER_REFERENCES')  OR define('IQB_REL_POLICY_VOUCHER_REFERENCES', 	[
	IQB_REL_POLICY_VOUCHER_REF_PI 	=> 'Policy Installment',
	IQB_REL_POLICY_VOUCHER_REF_CLM 	=> 'Claim'
]);

/*
|--------------------------------------------------------------------------
| POLICY-VOUCHER-RELATION INVOICE FLAG CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_FLAG_INVOICED__NO') 			OR define('IQB_FLAG_INVOICED__NO',	0); // Not Invoiced yet
defined('IQB_FLAG_INVOICED__YES') 			OR define('IQB_FLAG_INVOICED__YES',	1); // Invoiced
defined('IQB_FLAG_INVOICED__NOT_REQUIRED') 	OR define('IQB_FLAG_INVOICED__NOT_REQUIRED',	2); // NOT Required




/*
|--------------------------------------------------------------------------
| ACCOUNT CONSTANTS
|--------------------------------------------------------------------------
*/

// ACCOUNT DUTY AND TAX IDS
defined('IQB_AC_DNT_ID_VAT')    			OR define('IQB_AC_DNT_ID_VAT',	1);
defined('IQB_AC_DNT_ID_VAT_ON_PURCHASE')   	OR define('IQB_AC_DNT_ID_VAT_ON_PURCHASE',	2);
defined('IQB_AC_DNT_ID_TDS_ON_AC')   		OR define('IQB_AC_DNT_ID_TDS_ON_AC',	3); // TDS on Agent Commission
defined('IQB_AC_DNT_ID_SC_BSRP')   			OR define('IQB_AC_DNT_ID_SC_BSRP',	4); // Service Charge - Beema Samiti on Regular Policy
defined('IQB_AC_DNT_ID_SC_BSIP')   			OR define('IQB_AC_DNT_ID_SC_BSIP',	5); // Service Charge - Beema Samiti on Inward Policy
defined('IQB_AC_DNT_ID_TDS_ON_SFVR')   		OR define('IQB_AC_DNT_ID_TDS_ON_SFVR',	6); // TDS on Surveyor Fee - Vat Registered
defined('IQB_AC_DNT_ID_TDS_ON_SFVNR')   	OR define('IQB_AC_DNT_ID_TDS_ON_SFVNR',	7); // TDS on Surveyor Fee - Vat Not Registered


// ACCOUNT PARTY TYPES
defined('IQB_AC_PARTY_TYPE_AGENT') 					OR define('IQB_AC_PARTY_TYPE_AGENT',	'A');
defined('IQB_AC_PARTY_TYPE_CUSTOMER') 				OR define('IQB_AC_PARTY_TYPE_CUSTOMER',	'C');
defined('IQB_AC_PARTY_TYPE_COMPANY') 				OR define('IQB_AC_PARTY_TYPE_COMPANY',	'M');
defined('IQB_AC_PARTY_TYPE_SURVEYOR') 				OR define('IQB_AC_PARTY_TYPE_SURVEYOR',	'S');
defined('IQB_AC_PARTY_TYPE_GENERAL') 				OR define('IQB_AC_PARTY_TYPE_GENERAL',	'P');
defined('IQB_AC_PARTY_TYPES')    	OR define('IQB_AC_PARTY_TYPES', 	[
	IQB_AC_PARTY_TYPE_GENERAL				=> 'General Party',
	IQB_AC_PARTY_TYPE_AGENT					=> 'Agent',
	IQB_AC_PARTY_TYPE_CUSTOMER				=> 'Customer',
	IQB_AC_PARTY_TYPE_COMPANY				=> 'Company',
	IQB_AC_PARTY_TYPE_SURVEYOR				=> 'Surveyor'
]);


// PAYMENT RECEIVING MODES
defined('IQB_AC_PAYMENT_RECEIPT_MODE_CASH') 	OR define('IQB_AC_PAYMENT_RECEIPT_MODE_CASH',	'C');
defined('IQB_AC_PAYMENT_RECEIPT_MODE_CHEQUE') 	OR define('IQB_AC_PAYMENT_RECEIPT_MODE_CHEQUE',	'Q');
defined('IQB_AC_PAYMENT_RECEIPT_MODE_DRAFT') 	OR define('IQB_AC_PAYMENT_RECEIPT_MODE_DRAFT',	'D');
defined('IQB_AC_PAYMENT_RECEIPT_MODES')    	OR define('IQB_AC_PAYMENT_RECEIPT_MODES', 	[
	IQB_AC_PAYMENT_RECEIPT_MODE_CASH		=> 'Cash',
	IQB_AC_PAYMENT_RECEIPT_MODE_CHEQUE		=> 'Cheque',
	IQB_AC_PAYMENT_RECEIPT_MODE_DRAFT		=> 'Draft'
]);


// DEBIT/CREDIT CONSTANTS
defined('IQB_AC_FLAG_DEBIT') 	OR define('IQB_AC_FLAG_DEBIT',	'D');
defined('IQB_AC_FLAG_CREDIT') 	OR define('IQB_AC_FLAG_CREDIT',	'C');

/**
 * ACCOUNT GROUP ID - FOR PORTFOLIO SPECIFIC ACCOUNT DROPDOWN
 */
// Direct Premium Income (Direct premium income portfolio-wise)
defined('IQB_AC_ACCOUNT_GROUP_ID_DIRECT_PREMIUM_INCOME')  OR define('IQB_AC_ACCOUNT_GROUP_ID_DIRECT_PREMIUM_INCOME',	121);

// Premium Ceded (Treaty/FAC premium ceded portfolio-wise)
defined('IQB_AC_ACCOUNT_GROUP_ID_PREMIUM_CEDED') OR define('IQB_AC_ACCOUNT_GROUP_ID_PREMIUM_CEDED',	120);

// Reinsurance Commission Income (RI Treaty/FAC Commossion portfolio wise)
defined('IQB_AC_ACCOUNT_GROUP_ID_RCI') OR define('IQB_AC_ACCOUNT_GROUP_ID_RCI',	93);

// Reinsurance Premium Income (FAC Premium Portfolio-wise)
defined('IQB_AC_ACCOUNT_GROUP_ID_REINSURANCE_PREMIUM_INCOME') OR define('IQB_AC_ACCOUNT_GROUP_ID_REINSURANCE_PREMIUM_INCOME',	122);

//  Reinsurance Commission Expense (FAC commission Portfolio-wise)
defined('IQB_AC_ACCOUNT_GROUP_ID_RCE') OR define('IQB_AC_ACCOUNT_GROUP_ID_RCE',	102);

// Receivable From Reinsurer (Portfolio Withdrawl - Portfolio-wise, Portfolio Claim - Portfolio-wise)
defined('IQB_AC_ACCOUNT_GROUP_ID_RECEIVABLE_FROM_REINSURER') OR define('IQB_AC_ACCOUNT_GROUP_ID_RECEIVABLE_FROM_REINSURER',	32);

// Payable to Reinsurer (Portfolio Entry - Portfolio-wise)
defined('IQB_AC_ACCOUNT_GROUP_ID_PAYABLE_TO_REINSURER') OR define('IQB_AC_ACCOUNT_GROUP_ID_PAYABLE_TO_REINSURER',	124);

//  Claim Expense (Claim Expense Portfolio-wise)
defined('IQB_AC_ACCOUNT_GROUP_ID_CLAIM_EXPENSE') OR define('IQB_AC_ACCOUNT_GROUP_ID_CLAIM_EXPENSE',	101);


/**
 * Default Internal Account ID
 */
defined('IQB_AC_ACCOUNT_ID_INSURED_PARTY') 					OR define('IQB_AC_ACCOUNT_ID_INSURED_PARTY',				1);
defined('IQB_AC_ACCOUNT_ID_EXPENSE_BS_SERVICE_CHARGE') 		OR define('IQB_AC_ACCOUNT_ID_EXPENSE_BS_SERVICE_CHARGE', 	2);
defined('IQB_AC_ACCOUNT_ID_VAT_PAYABLE') 					OR define('IQB_AC_ACCOUNT_ID_VAT_PAYABLE',					3);
defined('IQB_AC_ACCOUNT_ID_STAMP_INCOME') 					OR define('IQB_AC_ACCOUNT_ID_STAMP_INCOME',					4);
defined('IQB_AC_ACCOUNT_ID_LIABILITY_BS_SERVICE_CHARGE') 	OR define('IQB_AC_ACCOUNT_ID_LIABILITY_BS_SERVICE_CHARGE', 	5);
defined('IQB_AC_ACCOUNT_ID_AGENCY_COMMISSION') 				OR define('IQB_AC_ACCOUNT_ID_AGENCY_COMMISSION', 			6);
defined('IQB_AC_ACCOUNT_ID_TDS_AGENCY_COMMISSION') 			OR define('IQB_AC_ACCOUNT_ID_TDS_AGENCY_COMMISSION', 		7);
defined('IQB_AC_ACCOUNT_ID_AGENCY_COMMISSION_PAYABLE') 		OR define('IQB_AC_ACCOUNT_ID_AGENCY_COMMISSION_PAYABLE', 	8);
defined('IQB_AC_ACCOUNT_ID_COLLECTION') 					OR define('IQB_AC_ACCOUNT_ID_COLLECTION', 					9);
defined('IQB_AC_ACCOUNT_ID_SERVICE_CHARGE_RECOVERY') 		OR define('IQB_AC_ACCOUNT_ID_SERVICE_CHARGE_RECOVERY', 		10);
defined('IQB_AC_ACCOUNT_ID_TDS_REINSURANCE') 				OR define('IQB_AC_ACCOUNT_ID_TDS_REINSURANCE',				11);
defined('IQB_AC_ACCOUNT_ID_PAYABLE_TO_REINSURER') 			OR define('IQB_AC_ACCOUNT_ID_PAYABLE_TO_REINSURER',			12);
defined('IQB_AC_ACCOUNT_ID_BS_AGR_PREMIUM_RECEIVABLE') 		OR define('IQB_AC_ACCOUNT_ID_BS_AGR_PREMIUM_RECEIVABLE',	13);

defined('IQB_AC_ACCOUNT_ID_SERVICE_CHARGE_REIMBURSED') 		OR define('IQB_AC_ACCOUNT_ID_SERVICE_CHARGE_REIMBURSED',	14);
defined('IQB_AC_ACCOUNT_ID_RECEIVABLE_FROM_LOCAL_INSURER') 	OR define('IQB_AC_ACCOUNT_ID_RECEIVABLE_FROM_LOCAL_INSURER',15);
defined('IQB_AC_ACCOUNT_ID_SURVEYOR_PARTY') 				OR define('IQB_AC_ACCOUNT_ID_SURVEYOR_PARTY',				16);
defined('IQB_AC_ACCOUNT_ID_CLAIM_PARTY') 					OR define('IQB_AC_ACCOUNT_ID_CLAIM_PARTY',					17);
defined('IQB_AC_ACCOUNT_ID_TDS_SURVEYOR') 					OR define('IQB_AC_ACCOUNT_ID_TDS_SURVEYOR',					18);
defined('IQB_AC_ACCOUNT_ID_PORTFOLIO_WITHDRAWL_ENTRY') 		OR define('IQB_AC_ACCOUNT_ID_PORTFOLIO_WITHDRAWL_ENTRY',	19);
defined('IQB_AC_ACCOUNT_ID_OWNERSHIP_TRANSFER_CHARGE') 		OR define('IQB_AC_ACCOUNT_ID_OWNERSHIP_TRANSFER_CHARGE',	20);




/*
|--------------------------------------------------------------------------
| BEEMA SAMITI REPORT CATEGORIES CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_BS_REPORT_CATEGORY_UW',  'UW'); // UNDERWRITING
define('IQB_BS_REPORT_CATEGORY_CL',  	'CL'); // CLAIM
define('IQB_BS_REPORT_CATEGORY_RI',  	'RI'); // RI
defined('IQB_BS_REPORT_CATEGORIES')    	OR define('IQB_BS_REPORT_CATEGORIES', 	[
	IQB_BS_REPORT_CATEGORY_UW	=> 'Underwriting',
	IQB_BS_REPORT_CATEGORY_CL	=> 'Claim',
	IQB_BS_REPORT_CATEGORY_RI	=> 'Re-Insurance',
]);

/*
|--------------------------------------------------------------------------
| REPORT TYPES CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_REPORT_TYPE_QUARTELRY',  'Q');
define('IQB_REPORT_TYPE_MONTHLY',  	'M');
defined('IQB_REPORT_TYPES')    	OR define('IQB_REPORT_TYPES', 	[
	IQB_REPORT_TYPE_MONTHLY		=> 'Monthly',
	IQB_REPORT_TYPE_QUARTELRY	=> 'Quarterly',
]);

