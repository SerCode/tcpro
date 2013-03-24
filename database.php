<?php
/**
 * database.php
 *
 * Displays the database maintenance page
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
require_once( "models/absence_group_model.php" );
require_once( "models/allowance_model.php" );
require_once( "includes/tcannouncement.class.php" );
require_once( "includes/tcavatar.class.php" );
require_once( "includes/tcconfig.class.php");
require_once( "includes/tcdaynote.class.php" );
require_once( "includes/tcgroup.class.php" );
require_once( "includes/tcholiday.class.php" );
require_once( "includes/tclogin.class.php" );
require_once( "includes/tclog.class.php" );
require_once( "includes/tcmonth.class.php" );
require_once( "includes/tcpermission.class.php" );
require_once( "includes/tcregion.class.php" );
require_once( "includes/tcstyles.class.php" );
require_once( "includes/tctemplate.class.php" );
require_once( "includes/tcuser.class.php" );
require_once( "includes/tcusergroup.class.php" );
require_once( "includes/tcuseroption.class.php" );

$A  = new Absence_model;
$AG = new Absence_group_model;
$AN = new tcAnnouncement;
$AV = new tcAvatar;
$B  = new Allowance_model;
$C  = new tcConfig;
$G  = new tcGroup;
$H  = new tcHoliday;
$L  = new tcLogin;
$LOG = new tcLog;
$M  = new tcMonth;
$N  = new tcDaynote;
$P  = new tcPermission;
$R  = new tcRegion;
$S  = new tcStyles;
$T  = new tcTemplate;
$U  = new tcUser;
$UG = new tcUserGroup;
$UO = new tcUserOption;

$error=FALSE;

/**
 * Check if allowed
 */
if (!isAllowed("manageDatabase")) showError("notallowed");

$monthnames = $CONF['monthnames'];
$today = getdate();
$curryear = $today['year']; // numeric value, 4 digits
$currmonth = $today['mon']; // numeric value

$maxsize = "1000000";
$message = array (
   'show'    => false,
   'success' => false,
   'text'    => ""
);
/**
 * =========================================================================
 * CLEANUP
 */
if ( isset($_POST['btn_dbmaint_clean']) ) {

   /**
    * Clean old templates older than year.month ...
    */
   if ( strlen($_POST['clean_year']) && strlen($_POST['clean_month']) ) {
      if ( $_POST['cleanup_confirm']=="CLEANUP" ) {
         if ( $_POST['chkDBCleanupUsers'] ) {
            /**
             * Delete Templates
             */
            $query  = "DELETE FROM `".$T->table."` WHERE " .
                      "`year`<".intval($_POST['clean_year'])." OR " .
                      "(`year`=".intval($_POST['clean_year'])." AND `month`<=".intval($_POST['clean_month']).")";
            $result = $T->db->db_query($query);
            /**
             * Delete Daynotes
             */
            $keydate=intval($_POST['clean_year'].$_POST['clean_month']."31");
            $query  = "DELETE FROM `".$N->table."` WHERE `yyyymmdd`<=".$keydate." AND `username`<>'all'";
            $result = $N->db->db_query($query);
         }
         if ( $_POST['chkDBCleanupMonths'] ) {
            /**
             * Delete Month Templates
             */
            $keydate=intval($_POST['clean_year'].$_POST['clean_month']);
            $query  = "DELETE FROM `".$M->table."` WHERE `yearmonth`<=".$keydate;
            $result = $M->db->db_query($query);
            /**
             * Delete Daynotes
             */
            $keydate=intval($_POST['clean_year'].$_POST['clean_month']."31");
            $query  = "DELETE FROM `".$N->table."` WHERE `yyyymmdd`<=".$keydate;
            $result = $N->db->db_query($query);
         }
         if ( $_POST['chkDBOptimize'] ) {
            /**
             * Optimize tables
             */
            $A->optimize();
            $AG->optimize();
            $AN->optimize();
            $B->optimize();
            $C->optimize();
            $G->optimize();
            $H->optimize();
            $LOG->optimize();
            $M->optimize();
            $N->optimize();
            $P->optimize();
            $R->optimize();
            $S->optimize();
            $T->optimize();
            $U->optimize();
            $UG->optimize();
            $UO->optimize();
         }
         /**
          * Log this event
          */
         $LOG->log("logDatabase",$L->checkLogin(),"Database cleanup before: ".$_POST['clean_year'].$_POST['clean_month']);
      } else {
         $error=TRUE;
         $err_short = $LANG['err_input_caption'];
         $err_long = $LANG['err_input_dbmaint_clean_confirm'];
         $err_module=$_SERVER['SCRIPT_NAME'];
         $err_btn_close=FALSE;
      }
   } else {
      $error=TRUE;
      $err_short = $LANG['err_input_caption'];
      $err_long = $LANG['err_input_dbmaint_clean'];
      $err_module=$_SERVER['SCRIPT_NAME'];
      $err_btn_close=FALSE;
   }
}
/**
 * =========================================================================
 * DELETE RECORDS
 */
