<?php
/**
 * userlist.php
 *
 * Displays and runs the user administration page
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
if (strlen($CONF['options']['lang'])) require ("languages/".$CONF['options']['lang'].".tcpro.php");
else require ("languages/english.tcpro.php");

require_once( "models/allowance_model.php" );
require_once( "models/avatar_model.php" );
require_once( "models/config_model.php" );
require_once( "models/daynote_model.php" );
require_once( "models/log_model.php" );
require_once( "models/login_model.php" );
require_once( "models/template_model.php" );
require_once( "models/user_model.php" );
require_once( "models/user_announcement_model.php" );
require_once( "models/user_group_model.php" );
require_once( "models/user_option_model.php" );

$AV = new Avatar_model;
$B = new Allowance_model;
$C = new Config_model;
$G = new Group_model;
$L = new Login_model;
$LOG = new Log_model;
$N  = new Daynote_model;
$T  = new Template_model;
$U  = new User_model;
$UA = new User_announcement_model;
$UG = new User_group_model;
$UO = new User_option_model;

$error=FALSE;

/**
 * Check if allowed
 */
if (!isAllowed("manageUsers")) showError("notallowed");

if ( !isset($_REQUEST['sort']) ) $sort="asc";
else $sort = $_REQUEST['sort'];

if ( !isset($_REQUEST['searchuser']) ) $searchuser="";
else $searchuser = trim($_REQUEST['searchuser']);

if ( !isset($_REQUEST['searchgroup']) ) $searchgroup="All";
else $searchgroup = trim($_REQUEST['searchgroup']);

if ( isset($_POST['btn_reset'])) {
   $searchuser="";
   $searchgroup="All";
}

$monthnames = $CONF['monthnames'];
$today = getdate();
$curryear = $today['year']; // numeric value, 4 digits
$currmonth = $today['mon']; // numeric value

/**
 * =========================================================================
 * DELETE
 */
if ( isset($_POST['btn_usr_del']) AND ($_POST['usr_hidden']!="admin") ) {

   $deluser = $_POST['usr_hidden'];
   // Get his fullname for the deletion notification name
   $U->findByName($deluser);
   $delname = $U->firstname." ".$U->lastname;

   // Drop user
   $U->deleteByName($deluser);

   // Drop his group memberships
   $UG->deleteByUser($deluser);

   // Drop his user options
   $UO->deleteByUser($deluser);

   // Drop his templates
   $T->deleteByUser($deluser);

   // Drop his daynotes
   $N->deleteByUser($deluser);

   // Drop his allowance records
   $B->deleteByUser($deluser);

   // Drop his announcement list
   $UA->deleteAllForUser($deluser);

   // Delete all avatars
   $AV->delete($U->username);

   // Log this event (Loglevel is checked in log())
   $LOG->log("logUser",$L->checkLogin(),"User deleted: ".$deluser." (".$delname.")");

   // Send notification e-Mails
   sendNotification("userdelete",$delname,"");
}
/**
 * =========================================================================
 * RESET PASSWORD
 */
