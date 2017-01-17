<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Style Definition : Schedule Print
 */
?>
<style type="text/css">
    body{
        /* -- Default Font (To support Unicode Characters) -- */
        font-family:  halant, freeserif, sourcesanspro;
        font-size: 10pt;
    }
    table{
        margin:0;
        padding:0;
        border-collapse: collapse;
        width: 100%;
    }

    table.margin-b-10{margin-bottom:10pt;}

    p { margin: 0pt; }

    td { vertical-align: top; padding: 3px; border:0.1mm solid #000000;}
    td.no-padding{padding: 0}
    .table td.border-top{border-top:0.1mm solid #000000;}
    .table td.bold{font-weight: bold}
    table thead td {
        background-color: #EEEEEE;
        text-align: center;
        border: 0.1mm solid #000000;
        font-variant: small-caps;
    }
    .border-b{border-bottom: 0.1mm solid #000000;}
    .underline{text-decoration: underline;}
    .no-border, td.no-border, table.no-border td{border:none !important;}
    .table td.cost {
        text-align: "." center;
    }
</style>