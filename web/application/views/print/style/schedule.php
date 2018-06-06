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

    .small{font-size:9pt;}
    .smaller{font-size:8pt;}

    .margin-t-10{margin-top:10px;}
    .text-right{text-align: right}
    .text-left{text-align: left}
    .text-bold{font-weight: bold;}

    table.margin-b-10,.margin-b-10{margin-bottom:10pt;}
    p { margin: 0pt; }

    td { vertical-align: top; padding: 3px; border:0.1mm solid #000000;}
    td.bold{font-weight: bold;}
    td.no-padding{padding: 0}
    .table td.border-top{border-top:0.1mm solid #000000;}
    .table td.bold{font-weight: bold}
    table thead td {
        background-color: #EEEEEE;
        text-align: center;
        border: 0.1mm solid #000000;
        font-variant: small-caps;
    }
    .no-border, td.no-border, table.no-border td, table.no-border th{border:none !important;}
    .border-b, td.border-b{border-bottom: 0.1mm solid #000000 !important;}
    .border-t, td.border-t{border-top: 0.1mm solid #000000 !important;}
    .underline{text-decoration: underline;}

    .table td.cost {
        text-align: "." center;
    }

    .table-footer{font-size:8pt; border: none; color:333; font-style: italic;}
    .table-footer td.border-t{border-top: 0.1mm solid #999;}

    /* -- Receipt Styles --*/
    .receipt-box{border: 0.1mm solid #666; padding:10px;}
    p.receipt-description {font-weight:14px; font-style: italic;}
    p.receipt-description strong{font-size: 16px; font-style: normal;}

</style>