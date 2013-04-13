<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * options_groups_inc.php
 *
 * Displays the group selection in the options bar
 *
 * @package TeamCalPro
 * @version 3.6.001 Dev
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */
?>
<!-- Group filter drop down -->
<select id="groupfilter" name="groupfilter" class="select">
   <option value="All" <?=($selectedGroup=="All"?"SELECTED":"")?>><?=$LANG['drop_group_all']?></option>
   <option value="Allbygroup" <?=($selectedGroup=="Allbygroup"?"SELECTED":"")?>><?=$LANG['drop_group_allbygroup']?></option>
   <?php
   $groups=$G->getAll(TRUE); // TRUE = exclude hidden
   foreach( $groups as $group ) {
      if (!isAllowed("viewAllGroups")) {
         if ($UG->isMemberOfGroup($user,$group['groupname']) OR $UG->isGroupManagerOfGroup($user,$group['groupname'])) { ?>
            <option value="<?=$group['groupname']?>" <?=(($selectedGroup==$group['groupname'])?'SELECTED':'')?>><?=$group['groupname']?></option>
         <?php }
      }
      else {
         if ($UO->true($user,"owngroupsonly") AND $UG->isMemberOfGroup($user,$group['groupname'])) { ?>
            <option value="<?=$group['groupname']?>" <?=(($selectedGroup==$group['groupname'])?'SELECTED':'')?>><?=$group['groupname']?></option>
         <?php } else { ?> 
            <option value="<?=$group['groupname']?>" <?=(($selectedGroup==$group['groupname'])?'SELECTED':'')?>><?=$group['groupname']?></option>
         <?php } 
      }
   }
   ?>
</select>
