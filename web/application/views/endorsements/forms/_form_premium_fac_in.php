<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Endorsement - Fac-In Premium Form
 */
$hidden_fields = ['policy_id' => $policy_record->id];
if(isset($record))
{
    $hidden_fields['id'] = $record->id;
}
?>
<?php echo form_open( $this->uri->uri_string(),
                        [
                            'class'     => 'form-iqb-general',
                            'data-pc'   => '.bootbox-body', // parent container ID
                            'id'        => '_form-premium'
                        ],
                        // Hidden Fields
                        $hidden_fields); ?>
<div class="row">
    <div class="col-md-6">

        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title">Supply Premium Information</h4>
            </div>
            <div class="box-body form-horizontal">
                <?php
                /**
                 * Load Form Components
                 */
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $form_elements,
                    'form_record'   => $endorsement_record
                ]);
                ?>
            </div>
        </div>

        <button type="submit" class="hide">Submit</button>
    </div>
</div>
<?php echo form_close();?>