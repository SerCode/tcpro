<?php
/**
 * declination.php
 *
 * Displays a declination management page
 *
 * @package TeamCalPro
 * @version 3.6.000
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
require_once ("helpers/global_helper.php");
getOptions();
if (strlen($CONF['options']['lang'])) require ("includes/lang/" . $CONF['options']['lang'] . ".tcpro.php");
else                                  require ("includes/lang/english.tcpro.php");

require_once( "models/config_model.php" );
require_once( "models/log_model.php" );
require_once( "models/login_model.php" );
require_once( "models/user_model.php" );
require_once( "models/user_group_model.php" );
require_once( "models/user_option_model.php" );

$C = new Config_model;
$L = new Login_model;
$LOG = new Log_model;
$U  = new User_model;
$UG = new User_group_model;
$UO = new User_option_model;

$error=FALSE;

/**
 * Check if allowed
 */
if (!isAllowed("editDeclination")) showError("notallowed");

/**
 * =========================================================================
 * APPLY
 */
if ( isset($_POST['btn_apply']) ) {
   $monthnames = $CONF['monthnames'];
   $today = getdate();
   $curryear = $today['year']; // numeric value, 4 digits
   $currmonth = $today['mon']; // numeric value
   $declineupdate=false;

   /**
    * Absence threshold declination
    */
   if ( isset($_POST['chk_declAbsence']) ) {

      $C->saveConfig("declAbsence","1");

      if ( strlen($_POST['txt_declThreshold']) ) $C->saveConfig("declThreshold",$_POST['txt_declThreshold']);
      else                                       $C->saveConfig("declThreshold","0");

      switch ($_POST['opt_declBase']) {
         case "all":   $C->saveConfig("declBase","all"); break;
         case "group": $C->saveConfig("declBase","group"); break;
         default:      $C->saveConfig("declBase","all"); break;
      }

      $declineupdate=true;
   }
   else {
      $C->saveConfig("declAbsence","0");
   }

   /**
    * Before date declination
    */
   if ( isset($_POST['chk_declBefore']) ) {
      if ( isset($_POST['opt_declBefore']) AND $_POST['opt_declBefore']=="Today" ) {
         $C->saveConfig("declBefore","Today");
         $declineupdate=true;
      }
      else if ( isset($_POST['opt_declBefore']) AND $_POST['opt_declBefore']=="Date" ) {
         if ( strlen($_POST['txt_declBeforeDate']) ) {
            $C->saveConfig("declBefore","Date");
            $declinebefore = str_replace("-","",$_POST['txt_declBeforeDate']);
            $C->saveConfig("declBeforeDate",$declinebefore);
            $declineupdate=true;
         }
         else {
            $error=TRUE;
            $err_short = $LANG['err_input_caption'];
            $err_long  = $LANG['err_input_declbefore'];
            $err_module=$_SERVER['SCRIPT_NAME'];
            $err_btn_close=FALSE;
         }
      }
      else {
         $error=TRUE;
         $err_short = $LANG['err_input_caption'];
         $err_long  = $LANG['err_input_declbefore'];
         $err_module=$_SERVER['SCRIPT_NAME'];
         $err_btn_close=FALSE;
      }
   }
   else {
      $C->saveConfig("declBefore","0");
   }

   /**
    * Declination Period
    */
   if ( isset($_POST['chk_declPeriod']) ) {
      if ( strlen($_POST['txt_declPeriodStart']) && strlen($_POST['txt_declPeriodEnd']) ) {
         $periodstart = str_replace("-","",$_POST['txt_declPeriodStart']);
         $periodend   = str_replace("-","",$_POST['txt_declPeriodEnd']);
         if ($periodend>$periodstart) {
            $C->saveConfig("declPeriod","1");
            $C->saveConfig("declPeriodStart",$periodstart);
            $C->saveConfig("declPeriodEnd",$periodend);
            $declineupdate=true;
         }
         else {
            $error=TRUE;
            $err_short = $LANG['err_input_caption'];
            $err_long  = $LANG['err_input_period'];
            $err_module=$_SERVER['SCRIPT_NAME'];
            $err_btn_close=FALSE;
         }
      }
      else {
         $C->saveConfig("declPeriod","0");
         $error=TRUE;
         $err_short = $LANG['err_input_caption'];
         $err_long  = $LANG['err_input_period'];
         $err_module=$_SERVER['SCRIPT_NAME'];
         $err_btn_close=FALSE;
      }
   }
   else {
      $C->saveConfig("declPeriod","0");
   }

   /**
    * Notfication options
    */
   if (isset($_POST['chk_declNotifyUser'])) $C->saveConfig("declNotifyUser","1"); else $C->saveConfig("declNotifyUser","0");
   if (isset($_POST['chk_declNotifyManager'])) $C->saveConfig("declNotifyManager","1"); else $C->saveConfig("declNotifyManager","0");
   if (isset($_POST['chk_declNotifyDirector'])) $C->saveConfig("declNotifyDirector","1"); else $C->saveConfig("declNotifyDirector","0");
   if (isset($_POST['chk_declNotifyAdmin'])) $C->saveConfig("declNotifyAdmin","1"); else $C->saveConfig("declNotifyAdmin","0");

   /**
    * Log this event
    */
   if ($declineupdate) {
      $LOG->log("logConfig",$L->checkLogin(),"Decline settings updated");
      header("Location: ".$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']);
   }
}

require("includes/header_html_inc.php");
echo "<body>\r\n";
echo "<div id=\"overDiv\" style=\"position:absolute; visibility:hidden; z-index:1000;\"></div>\r\n";
require("includes/header_app_inc.php");
require("includes/menu_inc.php");
?>
<table style="width: 100%;">
   <tr>
      <td valign="top">
         <form class="form" name="form-decl" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">

            <!--  DECLINATION MANAGEMENT ============================================== -->
            <table class="dlg">
               <tr>
                  <td class="dlg-header" colspan="2">
                     <?php printDialogTop($LANG['decl_title'],"manage_declination.html","ico_declination.png"); ?>
                  </td>
               </tr>

               <?php $style="2"; ?>
               <tr>
                  <td class="dlg-caption" colspan="2"><?=$LANG['decl_options']?></td>
               </tr>

               <!--  Absence threshold declination -->
               <?php if ($style=="1") $style="2"; else $style="1"; ?>
               <tr>
                  <td class="config-row<?=$style?>" style="text-align: left; width: 50%;">
                     <span class="config-key"><?=$LANG['decl_threshold']?></span><br>
                     <span class="config-comment"><?=$LANG['decl_threshold_comment']?></span>
                  </td>
                  <td class="config-row<?=$style?>" style="text-align: left;">
                     <input style="vertical-align: middle;" name="chk_declAbsence" id="chk_declAbsence" value="chk_declAbsence" type="checkbox" <?=($C->readConfig("declAbsence"))?'checked':''?>><?=$LANG['decl_activate']?><br>
                     <div style="padding-left: 20px;">
                        <?=$LANG['decl_threshold_value']?>:&nbsp;<input style="margin-bottom: 4px;" name="txt_declThreshold" type="text" class="text" size="4" maxlength="2" value="<?=$C->readConfig("declThreshold")?>"><br>
                        <?=$LANG['decl_based_on']?>
                        <input name="opt_declBase" type="radio" value="all" <?=($C->readConfig("declBase")=="all")?'CHECKED':''?>><?=$LANG['decl_base_all']?>
                        <input name="opt_declBase" type="radio" value="group" <?=($C->readConfig("declBase")=="group")?'CHECKED':''?>><?=$LANG['decl_base_group']?>
                     </div>
                  </td>
               </tr>

               <!--  Decline before -->
               <?php if ($style=="1") $style="2"; else $style="1"; ?>
               <tr>
                  <td class="config-row<?=$style?>" style="text-align: left;">
                     <span class="config-key"><?=$LANG['decl_before']?></span><br>
                     <span class="config-comment"><?=$LANG['decl_before_comment']?></span>
                  </td>
                  <td class="config-row<?=$style?>" style="text-align: left;">
                     <script type="text/javascript">$(function() { $( "#txt_declBeforeDate" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd" }); });</script>
                     <input style="vertical-align: middle;" name="chk_declBefore" id="chk_declBefore" value="chk_declBefore" type="checkbox" <?=($C->readConfig("declBefore"))?'checked':''?>><?=$LANG['decl_activate']?><br>
                     <div style="padding-left: 20px;">
                        <input name="opt_declBefore" value="Today" type="radio" <?=($C->readConfig("declBefore")=="Today")?'checked':''?>><?=$LANG['decl_before_today']?><br>
                        <input name="opt_declBefore" value="Date" type="radio" <?=($C->readConfig("declBefore")=="Date")?'checked':''?>><?=$LANG['decl_before_date']?><br>
                        <?php
                           if ($C->readConfig("declBeforeDate")) $declbeforedate = substr($C->readConfig("declBeforeDate"),0,4)."-".substr($C->readConfig("declBeforeDate"),4,2)."-".substr($C->readConfig("declBeforeDate"),6,2);
                           else $declbeforedate="";
                        ?>
                        <input style="margin: 4px 0px 0px 20px;" name="txt_declBeforeDate" id="txt_declBeforeDate" size="10" maxlength="10" type="text" class="text" value="<?=$declbeforedate?>">
                     </div>
                  </td>
               </tr>

               <!--  Declination period -->
               <?php if ($style=="1") $style="2"; else $style="1"; ?>
               <tr>
                  <td class="config-row<?=$style?>" style="text-align: left;">
                     <span class="config-key"><?=$LANG['decl_period']?></span><br>
                     <span class="config-comment"><?=$LANG['decl_period_comment']?></span>
                  </td>
                  <td class="config-row<?=$style?>" style="text-align: left;">
                     <script type="text/javascript">
                        $(function() { $( "#txt_declPeriodStart" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd" }); });
                        $(function() { $( "#txt_declPeriodEnd" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd" }); });
                     </script>
                     <input style="vertical-align: middle;" name="chk_declPeriod" value="chk_declPeriod" type="checkbox" <?=($C->readConfig("declPeriod"))?'checked':''?>><?=$LANG['decl_activate']?><br>
                     <div style="padding-left: 20px;">
                        <?php
                           if ($C->readConfig("declPeriodStart")) $declPeriodStart = substr($C->readConfig("declPeriodStart"),0,4)."-".substr($C->readConfig("declPeriodStart"),4,2)."-".substr($C->readConfig("declPeriodStart"),6,2);
                           else $declPeriodStart="";
                        ?>
                        <input style="margin: 4px 0px 4px 0px;" name="txt_declPeriodStart" id="txt_declPeriodStart" size="10" maxlength="10" type="text" class="text" value="<?=$declPeriodStart?>">&nbsp;<?=$LANG['decl_period_start']?><br>
                        <?php
                           if ($C->readConfig("declPeriodEnd")) $declPeriodEnd = substr($C->readConfig("declPeriodEnd"),0,4)."-".substr($C->readConfig("declPeriodEnd"),4,2)."-".substr($C->readConfig("declPeriodEnd"),6,2);
                           else $declPeriodEnd="";
                        ?>
                        <input name="txt_declPeriodEnd" id="txt_declPeriodEnd" size="10" maxlength="10" type="text" class="text" value="<?=$declPeriodEnd?>">&nbsp;<?=$LANG['decl_period_end']?>
                     </div>
                  </td>
               </tr>

               <!--  Notifications -->
               <?php if ($style=="1") $style="2"; else $style="1"; ?>
               <tr>
                  <td class="config-row<?=$style?>" style="text-align: left;">
                     <span class="config-key"><?=$LANG['decl_notify']?></span><br>
                     <span class="config-comment"><?=$LANG['decl_notify_comment']?></span>
                  </td>
                  <td class="config-row<?=$style?>" style="text-align: left;">
                     <input style="vertical-align: middle;" name="chk_declNotifyUser" id="chk_declNotifyUser" type="checkbox" value="chkDeclNotifyUser" <?=($C->readConfig("declNotifyUser"))?'checked':''?>><?=$LANG['decl_notify_user']?><br>
                     <input style="vertical-align: middle;" name="chk_declNotifyManager" id="chk_declNotifyManager" type="checkbox" value="chk_declNotifyManager" <?=($C->readConfig("declNotifyManager"))?'checked':''?>><?=$LANG['decl_notify_manager']?><br>
                     <input style="vertical-align: middle;" name="chk_declNotifyDirector" id="chk_declDirector" type="checkbox" value="chk_declDirector" <?=($C->readConfig("declNotifyDirector"))?'checked':''?>><?=$LANG['decl_notify_director']?><br>
                     <input style="vertical-align: middle;" name="chk_declNotifyAdmin" id="chk_declNotifyAdmin" type="checkbox" value="chk_declNotifyAdmin" <?=($C->readConfig("declNotifyAdmin"))?'checked':''?>><?=$LANG['decl_notify_admin']?><br>
                  </td>
               </tr>
               <tr>
                  <td class="dlg-menu" style="text-align: left;" colspan="2">
                     <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
                  </td>
               </tr>
            </table>
         </form>
      <br>
   </td>
</tr>
</table>
<?php require("includes/footer_inc.php"); ?>