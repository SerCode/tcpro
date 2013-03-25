<?php
/**
 * statistics.php
 *
 * Displays and runs the statistics page
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
$periodFrom = $yeartoday.$monthtoday."01";
$periodTo = $yeartoday.$monthtoday.$nofdays;
$statgroup = "%";
$periodAbsence = "All";
$periodAbsenceName = "All";

/**
 * =========================================================================
 * APPLY
 */
if (isset($_POST['btn_apply'])) {
   switch ( $_POST['period'] ) {
      case "curr_month":
         $periodFrom = $yeartoday.$monthtoday."01";
         $periodTo = $yeartoday.$monthtoday.$nofdays;
         break;
      case "curr_quarter":
         switch ($monthtoday) {
            case 1:
            case 2:
            case 3:
               $periodFrom = $yeartoday."0101";
               $periodTo = $yeartoday."0331";
               break;
            case 4:
            case 5:
            case 6:
               $periodFrom = $yeartoday."0401";
               $periodTo = $yeartoday."0630";
               break;
            case 7:
            case 8:
            case 9:
               $periodFrom = $yeartoday."0701";
               $periodTo = $yeartoday."0930";
               break;
            case 10:
            case 11:
            case 12:
               $periodFrom = $yeartoday."1001";
               $periodTo = $yeartoday."1231";
               break;
         }
         break;
      case "curr_half":
         switch ($monthtoday) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
               $periodFrom = $yeartoday."0101";
               $periodTo = $yeartoday."0630";
               break;
            case 7:
            case 8:
            case 9:
            case 10:
            case 11:
            case 12:
               $periodFrom = $yeartoday."0701";
               $periodTo = $yeartoday."1231";
               break;
         }
         break;
      case "curr_year":
         $periodFrom = $yeartoday."0101";
         $periodTo = $yeartoday."1231";
         break;
      case "curr_period":
         $periodFrom = str_replace("-","",$C->readConfig("defperiodfrom"));
         $periodTo = str_replace("-","",$C->readConfig("defperiodto"));
         break;
   }
   $periodAbsence = $_POST['periodabsence'];
   $A->findBySymbol($periodAbsence);
   $periodAbsenceName = $A->dspname;
   if ($_POST['periodgroup']=="All") $statgroup="%";
   else $statgroup = $_POST['periodgroup'];
}
else if (isset($_POST['btn_apply_custom'])) {
   $periodFrom = str_replace("-","",$_POST['rangefrom']);
   $periodTo = str_replace("-","",$_POST['rangeto']);
   $periodAbsence = $_POST['customabsence'];
   $A->findBySymbol($periodAbsence);
   $periodAbsenceName = $A->dspname;
   if ($_POST['customgroup']=="All") $statgroup="%";
   else $statgroup = $_POST['customgroup'];
}

/**
 * Make sure we have month templates for all years in the desired period.
 */
