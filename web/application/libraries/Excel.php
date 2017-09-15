<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  =======================================
 *  Author     : Muhammad Surya Ikhsanudin
 *  License    : Protected
 *  Email      : mutofiyah@gmail.com
 *
 *  Dilarang merubah, mengganti dan mendistribusikan
 *  ulang tanpa sepengetahuan Author
 *
 *  Reference: http://www.ahowto.net/php/easily-integrateload-phpexcel-into-codeigniter-framework/
 *
 *  =======================================
 */
require_once APPPATH . "/third_party/PHPExcel-1.8.1/PHPExcel.php";

class Excel extends PHPExcel
{
	public function __construct()
	{
		parent::__construct();
	}
}