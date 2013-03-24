<?php
/**
 * daynote.php
 *
 * Displays the daynote dialog
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

require_once( "models/config_model.php" );
require_once( "models/daynote_model.php" );
require_once( "models/login_model.php" );
require_once( "models/log_model.php" );
require_once( "models/user_model.php" );
require_once( "models/user_group_model.php" );

$C = new Config_model;
$L = new Login_model;
$LOG = new Log_model;
$N = new Daynote_model;
$U = new User_model;
$UG= new User_group_model;

$allowed=FALSE;
$event=NULL;

$user=$L->checkLogin();

/**
 * Check authorization
 */
if (!isset($_REQUEST['daynotefor'])) {
   /**
    * No user specified. Just display a not allowed message.
    */
   showError("notarget", TRUE);
}
if ( strtolower($_REQUEST['daynotefor'])=="all" AND !isAllowed("editGlobalDaynotes")) {
   /**
    * Trying to edit global daynotes while not allowed
    */
   showError("notallowed", TRUE);
}
else {
   /**
    * Personal daynote. Let's see if allowed...
    */
   if ($user==$_REQUEST['daynotefor'] OR isAllowed("editAllUserDaynotes")) {
      $allowed=TRUE;
   }
   else if ($UG->shareGroups($user, $_REQUEST['daynotefor']) AND isAllowed("editGroupUserDaynotes")) {
      $allowed=TRUE;
   }
   else {
      showError("notallowed", TRUE);
   }
}

if (isset ($_REQUEST['region'])) $region = $_REQUEST['region']; else $region = "default";

/**
 * Let's see if we have a note for this day already
 */
$daynote_exists = false;
if ( strtolower($_REQUEST['daynotefor'])=="all" ) {
   if ( $N->findByDay($_REQUEST['date'],"all",$region) ) $daynote_exists = true;
}
else {
   /**
    * Look for a user-specific daynote for this day And once you're at it get
    * the users full name.
    */
   if ( $N->findByDay($_REQUEST['date'],$_REQUEST['daynotefor'],$region) ) $daynote_exists = true;
   if ( !$U->findByName($_REQUEST['daynotefor']) ) {
      $event="warning";
      $warnmsg  = "*".$LANG['err_input_caption']."*\\n";
      $warnmsg .= $LANG['err_input_daynote_nouser'];
      $warnmsg .= $LANG['err_input_daynote_date'].$_REQUEST['date']."\\n";
      $warnmsg .= $LANG['err_input_daynote_username'].$_REQUEST['daynotefor']."\\n";
   }
   else {
      $daynote_user = $U->firstname." ".$U->lastname;
   }
}

/**
 * =========================================================================
 * SAVE
 */
if (isset($_POST['btn_save'])) {
   if ( strlen($_POST['daynote']) ) {
      $N->daynote = str_replace("\r\n","<br>",trim($_POST['daynote']));
      $N->update();
      /**
       * Log this event
       */
      $LOG->log("logDaynote",$L->checkLogin(),"Daynote updated: ".$_REQUEST['date']." - ".$_REQUEST['daynotefor']." - ".$region." : ".substr($N->daynote,0,20)."...");
   }
   else {
      $event="warning";
      $warnmsg  = "*".$LANG['err_input_caption']."*\\n";
      $warnmsg .= $LANG['err_input_daynote_save'];
   }
}
/**
 * =========================================================================
 * CREATE
 */
else if (isset($_POST['btn_create'])) {
   if ( strlen($_POST['daynote']) ) {
      $N->yyyymmdd = $_REQUEST['date'];
      $N->daynote = str_replace("\r\n","<br>",trim($_POST['daynote']));
      $N->username = $_REQUEST['daynotefor'];
      $N->region = $region;
      $N->create();
      /**
       * Log this event
       */
      $LOG->log("logDaynote",$L->checkLogin(),"Daynote created: ".$_REQUEST['date']." - ".$_REQUEST['daynotefor']." - ".$region." : ".substr($N->daynote,0,20)."...");
      $daynote_exists=true;
   }
   else {
      $event="warning";
      $warnmsg  = "*".$LANG['err_input_caption']."*\\n";
      $warnmsg .= $LANG['err_input_daynote_create'];
   }
}
/**
 * =========================================================================
 * DELETE
 */
