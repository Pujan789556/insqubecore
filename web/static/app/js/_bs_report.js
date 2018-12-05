/**
 * InsQube: Beema Samiti Report Module
 */

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