<?php
/**
 * phpinfo.php
 *
 * Displays the phpinfo page
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

require_once("models/config_model.php" );
require_once("models/user_model.php" );

$C = new Config_model;
$U = new User_model;
$error=FALSE;

/**
 * Check if allowed
 */
if (!isAllowed("viewEnvironment")) showError("notallowed");

require("includes/header.html.inc.php" );
echo "<body>\r\n";
require("includes/header.application.inc.php" );
require("includes/menu.inc.php");
?>
<div id="content">
   <div id="content-content">
      <table class="dlg">
         <tr>
            <td class="dlg-header" colspan="3">
               <?php printDialogTop($LANG['php_title'],"","ico_php.png"); ?>
            </td>
         </tr>
         <tr>
            <td class="dlg-body">
               <iframe src ="phpinfoshow.php" width="100%" height="800"></iframe>
            </td>
         </tr>
      </table>
   </div>
</div>
<?php require("includes/footer.html.inc.php"); ?>