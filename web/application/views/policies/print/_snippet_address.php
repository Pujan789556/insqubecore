<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Address
 *
 * 	Required Variables:
 * 		$address_record   OBJECT
 *
 * Address Format:
 *
 *      address1, address2
 *      city, state, zip
 */
if($address_record)
{
    $contact_data = [];

    // Address1
    $addr1 = $address_record->alt_address1_text ? $address_record->alt_address1_text : $address_record->address1_en;
    $addr2 = NULL;
    // Address 2
    if($address_record->address2){
        $addr2 = $address_record->address2;
    }

    // address1, address2
    $contact_data[] = implode(', ', array_filter([$addr1, $addr2]));

    // City, state, zip
    $city   = $address_record->city ?? NULL;
    $state  = $address_record->alt_state_text ? $address_record->alt_state_text : $address_record->state_name_en;
    $zip_postal_code = $address_record->zip_postal_code ?? NULL;
    $ct_state_zip = array_filter([$city, $state, $zip_postal_code]);

    $contact_data[] = implode(', ', $ct_state_zip);

    // Country
    // $contact_data[] = $address_record->country_name;

    echo implode('<br/>', $contact_data);
}