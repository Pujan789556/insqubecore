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
 *      <strong>Contact Name</strong> *
 *      address1
 *      address2
 *      city, state, zip
 *      country
 *
 *      Tel:
 *      Fax:
 *      Mobile:
 *      Email:
 *      Web:
 */
// echo '<pre>'; print_r($address_record); echo '</pre>';
?>
<?php if($address_record):?>
    <address class="no-margin-b">
        <?php
        echo "<p>";
            $contact_data = [];

            // Address1
            $contact_data[] = $address_record->alt_address1_text ? $address_record->alt_address1_text : $address_record->address1_en;

            // Address 2
            $contact_data[] = $address_record->address2 ?? NULL;
            echo implode('<br/>', $contact_data) . '<br/>';

            // City
            $city = $address_record->city ?? NULL;


            $zip_postal_code = $address_record->zip_postal_code ?? NULL;
            $ct_state_zip = array_filter([$city, $zip_postal_code]);
            echo $ct_state_zip ? implode(', ', $ct_state_zip) . '<br/>' : '';

            // State
            $state = $address_record->alt_state_text ? $address_record->alt_state_text : $address_record->state_name_en;
            echo implode(', ', [$state, $address_record->country_name]);
        echo "</p>";

        // phones, fax, mobile, web, email
        $phones = $address_record->phones ?? NULL;
        $faxes = $address_record->faxes ?? NULL;
        $mobile = $address_record->mobile ?? NULL;
        $web = $address_record->web ?? NULL;
        $email = $address_record->email ?? NULL;


        if( $phones || $faxes || $mobile || $web || $email)
        {
            // echo '<hr class="hr-medium"/>';
        }

        // Phones
        if($phones)
        {
            // remove extra spaces from each phone numbers
            $phones = array_map( function($phone) use($plain_text){
                $phone = trim($phone);
                return $plain_text ? $phone : '<a href="tel:'.$phone.'">'.$phone.'</a>';
            }, explode(',', $phones));

            echo '<p class="margin-b-5"><i class="fa fa-phone margin-r-5"></i>' . implode(' | ', $phones) . '</p>';
        }

        // Fax
        if($faxes)
        {
            // remove extra spaces
            $faxes = array_map(function($number) use($plain_text){
                $number = trim($number);
                return $plain_text ? $number : '<a href="fax:'.$number.'">'.$number.'</a>';
            }, explode(',', $faxes));

            echo '<p class="margin-b-5"><i class="fa fa-fax margin-r-5"></i>' . implode(' | ', $faxes) . '</p>';
        }

        // Mobile
        if($mobile){

            $mobile_text = $plain_text ? $mobile : '<a href="tel:'.$mobile.'" target="_blank">'.$mobile.'</a>';
            echo '<p class="margin-b-5"><i class="fa fa-phone-square margin-r-5"></i>' . $mobile_text . '</p>';
        }

        // Email
        if($email){
            $email_text = $plain_text ? $email : mailto($email);
            echo '<p class="margin-b-5"><i class="fa fa-envelope-o margin-r-5"></i>' . $email_text . '</p>';
        }

        // Web
        if($web){
            $web_text = $plain_text ? $web : anchor($web, '', 'target="_blank"');
            echo '<p class="margin-b-5"><i class="fa fa-link margin-r-5"></i>' . $web_text . '</p>';
        }
        ?>
    </address>
<?php endif?>