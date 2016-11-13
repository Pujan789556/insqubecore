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
				<h3 class="box-title"><?php echo $target->branch_name;?></h3>
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
                        'id' 	=> 'target-form-' . $target->id,
                        'data-pc' => '#target-dtl-box-'. $target->id // parent container ID
                    ],
                    // Hidden Fields
                    ['id' => $target->id]);?>
				<div class="box-body" id="target-dtl-box-<?php echo $target->id?>">

					<div class="form-group">
		                <label class="col-sm-3 control-label">
		                    Total Target<?php echo field_compulsary_text( TRUE )?>
		                </label>
		                <div class="col-sm-9">
		                    <input type="number" step="0.01" data-target="<?php echo $target->id; ?>" name="target_total" class="form-control _target _target-total" placeholder="Enter target total..." value="<?php echo set_value('target_total') ? set_value('target_total') : $target->target_total;?>">
		                            <?php if(form_error("target_total")):?><span class="help-block"><?php echo form_error("target_total"); ?></span><?php endif?>

                            <div class="math" data-target="<?php echo $target->id;?>"></div>

		                </div>
		            </div>

					<?php
					/**
					 * JSON Structure in Database (Target Details)
					 *
					 * 	[{
					 * 		"portfolio" : "12",
					 * 		"target" : "500.98",
					 * 		"children" : [{"portfolio":"22", "target": "300"}, {"portfolio":"29", "target": "200"}]
					 * 	},{
					 * 		"portfolio" : "13",
					 * 		"target" : "479"
					 * }]
					 *
					 * ------------------------------------------------------------------------------------
					 *
					 * Master Portfolio Array Structure
					 *
					 * 	[
					 * 		'12' => [
					 * 			'id' => '12',
					 * 			'name' => 'Agriculture',
					 * 			'parent_id' => '0',
					 * 			'children' = [
					 * 				'22' => ['id' => '22', 'name' => 'Cattle', 'parent_id' => '12'],
					 * 				'29' => ['id' => '29', 'name' => 'Fish', 'parent_id' => '12']
					 * 			]
					 * 		],
					 * 		'13' => [
					 * 			'id' => '13',
					 * 			'name' => 'Aviation',
					 * 			'parent_id' => '0'
					 * 		]
					 * 	]
					 */

					// Create Array Structure from DB
					$target_details = $target->target_details ? json_decode($target->target_details) : NULL;
					$list_data_db = [];
					if($target_details)
					{
						foreach($target_details as $t)
						{
							$single = [
								'id' => $t->portfolio,
								'target' => $t->target
							];
							$list_data_db["{$t->portfolio}"] = $single;

							// Let's check if we have children
							$children = $t->children ?? [];
							foreach($children as $child)
							{
								$single = ['id' => $child->portfolio, 'target' => $child->target];
								$list_data_db["{$t->portfolio}"]['children']["{$child->portfolio}"] = $single;
							}
						}
					}

					// Fill the missing portfolio if any.
					// This is required if any porfolio is deleted or disabled, or added new one
					// Those changed one will not be reflectetd on JSON field Above
					$list_data = [];
					$i = 0;
					foreach($portfolio as $portfolio_id=>$p)
					{
						$list_data["{$portfolio_id}"] = $p;
						$list_data["{$portfolio_id}"]['target'] = $list_data_db["{$portfolio_id}"]['target'] ?? '';

						// Do we have value from post?
						$target_name = "portfolio_target[$i]";
						if(set_value($target_name))
		                {
		                    $list_data["{$portfolio_id}"]['target'] = set_value($target_name, '', FALSE);
		                }
		                $i++;

						$children = $p['children'] ?? [];
						$j = 0;
						foreach($children as $child_id=>$c)
						{
							$list_data["{$portfolio_id}"]['children']["{$child_id}"] = $c;
							$list_data["{$portfolio_id}"]['children']["{$child_id}"]['target'] = $list_data_db["{$portfolio_id}"]['children']["{$child_id}"]['target'] ?? '';

							// Do we have value from post?
							$target_name = "child_portfolio_target[$j]";
							if(set_value($target_name))
			                {
			                    $list_data["{$portfolio_id}"]['children']["{$child_id}"]['target'] = set_value($target_name, '', FALSE);
			                }
			                $j++;
						}
					}

					foreach($list_data as $portfolio_id=>$p_data):?>
						<div class="form-group <?php echo form_error('portfolio_target[]') ? 'has-error' : '';?>">
							<?php echo form_hidden('portfolio_ids[]', $portfolio_id);?>
	                        <label for="" class="col-sm-3 control-label"><?php echo ucwords($p_data['name']) . field_compulsary_text( TRUE )?></label>
	                        <div class="col-sm-9">
	                        	<input type="number" data-target="<?php echo $target->id; ?>" data-portfolio-target="<?php echo $portfolio_id . $target->id;?>" step="0.01" name="portfolio_target[]" class="form-control _target-parent _target" placeholder="Enter portfolio target total..." value="<?php echo $p_data['target'];?>">
		                            <?php if(form_error("portfolio_target[]")):?><span class="help-block"><?php echo form_error("portfolio_target[]"); ?></span><?php endif?>

		                            <?php
		                            /**
		                             * Do we have children?
		                             */
		                            $children = $p_data['children'] ?? [];
		                            foreach($children as $child_id=>$c):

		                            	echo form_hidden('child_portfolio_ids[]', $child_id);
		                            	echo form_hidden('parent_ids[]', $c['parent_id']);
		                            ?>
		                            	<div class="row form-inline margin-t-10">
		                            		<label class="control-label col-sm-4"><?php echo $c['name'];?></label>
		                            		<input type="number" step="0.01" data-target="<?php echo $target->id; ?>" data-portfolio-target="<?php echo $portfolio_id . $target->id;?>" name="child_portfolio_target[]" class="form-control _target-child _target" placeholder="Enter portfolio target total..." value="<?php echo $c['target'];?>">
		                            		<?php if(form_error("child_portfolio_target[]")):?><span class="help-block"><?php echo form_error("child_portfolio_target[]"); ?></span><?php endif?>
		                            	</div>
		                        	<?php endforeach;?>
		                        	<div class="math" data-portfolio-target="<?php echo $portfolio_id . $target->id;?>"></div>
	                        </div>
	                    </div><hr/>
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