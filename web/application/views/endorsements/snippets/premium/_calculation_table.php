<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Premium Calculation Table
 *
 * English & Nepali
 */
$cost_calculation_table = json_decode($endorsement_record->cost_calculation_table ?? NULL);
$this->load->view('endorsements/snippets/premium/_calculation_table_inline',[
    'lang'                      => $lang,
    'cost_calculation_table'    => $cost_calculation_table
]);
?>
