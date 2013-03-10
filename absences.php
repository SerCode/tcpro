<?php
/**
 * absences.php
 *
 * Displays the absence types page
 *
 * @package TeamCalPro
 * @version 3.5.002
 * @author George Lewe
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */

//echo "<script type=\"text/javascript\">alert(\"Debug: \");</script>";

/**
 * Set parent flag to control access to child scripts
 */
define( '_VALID_TCPRO', 1 );

/**
 * Includes
 */
require_once ("config.tcpro.php");
require_once ("includes/functions.tcpro.php");
getOptions();
if (strlen($CONF['options']['lang'])) require ("includes/lang/" . $CONF['options']['lang'] . ".tcpro.php");
else                                  require ("includes/lang/english.tcpro.php");

require_once("includes/tcabsence.class.php" );
require_once("includes/tcabsencegroup.class.php" );
require_once("includes/tcallowance.class.php" );
require_once("includes/tcconfig.class.php" );
require_once("includes/tcgroup.class.php" );
require_once("includes/tcholiday.class.php" );
require_once("includes/tclog.class.php" );
require_once("includes/tclogin.class.php" );
require_once("includes/tctemplate.class.php" );
require_once("includes/tcuser.class.php" );
require_once("includes/tcusergroup.class.php" );
require_once("includes/tcuseroption.class.php" );

$A = new tcAbsence;
$AG = new tcAbsenceGroup;
$B = new tcAllowance;
$C = new tcConfig;
$G = new tcGroup;
$H = new tcHoliday;
$L = new tcLogin;
$LOG = new tcLog;
$T  = new tcTemplate;
$U  = new tcUser;
$UG = new tcUserGroup;
$UO = new tcUserOption;

$monthnames = $CONF['monthnames'];
$today = getdate();
$curryear = $today['year']; // numeric value, 4 digits
$currmonth = $today['mon']; // numeric value
$themearray = getThemes();
$error = false;

/**
 * Check if allowed
 */
if (!isAllowed("editAbsenceTypes")) showError("notallowed");

/**
 * =========================================================================
 * ADD
 */
