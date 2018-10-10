<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube PDF Class
 *
 * This class is used to instantiate MPDF library, so that we could
 * use it on CodeIgniter Controller wherever necessary.
 *
 * Reference: https://davidsimpson.me/2013/05/19/using-mpdf-with-codeigniter/
 *
 * Usage:
 *
 *         ...
 *
 *        // As PDF creation takes a bit of memory, we're saving the created file in /downloads/reports/
 *        $pdfFilePath = FCPATH."/downloads/reports/$filename.pdf";
 *
 *        $data['page_title'] = 'Hello world'; // pass data to the view
 *        if (file_exists($pdfFilePath) == FALSE)
 *        {
 *            ini_set('memory_limit','32M'); // boost the memory limit if it's low ;)
 *            $html = $this->load->view('pdf_report', $data, true); // render the view into HTML
 *
 *            $this->load->library('pdf');
 *            $pdf = $this->pdf->load();
 *
 *           $pdf->SetFooter($_SERVER['HTTP_HOST'].'|{PAGENO}|'.date(DATE_RFC822)); // Add a footer for good measure ;)
 *            $pdf->WriteHTML($html); // write the HTML into the PDF
 *            $pdf->Output($pdfFilePath, 'F'); // save to file because we can
 *        }
 *
 * @package     InsQube
 * @subpackage  Libraries
 * @category    Libraries
 * @author      IP Bastola <ip.bastola@gmail.com>
 * @link
 */
class Pdf {


    public function __construct()
    {
        $CI = & get_instance();
        log_message('Debug', 'Mpdf class is loaded.');
    }

    function load($config=NULL)
    {
        $default_config = [
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_header' => 9,
            'margin_footer' => 9,
            'orientation' => 'P'
        ];
        if ( $config )
        {
            $default_config = array_merge($default_config, $config);
        }
        // return new mPDF($param);
        return new \Mpdf\Mpdf($default_config);
    }

}