else if ( isset($_POST['btn_dbmaint_del']) ) {

   if ( $_POST['del_confirm']=="DELETE" ) {

      if ( isset($_POST['chkDBDeleteUsers']) ) {
         $query  = "DELETE FROM `".$U->table."` WHERE `username`<> 'admin'";
         $U->db->db_query($query);
         $query  = "DELETE FROM `".$UO->table."` WHERE `username`<> 'admin'";
         $UO->db->db_query($query);
         $query  = "DELETE FROM `".$N->table."` WHERE `username`!='all'";
         $N->db->db_query($query);
         $query  = "TRUNCATE TABLE `".$T->table."`";
         $T->db->db_query($query);
         $query  = "TRUNCATE TABLE `".$B->table."`";
         $B->db->db_query($query);
         /**
          * Log this event
          */
         $LOG->log("logDatabase",$L->checkLogin(),"Database delete: All Users");
      }

      if ( isset($_POST['chkDBDeleteGroups']) ) {
         $query  = "TRUNCATE TABLE `".$G->table."`";
         $G->db->db_query($query);
         $query  = "TRUNCATE TABLE `".$UG->table."`";
         $UG->db->db_query($query);
         /**
          * Log this event
          */
         $LOG->log("logDatabase",$L->checkLogin(),"Database delete: All Groups");
      }

      if ( isset($_POST['chkDBDeleteHolidays']) ) {
         $query  = "DELETE FROM `".$H->table."` WHERE `cfgname`<>'wend' AND `cfgname`<>'busi'";
         $H->db->db_query($query);
         /**
          * Log this event
          */
         $LOG->log("logDatabase",$L->checkLogin(),"Database delete: All Holidays");
      }

      if ( isset($_POST['chkDBDeleteRegions']) ) {
         $query  = "DELETE FROM `".$N->table."` WHERE `region`<>'default'";
         $result = $N->db->db_query($query);
         $query  = "DELETE FROM `".$M->table."` WHERE `region`<>'default'";
         $result = $M->db->db_query($query);
         $query  = "DELETE FROM `".$R->table."` WHERE `regionname`<>'default'";
         $result = $R->db->db_query($query);
                  /**
          * Log this event
          */
         $LOG->log("logDatabase",$L->checkLogin(),"Database delete: All Regions (except default)");
      }

      if ( isset($_POST['chkDBDeleteAbsence']) ) {
         $query  = "DELETE FROM `".$A->table."` WHERE `cfgname`<> 'present'";
         $A->db->db_query($query);
         /**
          * With no absence types it does not make sense to keep any
          * user templates or absence2group assignments.
          */
         $query  = "TRUNCATE TABLE `".$T->table."`";
         $T->db->db_query($query);
         $query  = "TRUNCATE TABLE `".$B->table."`";
         $B->db->db_query($query);
         $query  = "TRUNCATE TABLE `".$AG->table."`";
         $AG->db->db_query($query);
         /**
          * Log this event
          */
         $LOG->log("logDatabase",$L->checkLogin(),"Database delete: All Absence Types");
      }

      if ( isset($_POST['chkDBDeleteDaynotes']) ) {
         $query  = "DELETE FROM `".$N->table."` WHERE `username`='all'";
         $N->db->db_query($query);
         /**
          * Log this event
          */
         $LOG->log("logDatabase",$L->checkLogin(),"Database delete: All general Daynotes");
      }

      if ( isset($_POST['chkDBDeleteAnnouncements']) ) {
         $AN->clearAnnouncements();
         $AN->clearAnnouncementAssignments();
         /**
          * Log this event
          */
         $LOG->log("logDatabase",$L->checkLogin(),"Database delete: All Announcements");
      }

      if ( isset($_POST['chkDBDeleteOrphAnnouncements']) ) {
         $announcements = $AN->getAll();
         foreach ($announcements as $row) {
            $query2 = "SELECT * FROM ".$AN->uatable." WHERE ats='".$row['timestamp']."';";
            $result2 = $AN->db->db_query($query2);
            if ( !$AN->db->db_numrows($result2) ) $AN->delete($row['timestamp']);
         }
         /**
          * Log this event
          */
         $LOG->log("logAnnouncement",$L->checkLogin(),"Database delete: Orphaned Announcements");
      }

      if ( isset($_POST['chkDBDeleteLog']) ) {
         $query  = "TRUNCATE TABLE `".$CONF['db_table_log']."`";
         $LOG->db->db_query($query);
         /**
          * Log this event
          */
         $LOG->log("logDatabase",$L->checkLogin(),"Database delete: Log records cleared");
      }

      if ( isset($_POST['chkDBDeletePermissionSchemes']) ) {
         $query = "DELETE FROM ".$P->table." WHERE scheme != 'Default';";
         $P->db->db_query($query);
         /**
          * Log this event
          */
         $LOG->log("logDatabase",$L->checkLogin(),"Database delete: All custom permission schemes");
      }

   } else {
      $error=TRUE;
      $err_short = $LANG['err_input_caption'];
      $err_long = $LANG['err_input_dbmaint_del'];
      $err_module=$_SERVER['SCRIPT_NAME'];
      $err_btn_close=FALSE;
   }
}
/**
 * =========================================================================
 * EXPORT
 */
