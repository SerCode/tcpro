<?php
/**
 * groupassign.php
 *
 * Displays the group assignment matrix page
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

require_once( "models/config_model.php" );
require_once( "models/group_model.php" );
require_once( "models/log_model.php" );
require_once( "models/login_model.php" );
require_once( "includes/tcuser.class.php" );
require_once( "includes/tcusergroup.class.php" );
require_once( "includes/tcuseroption.class.php" );

$C = new Config_model;
$G = new Group_model;
$L = new Login_model;
$LOG = new Log_model;
$U  = new tcUser;
$UG = new tcUserGroup;
$UO = new tcUserOption;

$error=FALSE;

/**
 * Check if allowed
 */
if (!isAllowed("manageGroupMemberships")) showError("notallowed");

if ( !isset($_REQUEST['sort']) ) $sort="asc";
else $sort = $_REQUEST['sort'];

if ( !isset($_REQUEST['searchuser']) ) $searchuser="";
else $searchuser = trim($_REQUEST['searchuser']);

if (isset($_POST['btn_reset'])) $searchuser="";

/**
 * =========================================================================
 * APPLY
 */
if ( isset($_POST['btn_apply']) ) {

   $groups = $G->getAll();

   foreach($_POST as $key=>$value) {
      if (substr($key,0 ,3)== "hid" ) {
         //
         // Hidden field. Get the value of it
         //
         if($value == "true") {
            //
            // The associated radio set that was changed
            // Get the username out of the key - between two '#'
            //
            preg_match_all('/#(.*)#/', $key, $matches);
            $username = $matches[1][0];
            //
            // The radio button set name is the key value without the cgd prefix and the '#'
            //
            $radioFieldName = ltrim($key, "hid_");
            $radioFieldName = str_replace ( "#" , "" , $radioFieldName);

            if ($key=="hid_#".$username."#_t") {
               //
               // Set user type
               //
               $U->findByName($username);
               $U->clearUserType($CONF['UTDIRECTOR']);
               $U->clearUserType($CONF['UTADMIN']);
               switch ($_POST[$radioFieldName]) {
               case "admin":
                  $U->setUserType($CONF['UTADMIN']);
                  break;
               case "director":
                  $U->setUserType($CONF['UTDIRECTOR']);
                  break;
               case "user":
                  $U->setUserType($CONF['UTUSER']);
                  break;
               }
               $U->update($U->username);
            }
            else {
               $groupName = ltrim($radioFieldName, $username."_");
               switch ($_POST[$radioFieldName]) {
                  case "notmember":
                     $UG->deleteMembership($username,$groupName);
                     break;
                  case "member":
                     if (!$UG->isMemberOfGroup($username,$groupName)) {
                        $UG->createUserGroupEntry($username,$groupName,"member");
                     }
                     else {
                        $UG->updateUserGroupType($username,$groupName,"member");
                     }
                     break;
                  case "manager":
                     if (!$UG->isMemberOfGroup($username,$groupName)) {
                        $UG->createUserGroupEntry($username,$groupName,"manager");
                     }
                     else {
                        $UG->updateUserGroupType($username,$groupName,"manager");
                     }
                     $U->setUserType($CONF['UTMANAGER']);
                     $U->update($U->username);
                     break;
                  default:
                     break;
               }
            }
         }
      }
   }
   /*
    * Log this event
    */
   $LOG->log("logUser",$L->checkLogin(),"User Type, Group assigments updated");
}

