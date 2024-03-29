<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* Object Snippet: Object Popover
*/

$data           = ['record' => $record, 'ref' => $ref ?? ''];
$portfolio_id   = (int)$record->portfolio_id;
?>
<div class="box box-solid box-bordered">
    <div class="box-header with-border">
        <h4 class="box-title">Object Common Info</h4>
    </div>
    <table class="table table-bordered table-condensed no-margin">
        <tr>
            <th>Maximum Liability (Rs.)</th>
            <td><?php echo $record->amt_max_liability ? number_format($record->amt_max_liability, 2) : '-' ?></td>
        </tr>
        <tr>
            <th>Third Party Liability (Rs.)</th>
            <td><?php echo $record->amt_third_party_liability ? number_format($record->amt_third_party_liability, 2) : '-' ?></td>
        </tr>
        <tr>
            <th>Sum Insured (Rs.)</th>
            <td><?php echo $record->amt_sum_insured ? number_format($record->amt_sum_insured, 2) : '-' ?></td>
        </tr>
    </table>
</div>
<?php
/**
 * AGRICULTURE - CROP SUB-PORTFOLIO
 * ---------------------------------
 */
if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CROP_ID )
{
    $this->load->view('objects/snippets/_popup_agr_crop', $data);
}

/**
 * AGRICULTURE - CATTLE SUB-PORTFOLIO
 * ---------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_CATTLE_ID )
{
    $this->load->view('objects/snippets/_popup_agr_cattle', $data);
}

/**
 * AGRICULTURE - POULTRY SUB-PORTFOLIO
 * -----------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_POULTRY_ID )
{
    $this->load->view('objects/snippets/_popup_agr_poultry', $data);
}

/**
 * AGRICULTURE - FISH(Pisciculture) SUB-PORTFOLIO
 * ----------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_FISH_ID )
{
    $this->load->view('objects/snippets/_popup_agr_fish', $data);
}

/**
 * AGRICULTURE - BEE(Apiculture) SUB-PORTFOLIO
 * -------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_AGR_BEE_ID )
{
    $this->load->view('objects/snippets/_popup_agr_bee', $data);
}

/**
 * MOTOR PORTFOLIOS
 * ----------------
 * For all type of motor portfolios, we have same snippet
 */
else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MOTOR)) )
{
	$this->load->view('objects/snippets/_popup_motor', $data);
}

/**
 * PROPERTY - ALL PORTFOLIOS
 * -------------------------
 */
else if( in_array($portfolio_id,  array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__PROPERTY) ) )
{
    $this->load->view('objects/snippets/_popup_property', $data);
}


/**
 * BURGLARY - JEWELRY, HOUSEBREAKING, CASH IN SAFE
 * --------------------------------------------------
 */
else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MISC_BRG)) )
{
    $this->load->view('objects/snippets/_popup_misc_brg', $data);
}

/**
 * MARINE PORTFOLIOS
 * ----------------
 */
else if( in_array($portfolio_id, array_keys(IQB_PORTFOLIO__SUB_PORTFOLIO_LIST__MARINE)) )
{
	$this->load->view('objects/snippets/_popup_marine', $data);
}

/**
 * ENGINEERING - BOILER EXPLOSION
 * ------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_BL_ID )
{
    $this->load->view('objects/snippets/_popup_eng_bl', $data);
}

/**
 * ENGINEERING - CONTRACTOR ALL RISK
 * ---------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CAR_ID )
{
    $this->load->view('objects/snippets/_popup_eng_car', $data);
}

/**
 * ENGINEERING - CONTRACTOR PLANT & MACHINARY
 * ------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_CPM_ID )
{
    $this->load->view('objects/snippets/_popup_eng_cpm', $data);
}

/**
 * ENGINEERING - ELECTRONIC EQUIPMENT INSURANCE
 * ---------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EEI_ID )
{
    $this->load->view('objects/snippets/_popup_eng_eei', $data);
}

/**
 * ENGINEERING - ERECTION ALL RISKS
 * ---------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_EAR_ID )
{
    $this->load->view('objects/snippets/_popup_eng_ear', $data);
}

/**
 * ENGINEERING - MACHINE BREAKDOWN
 * ---------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_ENG_MB_ID )
{
    $this->load->view('objects/snippets/_popup_eng_mb', $data);
}

/**
 * MISCELLANEOUS - BANKER'S BLANKET(BB)
 * -------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_BB_ID )
{
    $this->load->view('objects/snippets/_popup_misc_bb', $data);
}

/**
 * MISCELLANEOUS - GROUP PERSONNEL ACCIDENT(GPA)
 * ---------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_GPA_ID )
{
    $this->load->view('objects/snippets/_popup_misc_gpa', $data);
}

/**
 * MISCELLANEOUS - PERSONNEL ACCIDENT(PA)
 * ---------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PA_ID )
{
    $this->load->view('objects/snippets/_popup_misc_pa', $data);
}

/**
 * MISCELLANEOUS - PUBLIC LIABILITY(PL)
 * ----------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_PL_ID )
{
    $this->load->view('objects/snippets/_popup_misc_pl', $data);
}

/**
 * MISCELLANEOUS - CASH IN TRANSIT
 * -------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CT_ID )
{
    $this->load->view('objects/snippets/_popup_misc_ct', $data);
}

/**
 * MISCELLANEOUS - CASH IN SAFE
 * -------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CS_ID )
{
    $this->load->view('objects/snippets/_popup_misc_cs', $data);
}

/**
 * MISCELLANEOUS - CASH IN COUNTER
 * -------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_CC_ID )
{
    $this->load->view('objects/snippets/_popup_misc_cc', $data);
}

/**
 * MISCELLANEOUS - EXPEDITION PERSONNEL ACCIDENT(EPA)
 * --------------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_EPA_ID )
{
    $this->load->view('objects/snippets/_popup_misc_epa', $data);
}

/**
 * MISCELLANEOUS - TRAVEL MEDICAL INSURANCE(TMI)
 * --------------------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_TMI_ID )
{
    $this->load->view('objects/snippets/_popup_misc_tmi', $data);
}

/**
 * MISCELLANEOUS - FIDELITY GUARANTEE (FG)
 * ----------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_FG_ID )
{
    $this->load->view('objects/snippets/_popup_misc_fg', $data);
}

/**
 * MISCELLANEOUS - HEALTH INSURANCE (HI)
 * ----------------------------------------
 */
else if( $portfolio_id == IQB_SUB_PORTFOLIO_MISC_HI_ID )
{
    $this->load->view('objects/snippets/_popup_misc_hi', $data);
}

