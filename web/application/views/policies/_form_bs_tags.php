<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Form : Policy - Beema Samiti Report Headings
 */
?>
<style>
.select2-dropdown .select2-search__field:focus, .select2-search--inline .select2-search__field:focus{
    border:none;
}
</style>
<?php
/**
 * BS Heading Types with Headings for Portfolio
 */
$sectioned_data_portfolio = [];
$src_data_ht_wise = [];
foreach( $bsrs_headings_portfolio as $single )
{
    $name = $single->heading_type_name_np . ' (' . $single->heading_type_name_en . ')';

    $sectioned_data_portfolio["{$single->heading_type_id}"] = $name;
    $src_data_ht_wise["{$single->heading_type_id}"][$single->id] = $single->name;
}

/**
 * Data For Policy
 */
$record_data_ht_wise = [];
foreach($bsrs_headings_policy as $single)
{
    $record_data_ht_wise["{$single->heading_type_id}"][] = $single->bsrs_heading_id;
}

// echo '<pre>'; print_r($sectioned_data_portfolio);echo '</pre>';

echo form_open( $this->uri->uri_string(),
    [
        'class' => 'form-horizontal form-iqb-general',
        'id'    => '_form-policy',
        'data-pc' => '#form-box-policy' // parent container ID
    ],
    // Hidden Fields
    isset($record) ? ['id' => $record->id] : []); ?>


    <?php foreach($sectioned_data_portfolio as $heading_type_id => $bsrs_heading_type_name): ?>
        <div class="box box-solid box-bordered">
            <div class="box-header with-border">
              <h4 class="box-title"><?php echo $bsrs_heading_type_name; ?></h4>
            </div>
            <div class="box-body">
                <?php
                /**
                 * Load Form Components
                 */
                $form_record = (object)[
                    'bsrs_heading_id' => $record_data_ht_wise["{$heading_type_id}"] ?? []
                ];

                $form_elements[0]['_data'] = $src_data_ht_wise["{$heading_type_id}"];
                $this->load->view('templates/_common/_form_components_horz', [
                    'form_elements' => $form_elements,
                    'form_record'   => $form_record
                ]);
                ?>
            </div>
        </div>
    <?php endforeach ?>


    <button type="submit" class="hide">Submit</button>
<?php echo form_close();?>

<script type="text/javascript">
    // Initialize Select2
    $.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
        $(".select-multiple").select2();
    });
</script>
