<?php
/**
 * log.php
 *
 * Displays the system log dialog
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

require_once("models/config_model.php" );
require_once("models/log_model.php" );
require_once("models/login_model.php" );
require_once("models/user_model.php" );

if ( !isset($_REQUEST['sort']) ) $sort="DESC";
else $sort = $_REQUEST['sort'];

$C   = new Config_model;
$LOG = new Log_model;
$L   = new Login_model;
$U   = new User_model;

/**
 * Check if allowed
 */
if (!isAllowed("viewSystemLog")) showError("notallowed");

$error=FALSE;
$logtypes = array (
   "Absence",
   "Announcement",
   "Config",
   "Database",
   "Daynote",
   "Group",
   "Holiday",
   "Login",
   "Loglevel",
   "Month",
   "Permission",
   "Region",
   "Registration",
   "User",
);

/**
 * =========================================================================
 * REFRESH
 */
if ( isset($_POST['btn_refresh']) ) {
   foreach ($logtypes as $lt) {
      /**
       * Set log levels
       */
      if ( isset($_POST['chk_log'.$lt]) AND $_POST['chk_log'.$lt]) $C->saveConfig("log".$lt,"1");
      else $C->saveConfig("log".$lt,"0");
      /**
       * Set log filters
       */
      if ( isset($_POST['chk_logfilter'.$lt]) AND $_POST['chk_logfilter'.$lt]) $C->saveConfig("logfilter".$lt,"1");
      else $C->saveConfig("logfilter".$lt,"0");
   }
   /**
    * Log this event
    */
   $LOG->log("logLoglevel",$L->checkLogin(),"Log settings updated");
   header("Location: ".$_SERVER['PHP_SELF']);
}
else if ( isset($_POST['btn_clear']) ) {
   $query  = "TRUNCATE TABLE `".$CONF['db_table_log']."`";
   $LOG->db->db_query($query);
   /**
    * Log this event
   */
   $LOG->log("logLogLevel",$L->checkLogin(),"Log page: Log records cleared");
}

