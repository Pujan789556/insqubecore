<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Contact Snippet Widget
 *
 * 	Required Variables:
 * 		$address_record   OBJECT
 *
 * Address Format:
 *
 *      Address1,  address2,  city, state, zip, country
 *      Tel: ..., Fax: ..., Mobile: ..., Email: ..., Web: ....
 */
?>
<?php
if($address_record)
{
    $line1   = [$prefix];
    $line1[] = $address_record->alt_address1_text ? $address_record->alt_address1_text : $address_record->address1_en;
    $line1[] = $address_record->address2 ?? NULL;
    $line1[] = $address_record->city ?? NULL;
    $line1[] = $address_record->alt_state_text ? $address_record->alt_state_text : $address_record->state_name_en;
    $line1[] = $address_record->zip_postal_code ?? NULL;
    $line1[] = $address_record->country_name;

    $line1 = array_filter($line1);

    echo '<p>' . implode(', ', $line1) . '</p>';

    $line2 = [];
    $line2[] = isset($address_record->phones) && !empty($address_record->phones) ? 'T: ' . $address_record->phones : NULL;
    $line2[] = isset($address_record->faxes) && !empty($address_record->faxes) ? 'F: ' . $address_record->faxes : NULL;
    $line2[] = isset($address_record->mobile) && !empty($address_record->mobile) ? 'M: ' . $address_record->mobile : NULL;
    $line2[] = isset($address_record->web) && !empty($address_record->web) ? 'W: ' . $address_record->web : NULL;
    $line2[] = isset($address_record->email) && !empty($address_record->email) ? 'E: ' . $address_record->email : NULL;

    $line2 = array_filter($line2);
    if(!empty($line2))
    {
        echo '<p>' . implode(', ', $line2) . '</p>';
    }
}
?>