if (isset ($_POST['btn_abs_add'])) {
   $error = false;

   if (!preg_match('/^[A-Z0-9]*$/', $_POST['abs_sym'])) {
      // Invalid absence symbol
      $error = true;
      $err_short = $LANG['err_input_caption'];
      $err_long .= $LANG['err_input_abs_invalid_1'];
      $err_long .= strtoupper(trim($_POST['abs_sym']));
      $err_long .= $LANG['err_input_abs_invalid_2'];
      $err_module=$_SERVER['SCRIPT_NAME'];
      $err_btn_close=FALSE;
   }
   else {
      // See if we have a duplicate symbol
      $absences = $A->getAll();
      foreach ($absences as $row) {
         if ($row['cfgsym'] == strtoupper(trim($_POST['abs_sym']))) {
            $error = true;
            $err_short = $LANG['err_input_caption'];
            $err_long = $LANG['err_input_abs_taken_1'];
            $err_long .= strtoupper(trim($_POST['abs_sym']));
            $err_long .= $LANG['err_input_abs_taken_2'];
            $err_module=$_SERVER['SCRIPT_NAME'];
            $err_btn_close=FALSE;
         }
      }
   }

   if (!$error) {
      if (strtoupper(trim($_POST['abs_sym'])) != '') {
         /**
          * Create the absence type
          */
         $A->cfgsym = strtoupper(trim($_POST['abs_sym']));
         $A->cfgname = "a" . strval(rand(1000, 9999));
         $A->dspsym = strtoupper(trim($_POST['abs_sym']));
         $A->dspname = trim($_POST['abs_name']);
         $A->dspcolor = strtoupper(trim($_POST['abs_color']));
         $A->dspbgcolor = strtoupper(trim($_POST['abs_bgcolor']));
         $A->allowance = trim($_POST['abs_allowance']);
         $A->factor = trim($_POST['abs_factor']);

         if (isset($_POST['chkAbsShowRemain'])) $A->setOptions($CONF['A_SHOWREMAIN']);
         if (isset($_POST['chkAbsShowTotals'])) $A->setOptions($CONF['A_SHOWTOTAL']);
         if (isset($_POST['chkApproval']))         $A->setOptions($CONF['A_APPROVAL']);
         if (isset($_POST['chkPresence']))         $A->setOptions($CONF['A_PRESENCE']);
         if (isset($_POST['chkManagerOnly']))      $A->setOptions($CONF['A_MGR_ONLY']);
         if (isset($_POST['chkHideInProfile']))    $A->setOptions($CONF['A_HIDE_IN_PROFILE']);
         if (isset($_POST['chkConfidential']))     $A->setOptions($CONF['A_CONFIDENTIAL']);

         $A->iconfile = NULL;
         if (isset($_POST['abs_icon']) AND $_POST['abs_icon'] != "no_icon") $A->iconfile = $_POST['abs_icon'];

         $A->create();

         /**
          * Assign it to all groups by default
          */
         $groups = $G->getAll();
         foreach ($groups as $Grow) {
            $AG->assign($A->cfgsym,$Grow['groupname']);
            //echo "<script type=\"text/javascript\">alert(\"Debug: ".$A->cfgsym."|".$Grow['groupname']."\");</script>";
         }

         /**
          * Create the theme css files so it includes this absence type
          */
         foreach ($themearray as $theme) {
            createCSS($theme["name"]);
         }

         sendNotification("absenceadd", strtoupper(trim($_POST['abs_sym'])), "");
         /**
          * Log this event
          */
         $LOG->log("logAbsence", $L->checkLogin(), "Absence created: " . $A->dspsym . " " . $A->dspname . " " . $A->dspcolor . " " . $A->dspbgcolor . " " . $A->allowance . " " . $A->factor . " " . $A->options);
      }
      else {
         /**
          * No absence symbol was submitted
          */
         $error = true;
         $err_short = $LANG['err_input_caption'];
         $err_long  = $LANG['err_input_abs_add'];
         $err_module=$_SERVER['SCRIPT_NAME'];
         $err_btn_close=FALSE;
      }
   }
}
/**
 * =========================================================================
 * UPDATE
 */