else if (isset($_POST['btn_delete'])) {
   if ( $N->findByDay($_REQUEST['date'],$_REQUEST['daynotefor'],$region) ) {
      $N->deleteByDay($_REQUEST['date'],$_REQUEST['daynotefor'],$region);
      /**
       * Log this event
       */
      $LOG->log("logDaynote",$L->checkLogin(),"Daynote deleted: ".$_REQUEST['date']." - ".$_REQUEST['daynotefor']." - ".$region." : ".substr($N->daynote,0,20)."...");
      $daynote_exists=false;
   }
}

require( "includes/header.html.inc.php" );
?>
<body>
   <div id="content">
      <div id="content-content">
         <form name="message" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']."&amp;date=".$_REQUEST['date']."&amp;daynotefor=".$_REQUEST['daynotefor']."&amp;region=".$region."&amp;datestring=".$_REQUEST['datestring']?>">
         <table class="dlg">
            <tr>
               <td class="dlg-header" colspan="3">
                  <?php
                  $title=$LANG['daynote_edit_title'].$_REQUEST['datestring']." (".$LANG['month_region'].": ".$region.")";
                  if ( $_REQUEST['daynotefor']!="all" ) $title .= " ".$LANG['daynote_edit_title_for']." ".$daynote_user;
                  printDialogTop($title,"daynote_dialog.html","ico_daynote.png");
                  ?>
               </td>
            </tr>
            <tr>
               <td class="dlg-body">
                  <table class="dlg-frame">
                     <tr>
                        <td class="dlg-body"><strong><?=$LANG['daynote_edit_msg_caption']?></strong><br>
                        <?=$LANG['daynote_edit_msg_hint']?></td>
                     </tr>
                     <tr>
                        <td class="dlg-body">
                           <textarea name="daynote" id="daynote" class="text" cols="50" rows="6"><?php if ( $daynote_exists ) echo str_replace("<br>","\r\n",stripslashes(trim($N->daynote))); else echo str_replace("<br>","\r\n",$LANG['daynote_edit_msg']); ?></textarea>
                           <br />
                        </td>
                     </tr>
                  </table>
               </td>
              </tr>
              <tr>
                 <td class="dlg-menu">
                    <?php
                    if ($daynote_exists) { ?>
                        <input name="btn_save" type="submit" class="button" value="<?=$LANG['btn_save']?>">
                        <input name="btn_delete" type="submit" class="button" value="<?=$LANG['btn_delete']?>">
                    <?php } else { ?>
                        <input name="btn_create" type="submit" class="button" value="<?=$LANG['btn_create']?>">
                    <?php } ?>
                    <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?daynote_dialog.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=740,height=500');" value="<?=$LANG['btn_help']?>">
                    <input name="btn_close" type="button" class="button" onclick="javascript:window.close();" value="<?=$LANG['btn_close']?>">
                    <input name="btn_done" type="button" class="button" onclick="javascript:closeme();" value="<?=$LANG['btn_done']?>">
                 </td>
              </tr>
            </table>
         </form>
      </div>
   </div>
<?php
switch ($event) {
   case "created": echo ("<script type=\"text/javascript\">alert(\"" . $LANG['daynote_edit_event_created'] . "\")</script>"); break;
   case "saved":   echo ("<script type=\"text/javascript\">alert(\"" . $LANG['daynote_edit_event_saved'] . "\")</script>"); break;
   case "deleted": echo ("<script type=\"text/javascript\">alert(\"" . $LANG['daynote_edit_event_deleted'] . "\")</script>"); break;
   case "warning": echo ("<script type=\"text/javascript\">alert(\"" . $warnmsg . "\")</script>"); break;
   default: break;
}
require( "includes/footer.html.inc.php" );
?>