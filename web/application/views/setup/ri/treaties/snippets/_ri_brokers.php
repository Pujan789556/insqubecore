<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Setup - RI - Treaty : Snippet - Brokers Snippet View
*/
?>
<div class="box box-primary">
	<div class="box-header with-border"><h3 class="box-title">Brokers</h3></div>
	<div class="box-body">
		<table class="table table-condensed table-responsive no-border">
			<?php
			$broker_sn = 1;
			foreach ($brokers as $broker):?>
				<tr>
					<td><?php echo $broker_sn++;?>.</td>
					<td>
						<a href="<?php echo site_url('companies/details/' . $broker->company_id)?>" target="_blank">
							<?php echo $broker->name?>
						</a>
					</td>
				</tr>
			<?php endforeach;?>
		</table>
	</div>
</div>