else if ( isset($_POST['btn_usr_pwd_reset']) AND ($_POST['usr_hidden']!="admin") ) {
   $U->findByName($_POST['usr_hidden']);
   $newpwd = generatePassword();
   $U->password = crypt($newpwd,$CONF['salt']);
   $U->last_pw_change = date("Y-m-d H:I:s");
   /**
    * Deploy the changes
    */
   $U->update($U->username);
   $U->clearStatus($CONF['USCHGPWD']);
   /**
    * Send notification e-mail
    */
   $message = $LANG['notification_greeting'];
   $message .= $LANG['notification_usr_pwd_reset'];
   $message .= $LANG['notification_usr_pwd_reset_user'];
   $message .= $_POST['usr_hidden'];
   $message .= "\r\n\r\n";
   $message .= $LANG['notification_usr_pwd_reset_pwd'];
   $message .= $newpwd;
   $message .= "\r\n\r\n";
   $message .= $LANG['notification_sign'];
   $to = $U->email;
   $subject = stripslashes($LANG['notification_usr_pwd_subject']);
   sendEmail($to, $subject, $message);
   /**
    * Log this event
    */
   $LOG->log("logUser",$L->checkLogin(),"User password reset: ".$U->username);
   echo ("<script type=\"text/javascript\">alert(\"".$LANG['user_pwd_reset_complete']."\");</script>");
}
require("includes/header_html_inc.php");
echo "<body>\r\n";
echo "<div id=\"overDiv\" style=\"position:absolute; visibility:hidden; z-index:1000;\"></div>\r\n";
require("includes/header_app_inc.php");
require("includes/menu_inc.php");
?>
<div id="content">
   <div id="content-content">
      <table class="dlg">
         <tr>
            <td style="padding: 8px 14px 8px 14px;">
               <form name="search" class="form" method="POST" action="<?=$_SERVER['PHP_SELF']."?searchuser=".$searchuser."&amp;searchgroup=".$searchgroup."&amp;sort=".$sort."&amp;lang=".$CONF['options']['lang']?>">
                  <?=$LANG['user_search']?>
                  <input name="searchuser" id="searchuser" size="30" type="text" class="text" value="<?=$searchuser?>">
                  <!-- Group filter drop down -->
                  &nbsp;&nbsp;<?=$LANG['nav_groupfilter']?>&nbsp;
                  <select id="searchgroup" name="searchgroup" class="select" onchange="javascript:">
                     <option value="All" <?=($searchgroup=="All"?"SELECTED":"")?>><?=$LANG['drop_group_all']?></option>
                     <?php
                        $groups = $G->getAll();
                        foreach ($groups as $row) {
                           $G->findByName(stripslashes($row['groupname']));
                           if (!$G->checkOptions($CONF['G_HIDE']) ) {
                              if ($searchgroup==$G->groupname)
                                 echo ("<option value=\"" . $searchgroup . "\" SELECTED=\"selected\">" . $searchgroup . "</option>");
                              else
                                 echo ("<option value=\"" . $G->groupname . "\" >" . $G->groupname . "</option>");
                           }
                        }
                     ?>
                  </select>&nbsp;&nbsp;
                  <input name="btn_search" type="submit" class="button" value="<?=$LANG['btn_search']?>">
                  <input name="btn_reset" type="submit" class="button" value="<?=$LANG['btn_reset']?>">
               </form>
            </td>
         </tr>
      </table>
      <br>

      <!--  USERS =========================================================== -->
      <?php $colspan="4"; ?>
      <table class="dlg">
         <tr>
            <td class="dlg-header" colspan="<?=$colspan?>">
               <?php printDialogTop($LANG['admin_user_title'],"manage_users.html","ico_users.png"); ?>
            </td>
         </tr>
         <tr>
            <td class="dlg-caption" style="text-align: left; padding-left: 8px;">
               <?php if ( $sort=="desc" ) { ?>
                  <a href="<?=$_SERVER['PHP_SELF']."?searchuser=".$searchuser."&amp;lang=".$CONF['options']['lang']."&amp;sort=asc"?>"><img src="themes/<?=$theme?>/img/asc.png" border="0" align="middle" alt="" title="<?=$LANG['log_sort_asc']?>"></a>
               <?php }else { ?>
                  <a href="<?=$_SERVER['PHP_SELF']."?searchuser=".$searchuser."&amp;lang=".$CONF['options']['lang']."&amp;sort=desc"?>"><img src="themes/<?=$theme?>/img/desc.png" border="0" align="middle" alt="" title="<?=$LANG['log_sort_desc']?>"></a>
                <?php } ?>
                &nbsp;<?=$LANG['admin_user_user']?>
            </td>
            <td class="dlg-caption" style="text-align: center;"><?=$LANG['admin_user_attributes']?></td>
            <td class="dlg-caption" style="text-align: left;"><?=$LANG['admin_user_lastlogin']?></td>
            <td class="dlg-caption" style="text-align: right; padding-right: 8px;"><?=$LANG['admin_user_action']?></td>
         </tr>
         <tr>
            <td class="dlg-row1" colspan="<?=$colspan?>"><img src="themes/<?=$theme?>/img/ico_add.png" alt="Add" title="Add" align="middle" style="padding-right: 2px;">
               <input name="btn_usr_create" type="button" class="button" value="<?=$LANG['btn_create']?>" onclick="javascript:this.blur();openPopup('addprofile.php?lang=<?=$CONF['options']['lang']?>','addprofile','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=no,dependent=1,width=560,height=680');">&nbsp;&nbsp;
               <i><?=$LANG['admin_create_new_user']?></i>
            </td>
         </tr>
         <tr>
            <td class="dlg-row1" colspan="<?=$colspan?>"><img src="themes/<?=$theme?>/img/ico_import.png" alt="Import" title="Import" align="middle" style="padding-right: 2px;">
               <input name="btn_usr_import" type="button" class="button" value="<?=$LANG['btn_import']?>" onclick="javascript:this.blur();openPopup('userimport.php?lang=<?=$CONF['options']['lang']?>','addprofile','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=no,dependent=1,width=510,height=400');">&nbsp;&nbsp;
               <i><?=$LANG['admin_import_user']?></i>
            </td>
         </tr>
            <?php
               if ($sort=="desc") $sortorder="DESC"; else $sortorder="ASC";
               if (strlen($searchuser)) {
                  $query  = "SELECT `username` FROM `".$U->table."` ".
                            "WHERE firstname LIKE '".$searchuser."' ".
                            "OR lastname LIKE '".$searchuser."' ".
                            "ORDER BY `lastname` ".$sortorder.",`firstname`;";
               }
               else {
                  $query  = "SELECT `username` FROM `".$U->table."` ORDER BY `lastname` ".$sortorder.",`firstname`;";
               }
               $result = $U->db->db_query($query);
               $numusers = $U->db->db_numrows($result);
               $ui=1;
               $printrow=1;
               while ( $row = $U->db->db_fetch_array($result,MYSQL_ASSOC) )
               {
                  if ($searchgroup!="All") {
                     if (!$UG->isMemberOfGroup($row['username'],$searchgroup)) continue;
                  }
                  $U->findByName($row['username']);
                  if ( $U->firstname!="" ) $showname = $U->lastname.", ".$U->firstname; else $showname = $U->lastname;
                  $templateUser = "";
                  if ( $U->checkUserType($CONF['UTADMIN']) ) {
                     $icon = "ico_usr_admin";
                     $icon_tooltip = $LANG['icon_admin'];
                  }else if ( $U->checkUserType($CONF['UTDIRECTOR']) ) {
                     $icon = "ico_usr_director";
                     $icon_tooltip = $LANG['icon_director'];
                  }else if ( $U->checkUserType($CONF['UTMANAGER']) ) {
                     $icon = "ico_usr_manager";
                     $icon_tooltip = $LANG['icon_manager'];
                  }else if ( $U->checkUserType($CONF['UTTEMPLATE']) ) {
                     $icon = "ico_users";
                     $icon_tooltip = $LANG['icon_template'];
                     $templateUser = $LANG['template_user'];
                  }else {
                     $icon = "ico_usr";
                     $icon_tooltip = $LANG['icon_user'];
                  }
                  if ( !$U->checkUserType($CONF['UTMALE']) ) $icon .= "_f.png";
                  else $icon .= ".png";
                  if ( !$U->checkStatus($CONF['USLOCKED']) ) $lockedicon = "";
                  else $lockedicon = "ico_locked.png";
                  if ( !$U->checkStatus($CONF['USHIDDEN']) ) $hiddenicon = "";
                  else $hiddenicon = "ico_delete.png";
                  if ( !$U->checkStatus($CONF['USLOGLOC']) ) $loglocicon = "";
                  else $loglocicon = "ico_onhold.png";
                  if ( !$UO->find($U->username,"verifycode") ) $verifyicon = "";
                  else $verifyicon = "ico_verify.png";

                  if ($printrow==1) $printrow=2; else $printrow=1;
                  $botstyle  = "";
                  $botborder = "";
                  if ($ui==$numusers) {
                     $botstyle  = " style=\"border-bottom: 1px solid #000000;\"";
                     $botborder = " border-bottom: 1px solid #000000;";
                  }
                  ?>
                  <!-- ".$showname." -->
                  <tr>
                     <td class="dlg-row<?=$printrow?>" <?=$botstyle?>><img src="themes/<?=$theme?>/img/<?=$icon?>" align="top" alt="" title="<?=$icon_tooltip?>" style="padding-right: 2px;\"><?=$showname?> (<?=$U->username?>) <?=$templateUser?></td>
                     <td class="dlg-row<?=$printrow?>" style="text-align: center; <?=$botborder?>">
                     <?php  if (strlen($loglocicon)) { ?>
                        <img src="themes/<?=$theme?>/img/<?=$loglocicon?>" width="16" height="16" align="top" alt="" style="padding-right: 2px;" title="<?=$LANG['tt_user_logloc']?>">
                     <?php } else { ?>
                        &nbsp;
                     <?php }
                     if (strlen($lockedicon)) { ?>
                        <img src="themes/<?=$theme?>/img/<?=$lockedicon?>" width="16" height="16" align="top" alt="" style="padding-right: 2px;" title="<?=$LANG['tt_user_locked']?>">
                     <?php } else { ?>
                        &nbsp;
                     <?php }
                     if (strlen($hiddenicon)) { ?>
                        <img src="themes/<?=$theme?>/img/<?=$hiddenicon?>" width="16" height="16" align="top" alt="" style="padding-right: 2px;" title="<?=$LANG['tt_user_hidden']?>">
                     <?php } else { ?>
                        &nbsp;
                     <?php }
                     if (strlen($verifyicon)) { ?>
                        <img src="themes/<?=$theme?>/img/<?=$verifyicon?>" width="16" height="16" align="top" alt="" style="padding-right: 2px;" title="<?=$LANG['tt_user_verify']?>">
                     <?php } else { ?>
                        &nbsp;
                     <?php } ?>
                     </td>
                     <td class="dlg-row<?=$printrow?>" <?=$botstyle?>><?=$U->last_login?></td>
                     <td class="dlg-row<?=$printrow?>" style="text-align: right; <?=$botborder?>">
                        <input name="btn_usr_edit" type="button" class="button" value="<?=$LANG['btn_edit']?>" onclick="javascript:this.blur();openPopup('editprofile.php?referrer=userlist&amp;lang=<?=$CONF['options']['lang']?>&amp;username=<?=$U->username?>','editprofile','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=no,dependent=1,width=600,height=680');">&nbsp;
                        <?php if ($U->username!="admin") { ?>
                        <form class="form" name="form-<?=$U->username?>-del" method="POST" action="<?=$_SERVER['PHP_SELF']?>?lang=<?=$CONF['options']['lang']?>">
                           <input name="usr_hidden" type="hidden" class="text" value="<?=$U->username?>">&nbsp;
                           <input name="btn_usr_del" type="submit" class="button" value="<?=$LANG['btn_delete']?>" onclick="return confirmSubmit('<?=$LANG['user_delete_confirm'].$U->username?>')">&nbsp;
                           <input name="btn_usr_pwd_reset" type="submit" class="button" value="<?=$LANG['btn_reset_password']?>" onclick="return confirmSubmit('<?=$LANG['user_pwd_reset_confirm'].$U->username?>')">&nbsp;
                        </form>
                        <?php } ?>
                     </td>
                  </tr>
                  <?php  $ui++;
               }
            ?>
      </table>
   </div>
</div>
<?php require("includes/footer_inc.php"); ?>