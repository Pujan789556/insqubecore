/**
 * InsQube: Policy Management
 */

// ---------------------------------------------------------------

/**
 * Policy Details Page - Tab Click
 */
$(document).on('click', '#policy-tabs a', function(e){
	e.preventDefault();
	var $this = $(this),
	$tab_box  = $($this.data('box'));
	$this.tab('show');

	$tab_box.html('Loading...');
	InsQube.load(e, this);
});

// $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
//   e.target // newly activated tab
//   e.relatedTarget // previous active tab
// })


// Initialize Select2
if($("#_iqb-filter-form-policy").length){
	$.getScript( __IQB__APP_THEME_URL + "plugins/select2/select2.full.min.js", function( data, textStatus, jqxhr ) {
	    //Initialize Select2 Elements
	    $("#_iqb-filter-form-policy select").select2();
	});

	// Reset on Reset button cliek
	$(document).on('click', '#_btn-filter-reset', function(){
		$("#_iqb-filter-form-policy select").select2();
	})

	// Focus on Submit button on select change
	$('#_iqb-filter-form-policy select').on('select2:select', function (e) {
	  	console.log('yes');
	  	$('#_btn-filter-submit').focus();
	});
}
