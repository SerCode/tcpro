<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * options_regions_inc.php
 *
 * Displays the region selection in the options bar
 *
 * @package TeamCalPro
 * @version 3.6.001 Dev
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */
?>
<!-- Region drop down -->
<select name="regionfilter" class="select">
   <?php
   $regions = $R->getAll();
   foreach ($regions as $reg) { ?>
      <option value="<?=$reg['regionname']?>" <?=(($selectedRegion==$reg['regionname'])?"SELECTED":"")?>><?=$reg['regionname']?></option>
   <?php } ?>
</select>
