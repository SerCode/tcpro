<?php
/**
 * config.php
 *
 * Displays the configuration page
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
else require ("includes/lang/english.tcpro.php");

require_once("models/config_model.php");
require_once("models/log_model.php");
require_once("models/login_model.php");
require_once("models/permission_model.php");
require_once("models/region_model.php");
require_once("models/user_model.php");
require_once("includes/timezones.inc.php");

$C = new Config_model;
$L = new Login_model;
$LOG = new Log_model;
$P = new Permission_model;
$R = new Region_model;
$U  = new User_model;

$error = false;

/**
 * Check if allowed
 */
if (!isAllowed("editConfig")) showError("notallowed");

$monthnames = $CONF['monthnames'];
$today = getdate();
$curryear = $today['year'];                   // numeric value, 4 digits
$currmonth = $today['mon'];                   // numeric value
$daytoday   = sprintf("%02d",$today['mday']); // Numeric representation of todays' day of the month
$monthtoday = sprintf("%02d",$today['mon']);  // Numeric representation of todays' month
$yeartoday  = $today['year'];                 // A full numeric representation of todays' year, 4 digits

/**
 * =========================================================================
 * APPLY
 */
if ( isset($_POST['btn_apply']) ) {
   /**
    * Calendar display options
    */
   if ($_POST['opt_showMonths']) $C->saveConfig("showMonths",$_POST['opt_showMonths']);
   if ( isset($_POST['chk_showWeekNumbers']) && $_POST['chk_showWeekNumbers'] ) $C->saveConfig("showWeekNumbers","1"); else $C->saveConfig("showWeekNumbers","0");
   if ($_POST['opt_firstDayOfWeek']) $C->saveConfig("firstDayOfWeek",$_POST['opt_firstDayOfWeek']);
   if ( isset($_POST['chk_satBusi']) && $_POST['chk_satBusi'] ) $C->saveConfig("satBusi","1"); else $C->saveConfig("satBusi","0");
   if ( isset($_POST['chk_sunBusi']) && $_POST['chk_sunBusi'] ) $C->saveConfig("sunBusi","1"); else $C->saveConfig("sunBusi","0");
   if ( isset($_POST['chk_includeRemainder']) && $_POST['chk_includeRemainder'] ) $C->saveConfig("includeRemainder","1"); else $C->saveConfig("includeRemainder","0");
   if ( isset($_POST['chk_includeRemainderTotal']) && $_POST['chk_includeRemainderTotal'] ) $C->saveConfig("includeRemainderTotal","1"); else $C->saveConfig("includeRemainderTotal","0");
   if ( isset($_POST['chk_includeTotals']) && $_POST['chk_includeTotals'] ) $C->saveConfig("includeTotals","1"); else $C->saveConfig("includeTotals","0");
   if ( isset($_POST['chk_showRemainder']) && $_POST['chk_showRemainder'] ) $C->saveConfig("showRemainder","1"); else $C->saveConfig("showRemainder","0");
   if ( isset($_POST['chk_includeSummary']) && $_POST['chk_includeSummary'] ) $C->saveConfig("includeSummary","1"); else $C->saveConfig("includeSummary","0");
   if ( isset($_POST['chk_showSummary']) && $_POST['chk_showSummary'] ) $C->saveConfig("showSummary","1"); else $C->saveConfig("showSummary","0");
   $C->saveConfig("usersPerPage",intval($_POST['txt_usersPerPage']));
   $C->saveConfig("repeatHeaderCount",intval($_POST['txt_repeatHeaderCount']));
   $C->saveConfig("todayBorderColor",strip_tags(stripslashes($_POST['txt_todayBorderColor'])));
   $C->saveConfig("todayBorderSize",intval($_POST['txt_todayBorderSize']));
   $C->saveConfig("pastDayColor",strip_tags(stripslashes($_POST['txt_pastDayColor'])));
   if (trim($_POST['sel_defregion']))    $C->saveConfig("defregion",trim($_POST['sel_defregion'])); else $C->saveConfig("defregion","default");
   if (trim($_POST['sel_defgroupfilter'])) $C->saveConfig("defgroupfilter",trim($_POST['sel_defgroupfilter'])); else $C->saveConfig("defgroupfilter","All");
   if ( isset($_POST['chk_hideManagers']) && $_POST['chk_hideManagers'] ) $C->saveConfig("hideManagers","1"); else $C->saveConfig("hideManagers","0");
   if ( isset($_POST['chk_hideDaynotes']) && $_POST['chk_hideDaynotes'] ) $C->saveConfig("hideDaynotes","1"); else $C->saveConfig("hideDaynotes","0");
   if ( isset($_POST['chk_markConfidential']) ) $C->saveConfig("markConfidential","1"); else $C->saveConfig("markConfidential","0");

   /**
    * User icons and avatars
    */
   if ( isset($_POST['chk_showUserIcons']) && $_POST['chk_showUserIcons'] ) $C->saveConfig("showUserIcons","1"); else $C->saveConfig("showUserIcons","0");
   if ( isset($_POST['chk_showAvatars']) && $_POST['chk_showAvatars'] ) $C->saveConfig("showAvatars","1"); else $C->saveConfig("showAvatars","0");
   $C->saveConfig("avatarWidth",intval($_POST['txt_avatarWidth']));
   $C->saveConfig("avatarHeight",intval($_POST['txt_avatarHeight']));

   /**
    * User custom fields
    */
   $C->saveConfig("userCustom1",strip_tags(stripslashes($_POST['txt_userCustom1'])));
   $C->saveConfig("userCustom2",strip_tags(stripslashes($_POST['txt_userCustom2'])));
   $C->saveConfig("userCustom3",strip_tags(stripslashes($_POST['txt_userCustom3'])));
   $C->saveConfig("userCustom4",strip_tags(stripslashes($_POST['txt_userCustom4'])));
   $C->saveConfig("userCustom5",strip_tags(stripslashes($_POST['txt_userCustom5'])));

   /**
    * User group assignment page
    */
   if (intval($_POST['txt_repeatHeadersAfter'])>0) $C->saveConfig("repeatHeadersAfter",intval($_POST['txt_repeatHeadersAfter'])); else $C->saveConfig("repeatHeadersAfter",10);
   if (intval($_POST['txt_repeatUsernamesAfter'])>0) $C->saveConfig("repeatUsernamesAfter",intval($_POST['txt_repeatUsernamesAfter'])); else $C->saveConfig("repeatUsernamesAfter",10);

   /**
    * General options
    */
   if (trim($_POST['sel_pscheme'])) $C->saveConfig("permissionScheme",trim($_POST['sel_pscheme'])); else $C->saveConfig("permissionScheme","Default");
   if ( isset($_POST['periodfrom']) AND isset($_POST['periodto']) ) {
      $fromstamp = strtotime($_POST['periodfrom']);
      $tostamp = strtotime($_POST['periodto']);
      if ($tostamp > $fromstamp) {
         $C->saveConfig("defperiodfrom",trim($_POST['periodfrom']));
         $C->saveConfig("defperiodto",trim($_POST['periodto']));
      }
      else {
         $C->saveConfig("defperiodfrom",$yeartoday."-01-01");
         $C->saveConfig("defperiodto",$yeartoday."-12-31");
         $_POST['periodfrom'] = $yeartoday."-01-01";
         $_POST['periodto'] = $yeartoday."-12-31";
      }
   }
   else {
      $C->saveConfig("defperiodfrom",$yeartoday."-01-01");
      $C->saveConfig("defperiodto",$yeartoday."-12-31");
      $_POST['periodfrom'] = $yeartoday."-01-01";
      $_POST['periodto'] = $yeartoday."-12-31";
   }
   
   $C->saveConfig("appSubTitle",htmlspecialchars($_POST['txt_appSubTitle']));
   if ($_POST['opt_homepage']) $C->saveConfig("homepage",$_POST['opt_homepage']);
   $C->saveConfig("welcomeTitle",htmlspecialchars(addslashes($_POST['txt_welcomeTitle'])));
   $C->saveConfig("welcomeText",htmlspecialchars(addslashes($_POST['txt_welcomeText'])));
   if ($_POST['sel_welcomeIcon']) $C->saveConfig("welcomeIcon",$_POST['sel_welcomeIcon']); else $C->saveConfig("welcomeIcon","No");
   if ( isset($_POST['chk_showLanguage']) && $_POST['chk_showLanguage'] ) $C->saveConfig("showLanguage","1"); else $C->saveConfig("showLanguage","0");
   if ( isset($_POST['chk_showGroup']) && $_POST['chk_showGroup'] ) $C->saveConfig("showGroup","1"); else $C->saveConfig("showGroup","0");
   if ( isset($_POST['chk_showRegion']) && $_POST['chk_showRegion'] ) $C->saveConfig("showRegion","1"); else $C->saveConfig("showRegion","0");
   if ( isset($_POST['chk_showToday']) && $_POST['chk_showToday'] ) $C->saveConfig("showToday","1"); else $C->saveConfig("showToday","0");
   if ( isset($_POST['chk_showStart']) && $_POST['chk_showStart'] ) $C->saveConfig("showStart","1"); else $C->saveConfig("showStart","0");
   $C->saveConfig("appFooterCpy",htmlspecialchars($_POST['txt_appFooterCpy']));
   if (trim($_POST['sel_theme'])) {
      $C->saveConfig("theme",trim($_POST['sel_theme']));
      createCSS(trim($_POST['sel_theme']));
   }
   else {
      $C->saveConfig("theme","tcpro");
      createCSS("tcpro");
   }
   if ( isset($_POST['chk_allowUserTheme']) && $_POST['chk_allowUserTheme'] ) $C->saveConfig("allowUserTheme","1"); else $C->saveConfig("allowUserTheme","0");
   if ( isset($_POST['chk_webMeasure']) && $_POST['chk_webMeasure'] ) $C->saveConfig("webMeasure","1"); else $C->saveConfig("webMeasure","0");

   /**
    * System options
    */
   if ( isset($_POST['chk_googleAnalytics']) && $_POST['chk_googleAnalytics'] ) {
      if (preg_match('/\bUA-\d{4,10}-\d{1,4}\b/', $_POST['txt_googleAnalyticsID'])) {
         $C->saveConfig("googleAnalytics","1");
         $C->saveConfig("googleAnalyticsID",$_POST['txt_googleAnalyticsID']);
      }
   }
   else {
      $C->saveConfig("googleAnalytics","0");
      $C->saveConfig("googleAnalyticsID","");
   }
   if ( isset($_POST['chk_jQueryCDN']) && $_POST['chk_jQueryCDN'] ) $C->saveConfig("jQueryCDN","1"); else $C->saveConfig("jQueryCDN","0");
   if ( isset($_POST['chk_debugHide']) && $_POST['chk_debugHide'] ) $C->saveConfig("debugHide","1"); else $C->saveConfig("debugHide","0");
   if ($_POST['sel_timeZone']) $C->saveConfig("timeZone",$_POST['sel_timeZone']); else $C->saveConfig("timeZone","default");

   /**
    * Email options
    */
   if ( isset($_POST['chk_emailNotifications']) && $_POST['chk_emailNotifications'] ) $C->saveConfig("emailNotifications","1"); else $C->saveConfig("emailNotifications","0");
   $C->saveConfig("mailFrom",strip_tags(stripslashes($_POST['txt_mailFrom'])));
   if (checkEmail($_POST['txt_mailReply'])) $C->saveConfig("mailReply",$_POST['txt_mailReply']);
   if ( isset($_POST['chk_mailSMTP']) && $_POST['chk_mailSMTP'] ) $C->saveConfig("mailSMTP","1"); else $C->saveConfig("mailSMTP","0");
   $C->saveConfig("mailSMTPhost",strip_tags(stripslashes($_POST['txt_mailSMTPhost'])));
   $C->saveConfig("mailSMTPport",intval($_POST['txt_mailSMTPport']));
   $C->saveConfig("mailSMTPusername",strip_tags(stripslashes($_POST['txt_mailSMTPusername'])));
   $C->saveConfig("mailSMTPpassword",strip_tags(stripslashes($_POST['txt_mailSMTPpassword'])));

   /**
    * User registration
    */
   if ( isset($_POST['chk_allowRegistration']) && $_POST['chk_allowRegistration'] ) $C->saveConfig("allowRegistration","1"); else $C->saveConfig("allowRegistration","0");
   if ( isset($_POST['chk_emailConfirmation']) && $_POST['chk_emailConfirmation'] ) $C->saveConfig("emailConfirmation","1"); else $C->saveConfig("emailConfirmation","0");
   if ( isset($_POST['chk_adminApproval']) && $_POST['chk_adminApproval'] ) $C->saveConfig("adminApproval","1"); else $C->saveConfig("adminApproval","0");

   /**
    * Login options
    */
   $C->saveConfig("pwdLength",intval($_POST['txt_pwdLength']));
   if ($_POST['opt_pwdStrength']) $C->saveConfig("pwdStrength",$_POST['opt_pwdStrength']);
   $C->saveConfig("badLogins",intval($_POST['txt_badLogins']));
   $C->saveConfig("gracePeriod",intval($_POST['txt_gracePeriod']));
   $C->saveConfig("cookieLifetime",intval($_POST['txt_cookieLifetime']));

   /**
    * Log this event
    */
   $LOG->log("logConfig",$L->checkLogin(),"Configuration changed");
   header("Location: ".$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']);
}
/**
 * =========================================================================
 * REBUILD STYLE SHEET
 */
if ( isset($_POST['btn_styles']) ) {
   if (trim($_POST['sel_theme'])) {
      $C->saveConfig("theme",trim($_POST['sel_theme']));
      createCSS(trim($_POST['sel_theme']));
   }
   else {
      $C->saveConfig("theme","tcpro");
      createCSS("tcpro");
   }

   /**
    * Log this event
    */
   $LOG->log("logConfig",$L->checkLogin(),"Style sheet rebuilt");
   header("Location: ".$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']);
}

require("includes/header.html.inc.php");
echo "<body>\r\n";
echo "<div id=\"overDiv\" style=\"position:absolute; visibility:hidden; z-index:1000;\"></div>\r\n";
require("includes/header.application.inc.php");
require("includes/menu.inc.php");

if (ini_get('register_globals')) {
   echo ("<script type=\"text/javascript\">alert(\"" . $LANG['admin_config_register_globals']. "\")</script>");
   ?>
   <table style="width: 100%;">
      <tr>
         <td style="padding-top: 8px; padding-bottom: 8px; width=24px;" width="24">
            <img src="themes/<?=$theme?>/img/icons/important.png" alt="">
         </td>
         <td class="erraction" style="padding-top: 8px; padding-bottom: 8px">
            <?=$LANG['admin_config_register_globals_on']?>
         </td>
      </tr>
   </table>
<?php } ?>

<div id="content">
   <div id="content-content">
      <form class="form" name="form-config" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
      <table class="dlg">
         <tr>
            <td class="dlg-header" colspan="2">
               <?php printDialogTop($LANG['admin_config_title'],"configuration.html","ico_configure.png"); ?>
            </td>
         </tr>

         <tr>
            <td class="dlg-menu" colspan="2" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?configuration.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
               <input name="btn_styles" type="submit" class="button" value="<?=$LANG['btn_styles']?>">
            </td>
         </tr>

         <!-- ===========================================================
              CALENDAR DISPLAY
         -->
         <?php $style="2"; ?>
         <tr>
            <td class="dlg-caption" colspan="2" style="text-align: left;"><?=$LANG['admin_config_display']?></td>
         </tr>

         <!-- showMonths -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_showmonths']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_showmonths_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <table>
                  <tr><td><input name="opt_showMonths" type="radio" value="1" <?=(($C->readConfig("showMonths")=="1")?"CHECKED":"")?>></td><td style="vertical-align: bottom;"><?=$LANG['admin_config_showmonths_1']?></td></tr>
                  <tr><td><input name="opt_showMonths" type="radio" value="2" <?=(($C->readConfig("showMonths")=="2")?"CHECKED":"")?>></td><td style="vertical-align: bottom;"><?=$LANG['admin_config_showmonths_2']?></td></tr>
                  <tr><td><input name="opt_showMonths" type="radio" value="3" <?=(($C->readConfig("showMonths")=="3")?"CHECKED":"")?>></td><td style="vertical-align: bottom;"><?=$LANG['admin_config_showmonths_3']?></td></tr>
                  <tr><td><input name="opt_showMonths" type="radio" value="6" <?=(($C->readConfig("showMonths")=="6")?"CHECKED":"")?>></td><td style="vertical-align: bottom;"><?=$LANG['admin_config_showmonths_6']?></td></tr>
                  <tr><td><input name="opt_showMonths" type="radio" value="12" <?=(($C->readConfig("showMonths")=="12")?"CHECKED":"")?>></td><td style="vertical-align: bottom;"><?=$LANG['admin_config_showmonths_12']?></td></tr>
               </table>
            </td>
         </tr>

         <!-- showWeekNumbers -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_weeknumbers']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_weeknumbers_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_showWeekNumbers" id="chk_showWeekNumbers" value="chk_showWeekNumbers" type="checkbox" <?=(intval($C->readConfig("showWeekNumbers"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- firstDayOfWeek -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_firstdayofweek']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_firstdayofweek_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <table>
                  <tr><td><input name="opt_firstDayOfWeek" type="radio" value="1" <?=(($C->readConfig("firstDayOfWeek")=="1")?"CHECKED":"")?>></td><td style="vertical-align: bottom;"><?=$LANG['admin_config_firstdayofweek_1']?></td></tr>
                  <tr><td><input name="opt_firstDayOfWeek" type="radio" value="7" <?=(($C->readConfig("firstDayOfWeek")=="7")?"CHECKED":"")?>></td><td style="vertical-align: bottom;"><?=$LANG['admin_config_firstdayofweek_7']?></td></tr>
               </table>
            </td>
         </tr>

         <!-- Saturday is Business Day -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_satbusi']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_satbusi_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_satBusi" id="chk_satBusi" value="chk_satBusi" type="checkbox" <?=(intval($C->readConfig("satBusi"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- Sunday is Business Day -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_sunbusi']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_sunbusi_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_sunBusi" id="chk_sunBusi" value="chk_sunBusi" type="checkbox" <?=(intval($C->readConfig("sunBusi"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- includeRemainder -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_remainder']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_remainder_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_includeRemainder" id="chk_includeRemainder" value="chk_includeRemainder" type="checkbox" <?=(intval($C->readConfig("includeRemainder"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- includeRemainderTotal -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_remainder_total']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_remainder_total_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_includeRemainderTotal" id="chk_includeRemainderTotal" value="chk_includeRemainderTotal" type="checkbox" <?=(intval($C->readConfig("includeRemainderTotal"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- includeTotals -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_totals']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_totals_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_includeTotals" id="chk_includeTotals" value="chk_includeTotals" type="checkbox" <?=(intval($C->readConfig("includeTotals"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- showRemainder -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_show_remainder']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_show_remainder_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_showRemainder" id="chk_showRemainder" value="chk_showRemainder" type="checkbox" <?=(intval($C->readConfig("showRemainder"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- includeSummary -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_summary']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_summary_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_includeSummary" id="chk_includeSummary" value="chk_includeSummary" type="checkbox" <?=(intval($C->readConfig("includeSummary"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- showSummary -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_show_summary']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_show_summary_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_showSummary" id="chk_showSummary" value="chk_showSummary" type="checkbox" <?=(intval($C->readConfig("showSummary"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- usersPerPage -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_usersperpage']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_usersperpage_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_usersPerPage" id="txt_usersPerPage" type="text" size="5" value="<?=intval($C->readConfig("usersPerPage"))?>">
            </td>
         </tr>

         <!-- repeatHeaderCount -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_repeatheadercount']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_repeatheadercount_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_repeatHeaderCount" id="txt_repeatHeaderCount" type="text" size="5" value="<?=intval($C->readConfig("repeatHeaderCount"))?>">
            </td>
         </tr>

         <!-- todayBorderColor -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_todaybordercolor']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_todaybordercolor_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_todayBorderColor" id="txt_todayBorderColor" type="text" size="5" maxlength="6" value="<?=$C->readConfig("todayBorderColor")?>">
            </td>
         </tr>

         <!-- todayBorderSize -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_todaybordersize']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_todaybordersize_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_todayBorderSize" id="txt_todayBorderSize" type="text" size="5" value="<?=intval($C->readConfig("todayBorderSize"))?>">
            </td>
         </tr>

         <!-- pastDayColor -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_pastdaycolor']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_pastdaycolor_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_pastDayColor" id="txt_pastDayColor" type="text" size="5" maxlength="6" value="<?=$C->readConfig("pastDayColor")?>">
             </td>
         </tr>

         <!-- defaultRegion -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_defregion']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_defregion_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <select id="sel_defregion" name="sel_defregion" class="select" onchange="javascript:">
                  <option value="default" <?=(($C->readConfig("defregion")=="default")?"SELECTED":"")?>>default</option>
                  <?php
                  $regions = $R->getAll();
                  foreach ($regions as $row) {
                     $R->findByName($row['regionname']);
                     if ($R->regionname!="default") {
                        echo "<option value=\"".$R->regionname."\"".(($C->readConfig("defregion")==$R->regionname)?"SELECTED":"").">".$R->regionname."</option>\n";
                     }
                  }
                  ?>
               </select>
            </td>
         </tr>

         <!-- defaultGroupFilter -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_defgroupfilter']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_defgroupfilter_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <select id="sel_defgroupfilter" name="sel_defgroupfilter" class="select" onchange="javascript:">
                  <option value="All" <?=(($C->readConfig("defgroupfilter")=="All")?"SELECTED":"")?>><?=$LANG['drop_group_all']?></option>
                  <option value="Allbygroup" <?=(($C->readConfig("defgroupfilter")=="Allbygroup")?"SELECTED":"")?>><?=$LANG['drop_group_allbygroup']?></option>
               </select>
            </td>
         </tr>

         <!-- hideManagers -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_hide_managers']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_hide_managers_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_hideManagers" id="chk_hideManagers" value="chk_hideManagers" type="checkbox" <?=(intval($C->readConfig("hideManagers"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- hideDaynotes -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_hide_daynotes']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_hide_daynotes_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_hideDaynotes" id="chk_hideDaynotes" value="chk_hideDaynotes" type="checkbox" <?=(intval($C->readConfig("hideDaynotes"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- markConfidential -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_mark_confidential']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_mark_confidential_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_markConfidential" id="chk_markConfidential" value="chk_markConfidential" type="checkbox" <?=(intval($C->readConfig("markConfidential"))?"CHECKED":"")?>>
            </td>
         </tr>

         <tr>
            <td class="dlg-menu" colspan="2" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?configuration.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
            </td>
         </tr>

         <!-- ===========================================================
              USER ICONS AND AVATARS
         -->
         <?php $style="2"; ?>
         <tr>
            <td class="dlg-caption" colspan="2" style="text-align: left;"><?=$LANG['admin_config_usericonsavatars']?></td>
         </tr>

         <!-- showUserIcons -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_usericons']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_usericons_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_showUserIcons" id="chk_showUserIcons" value="chk_showUserIcons" type="checkbox" <?=(intval($C->readConfig("showUserIcons"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- showAvatars -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_avatars']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_avatars_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_showAvatars" id="chk_showAvatars" value="chk_showAvatars" type="checkbox" <?=(intval($C->readConfig("showAvatars"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- avatarWidth -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_avatarwidth']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_avatarwidth_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_avatarWidth" id="txt_avatarWidth" type="text" size="5" maxlength="3" value="<?=intval($C->readConfig("avatarWidth"))?>">
            </td>
         </tr>

         <!-- avatarHeight -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_avatarheight']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_avatarheight_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_avatarHeight" id="txt_avatarHeight" type="text" size="5" maxlength="3" value="<?=intval($C->readConfig("avatarHeight"))?>">
            </td>
         </tr>

         <tr>
            <td class="dlg-menu" colspan="2" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?configuration.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
            </td>
         </tr>

         <!-- ===========================================================
              CUSTOM FIELDS
         -->
         <?php $style="2"; ?>
         <tr>
            <td class="dlg-caption" colspan="2" style="text-align: left;"><?=$LANG['admin_config_userCustom']?></td>
         </tr>

         <!-- userCustom1 -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_userCustom1']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_userCustom1_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_userCustom1" id="txt_userCustom1" type="text" size="50" value="<?=$C->readConfig("userCustom1")?>">
            </td>
         </tr>

         <!-- userCustom2 -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_userCustom2']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_userCustom2_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_userCustom2" id="txt_userCustom2" type="text" size="50" value="<?=$C->readConfig("userCustom2")?>">
            </td>
         </tr>

         <!-- userCustom3 -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_userCustom3']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_userCustom3_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_userCustom3" id="txt_userCustom3" type="text" size="50" value="<?=$C->readConfig("userCustom3")?>">
            </td>
         </tr>

         <!-- userCustom4 -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_userCustom4']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_userCustom4_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_userCustom4" id="txt_userCustom4" type="text" size="50" value="<?=$C->readConfig("userCustom4")?>">
            </td>
         </tr>

         <!-- userCustom5 -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_userCustom5']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_userCustom5_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_userCustom5" id="txt_userCustom5" type="text" size="50" value="<?=$C->readConfig("userCustom5")?>">
            </td>
         </tr>

         <tr>
            <td class="dlg-menu" colspan="2" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?configuration.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
            </td>
         </tr>

         <!-- ===========================================================
              USER GROUP ASSIGNMENT DISPLAY
         -->
         <?php $style="2"; ?>
         <tr>
            <td class="dlg-caption" colspan="2" style="text-align: left;"><?=$LANG['admin_config_usergroup']?></td>
         </tr>

         <!-- repeatHeadersAfter -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_repeatheadersafter']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_repeatheadersafter_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_repeatHeadersAfter" id="txt_repeatHeadersAfter" type="text" size="5" maxlength="3" value="<?=intval($C->readConfig("repeatHeadersAfter"))?>">
            </td>
         </tr>

         <!-- repeatUsernamesAfter -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_repeatusernamesafter']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_repeatusernamesafter_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_repeatUsernamesAfter" id="txt_repeatUsernamesAfter" type="text" size="5" maxlength="3" value="<?=intval($C->readConfig("repeatUsernamesAfter"))?>">
            </td>
         </tr>

         <tr>
            <td class="dlg-menu" colspan="2" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?configuration.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
            </td>
         </tr>

         <!-- ===========================================================
              GENERAL OPTIONS
         -->
         <?php $style="2"; ?>
         <tr>
            <td class="dlg-caption" colspan="2" style="text-align: left;"><?=$LANG['admin_config_general']?></td>
         </tr>

         <!-- permissionScheme -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_pscheme']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_pscheme_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <select id="sel_pscheme" name="sel_pscheme" class="select" onchange="javascript:">
                  <?php
                     $currscheme = $C->readConfig("permissionScheme");
                     $schemes = $P->getSchemes();
                     foreach ($schemes as $sch) {
                        if ($sch==$currscheme)
                           echo ("<option value=\"".$sch."\" SELECTED=\"selected\">".$sch."</option>");
                        else
                           echo ("<option value=\"".$sch."\" >".$sch."</option>");
                     }
                  ?>
               </select>
            </td>
         </tr>

         <!-- defaultPeriod -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_defperiod']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_defperiod_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <script type="text/javascript">
                  $(function() { $( "#periodfrom" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd" }); });
                  $(function() { $( "#periodto" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd" }); });
               </script>
               <?=$LANG['admin_config_defperiod_from']?>:&nbsp;
               <?php
               if (isset($_POST['periodfrom'])) $periodfromdate = $_POST['periodfrom']; else $periodfromdate = $C->readConfig("defperiodfrom");
               ?>
               <input name="periodfrom" id="periodfrom" size="10" maxlength="10" type="text" class="text" value="<?php echo $periodfromdate; ?>">
               &nbsp;&nbsp;
               <?=$LANG['admin_config_defperiod_to']?>:&nbsp;
               <?php
               if (isset($_POST['periodto'])) $periodtodate = $_POST['periodto']; else $periodtodate = $C->readConfig("defperiodto");
               ?>
               <input name="periodto" id="periodto" size="10" maxlength="10" type="text" class="text" value="<?php echo $periodtodate; ?>">
               <br>
            </td>
         </tr>

         <!-- appSubTitle -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_appsubtitle']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_appsubtitle_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_appSubTitle" id="txt_appSubTitle" type="text" size="50" value="<?=$C->readConfig("appSubTitle")?>">
            </td>
         </tr>

         <!-- initialHomepage -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_homepage']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_homepage_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <table>
                  <tr><td><input name="opt_homepage" type="radio" value="welcome" <?=(($C->readConfig("homepage")=="welcome")?"CHECKED":"")?>></td><td style="vertical-align: bottom;"><?=$LANG['admin_config_homepage_welcome']?></td></tr>
                  <tr><td><input name="opt_homepage" type="radio" value="calendar" <?=(($C->readConfig("homepage")=="calendar")?"CHECKED":"")?>></td><td style="vertical-align: bottom;"><?=$LANG['admin_config_homepage_calendar']?></td></tr>
               </table>
            </td>
         </tr>

         <!-- welcomeMessage -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_welcome']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_welcome_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_welcomeTitle" id="txt_welcomeTitle" type="text" size="50" value="<?=stripslashes($C->readConfig("welcomeTitle"))?>"><br />
               <textarea name="txt_welcomeText" id="txt_welcomeText" class="text" rows="10" cols="50"><?=stripslashes($C->readConfig("welcomeText"))?></textarea>
            </td>
         </tr>

         <!-- welcomeMessage Icon -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_welcomeIcon']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_welcomeIcon_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%; vertical-align: top;">
               <select id="sel_welcomeIcon" name="sel_welcomeIcon" class="select" onchange="javascript: document.welcomeIcon.src='<?=$CONF['app_homepage_dir']?>'+this.value;">
                  <option value="No" <?=(($C->readConfig("welcomeIcon")=="No")?"SELECTED":"")?>><?=$LANG['no']?></option>
                  <?php
                  $fileTypes = array ("gif", "jpg", "png");
                  $imgFiles = scanDirectory($CONF['app_homepage_dir']);
                  foreach ($imgFiles as $file) { ?>
                     <option style="background-image: url(<?=$CONF['app_homepage_dir'].$file?>); background-size: 16px 16px; background-repeat: no-repeat; padding-left: 20px;" value="<?=$file?>" <?=(($C->readConfig("welcomeIcon")==$file)?"SELECTED":"")?>><?=$file?></option>
                  <?php } ?>
               </select>
               &nbsp;<input name="btn_upload" type="button" class="button" onclick="javascript:this.blur();openPopup('upload.php?target=homepage','upload','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=500,height=400');" value="<?=$LANG['btn_upload']?>">
               <?php if($C->readConfig("welcomeIcon")!="No") { ?>
               <img src="<?=$CONF['app_homepage_dir'].$C->readConfig("welcomeIcon")?>" alt="" align="top" id="welcomeIcon">
               <?php } ?>
            </td>
         </tr>

         <!-- optionsBar -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_optionsbar']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_optionsbar_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <table>
                  <tr><td><input name="chk_showLanguage" id="chk_showLanguage" value="chk_showLanguage" type="checkbox" <?=(intval($C->readConfig("showLanguage"))?"CHECKED":"")?>></td><td style="vertical-align: middle;"><?=$LANG['admin_config_optionsbar_language']?></td></tr>
                  <tr><td><input name="chk_showGroup" id="chk_showGroup" value="chk_showGroup" type="checkbox" <?=(intval($C->readConfig("showGroup"))?"CHECKED":"")?>></td><td style="vertical-align: middle;"><?=$LANG['admin_config_optionsbar_group']?></td></tr>
                  <tr><td><input name="chk_showRegion" id="chk_showRegion" value="chk_showRegion" type="checkbox" <?=(intval($C->readConfig("showRegion"))?"CHECKED":"")?>></td><td style="vertical-align: middle;"><?=$LANG['admin_config_optionsbar_region']?></td></tr>
                  <tr><td><input name="chk_showToday" id="chk_showToday" value="chk_showToday" type="checkbox" <?=(intval($C->readConfig("showToday"))?"CHECKED":"")?>></td><td style="vertical-align: middle;"><?=$LANG['admin_config_optionsbar_today']?></td></tr>
                  <tr><td><input name="chk_showStart" id="chk_showStart" value="chk_showStart" type="checkbox" <?=(intval($C->readConfig("showStart"))?"CHECKED":"")?>></td><td style="vertical-align: middle;"><?=$LANG['admin_config_optionsbar_start']?></td></tr>
               </table>
            </td>
         </tr>

         <!-- appFooterCpy -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_appfootercpy']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_appfootercpy_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_appFooterCpy" id="txt_appFooterCpy" type="text" size="50" value="<?=$C->readConfig("appFooterCpy")?>">
            </td>
         </tr>

         <!-- Theme -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_theme']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_theme_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <select id="sel_theme" name="sel_theme" class="select" onchange="javascript:">
                  <?php
                  $themearray = getThemes();
                  foreach ($themearray as $theme) { ?>
                     <option value="<?=$theme['name']?>" <?=(($C->readConfig("theme")==$theme['name'])?"SELECTED":"")?>><?=$theme['name']?></option>
                  <?php } ?>
               </select>
            </td>
         </tr>

         <!-- User Theme -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_usertheme']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_usertheme_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_allowUserTheme" id="chk_allowUserTheme" value="chk_allowUserTheme" type="checkbox" <?=(intval($C->readConfig("allowUserTheme"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- User Theme -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_webMeasure']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_webMeasure_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_webMeasure" id="chk_webMeasure" value="chk_webMeasure" type="checkbox" <?=(intval($C->readConfig("webMeasure"))?"CHECKED":"")?>>
            </td>
         </tr>

         <tr>
            <td class="dlg-menu" colspan="2" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?configuration.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
            </td>
         </tr>

         <!-- ===========================================================
              SYSTEM OPTIONS
         -->
         <?php $style="2"; ?>
         <tr>
            <td class="dlg-caption" colspan="2" style="text-align: left;"><?=$LANG['admin_config_system_options']?></td>
         </tr>

         <!-- jQuery CDN -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_jQueryCDN']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_jQueryCDN_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_jQueryCDN" id="chk_jQueryCDN" value="chk_jQueryCDN" type="checkbox" <?=(intval($C->readConfig("jQueryCDN"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- Google Analytics -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_googleAnalytics']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_googleAnalytics_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input style="vertical-align: middle;" name="chk_googleAnalytics" id="chk_googleAnalytics" value="chk_googleAnalytics" type="checkbox" <?=(intval($C->readConfig("googleAnalytics"))?"CHECKED":"")?>><?=$LANG['btn_activate']?><br>
               <?=$LANG['admin_config_googleAnalyticsID']?>:&nbsp;&nbsp;<input class="text" name="txt_googleAnalyticsID" id="txt_googleAnalyticsID" type="text" size="24" value="<?=$C->readConfig("googleAnalyticsID")?>">
            </td>
         </tr>

         <!-- debugHide -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_debughide']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_debughide_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_debugHide" id="chk_debugHide" value="chk_debugHide" type="checkbox" <?=(intval($C->readConfig("debugHide"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- timeZone -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_timezone']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_timezone_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <select id="sel_timeZone" name="sel_timeZone" class="select" onchange="javascript:">
                  <option value="default" <?=(($C->readConfig("timeZone")=="default")?"SELECTED":"")?>>default</option>
                  <?php foreach ($timezone as $tz) { ?>
                  <option value="<?=$tz["name"]?>" <?=(($C->readConfig("timeZone")==$tz["name"])?"SELECTED":"")?>><?=$tz["name"]?></option>
                  <?php } ?>
               </select>
            </td>
         </tr>

         <tr>
            <td class="dlg-menu" colspan="2" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?configuration.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
            </td>
         </tr>


         <!-- ===========================================================
              EMAIL OPTIONS
         -->
         <?php $style="2"; ?>
         <tr>
            <td class="dlg-caption" colspan="2" style="text-align: left;"><?=$LANG['admin_config_mail_options']?></td>
         </tr>

         <!-- emailNotifications -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_emailnotifications']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_emailnotifications_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_emailNotifications" id="chk_emailNotifications" value="chk_emailNotifications" type="checkbox" <?=(intval($C->readConfig("emailNotifications"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- mailFrom -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_mailfrom']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_mailfrom_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_mailFrom" id="txt_mailFrom" type="text" size="50" value="<?=$C->readConfig("mailFrom")?>">
            </td>
         </tr>

         <!-- mailReply -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_mailreply']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_mailreply_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_mailReply" id="txt_mailReply" type="text" size="50" value="<?=$C->readConfig("mailReply")?>">
            </td>
         </tr>

         <!-- Use SMTP -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_mail_smtp']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_mail_smtp_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_mailSMTP" id="chk_mailSMTP" value="chk_mailSMTP" type="checkbox" <?=(intval($C->readConfig("mailSMTP"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- SMTP Host -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_mail_smtp_host']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_mail_smtp_host_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_mailSMTPhost" id="txt_mailSMTPhost" type="text" size="50" value="<?=$C->readConfig("mailSMTPhost")?>">
            </td>
         </tr>

         <!-- SMTP Port -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_mail_smtp_port']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_mail_smtp_port_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_mailSMTPport" id="txt_mailSMTPport" type="text" size="10" value="<?=$C->readConfig("mailSMTPport")?>">
            </td>
         </tr>

         <!-- SMTP Username -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_mail_smtp_username']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_mail_smtp_username_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_mailSMTPusername" id="txt_mailSMTPusername" type="text" size="50" value="<?=$C->readConfig("mailSMTPusername")?>">
            </td>
         </tr>

         <!-- SMTP Password -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_mail_smtp_password']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_mail_smtp_password_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_mailSMTPpassword" id="txt_mailSMTPpassword" type="text" size="50" value="<?=$C->readConfig("mailSMTPpassword")?>">
            </td>
         </tr>

         <tr>
            <td class="dlg-menu" colspan="2" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?configuration.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
            </td>
         </tr>

         <!-- ===========================================================
              USER REGISTRATION
         -->
         <?php $style="2"; ?>
         <tr>
            <td class="dlg-caption" colspan="2" style="text-align: left;"><?=$LANG['admin_config_registration']?></td>
         </tr>

         <!-- allowRegistration -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_allow_registration']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_allow_registration_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_allowRegistration" id="chk_allowRegistration" value="chk_allowRegistration" type="checkbox" <?=(intval($C->readConfig("allowRegistration"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- emailConfirmation -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_email_confirmation']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_email_confirmation_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_emailConfirmation" id="chk_emailConfirmation" value="chk_emailConfirmation" type="checkbox" <?=(intval($C->readConfig("emailConfirmation"))?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- adminApproval -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_admin_approval']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_admin_approval_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_adminApproval" id="chk_adminApproval" value="chk_adminApproval" type="checkbox" <?=(intval($C->readConfig("adminApproval"))?"CHECKED":"")?>>
            </td>
         </tr>

         <tr>
            <td class="dlg-menu" colspan="2" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?configuration.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
            </td>
         </tr>

         <!-- ===========================================================
              LOGIN OPTIONS
         -->
         <?php $style="2"; ?>
         <tr>
            <td class="dlg-caption" colspan="2" style="text-align: left;"><?=$LANG['admin_config_login']?></td>
         </tr>

         <!-- pwdLength -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_pwd_length']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_pwd_length_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_pwdLength" id="txt_pwdLength" type="text" size="4" maxlength="2" value="<?=intval($C->readConfig("pwdLength"))?>">
            </td>
         </tr>

         <!-- pwdStrength -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_pwd_strength']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_pwd_strength_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <table>
                  <tr><td><input name="opt_pwdStrength" type="radio" value="0" <?=(($C->readConfig("pwdStrength")=="0")?"CHECKED":"")?>></td><td style="vertical-align: bottom;">Minimum</td></tr>
                  <tr><td><input name="opt_pwdStrength" type="radio" value="1" <?=(($C->readConfig("pwdStrength")=="1")?"CHECKED":"")?>></td><td style="vertical-align: bottom;">Low</td></tr>
                  <tr><td><input name="opt_pwdStrength" type="radio" value="2" <?=(($C->readConfig("pwdStrength")=="2")?"CHECKED":"")?>></td><td style="vertical-align: bottom;">Medium</td></tr>
                  <tr><td><input name="opt_pwdStrength" type="radio" value="3" <?=(($C->readConfig("pwdStrength")=="3")?"CHECKED":"")?>></td><td style="vertical-align: bottom;">High</td></tr>
               </table>
            </td>
         </tr>

         <!-- badLogins -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_bad_logins']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_bad_logins_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_badLogins" id="txt_badLogins" type="text" size="4" maxlength="2" value="<?=intval($C->readConfig("badLogins"))?>">
            </td>
         </tr>

         <!-- gracePeriod -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_grace_period']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_grace_period_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_gracePeriod" id="txt_gracePeriod" type="text" size="4" maxlength="3" value="<?=intval($C->readConfig("gracePeriod"))?>">
            </td>
         </tr>

         <!-- cookieLifetime -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['admin_config_cookie_lifetime']?></span><br>
               <span class="config-comment"><?=$LANG['admin_config_cookie_lifetime_comment']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_cookieLifetime" id="txt_cookieLifetime" type="text" size="9" maxlength="6" value="<?=intval($C->readConfig("cookieLifetime"))?>">
            </td>
         </tr>

         <tr>
            <td class="dlg-menu" colspan="2" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?configuration.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
            </td>
         </tr>

      </table>
      </form>
   </div>
</div>
<script type="text/javascript">$(function() { $( "#txt_todayBorderColor, #txt_pastDayColor" ).ColorPicker({ onSubmit: function(hsb, hex, rgb, el) { $(el).val(hex.toUpperCase()); $(el).ColorPickerHide(); }, onBeforeShow: function () { $(this).ColorPickerSetColor(this.value); } }) .bind('keyup', function(){ $(this).ColorPickerSetColor(this.value); }); });</script>
<?php require("includes/footer.html.inc.php"); ?>