else if (isset($_POST['btn_export'])) {

   switch ($_POST['exp_table']) {
      case 'exp_all':      $what="all"; break;
      case 'exp_absence':  $what=$A->table; break;
      case 'exp_group':    $what=$G->table; break;
      case 'exp_holiday':  $what=$H->table; break;
      case 'exp_region':   $what=$R->table; break;
      case 'exp_log':      $what=$L->table; break;
      case 'exp_month':    $what=$M->table; break;
      case 'exp_styles':   $what=$S->table; break;
      case 'exp_template': $what=$T->table; break;
      case 'exp_user':     $what=$U->table; break;
      default:             $what="all"; break;
   }

   switch ($_POST['exp_format']) {
      case 'exp_format_csv': $format="csv"; break;
      case 'exp_format_sql': $format="sql"; break;
      case 'exp_format_xml': $format="xml"; break;
      default:               $format="sql"; break;
   }

   switch ($_POST['exp_output']) {
      case 'exp_output_browser': $type="browser"; break;
      case 'exp_output_file':    $type="download"; break;
      default:                   $type=""; break;
   }

   /**
    * Log this event
    */
   $LOG->log("logDatabase",$L->checkLogin(),"Database export: $format | $what | $type");
   header('Location: exportdata.php?format='.$format.'&what='.$what.'&type='.$type);
}
/**
 * =========================================================================
 * RESTORE
 */
