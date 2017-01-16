<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Style Definition : Schedule Print
 */
?>
<style type="text/css">
    body{
        /* -- Default Font (To support Unicode Characters) -- */
        font-family: freeserif;
        font-size: 8pt;
    }
    table{
        border-spacing: 0;
        border-collapse: collapse;
    }
    td, th{
        margin:0;
        padding: 0;
        vertical-align: top;
    }
    hr {
        height: 0px;
        margin-top: 10px;
        margin-bottom: 10px;
        border: 1px solid #fff;
    }
    .table{
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }
    .table-bordered {
        border: 1px solid #333;
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #333;
    }
    .table td, .table th {
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border-top: 1px solid #333;
    }
    .table-condensed td, .table-condensed th {
        padding: 5px;
    }
    .no-border th, .no-border td {
        border: none !important;
    }
    .no-padding{padding: 0 !important;}
    .no-border{border: none !important;}
    .no-margin{margin: 0 !important;}
    .table td.border-b, .border-b{border-bottom: 1px solid #999;}
    .table td.border-b-dark, .border-b-dark{border-bottom: 1px solid #333;}

    .table td.totals {
        /*text-align: right;*/
        /*border: 0.1mm solid #000000;*/
        font-weight: bold;
    }
    .table td.cost {
        text-align: "." center;
    }

    .table td.border-top{border-top: 1px solid #aaa;}

</style>