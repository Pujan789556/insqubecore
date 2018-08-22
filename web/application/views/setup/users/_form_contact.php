<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : User Contact
 */
$hidden = [
    'next_wizard' => $next_wizard ? 1 : 0
];
if (isset($record) )
{
    $hidden['id'] = $record->id;
}
?>
<?php echo form_open( $action_url,
                        [
                            'class' => 'form-horizontal form-iqb-general',
                            'data-pc' => '.bootbox-body' // parent container ID
                        ],
                        // Hidden Fields
                        $hidden); ?>
    <?php
    /**
     * Contact Form
     */
    $contact_record = isset($record) && !empty($record->contact) ? json_decode($record->contact) : NULL;
    $this->load->view('templates/_common/_form_contact', compact('contact_record'));
    ?>
    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>
