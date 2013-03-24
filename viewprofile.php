<?php
/**
 * viewprofile.php
 *
 * Displays the user profile dialog for viewing
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

require_once( "models/absence_model.php" );
require_once( "models/allowance_model.php" );
require_once( "models/avatar_model.php" );
require_once( "includes/tcconfig.class.php" );
require_once( "includes/tcgroup.class.php" );
require_once( "includes/tclogin.class.php" );
require_once( "includes/tctemplate.class.php" );
require_once( "includes/tcuser.class.php" );
require_once( "includes/tcusergroup.class.php" );

$A  = new Absence_model;
$AV = new Avatar_model;
$B  = new Allowance_model;
$C  = new tcConfig;
$G  = new tcGroup;
$L  = new tcLogin;
$T  = new tcTemplate;
$U  = new tcUser;
$UL = new tcUser;
$UG = new tcUserGroup;

// If a user is logged in, read his record
if ($user = $L->checkLogin()) $UL->findByName($user);

if ( isset($_REQUEST['username']) ) $req_username = $_REQUEST['username'];
else $req_username=$user;
$error = false;
$msg = false;

/**
 * Check if allowed
 */
if (!isAllowed("viewUserProfiles")) showError("notallowed", TRUE);

$U->findByName($req_username);

/**
 * Default period for absence count
 */
$today     = getdate();
$countfrom = str_replace("-","",$C->readConfig("defperiodfrom"));
$countto = str_replace("-","",$C->readConfig("defperiodto"));

/**
 * Process form
 */
if ( isset($_POST['btn_send']) ) {
   $to = $U->email;
   sendEmail($to, stripslashes($_POST['subject']), stripslashes($_POST['msg']));
   $msg = true;
   $message = $LANG['message_msgsent'];
}
else if ( isset($_POST['btn_refresh']) ) {
   /**
    * Adjust period for absence count
    */
   $countfrom = stripslashes($_POST['cntfrom']);
   $countto = stripslashes($_POST['cntto']);
}
require( "includes/header.html.inc.php" );
?>
<body>
   <div id="content">
      <div id="content-content">
         <form name="userprofile" method="POST" action="<?=$_SERVER['PHP_SELF']."?username=".$U->username."&amp;lang=".$CONF['options']['lang']?>">
            <table class="dlg">
               <tr>
                  <td class="dlg-header">
                     <?php printDialogTop($LANG['view_profile_title'],"view_profile.html","ico_users.png"); ?>
                  </td>
               </tr>
               <tr>
                  <td class="dlg-body">
                     <table class="dlg-frame">
                        <tr>
                           <td class="dlg-body" width="110" rowspan="9">
                              <?php
                              if ($AV->find($U->username)) {
                                 echo "<img src=\"".$AV->path.$AV->filename.".".$AV->fileextension."\" align=\"top\" border=\"0\" alt=\"".$U->username."\" title=\"".$U->username."\">";
                              }
                              else {
                                 echo "<img src=\"".$AV->path."noavatar.gif\" align=\"top\" border=\"0\" alt=\"No avatar\" title=\"No avatar\">";
                              }
                              ?>
                           </td>
                           <td class="dlg-body" width="80"><?=$LANG['show_profile_name']?></td>
                           <td class="dlg-body2"><b><?=$U->title." ".$U->firstname." ".$U->lastname?></b></td>
                        </tr>
                        <tr>
                           <td class="dlg-body" width="80"><?=$LANG['show_profile_uname']?></td>
                           <td class="dlg-body"><?=$U->username?></td>
                        </tr>
                        <tr>
                           <td class="dlg-body" width="80"><?=$LANG['show_profile_position']?></td>
                           <td class="dlg-body"><?=$U->position?></td>
                        </tr>
                        <tr>
                           <td class="dlg-body" width="80"><?=$LANG['show_profile_idnumber']?></td>
                           <td class="dlg-body"><?=$U->idnumber?></td>
                        </tr>
                        <tr>
                           <td class="dlg-body" width="80"><?=$LANG['show_profile_group']?></td>
                           <td class="dlg-body">
                              <?php
                              $ugroups = $UG->getAllforUser($U->username);
                              foreach ($ugroups as $row) {
                                 $G->findByName($row['groupname']);
                                 echo $row['groupname']." - ".$G->description." (".ucfirst($row['type']).")<br>";
                              }
                              ?>
                           </td>
                        </tr>
                        <tr>
                           <td class="dlg-body" width="80"><?=$LANG['show_profile_phone']?></td>
                           <td class="dlg-body"><?=$U->phone?></td>
                        </tr>
                        <tr>
                           <td class="dlg-body" width="80"><?=$LANG['show_profile_mobile']?></td>
                           <td class="dlg-body"><?=$U->mobile?></td>
                        </tr>
                        <tr>
                           <td class="dlg-body" width="80"><?=$LANG['show_profile_email']?></td>
                           <td class="dlg-body"><?=$U->email?></td>
                        </tr>
                     </table>
                  </td>
               </tr>

               <?php if (isAllowed("viewUserAbsenceCounts")) { ?>
               <tr>
                  <td class="dlg-bodyffc">
                     <div align="center">
                     <?php include( "includes/absencecount.inc.php" ); ?>
                     </div>
                  </td>
               </tr>
               <?php } ?>

               <tr>
                  <td class="dlg-menu">
                     <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?view_profile.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
                     <input name="btn_close" type="button" class="button" onclick="javascript:window.close();" value="<?=$LANG['btn_close']?>">
                  </td>
               </tr>
            </table>
         </form>
      </div>
   </div>
<?php
if ($msg) echo ("<script type=\"text/javascript\">alert(\"".$message."\")</script>");
require( "includes/footer.html.inc.php" );
?>
