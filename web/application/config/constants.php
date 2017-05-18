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
| 		1 hour 	= 1 * 60 * 60 	= 3600
| 		1 day  	= 24 * 60 * 60 	= 86400 (default)
| 		1 week 	= 86400 * 7  	= 604800
| 		1 month = 86400 * 30 	= 2592000
|
*/
define('CACHE_DURATION_HR', 3600);
define('CACHE_DURATION_6HRS', 21600);
define('CACHE_DURATION_HALF_DAY', 43200);
define('CACHE_DURATION_DAY', 86400);
define('CACHE_DURATION_WEEK', 604800);
define('CACHE_DURATION_MONTH', 2592000);

/*
|--------------------------------------------------------------------------
| APPLICATION SPECIFIC GENERAL CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_BLANK_SELECT', 	['' => 'Select...']);
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

define('IQB_FLAG_ON',    	1);
define('IQB_FLAG_OFF',		0);

define('IQB_FLAG_LOCKED',	1);
define('IQB_FLAG_UNLOCKED',	0);



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
| PARENT PORTFOLIO CONSTANTS
|--------------------------------------------------------------------------
|
*/
define('IQB_MASTER_PORTFOLIO_AGRICULTURE_ID',  1);
define('IQB_MASTER_PORTFOLIO_ENGINEERING_ID',  2);
define('IQB_MASTER_PORTFOLIO_FIRE_ID',         3);
define('IQB_MASTER_PORTFOLIO_MARINE_ID',       4);
define('IQB_MASTER_PORTFOLIO_MISC_ID',         5);
define('IQB_MASTER_PORTFOLIO_MOTOR_ID',        6);

/*
|--------------------------------------------------------------------------
| MOTOR PORTFOLIO - SUB-PORTFOLIO CODE, ID
|--------------------------------------------------------------------------
|
| WARNING: Should be exactly same as in database, add here if you add in db
|
*/
define('IQB_SUB_PORTFOLIO_MOTORCYCLE_CODE',        	  	'MCY');
define('IQB_SUB_PORTFOLIO_MOTORCYCLE_ID',          		36);
define('IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_CODE',     	'PVC');
define('IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID',     		37);
define('IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_CODE',  	'CVC');
define('IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID',  		38);

// SUB PORTFOLIO LIST
define('IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR',  [
	IQB_SUB_PORTFOLIO_MOTORCYCLE_ID 		=> IQB_SUB_PORTFOLIO_MOTORCYCLE_CODE,
	IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_ID 	=> IQB_SUB_PORTFOLIO_PRIVATE_VEHICLE_CODE,
	IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_ID => IQB_SUB_PORTFOLIO_COMMERCIAL_VEHICLE_CODE
]);



define('IQB_PORTFOLIO_OWNERSHIP_GOVT',      'G');
define('IQB_PORTFOLIO_OWNERSHIP_NON_GOVT',  'N');


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
defined('IQB_POLICY_STATUS_UNVERIFIED')     OR define('IQB_POLICY_STATUS_UNVERIFIED',   'U');
defined('IQB_POLICY_STATUS_VERIFIED')       OR define('IQB_POLICY_STATUS_VERIFIED',     'V');
defined('IQB_POLICY_STATUS_APPROVED')       OR define('IQB_POLICY_STATUS_APPROVED',     'R');
defined('IQB_POLICY_STATUS_INVOICED')       OR define('IQB_POLICY_STATUS_INVOICED',     'I');
defined('IQB_POLICY_STATUS_ACTIVE')         OR define('IQB_POLICY_STATUS_ACTIVE',       'A');
defined('IQB_POLICY_STATUS_CANCELED')       OR define('IQB_POLICY_STATUS_CANCELED',     'C');
defined('IQB_POLICY_STATUS_EXPIRED')        OR define('IQB_POLICY_STATUS_EXPIRED',      'E');

/*
|--------------------------------------------------------------------------
| POLICY TRANSACTION STATUS CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_POLICY_TXN_STATUS_DRAFT')          OR define('IQB_POLICY_TXN_STATUS_DRAFT',        'D');
defined('IQB_POLICY_TXN_STATUS_VERIFIED')       OR define('IQB_POLICY_TXN_STATUS_VERIFIED',     'V');
defined('IQB_POLICY_TXN_STATUS_RI_APPROVED')    OR define('IQB_POLICY_TXN_STATUS_RI_APPROVED',  'P');
defined('IQB_POLICY_TXN_STATUS_ACTIVE')         OR define('IQB_POLICY_TXN_STATUS_ACTIVE',       'A');


/*
|--------------------------------------------------------------------------
| POLICY TRANSACTION TYPES CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_POLICY_TXN_TYPE_FRESH')    OR define('IQB_POLICY_TXN_TYPE_FRESH',      1);
defined('IQB_POLICY_TXN_TYPE_RENEWAL')  OR define('IQB_POLICY_TXN_TYPE_RENEWAL',    2);
defined('IQB_POLICY_TXN_TYPE_ET')       OR define('IQB_POLICY_TXN_TYPE_ET',     	3); // ENDORSEMENT TRANSACTIONAL
defined('IQB_POLICY_TXN_TYPE_EG')       OR define('IQB_POLICY_TXN_TYPE_EG',     	4); // ENDORSEMENT GENERAL


/*
|--------------------------------------------------------------------------
| POLICY COST REFERENCE TRANSFER TYPES CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_POLICY_CRF_TRANSFER_TYPE_FULL')    				OR define('IQB_POLICY_CRF_TRANSFER_TYPE_FULL',      				1);
defined('IQB_POLICY_CRF_TRANSFER_TYPE_PRORATA_ON_DIFF') 		OR define('IQB_POLICY_CRF_TRANSFER_TYPE_PRORATA_ON_DIFF',    		2);
defined('IQB_POLICY_CRF_TRANSFER_TYPE_SHORT_TERM_RATE_ON_FULL') OR define('IQB_POLICY_CRF_TRANSFER_TYPE_SHORT_TERM_RATE_ON_FULL', 	3);
defined('IQB_POLICY_CRF_TRANSFER_TYPE_DIRECT_DIFF')       		OR define('IQB_POLICY_CRF_TRANSFER_TYPE_DIRECT_DIFF',     			4);


/*
|--------------------------------------------------------------------------
| POLICY COST REFERENCE COMPUTATION TYPES CONSTANTS
|--------------------------------------------------------------------------
*/
defined('IQB_POLICY_CRF_COMPUTE_AUTO')    	OR define('IQB_POLICY_CRF_COMPUTE_AUTO', 	1);
defined('IQB_POLICY_CRF_COMPUTE_MANUAL') 	OR define('IQB_POLICY_CRF_COMPUTE_MANUAL',  2);


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
	IQB_RI_SETUP_AC_BASIC_TYPE_AY 	=> 'Accounting Year',
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


/*
|--------------------------------------------------------------------------
| ACCOUNT CONSTANTS
|--------------------------------------------------------------------------
*/

// ACCOUNT DUTY AND TAX IDS
defined('IQB_AC_DUTY_AND_TAX_ID_VAT')    			OR define('IQB_AC_DUTY_AND_TAX_ID_VAT',	1);
defined('IQB_AC_DUTY_AND_TAX_ID_ON_PURCHASE')   	OR define('IQB_AC_DUTY_AND_TAX_ID_ON_PURCHASE',	2);


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


// DEBIT/CREDIT CONSTANTS
defined('IQB_AC_DEBIT') 	OR define('IQB_AC_DEBIT',	'D');
defined('IQB_AC_CREDIT') 	OR define('IQB_AC_CREDIT',	'C');






