<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * calendar.html.inc.php
 *
 * Shows the month calendar(s)
 *
 * @package TeamCalPro
 * @version 3.6.000
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */

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
?>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<div id="content">
   <div id="content-content">
      <?php
      for ($i = 1; $i<= $show_id; $i++) {
         echo showMonth(strval($year_id), $monthnames[$month_id], $groupfilter, $sort, $page);
         if ($month_id == 12) {
            $year_id += 1;
            $month_id = 1;
         }
         else {
            $month_id += 1;
         }
      } ?>
   </div>
</div>