elseif (isset ($_POST['btn_abs_update'])) {
   $error = false;

   if (!preg_match('/^[A-Z0-9]*$/', $_POST['abs_symbol'])) {
      // See if we have an invalid absence symbol
      $error = true;
      $err_short = $LANG['err_input_caption'];
      $err_long = $LANG['err_input_abs_invalid_1'];
      $err_long .= strtoupper(trim($_POST['abs_symbol']));
      $err_long .= $LANG['err_input_abs_invalid_2'];
      $err_module=$_SERVER['SCRIPT_NAME'];
      $err_btn_close=FALSE;
   }
   else {
      // See whether an updated symbol is already taken
      if ( strtoupper(trim($_POST['abs_symbol'])) != strtoupper(trim($_POST['abs_symbolhidden'])) ) {
         $absences = $A->getAll();
         foreach ($absences as $row) {
            if ($row['cfgsym'] == strtoupper(trim($_POST['abs_symbol']))) {
               $error = true;
               $err_short = $LANG['err_input_caption'];
               $err_long  = $LANG['err_input_abs_taken_1'];
               $err_long .= strtoupper(trim($_POST['abs_symbol']));
               $err_long .= $LANG['err_input_abs_taken_2'];
               $err_module=$_SERVER['SCRIPT_NAME'];
               $err_btn_close=FALSE;
            }
         }
      }
   }

   if (!$error) {
      $oldsym = strtoupper(trim($_POST['abs_symbolhidden']));
      $newsym = strtoupper(trim($_POST['abs_symbol']));

      $A->findBySymbol($oldsym);
      $A->cfgsym = $newsym;
      $A->dspsym = $newsym;
      $A->dspname = $_POST['abs_dspname'];
      $A->dspcolor = $_POST['abs_color'];
      $A->dspbgcolor = $_POST['abs_bgcolor'];
      $A->allowance = $_POST['abs_allowance'];
      $A->factor = $_POST['abs_factor'];

      $A->clearOptions($CONF['A_SHOWREMAIN']);
      if (isset($_POST['chkAbsShowRemain'])) $A->setOptions($CONF['A_SHOWREMAIN']);

      $A->clearOptions($CONF['A_SHOWTOTAL']);
      if (isset($_POST['chkAbsShowTotals'])) $A->setOptions($CONF['A_SHOWTOTAL']);

      $A->clearOptions($CONF['A_APPROVAL']);
      if (isset($_POST['chkApproval'])) $A->setOptions($CONF['A_APPROVAL']);

      $A->clearOptions($CONF['A_PRESENCE']);
      if (isset($_POST['chkPresence'])) $A->setOptions($CONF['A_PRESENCE']);

      $A->clearOptions($CONF['A_MGR_ONLY']);
      if (isset($_POST['chkManagerOnly'])) $A->setOptions($CONF['A_MGR_ONLY']);

      $A->clearOptions($CONF['A_HIDE_IN_PROFILE']);
      if (isset($_POST['chkHideInProfile'])) $A->setOptions($CONF['A_HIDE_IN_PROFILE']);

      $A->clearOptions($CONF['A_CONFIDENTIAL']);
      if (isset($_POST['chkConfidential'])) $A->setOptions($CONF['A_CONFIDENTIAL']);

      $A->update($oldsym);
      
      /**
       * Change all calendars to the new symbol
       */
      $T->replaceSymbol($oldsym, $newsym);

      /**
       * Create the current theme css file so it includes this absence type
       */
      foreach ($themearray as $theme) {
         createCSS($theme["name"]);
      }
      sendNotification("absencechange", $oldsym, "");

      /**
       * Log this event
       */
      $LOG->log("logAbsence", $L->checkLogin(), "Absence updated: " . $A->dspsym . " " . $A->dspname . " " . $A->dspcolor . " " . $A->dspbgcolor . " " . $A->allowance . " " . $A->factor . " " . $A->options);
   }
}
/**
 * =========================================================================
 * DELETE
 */
elseif (isset ($_POST['btn_abs_delete'])) {
   $delsym = strtoupper(trim($_POST['abs_symbolhidden']));
   $A->deleteBySymbol($delsym);
   $AG->unassignAbsence($delsym);
   $T->replaceSymbol($delsym, $CONF['present']);
   /**
    * Create the current theme css file so it is removed from there
    */
   foreach ($themearray as $theme) {
       createCSS($theme["name"]);
   }
   sendNotification("absencedelete", $delsym, "");
   /**
    * Log this event
    */
   $LOG->log("logAbsence", $L->checkLogin(), "Absence deleted: " . $delsym);
}
/**
 * Show HTML header
 * Use this file to adjust your meta tags and such
 */
require("includes/header.html.inc.php");

echo "<body>\r\n";
echo "<div id=\"overDiv\" style=\"position:absolute; visibility:hidden; z-index:1000;\"></div>\r\n";

/**
 * Show application header
 * This is the file to change in order to put different images at the top
 * of the main page.
 */
include ($CONF['app_root'] . "includes/header.application.inc.php");

/**
 * Show menu header
 * This is the file containing the TeamCal menu
 */
