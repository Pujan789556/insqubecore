<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Dashboard - Index
*/
?>
<div classs="row">
	<div class="col-lg-3 col-xs-6">
		<!-- small box -->
		<div class="small-box bg-aqua">
			<div class="inner">
				<h3>150</h3>
				<p>New Policies</p>
			</div>
			<div class="icon">
				<i class="ion ion-document-text"></i>
			</div>
			<a href="<?php echo site_url('policies') ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
		</div>
	</div>
	<!-- ./col -->
	<div class="col-lg-3 col-xs-6">
		<!-- small box -->
		<div class="small-box bg-green">
			<div class="inner">
				<h3>5</h3>
				<p>Claims</p>
			</div>
			<div class="icon">
				<i class="ion ion-document"></i>
			</div>
			<a href="<?php echo site_url('claims') ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
		</div>
	</div>
	<!-- ./col -->

	<div class="col-lg-3 col-xs-6">
		<!-- small box -->
		<div class="small-box bg-yellow">
			<div class="inner">
				<h3>44</h3>
				<p>Branches</p>
			</div>
			<div class="icon">
				<i class="ion ion-network"></i>
			</div>
			<a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
		</div>
	</div>
	<!-- ./col -->

	<div class="col-lg-3 col-xs-6">
		<!-- small box -->
		<div class="small-box bg-red">
			<div class="inner">
				<h3>100+</h3>
				<p>Customers</p>
			</div>
			<div class="icon">
				<i class="ion ion-person-add"></i>
			</div>
			<a href="<?php echo site_url('customers') ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
		</div>
	</div>
	<!-- ./col -->
</div>

<div class="row">
	<div class="col-lg-6 connectedSortable">
		<!-- Custom tabs (Charts with tabs)-->
		<div class="nav-tabs-custom">
			<!-- Tabs within a box -->
			<ul class="nav nav-tabs pull-right">
				<li class="active"><a href="#revenue-chart" data-toggle="tab">Area</a></li>
				<li><a href="#sales-chart" data-toggle="tab">Donut</a></li>
				<li class="pull-left header"><i class="fa fa-inbox"></i> Sales</li>
			</ul>
			<div class="tab-content no-padding">
				<!-- Morris chart - Sales -->
				<div class="chart tab-pane active" id="revenue-chart" style="position: relative; height: 300px;"></div>
				<div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;"></div>
			</div>
		</div>
	</div>
	<div class="col-lg-6">
		<div class="box box-solid bg-teal-gradient">
			<div class="box-header">
				<i class="fa fa-th"></i>
				<h3 class="box-title">Sales Graph</h3>
				<div class="box-tools pull-right">
					<button type="button" class="btn bg-teal btn-sm" data-widget="collapse"><i class="fa fa-minus"></i>
					</button>
					<button type="button" class="btn bg-teal btn-sm" data-widget="remove"><i class="fa fa-times"></i>
					</button>
				</div>
			</div>
			<div class="box-body border-radius-none">
				<div class="chart" id="line-chart" style="height: 280px;"></div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<?php
		/**
		 * Load Policy Widget
		 */
		$this->load->view('policies/widget/_widget_dashboard', ['records' => $policies]);
		?>
	</div>
</div>
