<?php
/**
 * verify.php
 *
 * Verifies a user registration
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

require_once ("includes/tcconfig.class.php");
require_once ("includes/tclog.class.php");
require_once ("includes/tcuser.class.php");
require_once ("includes/tcuseroption.class.php");

$C = new tcConfig;
$LOG = new tcLog;
$U = new tcUser;
$UA = new tcUser;
$UO = new tcUserOption;

$error = FALSE;
$info = FALSE;

/**
 * Check URL request parameter
 */
if (!isset ($_REQUEST['verify']) || !isset ($_REQUEST['username']) || strlen($_REQUEST['verify'])<>32 || !in_array($_REQUEST['username'],$U->getUsernames()) ) {
   /*
    * Link is incomplete or corrupt
    */
   showError("notarget", TRUE);
}
else {
   $rverify = trim($_REQUEST['verify']);
   $ruser = trim($_REQUEST['username']);
   if ($fverify = $UO->find($ruser, "verifycode")) {
      if ($fverify == $rverify) {
         /**
          * Found the user and a matching verify code
          */
         $UO->deleteUserOption($ruser, "verifycode");
         $info = $LANG['verify_info_success'];
         $U->findByName($ruser);
         $fullname = $U->firstname . " " . $U->lastname;
         if ($C->readConfig("adminApproval")) {
            /**
             * Success but admin needs to approve
             */
            $UA->findByName("admin");
            $subject = $LANG['verify_mail_subject'];
            $message = $LANG['verify_mail_greeting'];
            $message .= $LANG['verify_mail_message'];
            $message = str_replace("[USERNAME]",$U->username,$message);
            $to = $UA->email;
            $headers = "From: " . $C->readConfig("mailFrom") . "\r\n" . "Reply-To: " . $C->readConfig("mailReply") . "\r\n";
            mail($to, stripslashes($subject), stripslashes($message), $headers);

            $info .= $LANG['verify_info_approval'];
            $LOG->log("logRegistration", $U->username, "User verified, approval needed: " . $U->username . " (" . $fullname . ")");
         }
         else {
            /**
             * Success and no approval needed. Unlock and unhide user.
             */
            $U->clearStatus($CONF['USLOCKED']);
            $U->clearStatus($CONF['USHIDDEN']);
            $U->update($U->username);
            $LOG->log("logRegistration", $U->username, "User verified, unlocked and unhidden: " . $U->username . " (" . $fullname . ")");
         }
      }
      else {
         /**
          * Found the user but verify code does not match
          */
         $error = $LANG['verify_err_match'];
         $LOG->log("logRegistration", $U->username, "User verification code does not match: " . $U->username . " (" . $fullname . "): ".$rverify);
      }
   }
   else {
      /**
       * Found no verify code or there is none for this user
       */
      if (!$U->findByName($ruser)) {
         /**
          * No surprise, the user dos not exist
          */
         $error = $LANG['verify_err_user'];
         $LOG->log("logRegistration", $ruser, "User does not exist: " . $ruser." : ".$rverify);
      }
      else {
         /**
          * Verfiy code does not exist
          */
         $error = $LANG['verify_err_code'];
         $LOG->log("logRegistration", $ruser, "Verification code does not exist: " . $ruser . " : ".$rverify);
      }
   }
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
            <td class="dlg-header" colspan="3">
               <?php printDialogTop($LANG['verify_title'],"","ico_register.png"); ?>
            </td>
         </tr>
         <tr>
            <td class="dlg-body" style="padding-left: 8px;">
               <?php if ( strlen($error) ) { ?>
                  <fieldset><legend><?=$LANG['verify_result']?></legend>
                     <table class="dlg-frame">
                        <tr>
                           <td class="dlg-body" colspan="2">
                              <div class="erraction">
                              <?=$error?>
                              </div>
                           </td>
                        </tr>
                     </table>
                  </fieldset>
               <?php } elseif (strlen($info)) { ?>
                  <fieldset><legend><?=$LANG['verify_result']?></legend>
                     <table class="dlg-frame">
                        <tr>
                           <td class="dlg-body" colspan="2">
                              <div class="class">
                              <?=$info?>
                              </div>
                           </td>
                        </tr>
                     </table>
                  </fieldset>
               <?php } ?>
               <br>
            </td>
         </tr>
      </table>
   </div>
</div>
<?php require("includes/footer.html.inc.php"); ?>