else if ( isset($_POST['btn_rest_rest']) ) {

   $message['header']=$LANG['admin_dbmaint_rest_caption'];
   $message['title']=$LANG['admin_dbmaint_rest_caption']." ".$LANG['result'];

   if (strlen($_FILES['sqlfile']['name'])) {

      $updir = $CONF['app_root'].'sql/';
      //$upfile = $updir . basename($_FILES['sqlfile']['name']);
      $upfile = $updir . "tcpro_dbrestore_".date('Ymd_His').".sql";

      if (move_uploaded_file($_FILES['sqlfile']['tmp_name'], $upfile)) {
         /**
          * Restore database from file
          */
         $db = new myDB;
         $db->db_connect();
         if ($file_content = file($upfile)) {
            $query = "";
            foreach($file_content as $sql_line) {
               $tsl = trim($sql_line);
               if (($sql_line != "") && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != "#")) {
                  $query .= $sql_line;
                  if(preg_match("/;\s*$/", $sql_line)) {
                     $result = $db->db_query($query);
                     if (!$result) die(mysql_error());
                     $query = "";
                     $found=true;
                  }
               }
            }
            $message['show']=true;
            if (!$found) {
               $message['success']=false;
               $message['text'] = $LANG['admin_dbmaint_msg_001'];
            }
            else {
               $message['success']=true;
               $message['text'] = $LANG['admin_dbmaint_msg_002'];
               /**
                * Log this event
                */
               $LOG->log("logDatabase",$L->checkLogin(),"Database restored from: ".$_FILES['sqlfile']['name']);
            }
         }
      }
      else {
         $message['show']=true;
         $message['success']=false;
         $message['text'] = $LANG['admin_dbmaint_msg_003'];
      }
   }
   else {
      $message['show']=true;
      $message['success']=false;
      $message['text'] = $LANG['admin_dbmaint_msg_004'];
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
      <!--  DATABASE MANAGEMENT ================================================= -->
      <table class="dlg">
         <tr>
            <td class="dlg-header" colspan="4">
               <?php printDialogTop($LANG['admin_dbmaint_title'],"manage_database.html","ico_database.png"); ?>
            </td>
         </tr>

         <!-- MESSAGE -->
         <?php if ($message['show']) { ?>
            <?php $style="2"; ?>
            <tr>
               <td class="dlg-caption-<?=($message['success'])?"green":"red";?>" colspan="4" style="text-align: left;"><?=$message['header']?></td>
            </tr>

            <?php if ($style=="1") $style="2"; else $style="1"; ?>
            <tr>
               <td colspan="4" class="config-row<?=$style?>" style="text-align: left; width: 60%;">
                  <span class="config-key"><?=$message['title']?></span><br>
                  <span class="config-comment"><?=$message['text']?></span>
               </td>
            </tr>
         <?php } ?>

         <!--  Clean up -->
         <tr>
            <td class="dlg-caption" colspan="4"><?=$LANG['admin_dbmaint_cleanup_caption']?></td>
         </tr>
         <tr>
            <td class="dlg-help" colspan="4">
               <form class="form" name="form-db-clean" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
               <table>
                  <tr>
                     <td>
                        <?=$LANG['admin_dbmaint_cleanup_year']?>&nbsp;<input name="clean_year" type="text" class="text" size="6" maxlength="4" value="">&nbsp;&nbsp;&nbsp;&nbsp;
                        <?=$LANG['admin_dbmaint_cleanup_month']?>&nbsp;<input name="clean_month" type="text" class="text" size="6" maxlength="2" value="">&nbsp;&nbsp;&nbsp;&nbsp;
                        <?=$LANG['admin_dbmaint_cleanup_hint']?>
                        <br>
                     </td>
                  </tr>
               </table>
               <table>
                  <tr>
                     <td><input name="chkDBCleanupUsers" id="chkDBCleanupUsers" type="checkbox" value="chkDBCleanupUsers" CHECKED></td>
                     <td><?=$LANG['admin_dbmaint_cleanup_chkUsers']?></td>
                  </tr>
                  <tr>
                     <td><input name="chkDBCleanupMonths" id="chkDBCleanupMonths" type="checkbox" value="chkDBCleanupMonths" CHECKED></td>
                     <td><?=$LANG['admin_dbmaint_cleanup_chkMonths']?></td>
                  </tr>
                  <tr>
                     <td><input name="chkDBOptimize" id="chkDBOptimize" type="checkbox" value="chkDBOptimize" CHECKED></td>
                     <td><?=$LANG['admin_dbmaint_cleanup_chkOptimize']?></td>
                  </tr>
                  <tr><td colspan="2">
                  <br>
                  &nbsp;<?=$LANG['admin_dbmaint_cleanup_confirm']?>&nbsp;<input name="cleanup_confirm" type="text" class="text" size="6" maxlength="7" value="">&nbsp;&nbsp;&nbsp;&nbsp;
                  <input name="btn_dbmaint_clean" type="submit" class="button" value="<?=$LANG['btn_delete_records']?>">
                  </td></tr>
               </table>
               </form>
            </td>
         </tr>

         <!--  Delete -->
         <tr>
            <td class="dlg-caption" colspan="4"><?=$LANG['admin_dbmaint_del_caption']?></td>
         </tr>
         <tr>
            <td class="dlg-help" colspan="4">
               <form class="form" name="form-db-maint" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
               <table>
                  <tr><td><input name="chkDBDeleteUsers" id="chkDBDeleteUsers" type="checkbox" value="chkDBDeleteUsers"></td><td><?=$LANG['admin_dbmaint_del_chkUsers']?></td></tr>
                  <tr><td><input name="chkDBDeleteGroups" id="chkDBDeleteGroups" type="checkbox" value="chkDBDeleteGroups"></td><td><?=$LANG['admin_dbmaint_del_chkGroups']?></td></tr>
                  <tr><td><input name="chkDBDeleteHolidays" id="chkDBDeleteHolidays" type="checkbox" value="chkDBDeleteHolidays"></td><td><?=$LANG['admin_dbmaint_del_chkHolidays']?></td></tr>
                  <tr><td><input name="chkDBDeleteRegions" id="chkDBDeleteRegions" type="checkbox" value="chkDBDeleteRegions"></td><td><?=$LANG['admin_dbmaint_del_chkRegions']?></td></tr>
                  <tr><td><input name="chkDBDeleteAbsence" id="chkDBDeleteAbsence" type="checkbox" value="chkDBDeleteAbsence"></td><td><?=$LANG['admin_dbmaint_del_chkAbsence']?></td></tr>
                  <tr><td><input name="chkDBDeleteDaynotes" id="chkDBDeleteDaynotes" type="checkbox" value="chkDBDeleteDaynotes"></td><td><?=$LANG['admin_dbmaint_del_chkDaynotes']?></td></tr>
                  <tr><td><input name="chkDBDeleteAnnouncements" id="chkDBDeleteAnnouncements" type="checkbox" value="chkDBDeleteAnnouncements"></td><td><?=$LANG['admin_dbmaint_del_chkAnnouncements']?></td></tr>
                  <tr><td><input name="chkDBDeleteOrphAnnouncements" id="chkDBDeleteOrphAnnouncements" type="checkbox" value="chkDBDeleteOrphAnnouncements"></td><td><?=$LANG['admin_dbmaint_del_chkOrphAnnouncements']?></td></tr>
                  <tr><td><input name="chkDBDeleteLog" id="chkDBDeleteLog" type="checkbox" value="chkDBDeleteLog"></td><td><?=$LANG['admin_dbmaint_del_chkLog']?></td></tr>
                  <tr><td><input name="chkDBDeletePermissionSchemes" id="chkDBDeletePermissionSchemes" type="checkbox" value="chkDBDeletePermissionSchemes"></td><td><?=$LANG['admin_dbmaint_del_pschemes']?></td></tr>
                  <tr><td colspan="2">
                  <br>
                  &nbsp;<?=$LANG['admin_dbmaint_del_confirm']?>&nbsp;<input name="del_confirm" type="text" class="text" size="6" maxlength="6" value="">&nbsp;&nbsp;&nbsp;&nbsp;
                  <input name="btn_dbmaint_del" type="submit" class="button" value="<?=$LANG['btn_delete_records']?>">
                  </td></tr>
               </table>
               </form>
            </td>
         </tr>

         <!--  Export -->
         <tr>
            <td class="dlg-caption" colspan="4"><?=$LANG['admin_dbmaint_exp_caption']?></td>
         </tr>
         <tr>
            <td class="dlg-help" colspan="4">
               <form enctype="multipart/form-data" class="form" name="form-db-rest" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
               <table>
                  <tr>
                     <td>
                        <?=$LANG['exp_table']?>&nbsp;
                        <select name="exp_table" id="exp_table" class="select">
                           <option class="option" value="exp_all" SELECTED><?=$LANG['exp_table_all']?></option>
                           <option class="option" value="exp_absence"><?=$LANG['exp_table_absence']?></option>
                           <option class="option" value="exp_group"><?=$LANG['exp_table_group']?></option>
                           <option class="option" value="exp_holiday"><?=$LANG['exp_table_holiday']?></option>
                           <option class="option" value="exp_region"><?=$LANG['exp_table_region']?></option>
                           <option class="option" value="exp_log"><?=$LANG['exp_table_log']?></option>
                           <option class="option" value="exp_month"><?=$LANG['exp_table_month']?></option>
                           <option class="option" value="exp_template"><?=$LANG['exp_table_template']?></option>
                           <option class="option" value="exp_user"><?=$LANG['exp_table_user']?></option>
                        </select>
                     </td>
                  </tr>
               </table>
               <br>
               <table>
                  <tr>
                     <td style="vertical-align: top; padding-right: 20px;">
                        <strong><?=$LANG['exp_format']?></strong><br>
                        <input name="exp_format" id="exp_format_csv" type="radio" value="exp_format_csv"><?=$LANG['exp_format_csv']?><br>
                        <input name="exp_format" id="exp_format_sql" type="radio" value="exp_format_sql" CHECKED><?=$LANG['exp_format_sql']?><br>
                        <input name="exp_format" id="exp_format_xml" type="radio" value="exp_format_xml"><?=$LANG['exp_format_xml']?><br>
                        <br>
                        <input name="btn_export" type="submit" class="button" value="<?=$LANG['btn_export']?>">
                     </td>
                     <td style="vertical-align: top;">
                        <strong><?=$LANG['exp_output']?></strong><br>
                        <input name="exp_output" id="exp_output_browser" type="radio" value="exp_output_browser"><?=$LANG['exp_output_browser']?><br>
                        <input name="exp_output" id="exp_output_file" type="radio" value="exp_output_file" CHECKED><?=$LANG['exp_output_file']?><br>
                     </td>
                  </tr>
               </table>
               </form>
            </td>
         </tr>

         <!--  Restore -->
         <tr>
            <td class="dlg-caption" colspan="4"><?=$LANG['admin_dbmaint_rest_caption']?></td>
         </tr>
         <tr>
            <td class="dlg-help" colspan="4">
               <form enctype="multipart/form-data" class="form" name="form-db-rest" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
               <table>
                  <tr>
                     <td>
                        <?=$LANG['admin_dbmaint_rest_comment']?><br><br>
                        <input class="text" type="hidden" name="MAX_FILE_SIZE" value="<?PHP echo $maxsize; ?>">
                        <input class="text" type="file" name="sqlfile" size="46"><br><br>
                        <input name="btn_rest_rest" type="submit" class="button" value="<?=$LANG['btn_restore']?>">
                     </td>
                  </tr>
               </table>
               </form>
            </td>
         </tr>
      </table>
   </div>
</div>
<?php require("includes/footer.html.inc.php"); ?>