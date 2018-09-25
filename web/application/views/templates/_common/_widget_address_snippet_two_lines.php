<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Contact Snippet Widget
 *
 * 	Required Variables:
 * 		$record   OBJECT
 *
 * Address Format:
 *
 *      Address1,  address2,  city, state, zip, country
 *      Tel: ..., Fax: ..., Mobile: ..., Email: ..., Web: ....
 */
?>
<?php
if($record)
{
    echo "<p>";
        $line1   = [$prefix];
        $line1[] = $record->alt_address1_text ? $record->alt_address1_text : $record->address1_en;
        $line1[] = $record->address2 ?? NULL;
        $line1[] = $record->city ?? NULL;
        $line1[] = $record->alt_state_text ? $record->alt_state_text : $record->state_name_en;
        $line1[] = $record->zip_postal_code ?? NULL;
        $line1[] = $record->country_name;

        $line1 = array_filter($line1);

        echo implode(', ', $line1);
    echo "</p>";

    $line2 = [];
    $line2[] = isset($record->phones) && !empty($record->phones) ? 'T: ' . $record->phones : NULL;
    $line2[] = isset($record->faxes) && !empty($record->faxes) ? 'F: ' . $record->faxes : NULL;
    $line2[] = isset($record->mobile) && !empty($record->mobile) ? 'M: ' . $record->mobile : NULL;
    $line2[] = isset($record->web) && !empty($record->web) ? 'W: ' . $record->web : NULL;
    $line2[] = isset($record->email) && !empty($record->email) ? 'E: ' . $record->email : NULL;

    $line2 = array_filter($line2);
    if(!empty($line2))
    {
        echo '<p>' . implode(', ', $line2) . '</p>';
    }
}
?>