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
    $a1_name_col = "address1_{$lang}";
    $addr1 = $address_record->alt_address1_text ? $address_record->alt_address1_text : $address_record->{$a1_name_col};
    $addr2 = NULL;
    // Address 2
    if($address_record->address2){
        $addr2 = $address_record->address2;
    }

    // address1, address2
    $contact_data[] = implode(', ', array_filter([$addr1, $addr2]));

    // City, state, zip
    $state_name_col = "state_name_{$lang}";
    $city   = $address_record->city ?? NULL;
    $state  = $address_record->alt_state_text ? $address_record->alt_state_text : $address_record->{$state_name_col};
    $zip_postal_code = $address_record->zip_postal_code ?? NULL;
    $ct_state_zip = array_filter([$city, $state, $zip_postal_code]);

    $contact_data[] = implode(', ', $ct_state_zip);

    // Phone, Fax, Email
    // फोन फ्यक्स ईमेल मोबाईल
    if($address_record->phones){
        $label = $lang == 'np' ? 'फोन' : 'Phone';
        $contact_data[] = "{$label}: {$address_record->phones}";
    }

    if($address_record->faxes){
        $label = $lang == 'np' ? 'फ्यक्स' : 'Fax';
        $contact_data[] = "{$label}: {$address_record->faxes}";
    }

    if($address_record->mobile){
        $label = $lang == 'np' ? 'मोबाईल' : 'Mobile';
        $contact_data[] = "{$label}: {$address_record->mobile}";
    }

    if($address_record->email){
        $label = $lang == 'np' ? 'ईमेल' : 'Email';
        $contact_data[] = "{$label}: {$address_record->email}";
    }

    // Country
    // $contact_data[] = $address_record->country_name;

    echo implode('<br/>', $contact_data);
}