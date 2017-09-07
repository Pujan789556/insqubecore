<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Policy: Details - Policy User Card
 *  1. Sales Staff
 *  2. Created By User, Date
 *  3. Verified By User, Date
 *  4. Approved User, Date
 */
?>
<div class="box box-bordered box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">Staff Summary</h3>
    </div>

    <div class="box-body">
        <table class="table table-condensed">
            <thead>
                <tr>
                    <th>Sold By</th>
                    <th>Prepared By</th>
                    <th>Verified By</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php
                        // Soled By
                        $user_info = [];
                        $user_info[] = $record->sold_by_username . " - {$record->sold_by_code}";
                        $profile = $record->sold_by_profile ? json_decode($record->sold_by_profile) : NULL;
                        if($profile)
                        {
                            $user_info[] = $profile->name . ', ' . $profile->designation;
                        }
                        echo implode('<br/>', $user_info);
                        ?>
                    </td>
                    <td>
                        <?php
                        // Prepared By
                        $user_info = [];
                        $user_info[] = $record->created_by_username . " - {$record->created_by_code}";
                        $profile = $record->created_by_profile ? json_decode($record->created_by_profile) : NULL;
                        if($profile)
                        {
                            $user_info[] = $profile->name . ', ' . $profile->designation;
                        }
                        // Date
                        $user_info[] = 'Date: ' . $record->created_at;
                        echo implode('<br/>', $user_info);
                        ?>
                    </td>

                    <td>
                        <?php
                        // Verified By
                        if($record->verified_by)
                        {
                            $user_info = [];
                            $user_info[] = $record->verified_by_username . " - {$record->verified_by_code}";
                            $profile = $record->verified_by_profile ? json_decode($record->verified_by_profile) : NULL;
                            if($profile)
                            {
                                $user_info[] = $profile->name . ', ' . $profile->designation;
                            }
                            // Date
                            $user_info[] = 'Date: ' . $record->verified_at;
                            echo implode('<br/>', $user_info);
                        }
                        else
                        {
                            echo '-';
                        }

                        ?>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>