include ($CONF['app_root'] . "includes/menu.inc.php");
?>
<div id="content">
   <div id="content-content">
      <table class="dlg">
         <tr>
            <td class="dlg-header" colspan="5">
               <?php printDialogTop($LANG['admin_absence_title'],"manage_absence_types.html","ico_absences.png"); ?>
            </td>
         </tr>
         <tr>
            <td>
               <form class="form" name="form-abs-add" method="POST" action="<?=$_SERVER['PHP_SELF']?>?lang=<?=$CONF['options']['lang']?>">
               <table style="border-collapse: collapse; width: 100%;">
                  <tr>
                     <td class="dlg-caption" colspan="2" style="text-align: center;">[<?=$LANG['ea_column_symbol']?>]</td>
                     <td class="dlg-caption"><?=$LANG['ea_column_name']?></td>
                     <td class="dlg-caption"><?=$LANG['ea_column_color']?></td>
                     <td class="dlg-caption"><?=$LANG['ea_column_bgcolor']?></td>
                     <td class="dlg-caption-tt" style="text-align: center;"  title="<?=$LANG['ea_column_allowance_mouseover']?>"><?=$LANG['ea_column_allowance']?></td>
                     <td class="dlg-caption-tt" style="text-align: center;" title="<?=$LANG['ea_column_factor_mouseover']?>"><?=$LANG['ea_column_factor']?></td>
                     <td class="dlg-caption-tt" style="text-align: center;" title="<?=$LANG['ea_column_showremain_mouseover']?>"><?=$LANG['ea_column_showremain']?></td>
                     <td class="dlg-caption-tt" style="text-align: center;" title="<?=$LANG['ea_column_showtotals_mouseover']?>"><?=$LANG['ea_column_showtotals']?></td>
                     <td class="dlg-caption-tt" style="text-align: center;" title="<?=$LANG['ea_column_approval_mouseover']?>"><?=$LANG['ea_column_approval']?></td>
                     <td class="dlg-caption-tt" style="text-align: center;" title="<?=$LANG['ea_column_presence_mouseover']?>"><?=$LANG['ea_column_presence']?></td>
                     <td class="dlg-caption-tt" style="text-align: center;" title="<?=$LANG['ea_column_manager_only_mouseover']?>"><?=$LANG['ea_column_manager_only']?></td>
                     <td class="dlg-caption-tt" style="text-align: center;" title="<?=$LANG['ea_column_hide_in_profile_mouseover']?>"><?=$LANG['ea_column_hide_in_profile']?></td>
                     <td class="dlg-caption-tt" style="text-align: center;" title="<?=$LANG['ea_column_confidential_mouseover']?>"><?=$LANG['ea_column_confidential']?></td>
                     <td class="dlg-caption-tt" title="<?=$LANG['ea_column_groups_mouseover']?>"><?=$LANG['ea_column_groups']?></td>
                     <td class="dlg-caption" style="padding-right: 9px; text-align: right;"><?=$LANG['ea_column_action']?></td>
                  </tr>
                  <tr>
                     <td class="dlg-row1" width="34" style="text-align: center; vertical-align: middle;"><img src="themes/<?=$theme?>/img/ico_add.png" alt=""></td>
                     <td class="dlg-row1" width="30"><input name="abs_sym" size="1" maxlength="1" type="text" class="text" value=""></td>
                     <td class="dlg-row1" width="110"><input name="abs_name" size="16" type="text" class="text" value=""></td>
                     <td class="dlg-row1" width="80"><input name="abs_color" id="color-new" size="5" maxlength="6" type="text" class="text" value=""></td>
                     <td class="dlg-row1" width="80"><input name="abs_bgcolor" id="bgcolor-new" size="5" maxlength="6" type="text" class="text" value=""></td>
                     <td class="dlg-row1" width="30"><input name="abs_allowance" size="1" maxlength="3" type="text" class="text" value=""></td>
                     <td class="dlg-row1" width="30"><input name="abs_factor" size="1" maxlength="3" type="text" class="text" value=""></td>
                     <td class="dlg-row1" style="text-align: center;" width="30"><input name="chkAbsShowRemain" type="checkbox" value="chkAbsShowRemain"></td>
                     <td class="dlg-row1" style="text-align: center;" width="30"><input name="chkAbsShowTotals" type="checkbox" value="chkAbsShowTotals"></td>
                     <td class="dlg-row1" style="text-align: center;" width="30"><input name="chkApproval" type="checkbox" value="chkApproval"></td>
                     <td class="dlg-row1" style="text-align: center;" width="30"><input name="chkPresence" type="checkbox" value="chkPresence"></td>
                     <td class="dlg-row1" style="text-align: center;" width="30"><input name="chkManagerOnly" type="checkbox" value="chkManagerOnly"></td>
                     <td class="dlg-row1" style="text-align: center;" width="30"><input name="chkHideInProfile" type="checkbox" value="chkHideInProfile"></td>
                     <td class="dlg-row1" style="text-align: center;" width="30"><input name="chkConfidential" type="checkbox" value="chkConfidential"></td>
                     <td class="dlg-row1">&nbsp;</td>
                     <td class="dlg-row1" style="padding-right: 9px; text-align: right;"><input name="btn_abs_add" type="submit" class="button" value="<?=$LANG['btn_add']?>"></td>
                  </tr>
               </table>
               </form>
            </td>
         </tr>
         <?php
         $i = 1;
         $printrow = 1;
         $absids = "#color-new, #bgcolor-new, ";
         $absences = $A->getAll("dspname");
         $arows = count($absences);
         foreach ($absences as $row) {
            $A->findBySymbol($row['cfgsym']);
            if ($A->cfgsym != $CONF['present']) {
               if ($printrow == 1)
                  $printrow = 2;
               else
                  $printrow = 1;
               ?>
               <!-- <?=$A->dspname?> -->
               <tr>
                  <?php if ($i==$arows-1) $style =" style=\"border-bottom:1px solid #000000\""; else $style=""; ?>
                  <td class="dlg-row<?=$printrow?>"<?=$style?>>
                     <form class="form" name="form-abs-<?=$i?>" method="POST" action="<?=$_SERVER['PHP_SELF']?>?lang=<?=$CONF['options']['lang']?>">
                     <table style="border-collapse: collapse; border: 0px; width: 100%;">
                        <tr>
                           <td class="dlg-rowcell" width="30" height="20">
                              <table style="border-collapse: collapse; border: 0px; width: 100%;">
                                 <tr>
                                    <td class="<?=$A->cfgname?>" height="20">
                                    <?php if ($A->iconfile) { ?>
                                       <img align="top" alt="" src="<?=$CONF['app_icon_dir'].$A->iconfile?>" width="16" height="16" onmouseover="return overlib('<?=$LANG['ea_tt_icon']?>',<?=$CONF['ovl_tt_settings']?>);" onmouseout="return nd();">
                                    <?php
                                    }
                                    else {
                                       echo $A->dspsym;
                                    }
                                    ?>
                                    </td>
                                 </tr>
                              </table>
                           </td>
                           <td class="dlg-rowcell" width="30">
                              <input name="abs_symbolhidden" type="hidden" class="text" value="<?=$A->cfgsym?>">
                              <input name="abs_symbol" size="1" maxlength="1" type="text" class="text" style="text-align: center;" value="<?=$A->dspsym?>">
                           </td>
                           <td class="dlg-rowcell" width="110"><input name="abs_dspname" size="16" type="text" class="text" value="<?=$A->dspname?>"></td>
                           <td class="dlg-rowcell" width="80"><input name="abs_color" id="color-<?=$i?>" size="5" maxlength="6" type="text" class="text" value="<?=$A->dspcolor?>"></td>
                           <td class="dlg-rowcell" width="80"><input name="abs_bgcolor" id="bgcolor-<?=$i?>" size="5" maxlength="6" type="text" class="text" value="<?=$A->dspbgcolor?>"></td>
                           <td class="dlg-rowcell" width="30"><input name="abs_allowance" size="1" maxlength="3" type="text" class="text" style="text-align: center;" value="<?=$A->allowance?>"></td>
                           <td class="dlg-rowcell" width="30"><input name="abs_factor" size="1" maxlength="3" type="text" class="text" style="text-align: center;" value="<?=$A->factor?>"></td>
                           <td class="dlg-rowcell" style="text-align: center;" width="30"><input name="chkAbsShowRemain" type="checkbox" value="chkAbsShowRemain" <?=($A->checkOptions($CONF['A_SHOWREMAIN'])?'CHECKED':'')?>></td>
                           <td class="dlg-rowcell" style="text-align: center;" width="30"><input name="chkAbsShowTotals" type="checkbox" value="chkAbsShowTotals" <?=($A->checkOptions($CONF['A_SHOWTOTAL'])?'CHECKED':'')?>></td>
                           <td class="dlg-rowcell" style="text-align: center;" width="30"><input name="chkApproval" type="checkbox" value="chkApproval" <?=($A->checkOptions($CONF['A_APPROVAL'])?'CHECKED':'')?>></td>
                           <td class="dlg-rowcell" style="text-align: center;" width="30"><input name="chkPresence" type="checkbox" value="chkPresence" <?=($A->checkOptions($CONF['A_PRESENCE'])?'CHECKED':'')?>></td>
                           <td class="dlg-rowcell" style="text-align: center;" width="30"><input name="chkManagerOnly" type="checkbox" value="chkManagerOnly" <?=($A->checkOptions($CONF['A_MGR_ONLY'])?'CHECKED':'')?>></td>
                           <td class="dlg-rowcell" style="text-align: center;" width="30"><input name="chkHideInProfile" type="checkbox" value="chkHideInProfile" <?=($A->checkOptions($CONF['A_HIDE_IN_PROFILE'])?'CHECKED':'')?>></td>
                           <td class="dlg-rowcell" style="text-align: center;" width="30"><input name="chkConfidential" type="checkbox" value="chkConfidential" <?=($A->checkOptions($CONF['A_CONFIDENTIAL'])?'CHECKED':'')?>></td>
                           <td class="dlg-rowcell" width="50">
                              <div style="border: 1px solid #BBBBBB; padding: 1px;">
                              <?php
                              $all=true;
                              $groups = $G->getAll();
                              foreach ($groups as $row) {
                                 if (!$AG->isAssigned($A->cfgsym,$row['groupname'])) $all=false;
                              }
                              if ($all) echo $LANG['ea_groups_all']; else echo $LANG['ea_groups_selection'];
                              ?>
                              </div>
                           </td>
                           <td class="dlg-rowcell" style="text-align: left; width: 24px;">
                              <a href="javascript:this.blur();openPopup('absencegroup.php?abs=<?=$A->cfgsym?>','assign','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=500,height=440');">
                              <img align="top" alt="" src="themes/<?=$theme?>/img/ico_group.png" border="0" onmouseover="return overlib('<?=$LANG['ea_tt_groups']?>',<?=$CONF['ovl_tt_settings']?>);" onmouseout="return nd();"></a>
                           </td>
                           <td class="dlg-rowcell" style="text-align: right;">
                              <input name="btn_abs_icon" type="button" class="button" value="<?=$LANG['btn_icon']?>" onclick="javascript:this.blur();openPopup('absicon.php?absence=<?=$A->cfgsym?>&amp;lang=<?=$CONF['options']['lang']?>','absicon','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=600,height=460');">&nbsp;
                              <input name="btn_abs_update" type="submit" class="button" value="<?=$LANG['btn_update']?>">&nbsp;
                              <input name="btn_abs_delete" type="submit" class="button" value="<?=$LANG['btn_delete']?>" onclick="return confirmSubmit('<?=$LANG['ea_delete_confirm']?>')">
                           </td>
                        </tr>
                     </table>
                     </form>
                  </td>
               </tr>
               <?php
               $absids.="#color-".$i.", #bgcolor-".$i.", ";
               $i += 1;
            }
         }
         ?>
      </table>
   </div>
</div>
<?php
$absids=substr($absids,0,-2);
?>
<script type="text/javascript">$(function() { $( "<?=$absids?>" ).ColorPicker({ onSubmit: function(hsb, hex, rgb, el) { $(el).val(hex.toUpperCase()); $(el).ColorPickerHide(); }, onBeforeShow: function () { $(this).ColorPickerSetColor(this.value); } }) .bind('keyup', function(){ $(this).ColorPickerSetColor(this.value); }); });</script>
<?php require("includes/footer.html.inc.php"); ?>