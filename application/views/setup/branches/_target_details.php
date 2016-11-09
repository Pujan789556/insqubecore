<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Branch: Details View
*/
?>
<div class="row">
	<?php foreach($targets as $target):?>
	<div class="col-sm-6" id="target-<?php echo $target->id?>">
		<div class="box box-default box-solid collapsed-box">
			<div class="box-header with-border">
				<h3 class="box-title"><?php echo $target->branch_name;?> <small><strong>NRS: <?php echo $target->target_total?></strong></small></h3>
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
					</button>
				</div>
				<!-- /.box-tools -->
			</div>
			<!-- /.box-header -->
			<?php echo form_open( 'branches/save_target_details/'. $target->id,
                    [
                        'class' => 'form-iqb-general form-horizontal',
                        'data-pc' => '#target-dtl-box-'. $target->id // parent container ID
                    ],
                    // Hidden Fields
                    ['id' => $target->id]);?>
				<div class="box-body" id="target-dtl-box-<?php echo $target->id?>">

					<div class="form-group">
		                <label class="col-sm-4 control-label">
		                    Total Target
		                </label>
		                <div class="col-sm-8">
		                    <p class="form-control-static"><?php echo $target->target_total?></p>
		                </div>
		            </div>

					<?php

					// Do we have from Database?
					$target_details = $target->target_details ? json_decode($target->target_details) : NULL;
					$list_data_db = [];
					if($target_details)
					{
						foreach($target_details as $t_dtl)
						{
							$list_data_db["{$t_dtl->portfolio}"]['target'] = $t_dtl->target;
						}
					}

					// Fill the missing portfolio if any.
					// This is required if any porfolio is deleted or disabled, or added new one
					// Those changed one will not be reflectetd on JSON field Above
					$list_data = [];
					foreach($portfolio as $portfolio_id=>$portfolio_name)
					{
						$list_data["{$portfolio_id}"]['target'] 	= $list_data_db["{$portfolio_id}"]['target'] ?? '';
						$list_data["{$portfolio_id}"]['name'] 	= $portfolio_name;
					}


					// Do we have value from post?
					$i = 0;
					foreach($portfolio as $portfolio_id=>$portfolio_name)
					{
						$target_name = "portfolio_target[$i]";
						if(set_value($target_name))
		                {
		                    $list_data["{$portfolio_id}"]['target'] 	= set_value($target_name, '', FALSE);
		                }
		                $i++;
					}
					foreach($list_data as $portfolio_id=>$p_data):?>
						<div class="form-group <?php echo form_error('portfolio_target[]') ? 'has-error' : '';?>">
							<?php echo form_hidden('portfolio_ids[]', $portfolio_id);?>
	                        <label for="" class="col-sm-4 control-label"><?php echo ucwords($p_data['name']) . field_compulsary_text( TRUE )?></label>
	                        <div class="col-sm-8">
	                        	<input type="number" step="0.01" name="portfolio_target[]" class="form-control" placeholder="Enter portfolio target total..." value="<?php echo $p_data['target'];?>">
		                            <?php if(form_error("portfolio_target[]")):?><span class="help-block"><?php echo form_error("portfolio_target[]"); ?></span><?php endif?>
	                        </div>
	                    </div>
                	<?php  endforeach; ?>
				</div>

				<div class="box-footer">
		            <button type="submit" class="btn btn-primary pull-right">Save</button>
	          	</div>
          	<?php echo form_close();?>
			<!-- /.box-body -->
		</div>
	</div>
<?php endforeach;?>
</div>