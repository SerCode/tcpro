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
   /**
    * Fill the selection list based on what the logged in user may view
    */
   $luser = $L->checkLogin();
   $users = $U->getAllButAdmin();
   foreach ($users as $usr) {
      $allowed=FALSE;
      if ($usr['username']==$luser) {
         $allowed=TRUE;
      }
      else if ( !($usr['status']&$CONF['USHIDDEN']) AND $UG->shareGroups($usr['username'], $luser) ) {
         if (isAllowed("viewGroupUserCalendars")) {
            $allowed=TRUE;
         }
      }
      else if (!($usr['status']&$CONF['USHIDDEN'])) {
         if (isAllowed("viewAllUserCalendars")) {
            $allowed=TRUE;
         }
      }
      if ($allowed) {
         if ( $usr['firstname']!="" ) {
            $showname = $usr['lastname'].", ".$usr['firstname'];
         }
         else {
            $showname = $usr['lastname'];
         } ?>
         <option class="option" value="<?=$usr['username']?>" <?=(($selectedUser==$usr['username'])?'SELECTED':'')?>><?=$showname?></option>
      <?php }
   }
   ?>
</select>