require("includes/header_html_inc.php" );
echo "<body>\r\n";
require("includes/header_app_inc.php" );
require("includes/menu_inc.php" );
?>
<script type="text/javascript">$(function() { $( "#tabs" ).tabs(); });</script>
<div id="content">
   <div id="content-content">

      <table class="dlg">
         <tr>
            <td class="dlg-header" colspan="4">
               <?php printDialogTop($LANG['log_title'],"system_log_page.html","ico_log.png"); ?>
            </td>
         </tr>
         <tr>
            <td class="dlg-body">
               <div id="tabs">
                  <ul>
                     <li><a href="#tabs-1"><?=$LANG['log_title']?></a></li>
                     <li><a href="#tabs-2"><?=$LANG['log_settings']?></a></li>
                  </ul>

                  <!-- LOG -->
                  <div id="tabs-1">
                     <table class="dlg">
                        <tr class="logheader">
                           <td class="logheader">
                              <?php if ( $sort=="DESC" ) { ?>
                                 <a href="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']."&amp;sort=ASC"?>"><img src="themes/<?=$theme?>/img/asc.png" border="0" align="middle" alt="" title="<?=$LANG['log_sort_asc']?>"></a>
                              <?php }else { ?>
                                 <a href="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']."&amp;sort=DESC"?>"><img src="themes/<?=$theme?>/img/desc.png" border="0" align="middle" alt="" title="<?=$LANG['log_sort_desc']?>"></a>
                              <?php } ?>
                              &nbsp;<?=$LANG['log_header_timestamp']?>
                           </td>
                           <td class="logheader"><?=$LANG['log_header_type']?></td>
                           <td class="logheader"><?=$LANG['log_header_user']?></td>
                           <td class="logheader"><?=$LANG['log_header_event']?></td>
                        </tr>
                        <?php
                        $result=$LOG->read($sort);
                        $rowstyle=0;
                        while ( $row=$LOG->db->db_fetch_array($result,MYSQL_ASSOC) ) {
                           if ( $C->readConfig("logfilter".substr($row['type'],3)) ) {
                              /**
                               * Put the dashes and colons in the timestamp if not already in
                               */
                              if (strlen($row['timestamp'])==14)
                                 $timestamp=substr($row['timestamp'],0,4)."-".substr($row['timestamp'],4,2)."-".substr($row['timestamp'],6,2)." ".substr($row['timestamp'],8,2).":".substr($row['timestamp'],10,2).":".substr($row['timestamp'],12,2);
                              else
                                 $timestamp=$row['timestamp'];

                              $eventtype = substr($row['type'],3);
                              ?>
                              <tr class="logrow<?=$rowstyle?>">
                                 <td class="logrow<?=$rowstyle?>" style="white-space: nowrap;"><?=$timestamp?></td>
                                 <td class="logrow<?=$rowstyle?>"><?=$eventtype?></td>
                                 <td class="logrow<?=$rowstyle?>"><?=$row['user']?></td>
                                 <td class="logrow<?=$rowstyle?>"><?=str_replace("\n", "<br>", $row['event'])?></td>
                              </tr>
                              <?php
                           }
                           if ($rowstyle) $rowstyle=0; else $rowstyle=1;
                        }
                        ?>
                     </table>
                  </div>

                  <!-- LOG SETTINGS -->
                  <div id="tabs-2">
                     <fieldset><legend><?=$LANG['log_settings']?></legend>
                     <form class="form" name="log-settings" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
                        <table class="dlg">
                           <tr>
                              <td class="dlg-caption" style="text-align: left; border-left: 1px solid #777777;"><?=$LANG['log_settings_event']?></td>
                              <td class="dlg-caption" style="text-align: center; border-left: 1px solid #AAAAAA; border-right: 1px solid #AAAAAA;"><?=$LANG['log_settings_log']?></td>
                              <td class="dlg-caption" style="text-align: center; border-right: 1px solid #777777;"><?=$LANG['log_settings_show']?></td>
                           </tr>
                           <?php
                           $i=0; $style=0;
                           foreach ($logtypes as $lt) {
                              if ($style=="1") $style="2"; else $style="1";
                              $i++; ?>
                              <tr>
                                 <td class="config-row<?=$style?>" style="border-left: 1px solid #777777;"><?=$lt?></td>
                                 <td class="config-row<?=$style?>" style="text-align: center; border-left: 1px solid #AAAAAA; border-right: 1px solid #AAAAAA;">
                                    <?php if (isAllowed("editSystemLog")) { ?>
                                       <input type="checkbox" name="chk_log<?=$lt?>" value="chk_log<?=$lt?>" <?=($C->readConfig("log".$lt))?'CHECKED':''?>>
                                    <?php } else { ?>
                                       <img src="img/icons/checkmark.png" alt="" title="<?=$LANG['log_tt_notallowed']?>">
                                    <?php } ?>
                                 </td>
                                 <td class="config-row<?=$style?>" style="text-align: center; border-right: 1px solid #777777;">
                                    <input type="checkbox" name="chk_logfilter<?=$lt?>" value="chk_logfilter<?=$lt?>" <?=($C->readConfig("logfilter".$lt))?'CHECKED':''?>>
                                 </td>
                              </tr>
                           <?php } ?>
                           <tr>
                              <td class="dlg-menu" colspan="3" style="text-align: left;">
                                 <input name="btn_refresh" type="submit" class="button" style="font-size: 8pt;" value="<?=$LANG['btn_refresh']?>">
                                 <?php if (isAllowed("editSystemLog")) { ?>
                                    <input name="btn_clear" type="submit" class="button" style="font-size: 8pt;" value="<?=$LANG['log_btn_clearlog']?>" onclick="return confirmSubmit('<?=$LANG['log_clear_confirm']?>')">
                                 <?php } ?>
                              </td>
                           </tr>
                        </table>
                     </form>
                     </fieldset>
                  </div>
               </div>
           </td>
        </tr>
      </table>
   </div>
</div>
<?php require("includes/footer_inc.php" ); ?>