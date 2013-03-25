<?php
/**
 * error.php
 *
 * Displays an error message
 *
 * @package TeamCalPro
 * @version 3.6.000
 * @author George Lewe
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */

/**
 * Includes
 */
require_once ("config.tcpro.php");
require_once ("helpers/global_helper.php");
getOptions();
if (strlen($CONF['options']['lang'])) require ("includes/lang/" . $CONF['options']['lang'] . ".tcpro.php");
else                                  require ("includes/lang/english.tcpro.php");

?>
<div id="content">
   <div id="content-content">
      <table class="dlg">
          <tr>
              <td class="err-header"><?=$LANG['err_title']?></td>
          </tr>
          <tr>
              <td class="err-body">
                 <p class="erraction"><?=$err_short?></p>
                 <p class="errortext"><?=$err_long?></p>
                 <br>
                 <hr size="1">
                 <p><span class="module">Module: <?=$err_module?></span></p>
              </td>
          </tr>
         <tr>
           <td class="dlg-menu">
              <?php if ($err_btn_close) { ?>
              <input name="btn_close" type="button" class="button" onclick="javascript:window.close();" value="<?=$LANG['btn_close']?>">
              <?php } ?>
           </td>
         </tr>
      </table>
   </div>
</div>