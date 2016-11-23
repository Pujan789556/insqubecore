/**
 * InsQube: Customer Management
 */

// Hide Element on Load if any
$('[data-hideonload=yes]').closest('.form-group').hide();


// Customer List Search Filter
$(document).on('change', '#filter-type', function(e){
    var $this = $(this),
    	val = $this.val(),
    	$regbox = $('input[type="text"][name="filter_company_reg_no"]').closest('.form-group'),
        $ctznbox = $('input[type="text"][name="filter_citizenship_no"]').closest('.form-group'),
        $ppbox = $('input[type="text"][name="filter_passport_no"]').closest('.form-group');

    if (val == 'C') {
		$regbox.show();
		$ctznbox.hide();
		$ppbox.hide();
		$('input[type="text"][name="filter_passport_no"]').val('');
		$('input[type="text"][name="filter_citizenship_no"]').val('');
	}
  	else if (val == 'I') {
		$ctznbox.show();
		$ppbox.show();
		$regbox.hide();
		$('input[type="text"][name="filter_company_reg_no"]').val('');
      }
  	else{
  		$regbox.hide();
      	$ctznbox.hide();
      	$ppbox.hide();
      	$('[data-hideonload=yes]').val('');
  	}
});

$(document).on('click', '#_btn-filter-reset', function(){
	$('#filter-type').trigger('change');
})
