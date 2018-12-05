<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Contact Snippet Widget
 *
 * 	Required Variables:
 * 		$contact   OBJECT
 *
 * Address Format:
 *
 *      Address1,  address2,  city, state, zip, country
 *      Tel: ..., Fax: ..., Mobile: ..., Email: ..., Web: ....
 */
?>
<?php
if($contact)
{
    echo "<p>";
        $line1   = [$prefix];
        $line1[] = $contact->address1 ?? NULL;
        $line1[] = $contact->address2 ?? NULL;
        $line1[] = $contact->city ?? NULL;
        $line1[] = $contact->state ?? NULL;
        $line1[] = $contact->zip ?? NULL;
        $line1[] = get_country_name($contact->country);

        $line1 = array_filter($line1);

        echo implode(', ', $line1);
    echo "</p>";

    $line2 = [];
    $line2[] = isset($contact->phones) && !empty($contact->phones) ? 'T: ' . $contact->phones : NULL;
    $line2[] = isset($contact->fax) && !empty($contact->fax) ? 'F: ' . $contact->fax : NULL;
    $line2[] = isset($contact->mobile) && !empty($contact->mobile) ? 'M: ' . $contact->mobile : NULL;
    $line2[] = isset($contact->web) && !empty($contact->web) ? 'W: ' . $contact->web : NULL;
    $line2[] = isset($contact->email) && !empty($contact->email) ? 'E: ' . $contact->email : NULL;

    $line2 = array_filter($line2);
    if(!empty($line2))
    {
        echo '<p>' . implode(', ', $line2) . '</p>';
    }
}
?>