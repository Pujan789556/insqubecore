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


	/**
	 * AJAX Load Content if this is not Overview Tab
	 */
	if($this.attr('aria-controls') !== 'tab-policy-overview' )
	{
		$tab_box.html('Loading...');
		InsQube.load(e, this);
	}
});

// $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
//   e.target // newly activated tab
//   e.relatedTarget // previous active tab
// })
