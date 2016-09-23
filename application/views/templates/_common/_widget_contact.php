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
            echo $contact->address1;
            echo $contact->address2 ? '<br/>' . $contact->address2  : '';

            $ct_state_zip = array_filter([$contact->city, $contact->state, $contact->zip]);

            echo implode(', ', $ct_state_zip) . '<br/>';
            echo get_country_name($contact->country);
           
           echo '<hr/>';

            // Phones
            if($contact->phones)
            {
                // remove extra spaces from each phone numbers
                $phones = array_map(function($phone){
                    $phone = trim($phone);
                    return '<a href="tel:'.$phone.'">'.$phone.'</a>';
                }, explode(',', $contact->phones));

                echo '<p><i class="fa fa-phone margin-r-5"></i>' . implode(' | ', $phones) . '</p>';
            }

            // Fax
            if($contact->fax)
            {
                // remove extra spaces
                $fax = array_map(function($number){
                    $number = trim($number);
                    return '<a href="fax:'.$number.'">'.$number.'</a>';
                }, explode(',', $contact->fax));

                echo '<p><i class="fa fa-fax margin-r-5"></i>' . implode(' | ', $fax) . '</p>';
            }

            // Mobile
            if($contact->mobile){
                echo '<p><i class="fa fa-phone margin-r-5"></i>' . '<a href="tel:'.$contact->mobile.'" target="_blank">'.$contact->mobile.'</a></p>';
            }

            // Email
            if($contact->email){
                echo '<p><i class="fa fa-envelope-o margin-r-5"></i>' . mailto($contact->email) . '</p>';
            }

            // Web
            if($contact->web){
                echo '<p><i class="fa fa-link margin-r-5"></i>' . anchor($contact->web, '', 'target="_blank"') . '</p>';
            }
            ?>
        </address>
    <?php endif?>
</div>