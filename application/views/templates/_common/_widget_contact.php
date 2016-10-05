<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Contact Form
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
<div class="box-header with-border">
    <h3 class="box-title">Contact Address</h3>    
</div>
<div class="box-body">  
    <?php if($contact):?>
        <address>
            <strong><?php echo $contact->contact_name;?></strong><br/>
            <?php 
            $contact_data = [];
            $contact_data[] = $contact->address1 ?? NULL;
            $contact_data[] = $contact->address2 ?? NULL;
            implode('</br/>', $contact_data);

            $city = $contact->city ?? NULL;
            $state = $contact->state ?? NULL;
            $zip = $contact->zip ?? NULL;
            $ct_state_zip = array_filter([$city, $state, $zip]);

            echo $ct_state_zip ? implode(', ', $ct_state_zip) . '<br/>' : '';
            echo get_country_name($contact->country);

            // phones, fax, mobile, web, email
            $phones = $contact->phones ?? NULL;
            $fax = $contact->fax ?? NULL;
            $mobile = $contact->mobile ?? NULL;
            $web = $contact->web ?? NULL;
            $email = $contact->email ?? NULL;

           
            if( $phones || $fax || $mobile || $web || $email)
            {
                echo '<hr/>'; 
            }

            // Phones
            if($phones)
            {
                // remove extra spaces from each phone numbers
                $phones = array_map(function($phone){
                    $phone = trim($phone);
                    return '<a href="tel:'.$phone.'">'.$phone.'</a>';
                }, explode(',', $phones));

                echo '<p><i class="fa fa-phone margin-r-5"></i>' . implode(' | ', $phones) . '</p>';
            }

            // Fax
            if($fax)
            {
                // remove extra spaces
                $fax = array_map(function($number){
                    $number = trim($number);
                    return '<a href="fax:'.$number.'">'.$number.'</a>';
                }, explode(',', $fax));

                echo '<p><i class="fa fa-fax margin-r-5"></i>' . implode(' | ', $fax) . '</p>';
            }

            // Mobile
            if($mobile){
                echo '<p><i class="fa fa-phone margin-r-5"></i>' . '<a href="tel:'.$mobile.'" target="_blank">'.$mobile.'</a></p>';
            }

            // Email
            if($email){
                echo '<p><i class="fa fa-envelope-o margin-r-5"></i>' . mailto($email) . '</p>';
            }

            // Web
            if($web){
                echo '<p><i class="fa fa-link margin-r-5"></i>' . anchor($web, '', 'target="_blank"') . '</p>';
            }
            ?>
        </address>
    <?php endif?>
</div>