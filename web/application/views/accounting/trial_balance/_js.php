<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Javascript Goodies for Trial Balance Filter
*/
?>
<script type="text/javascript">

    /**
     * Filter Print Action
     */
    $(document).on('click', '#_btn-filter-print', function(e){
        e.preventDefault();
        var $this = $(this),
            $form = $this.closest('form'),
            print_url = $form.data('print-url');

            $form.attr('action', print_url)
                 .attr('target', '_blank')
                 .submit();
    });

    /**
    * Filter Search Action
    */
    $(document).on('click', '#_btn-filter-search', function(e){
        e.preventDefault();
        var $this = $(this),
            $form = $this.closest("form"),
            $box = $($form.data('box')),
            method = $form.data('method'),
            filter_url = $form.data('filter-url');

        $form.attr('action', filter_url)
             .removeAttr('target');

        $this.button('loading');
        InsQube.postForm($form[0], function(r){
            if(typeof r.status !== 'undefined' && r.status === 'success' && typeof r.html != 'undefined'){
                $box[method](r.html);
            }
            $this.button('reset');
        });
        return false;
    });


    // ---------------------------------------------------------------

	/**
	 * Filter - Select Quarter/Month Render on Type Change
	 */
	$(document).on('change', 'select[name="filter_type"]', function(e){
		e.preventDefault();
		var $this = $(this),
			v = $this.val(),
			$target = $('select[name="filter_fy_quarter_month"]');

			$target.empty();
			if( v == 'Q' ){
				$('#quarter-dropdown-template option').clone().appendTo($target);
			}else if( v == 'M' ){
				$('#month-dropdown-template option').clone().appendTo($target);
			}
	});
</script>