/**
 * InsQube: Customer Management
 *
 * Version: 1.1
 */

// ---------------------------------------------------------------

/**
 * Customer Details Page - Tab Click
 */
$(document).on('click', '#customer-tabs a', function(e){
    e.preventDefault();
    var $this = $(this),
    $tab_box  = $($this.data('box'));
    $this.tab('show');


    /**
     * AJAX Load Content if this is not Overview Tab
     */
    if($this.attr('aria-controls') !== 'tab-customer-overview' )
    {
        $tab_box.html('Loading...');
        InsQube.load(e, this);
    }
});

// ---------------------------------------------------------------

// Hide Element on Load if any
$('[data-hideonload=yes]').closest('.form-group').hide();


// Type Filter Change
$(document).on('change', '#filter-type', function(e){
    var $ref_both = $('[data-hideonload=yes]'),
        $ref_i = $('input[data-ref="I"]'),
        $ref_c = $('input[data-ref="C"]'),
        $ref_i_box = $ref_i.closest('.form-group'),
        $ref_c_box = $ref_c.closest('.form-group');

    if (this.value == 'C') {
        $ref_c_box.show();
        $ref_i_box.hide(function(){
            $ref_i.val('');
        })
    }
    else if (this.value == 'I'){
        $ref_i_box.show();
        $ref_c_box.hide(function(){
            $ref_c.val('');
        })
    }
    else{
      $ref_both.closest('.form-group').hide();
      $ref_both.val('');
    }
});

$(document).on('click', '#_btn-filter-reset', function(){
	$('#filter-type').trigger('change');
})
