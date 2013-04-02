<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * calendar_inc.php
 *
 * Shows the month calendar(s)
 *
 * @package TeamCalPro
 * @version 3.6.000
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
$groupfilter = intval($CONF['options']['groupfilter']);
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
         //echo "<script type=\"text/javascript\">alert(\"Debug: \");</script>";
         $pieces = explode('_', $key);
         $feuser = $pieces[2];
         $feyear = $pieces[3];
         $femonth = $pieces[4];
         $feday = $pieces[5];
         if ($T->getAbsence($feuser, $feyear, $femonth, $feday)!=$value) {
            $T->setAbsence($feuser, $feyear, $femonth, $feday, $value);
            /**
             * Log this event
             */
            //$LOG->log("logUser",$L->checkLogin(),"Calendar Fast Edit for '".$feuser."': ".$feyear."-".$femonth."-".$feday.": ".$value);
         }
      }
   }
}
?>
<div id="content">
   <div id="content-content">
      <?php
      if ($C->readConfig("fastEdit") AND isAllowed("viewFastEdit")) { ?>
         <script type="text/javascript">
            var jsusers = new Array(); 
            var viewMode = true;
            var absicon = new Array();
            <?php 
            $absences = $A->getAll();
            foreach ($absences as $abs) {
               echo "absicon[".$abs['id']."]='".$CONF['app_icon_dir'].$abs['icon']."';\r\n"; 
            }
            ?>
            function switchAbsIcon(ele, image) { 
               document.getElementById(ele).style.backgroundImage="url('"+image+"')";
            }
         </script>
         <form name="form-fastedit" class="form" method="POST" action="<?=$_SERVER['PHP_SELF']?>?action=calendar&amp;lang=<?=$CONF['options']['lang']?>">
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