<?php
/**
 * regions.php
 *
 * Displays the regions administration page
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

require_once("models/absence_model.php");
require_once("models/config_model.php");
require_once("models/holiday_model.php" );
require_once("models/log_model.php" );
require_once("models/login_model.php" );
require_once("models/month_model.php" );
require_once("models/region_model.php" );
require_once("includes/tcuser.class.php" );
require_once("includes/tcuseroption.class.php" );

$A = new Absence_model;
$C = new Config_model;
$H = new Holiday_model;
$L = new Login_model;
$LOG = new Log_model;
$M = new Month_model;
$M2 = new Month_model;
$R  = new Region_model;
$R2  = new Region_model;
$U  = new tcUser;
$UO  = new tcUserOption;

$error = false;

/**
 * Check if allowed
 */
if (!isAllowed("editRegions")) showError("notallowed");

/**
 * Get current month and year
 */
$today     = getdate();
$monthtoday = $CONF['monthnames'][$today['mon']];   // Numeric representation of todays' month
$yeartoday  = $today['year'];  // A full numeric representation of todays' year, 4 digits

/**
 * =========================================================================
 * ADD
 */
if ( isset($_POST['btn_reg_add']) ) {
   if (!preg_match('/^[a-zA-Z0-9-]*$/', $_POST['reg_nameadd'])) {
      /**
       * The region name is invalid
       */
      $error = true;
      $err_short = $LANG['err_input_caption'];
      $err_long  = $LANG['err_input_reg_invalid_1'];
      $err_long .= trim($_POST['reg_nameadd']);
      $err_long .= $LANG['err_input_reg_invalid_2'];
      $err_module=$_SERVER['SCRIPT_NAME'];
      $err_btn_close=FALSE;
   }
   else  if (strlen($rname=trim($_POST['reg_nameadd']))) {
      if ($R->findByName($rname)) {
         /**
          * The region already exists
          */
         $error = true;
         $err_short = $LANG['err_input_caption'];
         $err_long  = $LANG['err_input_region_exists'];
         $err_module=$_SERVER['SCRIPT_NAME'];
         $err_btn_close=FALSE;
      }
      else {
         /**
          * Create the new region
          */
         $R->regionname=$rname;
         $R->description=htmlspecialchars($_POST['reg_descadd'],ENT_QUOTES);
         $R->options=0x000000;
         if (isset($_POST['chkHide'])) $R->setOptions($CONF['R_HIDE']);
         $R->create();
         /**
          * Log this event
          */
         $LOG->log("logRegion",$L->checkLogin(),"Region added: ".$R->regionname);
      }
   }
   else {
      /**
       * No shortname was submitted
       */
      $error = true;
      $err_short = $LANG['err_input_caption'];
      $err_long = $LANG['err_input_reg_add'];
      $err_module=$_SERVER['SCRIPT_NAME'];
      $err_btn_close=FALSE;
   }
}
/**
 * =========================================================================
 * IMPORT
 */
