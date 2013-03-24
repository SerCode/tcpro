<?php
/**
 * popup.php
 *
 * Displays an announcement popup
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
require_once ("helpers/global_helper.php");
getOptions();
if (strlen($CONF['options']['lang'])) require ("includes/lang/" . $CONF['options']['lang'] . ".tcpro.php");
else                                  require ("includes/lang/english.tcpro.php");

require_once("models/announcement_model.php" );
require_once("models/log_model.php" );
require_once("models/login_model.php" );
require_once("models/user_model.php" );
require_once("models/user_announcement_model.php" );

$AN  = new Announcement_model;
$L   = new Login_model;
$LOG = new Log_model;
$U   = new User_model;
$UA  = new User_announcement_model;
$UL  = new User_model;

/**
 * Check authorization
 */
if (!isAllowed("viewAnnouncements")) {
   // Not authorized. Get outta here
   jsCloseAndReload("index.php");
}

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
   $LOG->log("logAnnouncement",$L->checkLogin(),"Announcement ".$ats." confirmed by ".$UL->username);
}

/**
 * =========================================================================
 * CONFIRM ALL
 */
else if ( isset($_POST['btn_confirm_all'])) {

   $UA->deleteAllForUser($UL->username);
   
   /**
    * Log this event
   */
   $LOG->log("logAnnouncement",$user,"All announcements confirmed by ".$UL->username);
}

require("includes/header.html.inc.php" );
?>
<body>
   <div id="content">
      <div id="content-content">
         <table class="dlg">
            <tr>
               <td class="dlg-header">
                  <?php printDialogTop($LANG['ann_title']." ".$U->firstname." ".$U->lastname,"announcement_popup.html","ico_bell.png"); ?>
               </td>
            </tr>
            <tr>
               <?php $uas = $UA->getAllForUser($UL->username);
               if (count($uas)) { ?>
                  <td class="config-row1" style="text-align: center; vertical-align: middle;">
                     <form class="form" name="form-all" method="POST" action="<?=$_SERVER['PHP_SELF']."?uname=".$_REQUEST['uname']?>">
                        <input name="btn_confirm_all" type="submit" class="button" value="<?=$LANG['btn_confirm_all']?>" onclick="return confirmSubmit('<?=$LANG['ann_confirm_all_confirm']?>')">
                     </form>
                  </td>
               <?php }
               else { ?>
                  <td colspan="2" class="config-row1"><?=$LANG['ann_no_ann']?></td>
               <?php } ?>
            </tr>
            <tr>
               <td class="dlg-body">
                  <?php
                  $uas=$UA->getAllForUser($_REQUEST['uname']);
                  foreach($uas as $ua) {
                     $AN->read($row['ats']);
                     if ($AN->popup) { ?>
                        <form class="form" name="form-ann-<?=$row['ats']?>" method="POST" action="<?=$_SERVER['PHP_SELF']."?uname=".$_REQUEST['uname']?>">
                        <table style="border-collapse: collapse; border: 0px; width: 100%;">
                           <tr>
                              <td width="90%"><?=$LANG['ann_id'].": ".$row['ats']."<br><br>".$AN->read($row['ats'])?></td>
                              <td style="text-align: right;" width="10%">
                                 <input class="text" type="hidden" name="ats" value="<?=$row['ats']?>">
                                 <input name="btn_confirm" type="submit" class="button" value="<?=$LANG['btn_confirm']?>" onclick="return confirmSubmit('<?=$LANG['ann_delete_confirm_1'].$row['ats'].$LANG['ann_delete_confirm_2']?>')">
                              </td>
                           </tr>
                        </table>
                        </form>
                        <HR size="1">
                     <?php }
                  }
                  ?>
               </td>
            </tr>
            <tr>
               <td class="dlg-menu">
                  <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?announcement_popup.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=740,height=500');" value="<?=$LANG['btn_help']?>">
                  <input name="btn_close" type="button" class="button" onclick="javascript:window.close();" value="<?=$LANG['btn_close']?>">
               </td>
            </tr>
         </table>
      </div>
   </div>
<?php require("includes/footer.html.inc.php"); ?>