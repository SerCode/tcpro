<?php
/**
 * announcement.php
 *
 * Displays the announcement page
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

require_once("models/announcement_model.php");
require_once("includes/tcconfig.class.php");
require_once("includes/tclog.class.php");
require_once("includes/tclogin.class.php");
require_once("includes/tcuser.class.php");
require_once("models/user_announcement_model.php");

$AN  = new Announcement_model;
$C   = new tcConfig;
$L   = new tcLogin;
$LOG = new tcLog;
$U   = new tcUser;
$UA  = new User_announcement_model;
$UL  = new tcUser;

$error=FALSE;

/**
 * Check if allowed
 */
if (!isAllowed("viewAnnouncements")) showError("notallowed");

$user=$L->checkLogin();
$UL->findByName($user);

/**
 * =========================================================================
 * CONFIRM
 */
if ( isset($_POST['btn_confirm']) && strlen($_POST['ats'])) {

   $UA->unassign($_POST['ats'], $UL->username);

   /**
    * Log this event
    */
   $chars = array("-", " ", ":");
   $ats = str_replace($chars, "", $_POST['ats']);
   $LOG->log("logAnnouncement",$user,"Announcement ".$ats." confirmed by ".$UL->username);
}

/**
 * =========================================================================
 * CONFIRM ALL
 */
else if ( isset($_POST['btn_confirm_all'])) {
   
   $AN->clearUserAnnouncements($UL->username);
   
   /**
    * Log this event
    */
   $LOG->log("logAnnouncement",$user,"All announcements confirmed by ".$UL->username);
}

require("includes/header.html.inc.php");
echo "<body>\r\n";
require("includes/header.application.inc.php");
require("includes/menu.inc.php");
?>
<div id="content">
   <div id="content-content">
      <table class="dlg">
         <tr>
            <td class="dlg-header" colspan="2">
               <?php printDialogTop($LANG['ann_title']." ".$UL->firstname." ".$UL->lastname,"announcement_display.html","ico_bell.png"); ?>
            </td>
         </tr>
         <tr>
            <td class="dlg-caption" style="text-align: left;"><?=$LANG['ann_col_ann']?></td>
            <td class="dlg-caption" style="text-align: center; padding-right: 8px;"><?=$LANG['ann_col_action']?></td>
         </tr>
            <tr>
               <?php $uas = $UA->getAllForUser($UL->username);
               if (count($uas)) { ?>
                  <td class="config-row1">&nbsp;</td>
                  <td class="config-row1" style="text-align: center; vertical-align: middle;">
                     <form class="form" name="form-ann" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
                        <input name="btn_confirm_all" type="submit" class="button" value="<?=$LANG['btn_confirm_all']?>" onclick="return confirmSubmit('<?=$LANG['ann_confirm_all_confirm']?>')">
                     </form>
                  </td>
               <?php }
               else { ?>
                  <td colspan="2" class="config-row1"><?=$LANG['ann_no_ann']?></td>
               <?php } ?>
            </tr>
         <?php $style="1";
         foreach ($uas as $ua) {
            if ($style=="1") $style="2"; else $style="1";
            ?>
            <tr>
               <td class="config-row<?=$style?>">
                  <fieldset><legend><img src="themes/<?=$theme?>/img/ico_bell.png" alt="" style="vertical-align: middle;">&nbsp;<?=$LANG['ann_id'].": ".$ua['ats']?></legend>
                     <br><?=$AN->read($ua['ats'])?>
                  </fieldset>
               </td>
               <td class="config-row<?=$style?>" style="text-align: center; vertical-align: middle;">
                  <form class="form" name="form-ann-<?=$ua['ats']?>" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
                     <input name="ats" type="hidden" class="text" value="<?=$ua['ats']?>">&nbsp;
                     <input name="btn_confirm" type="submit" class="button" value="<?=$LANG['btn_confirm']?>" onclick="return confirmSubmit('<?=$LANG['ann_delete_confirm_1'].$ua['ats'].$LANG['ann_delete_confirm_2']?>');">
                  </form>
               </td>
            </tr>
         <?php } ?>
      </table>
   </div>
</div>
<?php require("includes/footer.html.inc.php"); ?>