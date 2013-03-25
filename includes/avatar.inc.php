<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * avatar.inc.php
 *
 * HTML portion included in the user profile dialog for avatar management
 *
 * @package TeamCalPro
 * @version 3.6.000
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */

unset($CONF);
require ( "config.tcpro.php" );
require_once( $CONF['app_root']."helpers/global_helper.php" );
getOptions();
include( $CONF['app_root']."includes/lang/".$CONF['options']['lang'].".tcpro.php");
?>
<fieldset><legend><?=$LANG['ava_title']?></legend>
   <table style="width: 99%;">
      <tr>
         <td class="dlg-body" style="width: 120px;">
            <?php
            if ($AV->find($U->username)) {
               echo "<img  style=\"padding-right: 10px;\" src=\"".$AV->path.$AV->filename.".".$AV->fileextension."\" align=\"top\" border=\"0\" alt=\"".$U->username."\" title=\"".$U->username."\">";
            }
            else {
               echo "<img src=\"".$AV->path."noavatar.gif\" align=\"top\" border=\"0\" alt=\"No avatar\" title=\"No avatar\">";
            }
            ?>
         </td>
         <td class="dlg-body">
            <?php
            echo $LANG['ava_upload']."<br /><br />";
            ?>
            <input class="text" type="hidden" name="MAX_FILE_SIZE" value="<?php echo $AV->maxSize; ?>">
            <input class="text" type="file" name="imgfile" size="40"><br /><br />
            <input name="btn_avatar_upload" type="submit" class="button" value="<?php echo $LANG['btn_upload']; ?>">
            <br />
            <br />
         </td>
      </tr>
   </table>
</fieldset>
