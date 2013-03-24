<?php
/**
 * absencegroup.php
 *
 * Displays a dialog to assign absence types to groups
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

require_once("models/absence_model.php");
require_once("models/absence_group_model.php");
require_once("models/config_model.php");
require_once("models/group_model.php");
require_once("models/log_model.php" );
require_once("models/login_model.php" );

$A = new Absence_model;
$AG = new Absence_group_model;
$C = new Config_model;
$G = new Group_model;
$L = new Login_model;
$LOG = new Log_model;

/**
 * Check if allowed
 */
if (!isAllowed("editAbsenceTypes")) showError("notallowed",TRUE);
if (!isset($_REQUEST['abs'])) showError("notarget",TRUE);
$A->findBySymbol($_REQUEST['abs']);

/**
 * =========================================================================
 * ASSIGN
 */
if (isset($_POST['btn_assign'])) {
   /**
    * First unassign from all groups
    */
   $AG->unassignAbsence($_REQUEST['abs']);

   /**
    * Now assign the new selection
    */
   $logthis=false;
   foreach($_POST as $key=>$value) {
      if (substr($key,0,2)=="a-") {
         //echo "<script type=\"text/javascript\">alert(\"Debug: ".$key."\");</script>";
         if (strlen($key) && !$AG->isAssigned($_REQUEST['abs'],substr($key,2))) {
            $AG->assign($_REQUEST['abs'],substr($key,2));
            $logthis=true;
         }
      }
   }

   if ($logthis) {
      $LOG->log("logAbsence", $L->checkLogin(), "Absence to Group assignment changed for: ".$A->dspname);
      sendNotification("absencechange", $_REQUEST['absname'], "");
      header("Location: ".$_SERVER['PHP_SELF']."?abs=".$_REQUEST['abs']);
   }
}
/**
 * =========================================================================
 * ASSIGN ALL
 */
elseif (isset($_POST['btn_assign_all'])) {

   /**
    * First unassign from all groups
    */
   $AG->unassignAbsence($_REQUEST['abs']);

   /**
    * Now assign to all groups
    */
   $groups = $G->getAll();
   foreach ($groups as $row) {
      $AG->assign($_REQUEST['abs'],$row['groupname']);
   }
   $LOG->log("logAbsence", $L->checkLogin(), "Absence to Group assignment changed for: ".$A->dspname);
   sendNotification("absencechange", $_REQUEST['absname'], "");
   header("Location: ".$_SERVER['PHP_SELF']."?abs=".$_REQUEST['abs']);

}
/**
 * =========================================================================
 * DONE
 */
elseif (isset($_POST['btn_done'])) {

   jsCloseAndReload("absences.php");

}
include ($CONF['app_root'] . "includes/header.html.inc.php");
?>
<body>
   <div id="content">
      <div id="content-content">
         <form name="form1" method="post" action="<?=$_SERVER['PHP_SELF']?>?abs=<?=$_REQUEST['abs']?>">
         <table class="dlg">
            <tr>
               <td class="dlg-header">
                  <?php printDialogTop($LANG['abs_group_title'],"absence_assignment.html","ico_group.png"); ?>
               </td>
            </tr>
            <tr>
               <td class="dlg-body">
                  <fieldset><legend><?=$LANG['abs_group_frame_title']?></legend>
                     <table style="width: 99%;">
                        <tr class="dlg-body">
                           <td class="dlg-frame-body">
                              <?=$LANG['abs_group_hint']?><strong><?=$A->dspname?></strong>
                              <br />&nbsp;
                           </td>
                        </tr>
                     </table>
                     <table style="width: 99%;">
                        <?php
                           $rowstyle=1;
                           $groups = $G->getAll();
                           foreach ($groups as $row) {
                              $assigned="";
                              if ($AG->isAssigned($_REQUEST['abs'],$row['groupname'])) {
                                 $assigned="checked";
                              }
                              $gname = $row['groupname'];
                              echo '<tr class="row'.$rowstyle.'">
                                 <td class="dlg-frame-body" width="24">
                                    <input name="a-'.$gname.'" value="a-'.$gname.'" type="checkbox" '.$assigned.'>'.'
                                 </td>
                                 <td class="dlg-frame-body" style="padding-left: 4px;">
                                    '.$gname.'
                                 </td>
                              </tr>
                              ';
                              if ($rowstyle) $rowstyle=0; else $rowstyle=1;
                           }
                        ?>
                     </table>
                     <br />
                     <input name="btn_assign" type="submit" class="button" value="<?=$LANG['btn_assign']?>">
                     <input name="btn_assign_all" type="submit" class="button" value="<?=$LANG['btn_assign_all']?>">
                  </fieldset>
               </td>
            </tr>
            <tr>
               <td class="dlg-menu">
                  <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?absence_assignment.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=740,height=500');" value="<?=$LANG['btn_help']?>">
                  <input name="btn_close" type="button" class="button" onclick="javascript:window.close();" value="<?=$LANG['btn_close']?>">
                  <input name="btn_done" type="submit" class="button" value="<?=$LANG['btn_done']?>">
               </td>
            </tr>
         </table>
         </form>
      </div>
   </div>
<?php include_once ("includes/footer.html.inc.php"); ?>