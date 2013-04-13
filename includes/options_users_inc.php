<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * options_users_inc.php
 *
 * Displays the user selection in the options bar
 *
 * @package TeamCalPro
 * @version 3.6.001 Dev
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */
?>
<select name="obar_user" class="select">
   <?php
   $users = $U->getAllButAdmin();
   foreach ($users as $usr) { ?>
      <option value="<?=$usr['username']?>" <?=(($selectedUser==$usr['username'])?'SELECTED':'')?>><?=$usr['lastname']?>, <?=$usr['firstname']?></option>
   <?php } ?>
</select>
