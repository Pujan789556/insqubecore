<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Error From API/* ??
 *
 * Check if Error was generated on /api/*
 */
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$uris = explode( '/', parse_url($url, PHP_URL_PATH) ); // returns /uri1/uri2/...

// If first segment is "api", simply return JSON
if( $uris[1] === 'api')
{
	echo json_encode([
		'status'  => FALSE,
		'message' => 'Resource Not Found'
	]);
	exit(0);
}


$funny_headings = [
	// 'कुरो अलि मिलेन जस्तो छ !',
	'हैट कता पो आइपुगिएछ ?',
	// 'मजाक गर्नुको नि सीमा हुन्छ के !',
	// 'लु यो चैं अलि भएन ल !',
	// 'जे मन लाग्यो त्यै गर्ने अनि खोज्या काँ पाइन्छ त !',
	// 'खोज्या कुरो पाइएन भन्या के !'
];
$heading = $funny_headings[array_rand($funny_headings)];

$default_messsage = 'The page you requested was not found.';

if( strip_tags($message) == $default_messsage)
{
	$message = '<p>' .
				'साथी माफ गर्नुहोला। तपाईंले खोजेको कुरो पाइएन जस्तो छ। कताबाट एता आइपुग्नुभो कुन्नि ?' .  '<br/>' .
				'एक फेर IT को साथीहरुलाई यो कुरो पुर्याम न । '. '<br/>' .
				'तेसो गरे कसो होला ?' . '<br/>' . '<br/>' .
				'Dashboard मा जानु पर्ने हो भने <a href="'.APP_URL.'">यहाँँ क्लिक गरम् त</a>  !' . '<br/> <br/>' .
				'धन्यवाद !' .
				'</p>';
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>404 Page Not Found | खोज्या कुरो पाइएन</title>
<style type="text/css">

::selection { background-color: #E13300; color: white; }
::-moz-selection { background-color: #E13300; color: white; }

body {
	background-color: #ecf0f5;
	font: 16px/24px normal Helvetica, Arial, sans-serif;
	color: #eee;
}

a {
	color: #fff;
	background-color: transparent;
	font-weight: bold;
}

h1 {
	background-color: #c23321;
	/*color:#fff;*/
	font-size: 20px;
	font-weight: normal;
	margin: 0 0 20px 0;
	padding: 10px 10px;
	border-radius: 4px 4px 0px 0px;
}

code {
	font-family: Consolas, Monaco, Courier New, Courier, monospace;
	font-size: 12px;
	background-color: #f9f9f9;
	border: 1px solid #D0D0D0;
	color: #002166;
	display: block;
	margin: 14px 0 14px 0;
	padding: 12px 10px 12px 10px;
}
.box-outer {
	width: 100%;
	padding: 0;
	display: table;
	height: 100%;
	position: absolute;
	top: 0;
	left: 0;
	margin: 0;
}

.box-inner {
	padding: 0;
	vertical-align: middle;
	display: table-cell;
	margin: 0;
}

#container {
	margin: 0 auto;
	background-color: #dd4b39;
	/*color:#fff;*/
	/*background-color: #fff;
	color:#dd4b39;*/
	border: 1px solid #D0D0D0;
	box-shadow: 0 0 8px #D0D0D0;
	border-radius: 4px;
}

p {
	margin: 12px 15px 12px 15px;
}
@media(min-width:576px){#container{width:556px}}
@media(min-width:768px){#container{width:748px}}
@media(min-width:992px){#container{width:800px}}
@media(min-width:1200px){#container{width:800px}}
</style>
<!--[if lte IE 7]>
<style>
.box-outer {
	top: 0;
	display: inline-block;
}

.box-inner {
	display: inline-block;
	top: 50%;
	position: relative;
}

#container {
	display: inline-block;
	top: -50%;
	position: relative;
}
</style>
<![endif]-->
</head>
<body>
	<div class="box-outer">
		<div class="box-inner">
			<div id="container">
				<h1>⚠ <?php echo $heading; ?></h1>
				<?php echo $message; ?>
			</div>
		</div>
	</div>
</body>
</html>