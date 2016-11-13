/**
 * InsQube: Target Management
 *
 * 	Extra JS for managing Branch|Employee-wise Targets on Each Parent/Child Portfolio
 */


/**
 * Check Target Total On a Branch and its Portfolios
 */
function _dyn_check_total_target_math(t)
{
	var $this = $(t),
	dt = $this.data('target'),
	portfolio_total = target_total = 0, output = '';

	// totals
	target_total = $('._target-total[data-target="'+dt+'"]').val();

	// Target total sum from  parent portfolio
	$('._target-parent[data-target="'+dt+'"]').each(function(i, obj) {
	    portfolio_total += parseFloat($(this).val() ? $(this).val() : 0);
	});

	var r = target_total - portfolio_total;

	if( r > 0 ){
		output = '<span class="text-info">Remaining Total (Main Portfolio): '+r.toFixed(2)+'</span>';
	}
	else if(r < 0){
		output = '<span class="text-red">Exceding Amount (Main Portfolio): '+r.toFixed(2)+'</span>';
	}else{
		output = '<span class="text-green">Main Portfolio Distribution Completed!</span>';
	}

	$('.math[data-target="'+dt+'"]').html('<p class="margin-t-10 text-bold pull-right margin-b-0">'+output + '</p>');
}

$(document).on('keyup change', '._target', function(e){
    e.preventDefault();
    var $this = $(this),
    $form = $this.closest('form'),
    pt = $this.data("portfolio-target"),
    total = 0, childtotal = 0, output = '';

 	if($this.hasClass('_target-parent')){
 		total = $this.val();
 	}
 	else if($this.hasClass('_target-child')){
 		total = $('._target-parent[data-portfolio-target="'+pt+'"]').val();
 	}
 	else{
 		return _dyn_check_total_target_math(this);
 	}

 	total = parseFloat(total);

 	$('._target-child[data-portfolio-target="'+pt+'"]').each(function(i, obj) {
	    childtotal += parseFloat($(this).val() ? $(this).val() : 0);
	});

	var r = total - childtotal;

	// Let's check if this portfolio parent has no child/sub-portfolio
	if( $('._target-child[data-portfolio-target="'+pt+'"]').length == 0){
		r = 0;
	}

	if( r > 0 ){
		output = '<span class="text-info">Remaining Total (Sub Portfolio): '+r.toFixed(2)+'</span>';
	}
	else if(r < 0){
		output = '<span class="text-red">Exceding Amount (Sub Portfolio): '+r.toFixed(2)+'</span>';
	}else{
		output = '<span class="text-green">(Sub Portfolio) Distribution Completed!</span>';
	}

	$('.math[data-portfolio-target="'+pt+'"]').html('<p class="margin-t-10 text-bold pull-right margin-b-0">'+output + '</p>');

	// Target total check if parent portfolio change
	if($this.hasClass('_target-parent')){
 		return _dyn_check_total_target_math(this);
 	}

})