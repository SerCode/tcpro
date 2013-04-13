<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * options_absences_inc.php
 *
 * Displays the absence selection in the options bar
 *
 * @package TeamCalPro
 * @version 3.6.001 Dev
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */
?>
<!-- Absence filter drop down -->
<select id="absencefilter" name="absencefilter" class="select">
   <option value="All" <?=($selectedAbsence=="All"?"SELECTED":"")?>><?=$LANG['drop_group_all']?></option>
   <?php
   $absences = $A->getAll();
   foreach ($absences as $abs) { ?>
      <option value="<?=$abs['id']?>" <?=(($selectedAbsence==$abs['id'])?' SELECTED':'')?>><?=$abs['name']?></option>
   <?php } ?>
</select>
