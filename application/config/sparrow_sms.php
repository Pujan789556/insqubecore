<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your CodeIgniter root. Typically this will be your base URL,
| WITH a trailing slash:
|
|	http://example.com/
|
| If this is not set then CodeIgniter will guess the protocol, domain and
| path to your installation.
|
*/
$config['sparrow_sms_server']	= 'http://api.sparrowsms.com/v2/sms/';

/*
|--------------------------------------------------------------------------
| TOKEN & IDENTITY
|--------------------------------------------------------------------------
|
| Typically this will be your index.php file, unless you've renamed it to
| something else. If you are using mod_rewrite to remove the page set this
| variable so that it is blank.
|
*/
$config['sparrow_sms_token'] 		= 'bfkm5TcLVsi7D1W27UoF';
$config['sparrow_sms_identity'] 		= 'NECO';
