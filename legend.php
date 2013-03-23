<?php
/**
 * legend.php
 *
 * Displays the legend dialog
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

require_once("includes/tcabsence.class.php" );
require_once("includes/tcconfig.class.php" );
require_once("includes/tcholiday.class.php" );

$A = new tcAbsence;
$C = new tcConfig;
$H = new tcHoliday;

require("includes/header.html.inc.php" );
?>
<body>
   <div id="content">
      <div id="content-content">
         <table class="dlg">
            <tr>
               <td class="dlg-header" colspan="3">
                  <?php printDialogTop($LANG['teamcal_legend'],"legend_dialog.html","ico_legend.png"); ?>
               </td>
            </tr>
            <tr>
               <td class="dlg-body">
                  <table style="width: 98%;">
                     <tr>
                        <td width="50%" VALIGN="TOP">
                           <div align="center">
                              <table style="width: 100%;">
                                 <tr>
                                    <td class="dlg-caption" colspan="2"><?=$LANG['col_month_header']?></td>
                                 </tr>
                                 <?php
                                 $holidays = $H->getAll();
                                 foreach ($holidays as $holiday) {
                                    if ($holiday['cfgsym']=='0') $class="";
                                    else                     $class="-".$holiday['cfgname'];
                                    echo "
                                    <tr>
                                       <td class=\"daynum".$class."\" width=\"20\">15</td>
                                       <td class=\"legend\">".$LANG['dom_prefix']."&nbsp;".$holiday['dspname']."</td>
                                    </tr>";
                                 }
                                 ?>
                                 <tr>
                                    <td colspan="2">
                                       <hr size="1" />
                                    </td>
                                 </tr>
                                 <?php
                                 $holidays = $H->getAll();
                                 foreach ($holidays as $holiday) {
                                    if ($holiday['cfgsym']=='0') $class="";
                                    else $class="-".$holiday['cfgname'];
                                    echo "
                                    <tr>
                                       <td class=\"weekday".$class."\" width=\"20\">Xx</td>
                                       <td class=\"legend\">".$LANG['dow_prefix']."&nbsp;".$holiday['dspname']."</td>
                                    </tr>";
                                 }
                                 echo "
                                 <tr>
                                    <td class=\"weekday-note\" width=\"20\">Xx</td>
                                    <td class=\"legend\">".$LANG['dow_daynote']."&nbsp;</td>
                                 </tr>";
                                 ?>
                                 <tr>
                                    <td colspan="2">
                                       <hr size="1" />
                                    </td>
                                 </tr>
                                 <tr>
                                    <td class="month-button"><img src="themes/<?=$theme?>/img/date.png" width="16" height="16" border="0" alt="" /></td>
                                    <td class="legend"><?=$LANG['btn_edit_month']?></td>
                                 </tr>
                                 <tr>
                                    <td class="name-button"><img src="themes/<?=$theme?>/img/btn_edit.gif" width="16" height="16" border="0" alt="" /></td>
                                    <td class="legend"><?=$LANG['btn_edit_member']?></td>
                                 </tr>
                              </table>
                           </div>
                        </td>
                        <td style="width: 50%; vertical-align: top;">
                           <div align="center">
                              <table style="width: 100%;">
                                 <tr>
                                    <td class="dlg-caption" colspan="2"><?=$LANG['col_day_holidays']?></td>
                                 </tr>
                                 <?php
                                 $holidays = $H->getAll();
                                 foreach ($holidays as $holiday) { ?>
                                    <tr>
                                       <td class="day-<?=$holiday['cfgname']?>">&nbsp;</td>
                                       <td class="legend"><?=$holiday['dspname']?></td>
                                    </tr>
                                 <?php } ?>
                                 <tr>
                                    <td class="dlg-caption" colspan="2"><?=$LANG['col_day_absences']?></td>
                                 </tr>
                                 <?php $atypes = $A->getAll();
                                 foreach ($atypes as $atype) { ?>
                                       <tr>
                                          <td class="day-a<?=$atype['id']?>" width="20">
                                             <?php if ($atype['icon']) { ?>
                                                <img align="top" alt="" src="<?=$CONF['app_icon_dir'].$atype['icon']?>" width="16" height="16">
                                             <?php } else {
                                                echo $atype['symbol'];
                                             } ?>
                                          </td>
                                          <td class="legend"><?=$atype['name']?></td>
                                       </tr>
                                 <?php } ?>
                                 <tr>
                                    <td class="legend-today">&nbsp;</td>
                                    <td class="legend"><?=$LANG['legend_today']?></td>
                                 </tr>
                              </table>
                           </div>
                        </td>
                     </tr>
                  </table>
               </td>
            </tr>
            <tr>
               <td class="dlg-menu">
                  <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?legend_dialog.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=740,height=500');" value="<?=$LANG['btn_help']?>">
                  <input name="btn_close" type="button" class="button" onclick="javascript:window.close();" value="<?=$LANG['btn_close']?>">
               </td>
            </tr>
         </table>
      </div>
   </div>
<?php
require("includes/footer.html.inc.php");
?>
