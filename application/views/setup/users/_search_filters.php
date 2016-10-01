<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Search Filters
*/
?>
<form class="form-inline">
	<?php
	$this->load->view('templates/_common/_form_components_filter');
	?>
	<!-- <div class="form-group">
		<label for="exampleInputEmail1">Email address</label><span class="clearfix"></span>
		<input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
	</div>
	<div class="form-group">
		<label for="exampleInputEmail1">Email address</label><span class="clearfix"></span>
		<input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
	</div>
	<div class="form-group">
		<label for="exampleInputEmail1">Email address</label><span class="clearfix"></span>
		<input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
	</div>
	<div class="form-group">
		<label for="exampleInputEmail1">Email address</label><span class="clearfix"></span>
		<input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
	</div>
	<div class="form-group">
		<label for="exampleInputEmail1">Email address</label><span class="clearfix"></span>
		<input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
	</div>
	<div class="form-group">
		<label for="exampleInputPassword1">Password</label><span class="clearfix"></span>
		<input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
	</div> -->

	<div class="row" style="margin-top:10px;">
		<div class="col-xs-12">
			<button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Search</button>
			<button type="reset" class="btn btn-default">Clear</button>
		</div>
	</div>
</form>