else if ( isset($_POST['btn_import_ical']) ) {

   if (trim($_POST['icalreg_nameadd'])=='') {
      /**
       * No shortname was submitted
       */
      $error = true;
      $err_short = $LANG['err_input_caption'];
      $err_long  = $LANG['err_input_region_add'];
      $err_module=$_SERVER['SCRIPT_NAME'];
      $err_btn_close=FALSE;
   }
   else if ( trim($_FILES['ical_file']['tmp_name'])=='' ) {
      /**
       * No filename was submitted
       */
      $error = true;
      $err_short = $LANG['err_input_caption'];
      $err_long  = $LANG['err_input_no_filename'];
      $err_module=$_SERVER['SCRIPT_NAME'];
      $err_btn_close=FALSE;
   }
   else {
      $rname = preg_replace("/[^A-Za-z0-9_]/i",'',trim($_POST['icalreg_nameadd']));
      if ($R->findByName($rname)) {
         /**
          * The region already exists
          */
         $error = true;
         $err_short = $LANG['err_input_caption'];
         $err_long  = $LANG['err_input_region_exists'];
         $err_module=$_SERVER['SCRIPT_NAME'];
         $err_btn_close=FALSE;
      }
      else {
         /**
          * Let's create the new region first
          */
         $R->regionname=$rname;
         $R->description=htmlspecialchars($_POST['icalreg_descadd'],ENT_QUOTES);
         $R->options=0x000000;
         if (isset($_POST['icalchkHide'])) $R->setOptions($CONF['R_HIDE']);
         $R->create();
         /**
          * Log this event
          */
         $LOG->log("logRegion",$L->checkLogin(),"Region added: ".$R->regionname);

         /**
          * Now parse the iCal file (original code by Franz)
          */
         $begin_of_ical = 999999999999999999999999999999;
         $end_of_ical = 0;
         $iCalEvents = array();
         preg_match_all("#(?sU)BEGIN:VEVENT.*END:VEVENT#", file_get_contents($_FILES['ical_file']['tmp_name']), $events);
         foreach($events[0] as $event) {
            preg_match("#(?sU)DTSTART;.*DATE:([0-9]{8})#", $event, $start);
            preg_match("#(?sU)DTEND;.*DATE:([0-9]{8})#", $event, $end);
            $start = mktime (0,0,0, substr($start[1],4,2), substr($start[1],6,2), substr($start[1],0,4));
            $end = mktime (0,0,0, substr($end[1],4,2), substr($end[1],6,2), substr($end[1],0,4));
            /**
             * Catch the earliest and latest event date of the iCal file
             */
            if ($begin_of_ical > $start) $begin_of_ical = $start;
            if ($end_of_ical < $end) $end_of_ical = $end;
            /**
             * Save this event to the array
             */
            $iCalEvents[$start] = $end;
         };

         /**
          * Ok, now we have the new region created and all events in our array.
          * Let's loop through all events an do this for each:
          * - create a region month template for the event start and event end if not exists
          * - add the absence symbol(s) for this event to the month template(s)
          * - save the template(s)
          */
         $current_event = $begin_of_ical;
         $M->yearmonth = 0;
         while ($current_event < $end_of_ical) {
            $current_year = date("Y", $current_event);
            $current_month = date("M", $current_event);
            $current_yearmonth = date("Ym", $current_event);

            if ($M->yearmonth != $current_yearmonth) {
               /**
                * We don't have the month template we want. Two possible reasons:
                * - we haven't loaded one
                * - we stepped over a month border with our current_event
                *
                * Let's check with a second M2 instance if there is a template for this month yet.
                * We need the second instance cause findByName() would overwrite an M instance that we might still
                * have in memory and not saved yet.
                */
               if ( !$M2->findByName($R->regionname, $current_yearmonth) ) {
                  /**
                   * Seems there is no template for this month yet.
                   * If we have one in cache, write it first.
                   */
                  if ( $M->yearmonth ) {
                     $M->update($R->regionname, $M->yearmonth);
                  }
                  /**
                   * Create the new blank template
                   */
                  $M->region = $R->regionname;
                  $M->yearmonth = $current_yearmonth;
                  $M->template = createMonthTemplate((string)$current_year,(string)$current_month);
                  $M->create();
               }
               else {
                  /**
                   * There is a template for this month.
                   * Let's save the current and load the new.
                   */
                  $M->update($R->regionname, $M->yearmonth);
                  $M->findByName($R->regionname, $current_yearmonth);
               }
            }

            /**
             * Put the user-selected absence type in the month template for the current event date.
             */
            $dayno = date("j", $current_event);
            $start_of_iCal_period = min(array_keys($iCalEvents)); // Select start of earliest iCal period
            $end_of_iCal_period = $iCalEvents[$start_of_iCal_period]; // Select end of earliest iCal period
            if ($start_of_iCal_period <= $current_event) {
               if ($end_of_iCal_period >= $current_event) {
                  /**
                   * We are currently inbetween begin and end of an iCal period
                   */
                  if (substr($M->template, $dayno-1, 1) != 1) {
                     /**
                      * This is a business day. Only change the holiday type in this case.
                      */
                     $M->template[$dayno-1] = $_POST['icalHol'];
                  }
               } else {
                  /**
                   * We are done with this event period! Remove this period from the iCalEvents array.
                   * That makes the next one the earliest.
                   */
                  unset($iCalEvents[$start_of_iCal_period]);
               }
            }
            $current_event = strtotime("+1 day", $current_event);
         }
         /**
          * Ok, lets save the last month
          */
         $M->update($R->regionname, $M->yearmonth);
         /**
          * Log this event
          */
         $LOG->log("logRegion",$L->checkLogin(),"iCal file ".$_FILES['ical_file']['name']." imported into region: ".$R->regionname);
      }
   }
}
/**
 * =========================================================================
 * UPDATE
 */
