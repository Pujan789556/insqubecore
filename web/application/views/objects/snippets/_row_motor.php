<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Motor Row
*/
$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
?>
<td>
	<?php if($attributes):?>
		<?php echo $attributes->make;?>, <?php echo $attributes->model;?><br/>
		Reg No: <strong><?php echo $attributes->reg_no;?></strong>,
		Engine No: <strong><?php echo $attributes->engine_no;?></strong>,
		Chasis No: <strong><?php echo $attributes->chasis_no;?></strong>,
		<strong><?php echo $attributes->engine_capacity;?> <?php echo $attributes->ec_unit;?></strong>
		<span id="_text-ref-<?php echo $record->id?>" class="hide">
			<?php echo $attributes->make;?>, <?php echo $attributes->model;?>,
			<?php echo $attributes->reg_no;?>, <?php echo $attributes->engine_no;?>, <?php echo $attributes->chasis_no;?>
		</span>
		<span id="_popover-motor-<?php echo $record->id?>" class="hide">
			<?php
			/**
			 * Popover Content
			 */
			$this->load->view('objects/snippets/_popup_motor',['record' => $record]);
			?>
		</span>
	<?php endif;?>
</td>
<td><?php echo locked_unlocked_text($record->flag_locked);?></td>
<?php if( !$_flag__show_widget_row ):?>
	<td>
		<?php if($attributes):?>
		<a tabindex="0" class="preview-dom btn bg-orange btn-xs pull-left margin-r-5" data-dom="#_popover-motor-<?php echo $record->id?>" role="button" data-toggle="popup" title="Object Details - <?php echo $record->portfolio_name_en;?>"><i class="fa fa-search"></i></a>
		<?php endif;?>
	</td>
<?php endif;?>