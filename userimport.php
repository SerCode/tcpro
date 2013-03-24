<?php
/**
 * userimport.php
 *
 * Displays and runs the user import dialog
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

require_once( "models/config_model.php");
require_once( "models/login_model.php" );
require_once( "models/group_model.php" );
require_once( "models/user_model.php" );
require_once( "models/csv_model.php" );

$C = new Config_model;
$CSV = new csvImport;
$G = new Group_model;
$L = new Login_model;
$U = new User_model;

$error=FALSE;

/**
 * Check authorization
 */
if (!isAllowed("manageUsers")) showError("notallowed", TRUE);

/**
 * Process form
 */
global $HTTP_POST_FILES;
if ( isset($_POST['btn_import']) ) {
   $CSV->file_name = $HTTP_POST_FILES['file_source']['tmp_name'];
   $CSV->import($_POST['list_defgroup'],$_POST['list_deflang'],$_POST['chk_lockuser'],$_POST['chk_hideuser']);
}
elseif ( isset($_POST['btn_done']) ) {
   jsCloseAndReload("userlist.php");
}
require("includes/header.html.inc.php" );
?>
<body>
   <div id="content">
      <div id="content-content">
         <form method="post" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
            <table class="dlg">
               <tr>
                  <td class="dlg-header">
                     <?php printDialogTop($LANG['uimp_title'],"user_import.html","ico_import.png"); ?>
                  </td>
               </tr>
               <tr>
                  <td class="dlg-body" style="padding-left: 10px;">
                     <fieldset><legend><?=$LANG['uimp_title']?></legend>
                        <table style="width: 100%;">
                           <tr>
                              <td class="dlg-body">
                                 <?=$LANG['uimp_import']."<br /><br />"?>
                                 <table style="border: 0px; text-align: center">
                                    <tr>
                                       <td><?=$LANG['uimp_source']?></td>
                                       <td width="10">&nbsp;</td>
                                       <td>
                                          <input type="file" name="file_source" id="file_source" class="text" size="40" value="<?=$file_source?>">
                                       </td>
                                    </tr>
                                    <tr>
                                       <td><?=$LANG['uimp_defgroup']?></td>
                                       <td width="10">&nbsp;</td>
                                       <td>
                                          <select name="list_defgroup" id="list_defgroup" class="select">
                                          <?php
                                          $groups = $G->getAll();
                                          foreach ($groups as $row) {
                                             echo "<option class=\"option\" value=\"".$row['groupname']."\">".$row['groupname']."</option>";
                                          }
                                          ?>
                                          </select>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td><?=$LANG['uimp_deflang']?></td>
                                       <td width="10">&nbsp;</td>
                                       <td>
                                          <select name="list_deflang" id="list_deflang" class="select">
                                          <?php
                                          $array = getLanguages();
                                          foreach ($array as $langfile) {
                                             if ($langfile == $CONF['options']['lang'])
                                                echo ("<option value=\"" . $CONF['options']['lang'] . "\" SELECTED=\"selected\">" . $CONF['options']['lang'] . "</option>");
                                             else
                                                echo ("<option value=\"" . $langfile . "\" >" . $langfile . "</option>");
                                          }
                                          ?>
                                          </select>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td><?=$LANG['uimp_lockuser']?></td>
                                       <td width="10">&nbsp;</td>
                                       <td>
                                          <input name="chk_lockuser" id="chk_lockuser" type="checkbox" value="chk_lockuser" CHECKED>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td><?=$LANG['uimp_hideuser']?></td>
                                       <td width="10">&nbsp;</td>
                                       <td>
                                          <input name="chk_hideuser" id="chk_hideuser" type="checkbox" value="chk_hideuser" CHECKED>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td colspan="3">&nbsp;</td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                        </table>
                     </fieldset>
                  </td>
               </tr>
               <?php if (strlen($CSV->error)) { ?>
               <tr>
                  <td class="dlg-body" style="padding-left: 10px;">
                     <fieldset><legend><?=$LANG['uimp_error']?></legend>
                        <span style="color: #DD0000;"><?=$CSV->error?></span>
                     </fieldset>
                  </td>
               </tr>
               <?php }
               elseif ($CSV->count_imported || $CSV->count_skipped) { ?>
               <tr>
                  <td class="dlg-body" style="padding-left: 10px;">
                     <fieldset><legend><?=$LANG['uimp_success']?></legend>
                        <span style="color: #009900;">
                           <?=$CSV->count_imported.$LANG['uimp_success_1']?><br>
                           <?=$CSV->count_skipped.$LANG['uimp_success_2']?><br>
                        </span>
                     </fieldset>
                  </td>
               </tr>
               <?php } ?>
               <tr>
                  <td class="dlg-menu">
                     <input name="btn_import" type="submit" class="button" value="Import" onclick="javascript:var s=document.getElementById('file_source'); if(s!=null && s.value=='') {alert('<?=$LANG['uimp_error_file']?>'); s.focus(); return false;}">
                     <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?user_import.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=740,height=500');" value="<?=$LANG['btn_help']?>">
                     <input name="btn_close" type="button" class="button" onclick="javascript:window.close();" value="<?=$LANG['btn_close']?>">
                     <input name="btn_done" type="submit" class="button" value="<?=$LANG['btn_done']?>">
                  </td>
               </tr>
            </table>
         </form>
      </div>
   </div>
<?php require("includes/footer.html.inc.php"); ?>