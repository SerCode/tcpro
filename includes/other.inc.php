<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * other.inc.php
 * 
 * Displays the 'Other' tab in the user profile dialog
 *
 * @package TeamCalPro
 * @version 3.5.002 
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */

require_once ($CONF['app_root']."config.tcpro.php");
require_once ($CONF['app_root']."helpers/global_helper.php");
getOptions();
include( $CONF['app_root']."includes/lang/".$CONF['options']['lang'].".tcpro.php");
?>
<fieldset><legend><?=$LANG['other_title']?></legend>
   <table class="dlg-frame">
      <tr>
         <td class="dlg-body"><?=$C->readConfig("userCustom1")?></td>
         <td class="dlg-body">
            <input name="custom1" id="custom1" size="50" maxlength="80" type="text" class="text" value="<?=$U->custom1?>">
         </td>
      </tr>
      <tr>
         <td class="dlg-body"><?=$C->readConfig("userCustom2")?></td>
         <td class="dlg-body">
            <input name="custom2" id="custom2" size="50" maxlength="80" type="text" class="text" value="<?=$U->custom2?>">
         </td>
      </tr>
      <tr>
         <td class="dlg-body"><?=$C->readConfig("userCustom3")?></td>
         <td class="dlg-body">
            <input name="custom3" id="custom3" size="50" maxlength="80" type="text" class="text" value="<?=$U->custom3?>">
         </td>
      </tr>
      <tr>
         <td class="dlg-body"><?=$C->readConfig("userCustom4")?></td>
         <td class="dlg-body">
            <input name="custom4" id="custom4" size="50" maxlength="80" type="text" class="text" value="<?=$U->custom4?>">
         </td>
      </tr>
      <tr>
         <td class="dlg-body"><?=$C->readConfig("userCustom5")?></td>
         <td class="dlg-body">
            <input name="custom5" id="custom5" size="50" maxlength="80" type="text" class="text" value="<?=$U->custom5?>">
         </td>
      </tr>
      <tr>
         <td class="dlg-body"><?=$LANG['other_customFree']?></td>
         <td class="dlg-body">
            <textarea name="customFree" id="customFree" class="text" cols="47" rows="6"><?php if (strlen(trim($U->customFree))) echo stripslashes(str_replace("<br>","\r\n",trim($U->customFree))); else echo "";?></textarea>
         </td>
      </tr>
      <tr>
         <td class="dlg-body"><?=$LANG['other_customPopup']?></td>
         <td class="dlg-body">
            <textarea name="customPopup" id="customPopup" class="text" cols="47" rows="6"><?php if (strlen(trim($U->customPopup))) echo stripslashes(str_replace("<br>","\r\n",trim($U->customPopup))); else echo "";?></textarea>
         </td>
      </tr>
   </table>
</fieldset>