$M2=new tcMonth;
for ($y=intval(substr($periodFrom,0,4)); $y<=intval(substr($periodTo,0,4)); $y++) {
   for ($m=1; $m<=12; $m++) {
      $find=strval($y).sprintf("%02d",$m);
      if (!$M->findByName($CONF['options']['region'],$find)) {
         $M2->yearmonth = $find;
         $M2->region = $CONF['options']['region'];
         $mname = $LANG['monthnames'][$m];
         $M2->template = createMonthTemplate(substr($find,0,4), $mname);
         $M2->create();
      }
   }
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
               <?php printDialogTop($LANG['stat_title'],"statistics_display.html","ico_statistics.png"); ?>
            </td>
         </tr>
         <tr>
            <td class="dlg-body">
            <div align="center">
                  <table style="width: 98%">
                     <tr>
                        <td style="vertical-align: top; width: 50%;">
                           <fieldset><legend><?=$LANG['stat_choose_period']?></legend>
                           <form  name="period" method="POST" action="<?=($_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang'])?>">
                              <table>
                                 <tr>
                                    <td style="padding-right: 6px; vertical-align: top;">
                                       <select name="period" id="period" class="select">
                                          <option class="option" value="curr_month" <?=((isset($_POST['period']) AND $_POST['period']=="curr_month")?'selected':'')?>><?=$LANG['stat_period_month']?></option>
                                          <option class="option" value="curr_quarter" <?=((isset($_POST['period']) AND $_POST['period']=="curr_quarter")?'selected':'')?>><?=$LANG['stat_period_quarter']?></option>
                                          <option class="option" value="curr_half" <?=((isset($_POST['period']) AND $_POST['period']=="curr_half")?'selected':'')?>><?=$LANG['stat_period_half']?></option>
                                          <option class="option" value="curr_year" <?=((isset($_POST['period']) AND $_POST['period']=="curr_year")?'selected':'')?>><?=$LANG['stat_period_year']?></option>
                                          <option class="option" value="curr_period" <?=((isset($_POST['period']) AND $_POST['period']=="curr_period")?'selected':'')?>><?=$LANG['stat_period_period']?></option>
                                       </select>
                                       <select name="periodgroup" id="periodgroup" class="select">
                                          <option class="option" value="All" <?=($statgroup=="All"?"selected":"")?>><?=$LANG['drop_group_all']?></option>
                                          <?php
                                          $groups = $G->getAll();
                                          foreach ($groups as $row) {
                                             $G->findByName(stripslashes($row['groupname']));
                                             if (!$G->checkOptions($CONF['G_HIDE']) ) {
                                                if ($statgroup==$G->groupname)
                                                   echo ("<option value=\"".$statgroup."\" selected>".$statgroup."</option>");
                                                else
                                                   echo ("<option value=\"".$G->groupname."\">".$G->groupname."</option>");
                                             }
                                          }
                                          ?>
                                       </select>
                                       <select name="periodabsence" id="periodabsence" class="select">
                                          <option class="option" value="All" <?=($periodAbsence=="All"?"selected":"")?>><?=$LANG['drop_group_all']?></option>
                                          <?php
                                          $absences = $A->getAll();
                                          foreach ($absences as $abs) {
                                             if ($periodAbsence == $abs['id'])
                                                echo ("<option value=\"".$abs['symbol']."\" selected>".$abs['name']."</option>");
                                             else
                                                echo ("<option value=\"".$abs['symbol']."\" >".$abs['name']."</option>");
                                          }
                                          ?>
                                       </select>
                                    </td>
                                    <td style="vertical-align: middle;">
                                       <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
                                    </td>
                                 </tr>
                              </table>
                           </form>
                           </fieldset>
                        </td>
                        <td style="vertical-align: top; width: 50%;">
                           <fieldset><legend><?=$LANG['stat_choose_custom_period']?></legend>
                           <form  name="period_custom" method="POST" action="<?=($_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang'])?>">
                              <table>
                                 <tr>
                                    <td style="padding-right: 6px;">
                                       <script type="text/javascript">
                                          $(function() { $( "#rangefrom" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd" }); });
                                          $(function() { $( "#rangeto" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd" }); });
                                       </script>
                                       <?php
                                       if (isset($_POST['rangefrom'])) $rangefromdate = $_POST['rangefrom']; else $rangefromdate = $yeartoday."-01-01"
                                       ?>
                                       <input name="rangefrom" id="rangefrom" size="10" maxlength="10" type="text" class="text" value="<?php echo $rangefromdate; ?>">
                                    </td>
                                    <td style="padding-right: 6px;">
                                       <?php
                                       if (isset($_POST['rangeto'])) $rangetodate = $_POST['rangeto']; else $rangetodate = $yeartoday."-12-31"
                                       ?>
                                       <input name="rangeto" id="rangeto" size="10" maxlength="10" type="text" class="text" value="<?php echo $rangetodate; ?>">
                                    </td>
                                    <td style="padding-right: 6px;">
                                       <select name="customgroup" id="customgroup" class="select">
                                          <option class="option" value="All" <?=($statgroup=="All"?"SELECTED":"")?>><?=$LANG['drop_group_all']?></option>
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
                                       <select name="customabsence" id="customabsence" class="select">
                                          <option class="option" value="All" <?=($periodAbsence=="All"?"SELECTED":"")?>><?=$LANG['drop_group_all']?></option>
                                          <?php
                                          $absences = $A->getAll();
                                          foreach ($absences as $abs) {
                                             if ($periodAbsence == $abs['id'])
                                                echo ("<option value=\"" . $abs['symbol'] . "\" SELECTED=\"selected\">" . $abs['name'] . "</option>");
                                             else
                                                echo ("<option value=\"" . $abs['symbol'] . "\" >" . $abs['name'] . "</option>");
                                          }
                                          ?>
                                       </select>
                                    </td>
                                    <td style="vertical-align: middle;">
                                       <input name="btn_apply_custom" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
                                    </td>
                                 </tr>
                              </table>
                           </form>
                           </fieldset>
		                  </td>
                     </tr>
                  </table>

                  <!-- TOTAL ABSENCE USER-->
                  <?php
                  $legend=array();
                  $value=array();
                  if ($statgroup=="%") $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;All)";
                  else $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;".$statgroup.")";
                  if ($periodAbsence=="All") $forabsence = "&nbsp;(".$LANG['stat_absence'].":&nbsp;All)";
                  else $forabsence = "&nbsp;(".$LANG['stat_absence'].":&nbsp;".$periodAbsenceName.")";
                  echo "<fieldset style=\"text-align: left; width: 96%;\"><legend>".$LANG['stat_results_total_absence_user'].$periodFrom."-".$periodTo.$forgroup.$forabsence."</legend>";
                  echo "<table><tr><td style=\"vertical-align: top;\">\n\r";
                  echo "<table>\n\r";
                  /**
                   * Get totals per user
                   */
                  $totaluser=0;
                  $groups = $G->getAllByGroup($statgroup);
                  foreach ($groups as $group) {
                     $G->findByName($group['groupname']);
                     if (!$G->checkOptions($CONF['G_HIDE']) ) {
                        $total=0;
                        $gusers = $UG->getAllforGroup($group['groupname']);
                        foreach ($gusers as $guser) {
                           $U1->findByName($guser);
                           if ( !$U1->checkUserType($CONF['UTTEMPLATE']) ) {
                              $total=0;
                              if ($periodAbsence=="All") {
                                 $absences=$A->getAll();
                                 foreach ($absences as $abs) {
                                    if ($A->get($abs['id']) AND !$A->counts_as_present) {
                                       $count=countAbsence($guser,$A->id,$periodFrom,$periodTo);
                                       $total+=$count;
                                       $totaluser+=$count;
                                    }
                                 }
                              }
                              else {
                                 if ($A->get($periodAbsence) AND !$A->counts_as_present) {
                                    $count=countAbsence($guser,$A->id,$periodFrom,$periodTo);
                                    $total+=$count;
                                    $totaluser+=$count;
                                 }
                              }
                              if ( strlen($U1->firstname)) $displayname = $U1->lastname.", ".$U1->firstname;
                              else                         $displayname = $U1->lastname;
                              $legend[] = $displayname;
                              $value[] = $total;
                              echo "<tr><td class=\"stat-caption\">".$displayname."</td><td class=\"stat-value\">".sprintf("%1.1f",$total)." days</td></tr>\n\r";
                           }
                        }
                     }
                  }
                  echo "<tr><td class=\"stat-sum-caption\">".$LANG['stat_results_all_members']."</td><td class=\"stat-sum-value\"><b>".sprintf("%1.1f",$totaluser)." days</b></td></tr>\n\r";
                  echo "</table>\n\r";
                  echo "</td><td style=\"vertical-align: top; padding-left: 20px;\">";
                  $header = $LANG['stat_results_total_absence_user'].$periodFrom."-".$periodTo;
                  $footer = "";
                  echo $ST->barGraphH($legend,$value,$diagramwidth,$barareawidth,"red",$header,$footer);
                  echo "</td></tr></table>\n\r";
                  echo "</fieldset><br>\n\r";
                  ?>

                  <!-- TOTAL PRESENCE USER-->
                  <?php
                  $legend=array();
                  $value=array();
                  if ($statgroup=="%") $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;All)";
                  else $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;".$statgroup.")";
                  echo "<fieldset style=\"text-align: left; width: 96%;\"><legend>".$LANG['stat_results_total_presence_user'].$periodFrom."-".$periodTo.$forgroup."</legend>";
                  echo "<table><tr><td style=\"vertical-align: top;\">\n\r";
                  echo "<table>\n\r";
                  /**
                   * Get totals per user
                   */
                  $totaluser=0;
                  $groups = $G->getAllByGroup($statgroup);
                  foreach ($groups as $group) {
                     $G->findByName($group['groupname']);
                     if (!$G->checkOptions($CONF['G_HIDE']) ) {
                        $total=0;
                        $gusers = $UG->getAllforGroup($group['groupname']);
                        foreach ($gusers as $guser) {
                           $U1->findByName($guser);
                           if ( !$U1->checkUserType($CONF['UTTEMPLATE']) ) {
                              $total=0;
                              /*
                               * Count all non-absences
                               */
                              $count=countAbsence($guser,0,$periodFrom,$periodTo);
                              $total+=$count;
                              $totaluser+=$count;
                              /*
                               * Count all absences that count as present
                               */
                              $absences=$A->getAll();
                              foreach($absences as $abs) {
                                 if ($A->get($abs['id']) AND $A->counts_as_present) {
                                    $count=countAbsence($guser,$A->id,$periodFrom,$periodTo);
                                    $total+=$count;
                                    $totaluser+=$count;
                                 }
                              }
                              if ( strlen($U1->firstname)) $displayname = $U1->lastname.", ".$U1->firstname;
                              else                         $displayname = $U1->lastname;
                              $legend[] = $displayname;
                              $value[] = $total;
                              echo "<tr><td class=\"stat-caption\">".$displayname."</td><td class=\"stat-value\">".sprintf("%1.1f",$total)." days</td></tr>\n\r";
                           }
                        }
                     }
                  }
                  echo "<tr><td class=\"stat-sum-caption\">".$LANG['stat_results_all_members']."</td><td class=\"stat-sum-value\"><b>".sprintf("%1.1f",$totaluser)." days</b></td></tr>\n\r";
                  echo "</table>\n\r";
                  echo "</td><td style=\"vertical-align: top; padding-left: 20px;\">";
                  $header = $LANG['stat_results_total_presence_user'].$periodFrom."-".$periodTo;
                  $footer = "";
                  echo $ST->barGraphH($legend,$value,$diagramwidth,$barareawidth,"green",$header,$footer);
                  echo "</td></tr></table>\n\r";
                  echo "</fieldset><br>\n\r";
                  ?>

                  <!-- TOTAL ABSENCE GROUP -->
                  <?php
                  $legend=array();
                  $value=array();
                  if ($statgroup=="%") $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;All)";
                  else $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;".$statgroup.")";
                  if ($periodAbsence=="All") $forabsence = "&nbsp;(".$LANG['stat_absence'].":&nbsp;All)";
                  else $forabsence = "&nbsp;(".$LANG['stat_absence'].":&nbsp;".$periodAbsenceName.")";
                  echo "<fieldset style=\"text-align: left; width: 96%;\"><legend>".$LANG['stat_results_total_absence_group'].$periodFrom."-".$periodTo.$forgroup.$forabsence."</legend>";
                  echo "<table><tr><td style=\"vertical-align: top;\">\n\r";
                  echo "<table>\n\r";
                  /**
                   * Get totals per group
                   */
                  $totalgroup=0;
                  $groups = $G->getAllByGroup($statgroup);
                  foreach ($groups as $group) {
                     $G->findByName($group['groupname']);
                     if (!$G->checkOptions($CONF['G_HIDE']) ) {
                        $total=0;
                        $gusers = $UG->getAllforGroup($group['groupname']);
                        foreach ($gusers as $guser) {
                           $U1->findByName($guser);
                           if ( $periodAbsence=="All" ) {
                              $absences=$A->getAll();
                              foreach ($absences as $abs) {
                                 if ($A->get($abs['id']) AND !$A->counts_as_present) {
                                    $total+=countAbsence($guser,$A->id,$periodFrom,$periodTo);
                                 }
                              }
                           }
                           else {
                              if ($A->get($periodAbsence) AND !$A->counts_as_present) {
                                 $total+=countAbsence($guser,$A->id,$periodFrom,$periodTo);
                              }
                           }
                        }
                        $legend[] = $group['groupname'];
                        $value[] = $total;
                        $totalgroup += $total;
                        echo "<tr><td class=\"stat-caption\">".$LANG['stat_results_group'].$group['groupname']."</td><td class=\"stat-value\">".sprintf("%1.1f",$total)." days</td></tr>\n\r";
                     }
                  }

                  /**
                   * Get totals of all team members
                   */
                  $totaluser=0;
                  $absences = $A->getAll();
                  foreach ($absences as $abs) {
                     if ($A->get($abs['id']) AND !$A->counts_as_present) $totaluser+=countAbsence("%",$abs['id'],$periodFrom,$periodTo);
                  }
                  echo "<tr><td class=\"stat-sum-caption\">".$LANG['stat_results_all_groups']."</td><td class=\"stat-sum-value\"><b>".sprintf("%1.1f",$totalgroup)." days</b></td></tr>\n\r";
                  echo "<tr><td class=\"stat-sum-caption\">".$LANG['stat_results_all_members']."</td><td class=\"stat-sum-value\"><b>".sprintf("%1.1f",$totaluser)." days</b></td></tr>\n\r";

                  echo "</table>\n\r";
                  echo "</td><td style=\"vertical-align: top; padding-left: 20px;\">";
                  $header = $LANG['stat_results_total_absence_group'].$periodFrom."-".$periodTo;
                  $footer = "";
                  echo $ST->barGraphH($legend,$value,$diagramwidth,$barareawidth,"red",$header,$footer);
                  echo "</td></tr></table>\n\r";
                  echo "</fieldset><br>\n\r";
                  ?>

                  <!-- TOTAL PRESENCE GROUP -->
                  <?php
                  if ($statgroup=="%") $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;All)";
                  else $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;".$statgroup.")";
                  echo "<fieldset style=\"text-align: left; width: 96%;\"><legend>".$LANG['stat_results_total_presence_group'].$periodFrom."-".$periodTo.$forgroup."</legend>";
                  echo "<table><tr><td style=\"vertical-align: top;\">\n\r";
                  echo "<table>\n\r";
                  /**
                   * Get totals per group
                   */
                  $totalgroup=0;
                  $legend=array();
                  $value=array();
                  $groups = $G->getAllByGroup($statgroup);
                  foreach ($groups as $group) {
                     $G->findByName($group['groupname']);
                     if (!$G->checkOptions($CONF['G_HIDE']) ) {
                        $total=0;
                        $gusers = $UG->getAllforGroup($group['groupname']);
                        foreach ($gusers as $guser) {
                           /*
                            * Count all non-absences
                            */
                           $count=countAbsence($guser,0,$periodFrom,$periodTo);
                           $total+=$count;
                           $totaluser+=$count;
                           /*
                            * Count all absences that count as present
                            */
                           $absences=$A->getAll();
                           foreach($absences as $abs) {
                              if ($A->get($abs['id']) AND $A->counts_as_present) {
                                 $total+=countAbsence($guser,$A->id,$periodFrom,$periodTo);
                              }
                           }
                        }
                        $legend[] = $group['groupname'];
                        $value[] = $total;
                        $totalgroup += $total;
                        echo "<tr><td class=\"stat-caption\">".$LANG['stat_results_group'].$group['groupname']."</td><td class=\"stat-value\">".sprintf("%1.1f",$total)." days</td></tr>\n\r";
                     }
                  }

                  /**
                   * Get totals of all team members
                   */
                  $totaluser=0;
                  $totaluser+=countAbsence('%',0,$periodFrom,$periodTo);
                  $absences = $A->getAll();
                  foreach ($absences as $absA) {
                     if ($A->get($abs['id']) AND $A->counts_as_present) $totaluser+=countAbsence('%',$abs['id'],$periodFrom,$periodTo);
                  }
                  echo "<tr><td class=\"stat-sum-caption\">".$LANG['stat_results_all_groups']."</td><td class=\"stat-sum-value\"><b>".sprintf("%1.1f",$totalgroup)." days</b></td></tr>\n\r";
                  echo "<tr><td class=\"stat-sum-caption\">".$LANG['stat_results_all_members']."</td><td class=\"stat-sum-value\"><b>".sprintf("%1.1f",$totaluser)." days</b></td></tr>\n\r";

                  echo "</table>\n\r";
                  echo "</td><td style=\"vertical-align: top; padding-left: 20px;\">";
                  $header = $LANG['stat_results_total_presence_group'].$periodFrom."-".$periodTo;
                  $footer = "";
                  echo $ST->barGraphH($legend,$value,$diagramwidth,$barareawidth,"green",$header,$footer);
                  echo "</td></tr></table>\n\r";
                  echo "</fieldset><br>\n\r";
                  ?>

                  <!-- TOTAL ABSENCE BY TYPE -->
                  <?php
                  if ($statgroup=="%") $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;All)";
                  else $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;".$statgroup.")";
                  echo "<fieldset style=\"text-align: left; width: 96%;\"><legend>".$LANG['stat_results_total_per_type'].$periodFrom."-".$periodTo.$forgroup."</legend>";
                  echo "<table><tr><td style=\"vertical-align: top;\">\n\r";
                  echo "<table>\n\r";
                  /**
                   * Get totals per absence type
                   */
                  $legend=array();
                  $value=array();
                  $sum=0;
                  $absences = $A->getAll();
                  foreach ($absences as $abs) {
                     if (!$abs['counts_as_present']) {
                        $total=0;
                        $groups = $G->getAllByGroup($statgroup);
                        foreach ($groups as $group) {
                           $G->findByName($group['groupname']);
                           if (!$G->checkOptions($CONF['G_HIDE']) ) {
                              $gusers = $UG->getAllforGroup($group['groupname']);
                              foreach ($gusers as $guser) {
                                 $total+=countAbsence($guser,$abs['id'],$periodFrom,$periodTo);
                              }
                           }
                        }
                        $sum+=$total;
                        $legend[] = $abs['name'];
                        $value[] = $total;
                        echo "<tr><td class=\"stat-caption\">".$abs['name']."</td><td class=\"stat-value\">".sprintf("%1.1f",$total)." days</td></tr>\n\r";
                     }
                  }
                  echo "<tr><td class=\"stat-sum-caption\">".$LANG['stat_results_all_members']."</td><td class=\"stat-sum-value\"><b>".sprintf("%1.1f",$sum)." days</b></td></tr>\n\r";
                  echo "</table>\n\r";
                  echo "</td><td style=\"vertical-align: top; padding-left: 20px;\">";
                  $header = $LANG['stat_results_total_per_type'].$periodFrom."-".$periodTo;
                  $footer = "";
                  echo $ST->barGraphH($legend,$value,$diagramwidth,$barareawidth,"orange",$header,$footer);
                  echo "</td></tr></table>\n\r";
                  echo "</fieldset><br>\n\r";
                  ?>

                  <!-- TOTAL REMAINDER BY TYPE -->
                  <?php
                  if ($statgroup=="%") $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;All)";
                  else $forgroup = "&nbsp;(".$LANG['stat_group'].":&nbsp;".$statgroup.")";
                  echo "<fieldset style=\"text-align: left; width: 96%;\"><legend>".$LANG['stat_results_remainders'].$yeartoday.$forgroup."</legend>";
                  echo "<table><tr><td style=\"vertical-align: top;\">\n\r";
                  echo "<table>\n\r";
                  /**
                   * Get total remainders per absence type for current year
                   */
                  $legend=array();
                  $value=array();
                  $sum=0;
                  $absences = $A->getAll();
                  foreach ($absences as $abs) {
                     if ($A->get($abs['id']) AND !$A->counts_as_present AND $A->allowance AND $A->factor) {
                        $total=0;

                        $groups = $G->getAllByGroup($statgroup);
                        foreach ($groups as $group) {
                           $G->findByName($group['groupname']);
                           if (!$G->checkOptions($CONF['G_HIDE']) ) {
                              $gusers = $UG->getAllforGroup($group['groupname']);
                              foreach ($gusers as $guser) {
                                 if ( $B->find($guser,$A->id) ) {
                                    $lstyr = $B->lastyear;
                                    $allow = $B->curryear;
                                 }else{
                                    $lstyr = 0;
                                    $allow = $A->allowance;
                                 }
                                 $periodFrom = $yeartoday."0101";
                                 $periodTo = $yeartoday."1231";
                                 $taken=countAbsence($guser,$A->id,$periodFrom,$periodTo);
                                 $total += ($lstyr+$allow)-($taken);
                                 $sum += $total;
                              }
                           }
                        }
                        $legend[] = $abs['name'];
                        $value[] = $total;
                        echo "<tr><td class=\"stat-caption\">".$abs['name']."</td><td class=\"stat-value\">".sprintf("%1.1f",$total)." days</td></tr>\n\r";
                     }
                  }
                  echo "<tr><td class=\"stat-sum-caption\">".$LANG['stat_results_all_members']."</td><td class=\"stat-sum-value\"><b>".sprintf("%1.1f",$sum)." days</b></td></tr>\n\r";
                  $fromtoday=$yeartoday.$monthtoday.$daytoday;
                  $remainBusi=countBusinessDays($fromtoday,$periodTo);
                  echo "<tr><td class=\"stat-sum-caption\">Remaining Business Days</td><td class=\"stat-sum-value\"><b>".sprintf("%1.1f",$remainBusi)." days</b></td></tr>\n\r";
                  $remainBusi=countBusinessDays($fromtoday,$periodTo,1);
                  echo "<tr><td class=\"stat-sum-caption\">Remaining Man Days</td><td class=\"stat-sum-value\"><b>".sprintf("%1.1f",$remainBusi)." days</b></td></tr>\n\r";
                  echo "</table>\n\r";
                  echo "</td><td style=\"vertical-align: top; padding-left: 20px;\">";
                  $header = $LANG['stat_results_remainders'].$yeartoday;
                  $footer = "";
                  echo $ST->barGraphH($legend,$value,$diagramwidth,$barareawidth,"cyan",$header,$footer);
                  echo "</td></tr></table>\n\r";
                  echo "</fieldset><br>\n\r";
                  ?>

               </div>
            </td>
         </tr>
      </table>
   </div>
</div>
<?php require( "includes/footer.html.inc.php" ); ?>