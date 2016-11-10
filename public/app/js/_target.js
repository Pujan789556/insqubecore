/**
 * InsQube: Target Management
 *
 * 	Extra JS for managing Branch|Employee-wise Targets on Each Parent/Child Portfolio
 */


/**
 * Check Target Total On Portfolio & its Children
 */
function _dyn_check_target_math(f, t)
{
	$t = $(t);

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
 	else{
 		total = $('._target-parent[data-portfolio-target="'+pt+'"]').val();
 	}

 	total = parseFloat(total);

 	$('._target-child[data-portfolio-target="'+pt+'"]').each(function(i, obj) {
	    childtotal += parseFloat($(this).val() ? $(this).val() : 0);
	});

	var r = total - childtotal;

	if( r > 0 ){
		output = '<span class="text-info">Remaining Total: '+r.toFixed(2)+'</span>';
	}
	else if(r < 0){
		output = '<span class="text-red">Exceding Amount: '+r.toFixed(2)+'</span>';
	}else{
		output = '<span class="text-green">Distribution Completed!</span>';
	}

	$('.math[data-portfolio-target="'+pt+'"]').html('<p class="margin-t-10 text-bold pull-right margin-b-0">'+output + '</p>');

})