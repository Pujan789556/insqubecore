<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Script for list Page:
*
* Account Group ID filter selectable
*/
?>
<script type="text/javascript">
    // Initialize Select2
    $.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
        //Initialize Select2 Elements
        $('select[data-ddtype="select"]').select2();

        $(document).on('click', '#_btn-filter-reset', function(){
        	$('select[data-ddtype="select"]').select2();
        });
    });
</script>