else if ( isset($_POST['btn_reg_update']) ) {
   //
   // Update the region record
   //
   $R->regionname=preg_replace("/[^a-z0-9]/i",'',$_POST['reg_name']);
   $R->description=htmlspecialchars($_POST['reg_desc'],ENT_QUOTES);
   $R->options=0x000000;
   if (isset($_POST['chkHide'])) $R->setOptions($CONF['R_HIDE']);
   $R->update($_POST['reg_namehidden']);
   //
   // Update the config defregion record
   // Update all month templates for this region
   // Update all user option records that had this region as defregion
   //
   $C->updateRegion($_POST['reg_namehidden'], $R->regionname);
   $M->updateRegion($_POST['reg_namehidden'], $R->regionname);
   $UO->updateRegion($_POST['reg_namehidden'], $R->regionname);
   /**
    * Log this event
    */
   $LOG->log("logRegion",$L->checkLogin(),"Region updated: ".$R->regionname);
}
/**
 * =========================================================================
 * DELETE
 */
else if ( isset($_POST['btn_reg_delete']) ) {
   //
   // Delete the region record
   // Delete all month templates for this region
   // Update the config defregion back to default
   // Update all user option records back to default
   //
   $R->deleteByName($_POST['reg_namehidden']);
   $M->deleteRegion($_POST['reg_namehidden']);
   $C->updateRegion($_POST['reg_namehidden'], 'default');
   $UO->updateRegion($_POST['reg_namehidden'], 'default');
   /**
    * Log this event
    */
   $LOG->log("logRegion",$L->checkLogin(),"Region deleted: ".$_POST['reg_namehidden']);
}
/**
 * =========================================================================
 * MERGE
 */
