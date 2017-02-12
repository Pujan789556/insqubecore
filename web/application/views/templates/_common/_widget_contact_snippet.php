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
?>
<?php if($contact):?>
    <address class="no-margin-b">
        <?php
        echo "<p>";
            $contact_data = [];
            $contact_data[] = $contact->address1 ?? NULL;
            $contact_data[] = $contact->address2 ?? NULL;
            echo implode('<br/>', $contact_data);

            $city = $contact->city ?? NULL;
            $state = $contact->state ?? NULL;
            $zip = $contact->zip ?? NULL;
            $ct_state_zip = array_filter([$city, $state, $zip]);

            echo $ct_state_zip ? implode(', ', $ct_state_zip) . '<br/>' : '';
            echo get_country_name($contact->country);
        echo "</p>";

        // phones, fax, mobile, web, email
        $phones = $contact->phones ?? NULL;
        $fax = $contact->fax ?? NULL;
        $mobile = $contact->mobile ?? NULL;
        $web = $contact->web ?? NULL;
        $email = $contact->email ?? NULL;


        if( $phones || $fax || $mobile || $web || $email)
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
        if($fax)
        {
            // remove extra spaces
            $fax = array_map(function($number) use($plain_text){
                $number = trim($number);
                return $plain_text ? $number : '<a href="fax:'.$number.'">'.$number.'</a>';
            }, explode(',', $fax));

            echo '<p class="margin-b-5"><i class="fa fa-fax margin-r-5"></i>' . implode(' | ', $fax) . '</p>';
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