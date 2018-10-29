<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Javascript Goodies for Ledger Filter
*/
?>
<script type="text/javascript">
    // Initialize Select2
    $.getScript( "<?php echo THEME_URL; ?>plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
        //Initialize Select2 Elements
        $('select#filter_account_id').select2();

        $(document).on('click', '#_btn-filter-reset', function(){
        	$('select#filter_account_id').select2();
        });
    });

    // ---------------------------------------------------------------

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

    // ---------------------------------------------------------------

	// Find Party
    function __find_party(a)
    {
        var $this       = $(a),
            rowId       = 'party-box',
            widgetType = $('#'+rowId).data('widget-party'),
            $targetRow  = $('#' + rowId ),
            $partyType  = $( 'select[data-field="party_type"]', $targetRow ),
            pt          = $partyType.val();

        // Valid Party Type?
        if( ! pt )
        {
            toastr.warning('Please select party type first.', 'OOPS!');
            $partyType.closest('div.form-group').addClass('has-error');
            return false;
        }
        var widgetReference = rowId + ':' + widgetType;

        $this.button('loading');
        InsQube.options.__btn_loading = $this;
        $.getJSON('<?php echo base_url()?>ac_parties/finder/' + pt + '/' + widgetReference, function(r){
            if( typeof r.html !== 'undefined' && r.html != '' ){
                bootbox.dialog({
                    className: 'modal-default',
                    size: 'large',
                    title: 'Find Party',
                    closeButton: true,
                    message: r.html,
                    buttons:{
                        cancel: {
                            label: "Close",
                            className: 'btn-default'
                        }
                    }
                });
            }
            // Reset Loading
            $this.button('reset');
        });
    }

    // Reset Party
    function __reset_party(a)
    {
        var $this       = $(a),
            rowId       = 'party-box';

        $( '#' + rowId + ' ._text-ref-party').html('');
        $( '#' + rowId + ' input[data-field="party_id"]').val('');
    }

    // Select Party
    function __do_select(a){
        var $a = $(a),
        selectable  = $a.data('selectable'),
        $targetRow  = $('#' + $a.data('target-rowid')),
        fields      = selectable.fields,
        html        = selectable.html;

        if( typeof fields === 'object'){
            for(var i = 0; i < fields.length; i++) {

                var obj = fields[i];
                $( 'input[data-field="'+obj.ref+'"]', $targetRow ).val(obj.val);
            }
        }
        if( typeof html === 'object'){
            for(var i = 0; i < html.length; i++) {
                var obj = html[i];
                $('.' + obj.ref, $targetRow ).html(obj.val);
            }
        }

        // Close the bootbox if any
        var $bootbox = $a.closest('.bootbox');
        $('button[data-bb-handler="cancel"]', $bootbox).trigger('click');
    }
</script>