else if ( isset($_POST['btn_reg_merge']) ) {

   $R->regionname=$_POST['sRegion'];
   $R2->regionname=$_POST['tRegion'];

   if ($R->regionname==$R2->regionname) {
      /**
       * Same source and target region
       */
      $error = true;
      $err_short = $LANG['err_input_caption'];
      $err_long  = $LANG['err_input_same_region'];
      $err_module=$_SERVER['SCRIPT_NAME'];
      $err_btn_close=FALSE;
   }
   else {
      /**
       * Loop through every month of the source region
       */
      $result = $M->db->db_query("SELECT * FROM `".$M->table."` WHERE `region` = '".$R->regionname."' ORDER BY `yearmonth`;");
      while ( $row = $M->db->db_fetch_array($result,MYSQL_ASSOC) ) {
         /**
          * Try to find the same month in the target region
          */
         print("Source month ".$R->regionname."-".$row['yearmonth']."<br>");
         if ($M2->findByName($R2->regionname, $row['yearmonth'])) {
            /**
             * Now copy each event from source to target but check the overwrite flag
             */
            for ($i = 0; $i<strlen($row['template']); $i++) {
                if ($M2->template[$i] == 0 OR $M2->template[$i] == 1) {
                  /**
                   * Nothing in the target template yet. Just overwrite with source.
                   */
                   $M2->template[$i] = $row['template'][$i];
                }
                else {
                  /**
                   * Absence in the target template. Only overwrite if user wanted it.
                   */
                   if (isset($_POST['chkOverwrite'])) $M2->template[$i] = $row['template'][$i];
                }
            }
            /**
             * And save the template
             */
            $M2->update($R2->regionname, $row['yearmonth']);
         }
      }
      /**
       * Log this event
       */
      $LOG->log("logRegion",$L->checkLogin(),"Regions merged: ".$_POST['sRegion']." => ".$_POST['tRegion']);
   }
}
require("includes/header.html.inc.php");
echo "<body>\r\n";
echo "<div id=\"overDiv\" style=\"position:absolute; visibility:hidden; z-index:1000;\"></div>\r\n";
require("includes/header.application.inc.php");
require("includes/menu.inc.php");
?>
<div id="content">
   <div id="content-content">
      <!--  REGIONS =========================================================== -->
      <table class="dlg">
            <tr>
            <td class="dlg-header" colspan="3">
               <?php printDialogTop($LANG['admin_region_title'],"manage_regions.html","ico_region.png"); ?>
            </td>
         </tr>
         <tr>
            <td>
               <!-- Add region -->
               <form class="form" name="form-region-add" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
                  <table style="border-collapse: collapse; border: 0px; width: 100%;">
                     <tr>
                        <td class="dlg-caption" colspan="5"><strong><?=$LANG['region_caption_add']?></strong></td>
                     </tr>
                     <tr>
                        <td class="dlg-caption-gray" colspan="5">
                           <table style="border-collapse: collapse; border: 0px; width: 100%;">
                              <tr>
                                 <td width="5%" style="text-align: left;">&nbsp;</td>
                                 <td width="20%" style="text-align: left;"><?=$LANG['column_shortname']?></td>
                                 <td width="30%" style="text-align: left;"><?=$LANG['column_description']?></td>
                                 <td width="10%" style="text-align: left;"><?=$LANG['column_hide']?></td>
                                 <td width="35%" style="text-align: left;"><?=$LANG['column_action']?></td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                     <tr>
                        <td class="dlg-row1" width="5%"><img src="themes/<?=$theme?>/img/ico_add.png" alt="Region" title="<?=$LANG['tt_add_region']?>" align="middle" style="padding-right: 2px;"></td>
                        <td class="dlg-row1" width="20%"><input name="reg_nameadd" size="16" type="text" class="text" id="reg_nameadd" value=""></td>
                        <td class="dlg-row1" width="30%"><input name="reg_descadd" size="34" type="text" class="text" id="reg_descadd" value=""></td>
                        <td class="dlg-row1" width="10%"><input name="chkHide" type="checkbox" value="chkHide"></td>
                        <td class="dlg-row1" width="35%"><input name="btn_reg_add" type="submit" class="button" value="<?=$LANG['btn_add']?>"></td>
                     </tr>
                  </table>
               </form>

               <!-- Add iCal as new region -->
               <form class="form" name="form-ical-add" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>" enctype="multipart/form-data">
                  <table style="border-collapse: collapse; border: 0px; width: 100%;">
                     <tr>
                        <td class="dlg-row2" width="5%"><img src="themes/<?=$theme?>/img/ico_calendar.png" alt="Region" title="<?=$LANG['tt_add_ical']?>" align="middle" style="padding-right: 2px;"></td>
                        <td class="dlg-row2" width="20%"><input name="icalreg_nameadd" size="16" type="text" class="text" id="icalreg_nameadd" value=""></td>
                        <td class="dlg-row2" width="30%"><input name="icalreg_descadd" size="34" type="text" class="text" id="icalreg_descadd" value=""></td>
                        <td class="dlg-row2" width="10%"><input name="icalchkHide" type="checkbox" value="icalchkHide"></td>
                        <td class="dlg-row2" width="35%">
                           <input type="file" class="button" accept="text/calendar" name="ical_file">
                           <select name="icalHol" id="icalHol" class="select">
                           <?php
                           $result = $H->db->db_query("SELECT * FROM `".$H->table."` ORDER BY `dspname`;");
                           while ( $row = $H->db->db_fetch_array($result,MYSQL_ASSOC) ){
                              $H->findBySymbol($row['cfgsym']);
                              if ( $row['cfgname']!="wend" AND $row['cfgname']!="busi" ) {
                                 echo "                                 <option class=\"option\" value=\"".$row['cfgsym']."\">".$row['dspname']."</option>\n";
                              }
                           }
                           ?>
                           </select>
                           <input type="submit" class="button" value="<?=$LANG['btn_import_ical']?>" name="btn_import_ical">
                           <p><?=$LANG['region_ical_description']?></p>
                         </td>
                     </tr>
                  </table>
               </form>

               <?php
               $query  = "SELECT `regionname` FROM `".$R->table."` ORDER BY `regionname`;";
               $result = $R->db->db_query($query);
               $i=1;
               $printrow=1;
               echo "
               <!-- default -->
               <form class=\"form\" name=\"form-grp-".$i."\" method=\"POST\" action=\"".$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']."\">
                  <table style=\"border-collapse: collapse; border: 0px; width: 100%;\">
                     <tr>
                        <td class=\"dlg-caption\" colspan=\"5\"><strong>".$LANG['region_caption_existing']."</strong></td>
                     </tr>
                     <tr>
                        <td class=\"dlg-caption-gray\" colspan=\"5\">
                           <table style=\"border-collapse: collapse; border: 0px; width: 100%;\">
                              <tr>
                                 <td width=\"5%\" style=\"text-align: left;\">&nbsp;</td>
                                 <td width=\"20%\" style=\"text-align: left;\">".$LANG['column_shortname']."</td>
                                 <td width=\"30%\" style=\"text-align: left;\">".$LANG['column_description']."</td>
                                 <td width=\"10%\" style=\"text-align: left;\">".$LANG['column_hide']."</td>
                                 <td width=\"35%\" style=\"text-align: left;\">".$LANG['column_action']."</td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                     <tr>
                        <td class=\"dlg-row".$printrow."\" width=\"5%\"><img src=\"themes/".$theme."/img/ico_region.png\" alt=\"Region\" title=\"".$LANG['tt_region']."\" align=\"middle\" style=\"padding-right: 2px;\"></td>
                        <td class=\"dlg-row".$printrow."\" width=\"20%\"><input name=\"reg_namehidden\" type=\"hidden\" class=\"text\" value=\"default\"><input name=\"reg_name\" size=\"16\" type=\"text\" class=\"text\" value=\"default\" DISABLED></td>
                        <td class=\"dlg-row".$printrow."\" width=\"30%\"><input name=\"reg_desc\" size=\"34\" type=\"text\" class=\"text\" value=\"Default Region\" DISABLED></td>
                        <td class=\"dlg-row".$printrow."\" width=\"10%\">&nbsp;</td>
                        <td class=\"dlg-row".$printrow."\" width=\"35%\">
                           <input name=\"btn_reg_edit\" type=\"submit\" class=\"button\" value=\"".$LANG['btn_edit']."\" onclick=\"javascript:openPopup('editmonth.php?lang=".$CONF['options']['lang']."&amp;region=default&amp;Year=".$yeartoday."&amp;Month=".$monthtoday."','shop','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=no,dependent=1,width=960,height=300');\" >
                        </td>
                     </tr>
                  </table>
               </form>
               ";

               while ( $row = $R->db->db_fetch_array($result,MYSQL_ASSOC) ){
                  $R->findByName(stripslashes($row['regionname']));
                  if ($R->regionname!="default") {
                     if ($printrow==1) $printrow=2; else $printrow=1;
                     echo "
                     <!-- ".$R->regionname." -->
                     <form class=\"form\" name=\"form-grp-".$i."\" method=\"POST\" action=\"".$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']."\">
                        <table style=\"border-collapse: collapse; border: 0px; width: 100%;\">
                           <tr>
                              <td class=\"dlg-row".$printrow."\" width=\"5%\"><img src=\"themes/".$theme."/img/ico_region.png\" alt=\"Region\" title=\"".$LANG['tt_region']."\" align=\"middle\" style=\"padding-right: 2px;\"></td>
                              <td class=\"dlg-row".$printrow."\" width=\"20%\"><input name=\"reg_namehidden\" type=\"hidden\" class=\"text\" value=\"".$R->regionname."\"><input name=\"reg_name\" size=\"16\" type=\"text\" class=\"text\" value=\"".$R->regionname."\"></td>
                              <td class=\"dlg-row".$printrow."\" width=\"30%\"><input name=\"reg_desc\" size=\"34\" type=\"text\" class=\"text\" value=\"".$R->description."\"></td>
                              <td class=\"dlg-row".$printrow."\" width=\"10%\"><input name=\"chkHide\" type=\"checkbox\" value=\"chkHide\" ".($R->checkOptions($CONF['R_HIDE'])?'CHECKED':'')."></td>
                              <td class=\"dlg-row".$printrow."\" width=\"35%\">
                                 <input name=\"btn_reg_update\" type=\"submit\" class=\"button\" value=\"".$LANG['btn_update']."\">&nbsp;
                                 <input name=\"btn_reg_delete\" type=\"submit\" class=\"button\" value=\"".$LANG['btn_delete']."\" onclick=\"return confirmSubmit('".$LANG['reg_delete_confirm'].": ".$R->regionname."')\" >&nbsp;
                                 <input name=\"btn_reg_edit\" type=\"submit\" class=\"button\" value=\"".$LANG['btn_edit']."\" onclick=\"javascript:openPopup('editmonth.php?lang=".$CONF['options']['lang']."&amp;region=".$R->regionname."&amp;Year=".$yeartoday."&amp;Month=".$monthtoday."','shop','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=no,dependent=1,width=960,height=300');\" >
                              </td>
                           </tr>
                        </table>
                     </form>
                     ";
                     $i+=1;
                  }
               }
               ?>

               <!-- Merge regions -->
               <form class="form" name="form-region-merge" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
                  <table style="border-collapse: collapse; border: 0px; width: 100%;">
                     <tr>
                        <td class="dlg-caption" colspan="5">
                           <strong><?=$LANG['region_caption_merge']?></strong>
                        </td>
                     </tr>
                     <tr>
                        <td class="dlg-caption-gray" colspan="5">
                           <table style="border-collapse: collapse; border: 0px; width: 100%;">
                              <tr>
                                 <td width="5%" style="text-align: left;">&nbsp;</td>
                                 <td width="25%" style="text-align: left;"><?=$LANG['column_source_region']?></td>
                                 <td width="25%" style="text-align: left;"><?=$LANG['column_target_region']?></td>
                                 <td width="10%" style="text-align: left;"><?=$LANG['column_overwrite']?></td>
                                 <td width="35%" style="text-align: left;"><?=$LANG['column_action']?></td>
                              </tr>
                           </table>
                        </td>
                     </tr>
                     <tr>
                        <td class="dlg-row2" width="5%"><img src="themes/<?=$theme?>/img/ico_region.png" alt="Region" title="<?=$LANG['tt_add_region']?>" align="middle"><img src="themes/<?=$theme?>/img/ico_region.png" alt="Region" title="<?=$LANG['tt_add_region']?>" align="middle" style="padding-right: 2px;"></td>
                        <td class="dlg-row2" width="25%">
                           <select name="sRegion" id="sRegion" class="select">
                           <?php
                           $result = $R->db->db_query("SELECT * FROM `".$R->table."` ORDER BY `regionname`;");
                           while ( $row = $R->db->db_fetch_array($result,MYSQL_ASSOC) ) {
                              echo "<option class=\"option\" value=\"".$row['regionname']."\">".$row['regionname']."</option>\n";
                           }
                           ?>
                           </select>
                        </td>
                        <td class="dlg-row2" width="25%">
                           <select name="tRegion" id="tRegion" class="select">
                           <?php
                           $result = $R2->db->db_query("SELECT * FROM `".$R2->table."` ORDER BY `regionname`;");
                           while ( $row = $R2->db->db_fetch_array($result,MYSQL_ASSOC) ) {
                              echo "<option class=\"option\" value=\"".$row['regionname']."\">".$row['regionname']."</option>\n";
                           }
                           ?>
                           </select>
                        </td>
                        <td class="dlg-row2" width="10%"><input name="chkOverwrite" type="checkbox" value="chkOverwrite"></td>
                        <td class="dlg-row2" width="35%"><input name="btn_reg_merge" type="submit" class="button" value="<?=$LANG['btn_merge']?>"></td>
                     </tr>
                  </table>
               </form>
            </td>
         </tr>
      </table>
   </div>
</div>
<?php require("includes/footer.html.inc.php"); ?>