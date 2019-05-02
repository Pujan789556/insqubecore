<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Row: MISCELLANEOUS - Group Personnel Accident(GPA)
*/
$attributes = $record->attributes ? json_decode($record->attributes) : NULL;
$snippet    = _OBJ_MISC_GPA_select_text($record);
?>
<td>
    <?php if($attributes):?>
        <?php echo $snippet; ?>
        <span id="_popover-misc-gpa-<?php echo $record->id?>" class="hide">
            <?php
            /**
             * Popover Content
             */
            $this->load->view($this->data['_view_base'] . '/snippets/_popup_misc_gpa',['record' => $record]);
            ?>
        </span>
    <?php endif;?>
</td>
<td><?php echo locked_unlocked_text($record->flag_locked);?></td>
<?php if( !$_flag__show_widget_row ):?>
    <td>
        <?php if($attributes):?>
        <a tabindex="0" class="preview-dom btn bg-orange btn-xs pull-left margin-r-5" data-dom="#_popover-misc-gpa-<?php echo $record->id?>" role="button" data-toggle="popup" title="Object Details - <?php echo $record->portfolio_name_en;?>"><i class="fa fa-search"></i></a>
        <?php endif;?>
    </td>
<?php endif;?>