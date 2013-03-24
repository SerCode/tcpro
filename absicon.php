<?php
/**
 * absicon.php
 *
 * Displays the dialog to assign an icon to an absence type
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

require_once("models/config_model.php");
require_once("models/absence_model.php");
require_once("models/log_model.php");
require_once("models/login_model.php");

$A = new Absence_model;
$C = new Config_model;
$L = new Login_model;
$LOG = new Log_model;

/**
 * Check if allowed
 */
if (!isAllowed("editAbsenceTypes")) showError("notallowed",TRUE);

/**
 * What's the request
 */
$thisabsence = $_REQUEST['absence'];
$A->findBySymbol($thisabsence);
$thisabsencename = $A->dspname;
$thisicon=$A->iconfile;

/**
 * =========================================================================
 * APPLY
 */
if ( isset($_POST['btn_apply']) ) {

   foreach($_POST as $key=>$value) {
      if ($key=="icon") {
         if ($value=="noicon") $A->iconfile = "";
         else                  $A->iconfile = $value;
         $A->update($thisabsence);
      }
   }
   /**
    * Log this event
    */
   $LOG->log("logAbsence",$L->checkLogin(),"Absence icon changed: $thisabsence : $thisicon");
}
/**
 * =========================================================================
 * DONE
 */
elseif (isset ($_POST['btn_done'])) {

   jsCloseAndReload("absences.php");

}

/**
 * Show HTML header
 * Use this file to adjust your meta tags and such
 */
require("includes/header.html.inc.php");
?>
<body>
   <div id="content">
      <div id="content-content">
         <!--  ICONS =========================================================== -->
         <form name="assign" class="form" method="POST" action="<?=$_SERVER['PHP_SELF']."?absence=".$thisabsence."&amp;lang=".$CONF['options']['lang']?>">
         <table class="dlg">
            <tr>
               <td class="dlg-header" colspan="10">
                  <?php printDialogTop($LANG['absicon_title'].$thisabsencename,"absence_icons.html","ico_icon.png"); ?>
               </td>
            </tr>
            <!-- Sub Captions -->
            <tr>
            <?php
            $printrow=1;
            $scanFor = array (
               "gif",
               "jpg",
               "png"
            );
            $scanarray = scanDirectory($CONF['app_icon_dir'], $scanFor);
            if (!strlen($A->iconfile)) $iconcheck="CHECKED"; else $iconcheck="";
            echo "<td class=\"dlg-row".$printrow."\">\n";
            echo "\t<input name=\"icon\" id=\"noicon\" type=\"radio\" value=\"noicon\" ".$iconcheck.">";
            echo $LANG['absicon_none']."\n";
            echo "</td>";
            $i=2;
            foreach ($scanarray as $iconfile) {
               if ($printrow == 1) $printrow = 2;
               else                $printrow = 1;
               if ($iconfile==$A->iconfile) $iconcheck="CHECKED"; else $iconcheck="";
               echo "<td class=\"dlg-row".$printrow."\">\n";
               echo "\t<input name=\"icon\" id=\"".$iconfile."\" type=\"radio\" value=\"".$iconfile."\" ".$iconcheck.">";
               echo "<img src=\"".$CONF['app_icon_dir'].$iconfile."\" border=\"0\" align=\"middle\" alt=\"".$iconfile."\">\n";
               echo "</td>";
               if ($i>9) {
                  $i=1;
                  echo "</tr>";
                  echo "<tr>";
               }
               else {
                  $i++;
               }
            }
            if ($i<=9) {
               for ($k=$i; $k<=10; $k++) {
                  if ($printrow == 1) $printrow = 2;
                  else                $printrow = 1;
                  echo "<td class=\"dlg-row".$printrow."\">&nbsp;</td>";
               }
               echo "</tr>";
            }
            ?>
            <tr>
               <td class="dlg-menu" colspan="10">
                  <input name="btn_upload" type="button" class="button" onclick="javascript:this.blur();openPopup('upload.php?target=icon','upload','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=500,height=400');" value="<?=$LANG['btn_upload']?>">
                  <input name="btn_refresh" type="submit" class="button" value="<?=$LANG['btn_refresh']?>">
                  <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
                  <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?absence_icons.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=740,height=500');" value="<?=$LANG['btn_help']?>">
                  <input name="btn_close" type="button" class="button" onclick="javascript:window.close();" value="<?=$LANG['btn_close']?>">
                  <input name="btn_done" type="submit" class="button" value="<?=$LANG['btn_done']?>">
               </td>
            </tr>
         </table>
         </form>
         <br>
      </div>
   </div>
<?php require("includes/footer.html.inc.php"); ?>
