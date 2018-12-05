<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Schedule Component - Footer
 *
 * English & Nepali
 */
?>
<?php if($lang == 'en'): ?>
    <table class="table no-border" width="100%">
        <tr>
            <td width="50%">Please attach this endorsement with its policy schedule.</td>
            <td>
                Signed for and on behalf of the <br><strong><?php echo htmlspecialchars($this->settings->orgn_name_en)?></strong>
                <br><br><br>
                Authorized Signature:
                <br>
                Name:<br>
                Designation:
            </td>
        </tr>
    </table>
<?php else: ?>
    <table class="table no-border">
        <tr>
            <td width="50%">कृपया यो सम्पुष्टी बीमालेखमा संलग्न गर्नुहोला । </td>
            <td align="left">
                <h4 class="underline"><?php echo htmlspecialchars($this->settings->orgn_name_np)?> तर्फबाट अधिकार प्राप्त अधिकारीको</h4>
                <p style="line-height: 30px">दस्तखत:</p>
                <p>नाम थर:</p>
                <p>दर्जा:</p>
            </td>
        </tr>
    </table>
<?php endif; ?>