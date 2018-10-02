<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Premium Component - Premiium Table
 *
 * Having both Calculation and Summary Table
 *
 * English & Nepali
 */
/**
 * Load Cost Calculation Details
 */
$this->load->view('endorsements/snippets/premium/_calculation_table', [
    'lang'               => $lang,
    'endorsement_record' => $endorsement_record
]);

/**
 * Load Cost Summary
 */
$this->load->view('endorsements/snippets/premium/_summary_table', [
    'lang'               => $lang,
    'endorsement_record' => $endorsement_record
]);
?>
