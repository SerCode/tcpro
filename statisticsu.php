<?php
/**
 * statisticsu.php
 *
 * Displays and runs the statistics page for single users
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

require_once( "models/absence_model.php" );
require_once( "models/allowance_model.php" );
require_once( "models/config_model.php" );
require_once( "models/group_model.php" );
require_once( "models/login_model.php" );
require_once( "models/month_model.php" );
require_once( "models/statistic_model.php" );
require_once( "models/template_model.php" );
require_once( "models/user_model.php" );
require_once( "models/user_group_model.php" );

$A = new Absence_model;
$B = new Allowance_model;
$C = new Config_model;
$G = new Group_model;
$L = new Login_model;
$M = new Month_model;
$ST = new Statistic_model;
$T = new Template_model;
$U = new User_model;
$U1 = new User_model;
$UL = new User_model;
$UG = new User_group_model;
$error=FALSE;

/**
 * Check if allowed
 */
if (!isAllowed("viewStatistics")) showError("notallowed");

/**
 * Set diagram size
 */
$diagramwidth=550;
$barareawidth=450;

/**
 * Get current user
 */
$user=$L->checkLogin();
$UL->findByName($user);

/**
 * Get Today
 */
$today      = getdate();
$daytoday   = sprintf("%02d",$today['mday']);   // Numeric representation of todays' day of the month
$monthtoday = sprintf("%02d",$today['mon']);    // Numeric representation of todays' month
$yeartoday  = $today['year'];                   // A full numeric representation of todays' year, 4 digits
$nofdays    = sprintf("%02d",date("t",time()));

/**
 * Defaults
 */
$statgroup = "%";
$statuser = "%";
$periodFrom = $yeartoday."0101";
$periodTo = $yeartoday."1231";

/**
 * Process form
 */