require("includes/header.html.inc.php");
echo "<body>\r\n";
echo "<div id=\"overDiv\" style=\"position:absolute; visibility:hidden; z-index:1000;\"></div>\r\n";
require("includes/header.application.inc.php");
require("includes/menu.inc.php");
?>
<div id="content">
   <div id="content-content">
      <table class="dlg">
         <tr>
            <td style="padding: 8px 14px 8px 14px;">
               <form name="search" class="form" method="POST" action="<?=$_SERVER['PHP_SELF']."?searchuser=".$searchuser."&amp;sort=".$sort."&amp;lang=".$CONF['options']['lang']?>">
                  <?=$LANG['user_search']?>
                  <input name="searchuser" id="searchuser" size="30" type="text" class="text" value="<?=$searchuser?>">
                  <input name="btn_search" type="submit" class="button" value="<?=$LANG['btn_search']?>">
                  <input name="btn_reset" type="submit" class="button" value="<?=$LANG['btn_reset']?>">
               </form>
            </td>
         </tr>
      </table>
      <br>

      <?php
      /**
       *  See how many groups we have and put each groupname in an array
       */
      $groups = $G->getAll();
      $nofgroups = count($groups);
      $gcols = $nofgroups + 1; // Have to add the column for user type
      foreach ($groups as $row) {
         $grouparray[] = $row['groupname'];
      }
      $groupsperblock = intval($C->readConfig("repeatUsernamesAfter"));
      $groupblocks = floor($gcols/$groupsperblock);
      if ($gcols%$groupsperblock) $groupblocks++;
      ?>

      <!--  USERS =========================================================== -->

      <form name="assign" class="form" method="POST" action="<?=$_SERVER['PHP_SELF']."?searchuser=".$searchuser."&amp;sort=".$sort."&amp;lang=".$CONF['options']['lang']?>">

         <table class="dlg">
            <tr>
               <td class="dlg-header" colspan="<?=($gcols*3)+4+($groupblocks-4)?>">
                  <?php printDialogTop($LANG['uassign_title'],"group_assignment.html","ico_users.png"); ?>
               </td>
            </tr>
            <!-- Captions -->
            <tr>
               <td class="dlg-caption" style="text-align: left; padding-left: 8px;">
                  <?php if ( $sort=="desc" ) { ?>
                     <a href="<?=$_SERVER['PHP_SELF']."?searchuser=".$searchuser."&amp;lang=".$CONF['options']['lang']."&amp;sort=asc"?>"><img src="themes/<?=$theme?>/img/asc.png" border="0" align="middle" alt="" title="<?=$LANG['log_sort_asc']?>"></a>
                  <?php }else { ?>
                     <a href="<?=$_SERVER['PHP_SELF']."?searchuser=".$searchuser."&amp;lang=".$CONF['options']['lang']."&amp;sort=desc"?>"><img src="themes/<?=$theme?>/img/desc.png" border="0" align="middle" alt="" title="<?=$LANG['log_sort_desc']?>"></a>
                   <?php } ?>
                   &nbsp;<?=$LANG['admin_user_user']?>
               </td>
               <td class="dlg-caption" style="text-align: center;" colspan="3"><?=$LANG['uassign_usertype']?></td>
               <?php for ($groupindex=0; $groupindex<$nofgroups; $groupindex++ ) {
                  if ( $groupindex>0 && (($groupindex+1)%$groupsperblock) == 0 ) { ?>
                     <td class="dlg-caption" style="text-align: center;"><?=$LANG['admin_user_user']?></td>
                  <?php } ?>
                  <td class="dlg-caption" style="text-align: center;" colspan="3"><?=$grouparray[$groupindex]?></td>
               <?php } ?>
            </tr>
            <!-- Sub Captions -->
            <?php $printrow=1; ?>
            <tr>
               <td class="dlg-row<?=$printrow?>" style="text-align: center;">&nbsp;</td>
               <td class="dlg-row<?=$printrow?>" style="border-left: 1px solid #bbbbbb; text-align: center;"><img src="themes/<?=$theme?>/img/ico_usr_member.png" align="middle" alt="ico_usr_member.png" title="<?=$LANG['uassign_tt_member']?>"></td>
               <td class="dlg-row<?=$printrow?>" style="text-align: center;"><img src="themes/<?=$theme?>/img/ico_usr_director.png" align="middle" alt="ico_usr_director.png" title="<?=$LANG['uassign_tt_director']?>"></td>
               <td class="dlg-row<?=$printrow?>" style="border-right: 1px solid #bbbbbb; text-align: center;"><img src="themes/<?=$theme?>/img/ico_usr_admin.png" align="middle" alt="ico_usr_admin.png" title="<?=$LANG['uassign_tt_admin']?>"></td>
               <?php for ($i=0; $i<$gcols-1; $i++) {
                  if ( $i>0 && (($i+1)%$groupsperblock) == 0) { ?>
                     <td class="dlg-row<?=$printrow?>" style="border-left: 1px solid #bbbbbb; text-align: center;">&nbsp;</td>
                  <?php } ?>
                     <td class="dlg-row<?=$printrow?>" style="border-left: 1px solid #bbbbbb; text-align: center;"><img src="themes/<?=$theme?>/img/btn_del.gif" align="middle" alt="ico_usr.png" title="<?=$LANG['uassign_tt_gnotmember']?>"></td>
                     <td class="dlg-row<?=$printrow?>" style="text-align: center;"><img src="themes/<?=$theme?>/img/ico_usr.png" align="middle" alt="ico_usr.png" title="<?=$LANG['uassign_tt_gmember']?>"></td>
                     <?php if ($i==$gcols) $color="#000000"; else $color="#bbbbbb"; ?>
                     <td class="dlg-row<?=$printrow?>" style="border-right: 1px solid <?=$color?>; text-align: center;"><img src="themes/<?=$theme?>/img/ico_usr_manager.png" align="middle" alt="ico_usr_manager.png" title="<?=$LANG['uassign_tt_gmanager']?>"></td>
               <?php } ?>
            </tr>
            <?php
            if ($sort=="desc") $sortorder="DESC"; else $sortorder="ASC";
            if (strlen($searchuser)) {
               $query  = "SELECT `username` FROM `".$U->table."` ".
                         "WHERE username!='admin' ".
                         "AND firstname LIKE '".$searchuser."' ".
                         "OR lastname LIKE '".$searchuser."' ".
                         "ORDER BY `lastname` ".$sortorder.",`firstname`;";
            }
            else {
               $query = "SELECT `username` FROM `".$U->table."` WHERE username!='admin' ORDER BY `lastname` ".$sortorder.",`firstname`;";
            }
            $norows=1;
            $result = $U->db->db_query($query);
            while ( $row = $U->db->db_fetch_array($result,MYSQL_ASSOC) ) {
               $userchk="";
               $directorchk="";
               $adminchk="";
               $U->findByName($row['username']);
               if ( $U->firstname!="" ) $showname = $U->lastname.", ".$U->firstname; else $showname = $U->lastname;
               if ( $U->checkUserType($CONF['UTADMIN']) ) {
                  $icon = "ico_usr_admin.png";
                  $icon_tooltip = $LANG['icon_admin'];
                  $adminchk="CHECKED";
               }
               else if ( $U->checkUserType($CONF['UTDIRECTOR']) ) {
                  $icon = "ico_usr_director.png";
                  $icon_tooltip = $LANG['icon_director'];
                  $directorchk="CHECKED";
               }
               else if ( $U->checkUserType($CONF['UTMANAGER']) ) {
                  $icon = "ico_usr_manager.png";
                  $icon_tooltip = $LANG['icon_manager'];
                  $userchk="CHECKED";
               }
               else {
                  $icon = "ico_usr.png";
                  $icon_tooltip = $LANG['icon_user'];
                  $userchk="CHECKED";
               }

               if ($printrow==1) $printrow=2; else $printrow=1;
               $showname = str_replace(" ","&nbsp;",$showname."&nbsp;(".$U->username.")");
               /*
                * User Type
                */
               echo "
                  <!-- ".$showname." -->
                  <tr>
                     <td class=\"dlg-row".$printrow."\" style=\"white-space:nowrap;\"><img src=\"themes/".$theme."/img/".$icon."\" align=\"middle\" alt=\"".$icon_tooltip."\" title=\"".$icon_tooltip."\" style=\"padding-right: 2px;\">$showname</td>
                     <td class=\"dlg-row".$printrow."\" style=\"border-left: 1px solid #bbbbbb; text-align: center;\">";
               if (isAllowed("manageUsers"))
                  echo "<input name=\"".$U->username."_t\" id=\"".$U->username."_t_user\" type=\"radio\" value=\"user\" ".$userchk." onclick=\"changedRadio('hid_#".$U->username."#_t')\">";
               else if ($userchk=="CHECKED")
                  echo "<img src=\"img/icons/checkmark.png\" alt=\"\" align=\"middle\">";
               else
                  echo "&nbsp;";
               echo "</td>
                     <td class=\"dlg-row".$printrow."\" style=\"text-align: center;\">";
               if (isAllowed("manageUsers"))
                  echo "<input name=\"".$U->username."_t\" id=\"".$U->username."_t_director\" type=\"radio\" value=\"director\" ".$directorchk." onclick=\"changedRadio('hid_#".$U->username."#_t')\">";
               else if ($directorchk=="CHECKED")
                  echo "<img src=\"img/icons/checkmark.png\" alt=\"\" align=\"middle\">";
               else
                  echo "&nbsp;";
               echo "</td>
                     <td class=\"dlg-row".$printrow."\" style=\"border-right: 1px solid #bbbbbb; text-align: center;\">";
               if (isAllowed("manageUsers"))
                  echo "<input name=\"".$U->username."_t\" id=\"".$U->username."_t_admin\" type=\"radio\" value=\"admin\" ".$adminchk." onclick=\"changedRadio('hid_#".$U->username."#_t')\">
                        <input id=\"hid_#".$U->username."#_t\" name=\"hid_#".$U->username."#_t\" type=\"hidden\" value=\"false\">";
               else if ($adminchk=="CHECKED")
                  echo "<img src=\"img/icons/checkmark.png\" alt=\"\" align=\"middle\">";
               else
                  echo "&nbsp;";
               echo "</td>";
               /*
                * Memberships
                */
               $gnum=1;
               for ($groupindex=0; $groupindex<$nofgroups; $groupindex++ ) {
                  if ($groupindex>0 && ($groupindex+1)%$groupsperblock==0) {
                     echo "<td class=\"dlg-row".$printrow."\" style=\"white-space:nowrap;\"><img src=\"themes/".$theme."/img/".$icon."\" align=\"middle\" alt=\"".$icon_tooltip."\" title=\"".$icon_tooltip."\" style=\"padding-right: 2px;\">$showname</td>";
                  }
                  $notmemberchk="";
                  $memberchk="";
                  $managerchk="";
                  if ($UG->isMemberOfGroup($U->username,$grouparray[$groupindex])) {
                     if ($UG->type=="manager") {
                        $managerchk="CHECKED";
                     }
                     else if ($UG->type=="member") {
                        $memberchk="CHECKED";
                     }
                  }
                  else {
                     $notmemberchk="CHECKED";
                  }
                  echo "<td class=\"dlg-row".$printrow."\" style=\"border-left: 1px solid #bbbbbb; text-align: center;\">
                        <input name=\"".$U->username."_".$grouparray[$groupindex]."\" id=\"".$U->username."_".$grouparray[$groupindex]."_t_notmember\" type=\"radio\" value=\"notmember\" ".$notmemberchk." onclick=\"changedRadio('hid_#".$U->username."#_".$grouparray[$groupindex]."')\">
                     </td>";
                  $tdid="";
                  echo "<td class=\"dlg-row".$printrow."\" style=\"text-align: center;\">
                        <input name=\"".$U->username."_".$grouparray[$groupindex]."\" id=\"".$U->username."_".$grouparray[$groupindex]."_t_member\" type=\"radio\" value=\"member\" ".$memberchk." onclick=\"changedRadio('hid_#".$U->username."#_".$grouparray[$groupindex]."')\">
                     </td>";
                  if ($gnum==$gcols-1) $color="#000000"; else $color="#bbbbbb";
                  echo "<td class=\"dlg-row".$printrow."\" style=\"border-right: 1px solid ".$color."; text-align: center;\">
                        <input name=\"".$U->username."_".$grouparray[$groupindex]."\" id=\"".$U->username."_".$grouparray[$groupindex]."_t_manager\" type=\"radio\" value=\"manager\" ".$managerchk." onclick=\"changedRadio('hid_#".$U->username."#_".$grouparray[$groupindex]."')\">
                        <input id=\"hid_#".$U->username."#_".$grouparray[$groupindex]."\" name=\"hid_#".$U->username."#_".$grouparray[$groupindex]."\" type=\"hidden\" value=\"false\">
                     </td>
                  ";
                  $gnum++;
               }
               echo "</tr>";
            ?>

            <?php if ($norows==intval($C->readConfig("repeatHeadersAfter"))) { ?>
               <!-- Captions -->
               <tr>
                  <td class="dlg-caption" style="text-align: left; padding-left: 8px;">
                     <?php if ( $sort=="desc" ) { ?>
                        <a href="<?=$_SERVER['PHP_SELF']."?searchuser=".$searchuser."&amp;lang=".$CONF['options']['lang']."&amp;sort=asc"?>"><img src="themes/<?=$theme?>/img/asc.png" border="0" align="middle" alt="" title="<?=$LANG['log_sort_asc']?>"></a>
                     <?php }else { ?>
                        <a href="<?=$_SERVER['PHP_SELF']."?searchuser=".$searchuser."&amp;lang=".$CONF['options']['lang']."&amp;sort=desc"?>"><img src="themes/<?=$theme?>/img/desc.png" border="0" align="middle" alt="" title="<?=$LANG['log_sort_desc']?>"></a>
                      <?php } ?>
                      &nbsp;<?=$LANG['admin_user_user']?>
                  </td>
                  <td class="dlg-caption" style="text-align: center;" colspan="3"><?=$LANG['uassign_usertype']?></td>
                  <?php for ($groupindex=0; $groupindex<$nofgroups; $groupindex++ ) {
                     if ($groupindex>0 && ($groupindex+1)%$groupsperblock==0) { ?>
                     <td class="dlg-caption" style="text-align: center;"><?=$LANG['admin_user_user']?></td>
                     <?php } ?>
                     <td class="dlg-caption" style="text-align: center;" colspan="3"><?=$grouparray[$groupindex]?></td>
                  <?php } ?>
               </tr>
               <!-- Sub Captions -->
               <?php $printrow=1; ?>
               <tr>
                  <td class="dlg-row<?=$printrow?>" style="text-align: center;">&nbsp;</td>
                  <td class="dlg-row<?=$printrow?>" style="border-left: 1px solid #bbbbbb; text-align: center;"><img src="themes/<?=$theme?>/img/ico_usr_member.png" align="middle" alt="ico_usr_member.png" title="<?=$LANG['uassign_tt_member']?>"></td>
                  <td class="dlg-row<?=$printrow?>" style="text-align: center;"><img src="themes/<?=$theme?>/img/ico_usr_director.png" align="middle" alt="ico_usr_director.png" title="<?=$LANG['uassign_tt_director']?>"></td>
                  <td class="dlg-row<?=$printrow?>" style="border-right: 1px solid #bbbbbb; text-align: center;"><img src="themes/<?=$theme?>/img/ico_usr_admin.png" align="middle" alt="ico_usr_admin.png" title="<?=$LANG['uassign_tt_admin']?>"></td>
                  <?php for ($i=0; $i<$gcols-1; $i++) {
                     if ( $i>0 && (($i+1)%$groupsperblock) == 0) { ?>
                        <td class="dlg-row<?=$printrow?>" style="border-left: 1px solid #bbbbbb; text-align: center;">&nbsp;</td>
                     <?php } ?>
                        <td class="dlg-row<?=$printrow?>" style="border-left: 1px solid #bbbbbb; text-align: center;"><img src="themes/<?=$theme?>/img/btn_del.gif" align="middle" alt="ico_usr.png" title="<?=$LANG['uassign_tt_gnotmember']?>"></td>
                        <td class="dlg-row<?=$printrow?>" style="text-align: center;"><img src="themes/<?=$theme?>/img/ico_usr.png" align="middle" alt="ico_usr.png" title="<?=$LANG['uassign_tt_gmember']?>"></td>
                        <?php if ($i==$gcols) $color="#000000"; else $color="#bbbbbb"; ?>
                        <td class="dlg-row<?=$printrow?>" style="border-right: 1px solid <?=$color?>; text-align: center;"><img src="themes/<?=$theme?>/img/ico_usr_manager.png" align="middle" alt="ico_usr_manager.png" title="<?=$LANG['uassign_tt_gmanager']?>"></td>
                  <?php } ?>
               </tr>
            <?php $norows=0; } ?>

            <?php
            $norows++;
            }
            ?>
            <tr>
               <td class="dlg-menu" colspan="<?=($gcols*3)+4+($groupblocks-4)?>" style="text-align: left;">
                  <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
                  <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?group_assignment.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
               </td>
            </tr>
         </table>
         </form>
      </div>
   </div>
<?php require("includes/footer.html.inc.php"); ?>