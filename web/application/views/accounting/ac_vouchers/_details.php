<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Voucher: Details View
*/
$debit_total = 0;
$credit_total = 0;
?>
<style type="text/css">
    .cr-indent{padding-left: 50px !important;}
</style>
<div class="box box-bordered box-default">
    <div class="box-header with-border border-dark bg-gray">
        <h3 class="no-margin">
	        <span class="pull-left">Voucher Details</span>
            <span class="pull-right">
                <?php if( $this->dx_auth->is_authorized('ac_vouchers', 'print.voucher') ): ?>
                        <a href="#"
                            title="Print Voucher (Ctrl + P)"
                            class="btn btn-sm btn-outline"
                            onclick="window.print()"
                            data-toggle="tooltip">
                            <i class="fa fa-print"></i>
                        </a>
                <?php endif?>
            </span>
        </h3>
    </div>

    <div class="box-body">
        <div class="box box-bordered box-solid" id="voucher-card">
        	<div class="box-header with-border border-dark printable print-only"><h2>Voucher</h2></div>
            <div class="box-body printable no-padding" id="voucher-card">
                <table class="table dont-break">
                	<thead>
                		<tr>
                			<th colspan="2">Voucher Code: <?php echo $record->voucher_code;?></th>
                		</tr>
                	</thead>
                	<tbody>
                		<tr>
                			<td>Date : <?php echo $record->voucher_date;?></td>
                			<td>Fiscal Year: <?php echo $record->fy_code_np . " ({$record->fy_code_en})";?></td>
                		</tr>
                		<tr>
                			<td>Voucher Type: <?php echo $record->voucher_type_name;?></td>
                			<td>Branch : <?php echo $record->branch_name_en;?></td>
                		</tr>
                	</tbody>
                </table>
                <table class="table table-bordered dont-break table-hover">
                	<thead>
                		<tr>
                			<th class="text-center">Particulars (Account-Party)</th>
                			<th class="text-center">Debit (Rs.)</th>
                			<th class="text-center">Credit (Rs.)</th>
                		</tr>
                	</thead>
                	<tbody>
                		<?php
                		foreach($debit_rows as $row):
                			$debit_total += $row->amount;
                			?>
                			<tr>
                				<!-- <td><?php echo $row->sno?></td> -->
                				<td>
                                    <?php
                                    // echo ac_account_group_path_formatted($row->acg_path, $row->account_name);
                                    // Account Name - Party Name (Party Type)
                                    $particulars = [$row->account_name];
                                    if($row->party_name)
                                    {
                                        $particulars[] = $row->party_name . ' (' . ac_party_types_dropdown(false)[$row->party_type] . ')';
                                    }
                                    echo implode(' - ', $particulars);
                                    ?>
                                </td>
                				<td class="text-right"><?php echo number_format($row->amount, 2)?></td>
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
                				<!-- <td><?php echo $row->sno?></td> -->
                                <td class="cr-indent">
                                    <?php
                                    // echo ac_account_group_path_formatted($row->acg_path, $row->account_name);
                                    // Account Name - Party Name (Party Type)
                                    $particulars = [$row->account_name];
                                    if($row->party_name)
                                    {
                                        $particulars[] = $row->party_name . ' (' . ac_party_types_dropdown(false)[$row->party_type] . ')';
                                    }
                                    echo implode(' - ', $particulars);
                                    ?>
                                </td>
                				<td>&nbsp;</td>
                				<td class="text-right"><?php echo number_format($row->amount, 2)?></td>
                			</tr>
            			<?php endforeach;?>
                	</tbody>
                	<tfoot class="text-bold">
                		<tr>
                            <td>
                                <em>
                                    <?php echo nl2br(htmlspecialchars( $this->security->xss_clean($record->narration)));?>
                                </em>
                            </td>
                            <td class="text-right"><?php echo number_format($debit_total, 2);?></td>
                            <td class="text-right"><?php echo number_format($credit_total, 2);?></td>
                        </tr>
                	</tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