if (isset($_POST['btn_apply_group'])) {
   $statuser="%";
   if ($_POST['sel_group']=="All") $statgroup="%";
   else $statgroup = $_POST['sel_group'];
}
else if (isset($_POST['btn_apply_user'])) {
   $statgroup="%";
   if ($_POST['sel_user']=="All") $statuser="%";
   else $statuser = $_POST['sel_user'];
}
require( "includes/header.html.inc.php" );
echo "<body>\r\n";
echo "<div id=\"overDiv\" style=\"position:absolute; visibility:hidden; z-index:1000;\"></div>";
require( "includes/header.application.inc.php" );
require( "includes/menu.inc.php" );
?>
<div id="content">
   <div id="content-content">
      <table class="dlg">
         <tr>
            <td class="dlg-header">
               <?php printDialogTop($LANG['stat_u_title']." ".$yeartoday,"statistics_display_2.html","ico_statistics.png"); ?>
            </td>
         </tr>
         <tr>
            <td class="dlg-body">
               <div align="center">
                  <table style="width: 98%">
                     <tr>
                        <td style="vertical-align: top;">
                           <fieldset><legend><?=$LANG['stat_u_sel_group_user']?></legend>
                           <form  name="period" method="POST" action="<?=($_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang'])?>">
                              <table>
                                 <tr>

                                    <td style="padding-right: 6px; vertical-align: top;"><strong><?=$LANG['stat_u_sel_group']?></strong>
                                       <select name="sel_group" class="select">
                                          <option class="option" value="All" <?=($statgroup=="%"?"SELECTED":"")?>><?=$LANG['drop_group_all']?></option>
                                          <?php
                                          $groups = $G->getAll();
                                          foreach ($groups as $row) {
                                             $G->findByName(stripslashes($row['groupname']));
                                             if (!$G->checkOptions($CONF['G_HIDE']) ) {
                                                if ($statgroup==$G->groupname)
                                                   echo ("<option value=\"" . $statgroup . "\" SELECTED=\"selected\">" . $statgroup . "</option>");
                                                else
                                                   echo ("<option value=\"" . $G->groupname . "\" >" . $G->groupname . "</option>");
                                             }
                                          }
                                          ?>
                                       </select>
                                    </td>
                                    <td style="vertical-align: middle;">
                                       <input name="btn_apply_group" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
                                       &nbsp;&nbsp;&nbsp;
                                    </td>

                                    <td style="padding-right: 6px; vertical-align: top;"><strong><?=$LANG['stat_u_sel_user']?></strong>
                                       <select name="sel_user" class="select">
                                          <option class="option" value="All" <?=($statuser=="%"?"SELECTED":"")?>><?=$LANG['drop_group_all']?></option>
                                          <?php
                                          $users = $U->getAll();
                                          foreach ($users as $row) {
                                             if ( $row['username']!="admin" )
                                             {
                                                if ($statuser==$row['username'])
                                                   echo ("<option value=\"" . $row['username'] . "\" selected>" . $row['lastname'].", ".$row['firstname']."</option>\n");
                                                else
                                                   echo ("<option value=\"" . $row['username'] . "\">" . $row['lastname'].", ".$row['firstname']."</option>\n");
                                             }
                                          }
                                          ?>
                                       </select>
                                    </td>
                                    <td style="vertical-align: middle;">
                                       <input name="btn_apply_user" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
                                    </td>
                                 </tr>
                              </table>
                           </form>
                           </fieldset>
                        </td>
                     </tr>
                  </table>

                  <!-- TOTAL REMAINDER PER USER AND ABSENCE -->
                  <?php
                     unset($legend);
                     unset($value);
                     if ($statgroup=="%") $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;All)";
                     else $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;".$statgroup.")";
                     /**
                      * Loop through all groups
                      */
                     $groups = $G->getAllByGroup($statgroup);
                     foreach ($groups as $group) {
                        $G->findByName($group['groupname']);
                        if (!$G->checkOptions($CONF['G_HIDE']) ) {
                           $total=0;
                           /**
                            * Now loop through all users in this group
                            */
                           $gusers = $UG->getAllforGroup($group['groupname']);
                           foreach ($gusers as $guser) {
                              $U1->findByName($guser);
                              if ( !$U1->checkUserType($CONF['UTTEMPLATE']) ) {
                                 if ( strlen($U1->firstname)) $displayname = $U1->lastname.", ".$U1->firstname;
                                 else                         $displayname = $U1->lastname;
                                 echo "<fieldset style=\"text-align: left; width: 96%;\"><legend>".$LANG['stat_graph_u_remainder_title_1'].$periodFrom.'-'.$periodTo.$LANG['stat_graph_u_remainder_title_2'].$displayname."</legend>";
                                 echo "<table><tr><td style=\"vertical-align: top;\">\n\r";
                                 echo "<table>\n\r";
                                 echo "<tr>" .
                                       "   <td class=\"stat-header\" style=\"border-bottom: 1px solid #999999;\">".$LANG['stat_u_type']."</td>" .
                                       "   <td class=\"stat-header-r\" style=\"width: auto;\">".($yeartoday-1)."</td>" .
                                       "   <td class=\"stat-header-r\" style=\"width: auto;\">".$yeartoday."</td>" .
                                       "   <td class=\"stat-header-r\" style=\"width: auto;\">".$LANG['stat_u_taken']."</td>" .
                                       "   <td class=\"stat-header-r\">".$LANG['stat_u_total_remainder']."</td>" .
                                       "</tr>\n\r";

                                 /**
                                  * Get total remainders per absence type for current year for this user
                                  */
                                 unset($legend);
                                 unset($value);
                                 $sum=0;
                                 $absences = $A->getAll();
                                 foreach ($absences as $abs) {
                                    if ($A->get($abs['id']) AND !$A->counts_as_present AND $A->allowance AND $A->factor) {
                                       $total=0;
                                       if ( $B->find($guser,$A->id) ) {
                                          $lstyr = $B->lastyear;
                                          $allow = $B->curryear;
                                       }else{
                                          $lstyr = 0;
                                          $allow = $A->allowance;
                                       }
                                       $taken=countAbsence($guser,$A->id,$periodFrom,$periodTo);
                                       $total += ($lstyr+$allow)-($taken);
                                       $sum += $total;
                                       $legend[] = $A->name;
                                       $value[] = $total;
                                       echo "<tr>" .
                                             "   <td class=\"stat-caption\" style=\"white-space:nowrap;\">".$A->name."</td>" .
                                             "   <td class=\"stat-value\" style=\"width: auto;\">".$lstyr."</td>" .
                                             "   <td class=\"stat-value\" style=\"width: auto;\">".$allow."</td>" .
                                             "   <td class=\"stat-value\" style=\"width: auto; color: #AA0000;\">".$taken."</td>" .
                                             "   <td class=\"stat-value\">".sprintf("%1.1f",$total)." days</td>" .
                                             "</tr>\n\r";
                                    }
                                 }
                              }
                              echo "<tr>" .
                                   "   <td class=\"stat-sum-caption\">".$LANG['stat_u_total']."</td>" .
                                   "   <td class=\"stat-sum-value\" style=\"width: auto;\">&nbsp;</td>" .
                                   "   <td class=\"stat-sum-value\" style=\"width: auto;\">&nbsp;</td>" .
                                   "   <td class=\"stat-sum-value\" style=\"width: auto;\">&nbsp;</td>" .
                                   "   <td class=\"stat-sum-value\"><b>".sprintf("%1.1f",$sum)." days</b></td>" .
                                   "</tr>\n\r";
                              echo "</table>\n\r";
                              echo "</td><td style=\"vertical-align: top; padding-left: 20px;\">";
                              $header = $LANG['stat_graph_u_remainder_title_1'].$periodFrom.'-'.$periodTo.$LANG['stat_graph_u_remainder_title_2'].$displayname;
                              $footer = "";
                              echo $ST->barGraphH($legend,$value,$diagramwidth,$barareawidth,"green",$header,$footer);
                              echo "</td></tr></table>\n\r";
                              echo "</fieldset><br>\n\r";
                           }
                        }
                     }
                  ?>

               </div>
            </td>
         </tr>
      </table>
   </div>
</div>
<?php require( "includes/footer.html.inc.php" ); ?>