<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Voucher: Details View - Tab Overview
*/
$debit_total = 0;
$credit_total = 0;
?>
<div class="box box-bordered box-success">
    <div class="box-header with-border border-dark bg-green">
        <h3 class="no-margin">
	        <span class="pull-left">Voucher Card</span>
            <span class="pull-right">
                <?php if( $this->dx_auth->is_authorized('ac_vouchers', 'print.voucher') ): ?>
                        <a href="<?php echo site_url('ac_vouchers/print/' . $record->id)?>"
                            title="Print Voucher"
                            class="btn btn-sm btn-outline"
                            data-toggle="tooltip">
                            <i class="fa fa-print"></i>
                        </a>
                <?php endif?>
            </span>
        </h3>
    </div>

    <div class="box-body bg-gray-light">
        <div class="box no-border">
            <div class="box-body no-padding">
                <table class="table">
                	<thead>
                		<tr>
                			<th>Voucher Code: <?php echo $record->voucher_code;?></th>
                		</tr>
                	</thead>
                	<tbody>
                		<tr>
                			<td>Date : <?php echo $record->voucher_date;?></td>
                			<td>Fiscal Year: <?php echo $record->fy_code_np . " ({$record->fy_code_en})";?></td>
                		</tr>
                		<tr>
                			<td>Voucher Type: <?php echo $record->voucher_type_name;?></td>
                			<td>Branch : <?php echo $record->branch_name;?></td>
                		</tr>
                	</tbody>
                </table>
                <table class="table table-bordered">
                	<thead>
                		<tr>
                			<th>S.N.</th>
                			<th>Account</th>
                			<th>Party</th>
                			<th class="text-right">Debit (Rs.)</th>
                			<th class="text-right">Credit (Rs.)</th>
                		</tr>
                	</thead>
                	<tbody>
                		<?php
                		foreach($debit_rows as $row):
                			$debit_total += $row->amount;
                			?>
                			<tr>
                				<td><?php echo $row->sno?></td>
                				<td>
                					<?php
									$path_str = [];
									if( count($row->acg_path) > 2 )
									{
										array_shift($row->acg_path); // Remove "Chart of Account"
										foreach($row->acg_path as $path)
										{
											$path_str[]=$path->name;
										}
									}
									else
									{
										$path_str[] = $row->group_name;
									}
									$path_str[] = $row->account_name;

									echo implode('<i class="fa fa-angle-right text-bold text-red" style="margin:0 5px;"></i>', $path_str);
									?>
                				</td>
                				<td>
                					<?php
                					echo $row->party_name;
                					echo $row->party_name ? ' (' . ac_party_types_dropdown(false)[$row->party_type] . ')' : '';
                					?>
                				</td>
                				<td class="text-right"><?php echo number_format($row->amount, 2, '.', '')?></td>
                				<td>&nbsp;</td>
                			</tr>
            			<?php endforeach;?>
                	</tbody>

                	<tbody>
                		<?php
                		foreach($credit_rows as $row):
                			$credit_total += $row->amount;
                			?>
                			<tr>
                				<td><?php echo $row->sno?></td>
                				<td>
                					<?php
									$path_str = [];
									if( count($row->acg_path) > 2 )
									{
										array_shift($row->acg_path); // Remove "Chart of Account"
										foreach($row->acg_path as $path)
										{
											$path_str[]=$path->name;
										}
									}
									else
									{
										$path_str[] = $row->group_name;
									}
									$path_str[] = $row->account_name;

									echo implode('<i class="fa fa-angle-right text-bold text-red" style="margin:0 5px;"></i>', $path_str);
									?>
                				</td>
                				<td>
                					<?php
                					echo $row->party_name;
                					echo $row->party_name ? ' (' . ac_party_types_dropdown(false)[$row->party_type] . ')' : '';
                					?>
                				</td>
                				<td>&nbsp;</td>
                				<td class="text-right"><?php echo number_format($row->amount, 2, '.', '')?></td>
                			</tr>
            			<?php endforeach;?>
                	</tbody>
                	<tfoot class="text-bold">
                		<?php if($record->narration):?>
                			<tr>
                				<td>&nbsp;</td>
                				<td colspan="2">
                					<em>
                						<?php echo nl2br(htmlspecialchars( $this->security->xss_clean($record->narration)));?>
                					</em>
                				</td>
                				<td>&nbsp;</td>
                				<td>&nbsp;</td>
                			</tr>
                		<?php endif;?>
                		<tr>
                			<td colspan="3">&nbsp;</td>
                			<td class="text-right"><?php echo number_format($debit_total, 2, '.', '');?></td>
                			<td class="text-right"><?php echo number_format($credit_total, 2, '.', '');?></td>
                		</tr>
                	</tfoot>

                </table>
            </div>
        </div>
    </div>
</div>