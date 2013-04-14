<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * calendar_inc.php
 *
 * Shows the month calendar(s)
 *
 * @package TeamCalPro
 * @version 3.6.002 Dev
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */

//echo "<script type=\"text/javascript\">alert(\"Debug: \");</script>";

if (isset($_REQUEST['sort']))
   if (strtoupper($_REQUEST['sort'])=="DESC") $sort="DESC"; else $sort="ASC";
else $sort="ASC";

if ( !isset($_REQUEST['page']) OR !is_numeric($_REQUEST['page'])) $page=1;
else $page=intval($_REQUEST['page']);

$monthnames = $CONF['monthnames'];
getOptions();
$groupfilter = $CONF['options']['groupfilter'];
$month_id = intval($CONF['options']['month_id']);
$year_id = intval($CONF['options']['year_id']);
$show_id = intval($CONF['options']['show_id']);
$region = $CONF['options']['region'];

// ============================================================================
/*
 * Process Fast Edit form if submitted
 */
if ( isset($_POST['btn_fastedit_apply']) ) {
   /**
    * Loop thru each listbox
    */
   foreach($_POST as $key=>$value) {
      if (substr($key,0,8)== "sel_abs_" ) {
         /*
          * Explode the key
          */
         $pieces = explode('_', $key);
         $feuser = $pieces[2];
         $feyear = $pieces[3];
         $femonth = $pieces[4];
         $feday = $pieces[5];
         
         /*
          * Check whether the listbox was changed by comapring its value to the
          * hidden field that holds the original value. Its name has the same
          * suffix as the listbox's name
          */
         $hidkey='hid_abs_'.$feuser.'_'.$feyear.'_'.$femonth.'_'.$feday;
         if ($_POST[$hidkey]!=$value) {
            $T->setAbsence($feuser, $feyear, $femonth, $feday, $value);
            /**
             * Log this event
             */
            $LOG->log("logUser",$L->checkLogin(),"Calendar Fast Edit for '".$feuser."': ".$feyear."-".$femonth."-".$feday.": ".$A->getName($value));
         }
      }
   }
}
?>
<div id="content">
   <div id="content-content">
      <?php
      /*
       * The Javascript and Form is only needed when Fast Edit is on
       */
      if ($C->readConfig("fastEdit") AND isAllowed("viewFastEdit")) { ?>
         <script type="text/javascript">
            //
            // This script prepares Fast Edit and the background images
            // of the absence list boxes so they show the abs icon.
            // The global variables are used to store the usernames
            // and all absence icons. The listbox background image is
            // switches then by index passed by the value of the selected
            // entry.
            //
            var jsusers = new Array(); 
            var viewMode = true;
            //
            // Now load all absence icons in the rest of the array
            // 
            var absicon = new Array();
            <?php
            $absences = $A->getAll();
            foreach ($absences as $abs) {
               echo "absicon[".$abs['id']."]='".$CONF['app_icon_dir'].$abs['icon']."';\r\n"; 
            }
            ?>
            //
            // This function switches the listbox background image based on
            // it selected value. It represents the abs id which is the index
            // for this icon array here.
            //
            function switchAbsIcon(ele, image) { 
               document.getElementById(ele).style.backgroundImage="url('"+image+"')";
            }
         </script>
         <form name="form-fastedit" class="form" method="POST" action="<?=$_SERVER['PHP_SELF']."?action=calendar&amp;".setRequests()?>">
      <?php }
       
      for ($i = 1; $i<= $show_id; $i++) {
         echo showMonth(strval($year_id), $monthnames[$month_id], $groupfilter, $sort, $page);
         if ($month_id == 12) {
            $year_id += 1;
            $month_id = 1;
         }
         else {
            $month_id += 1;
         }
      }
      
      if ($C->readConfig("fastEdit") AND isAllowed("viewFastEdit")) { ?>
         </form>
      <?php } ?>
      </div>
</div>