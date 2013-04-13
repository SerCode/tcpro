<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * options_years_inc.php
 *
 * Displays the year selection in the options bar
 *
 * @package TeamCalPro
 * @version 3.6.001 Dev
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */
?>
<!-- Year drop down -->
<select name="obar_year" class="select">
   <?php
   $today = getdate();
   $curryear = $today['year'];
   ?>
   <option value="<?=$curryear-1?>" <?=$selectedYear==$curryear-1?' SELECTED':''?> ><?=$curryear-1?></option>
   <option value="<?=$curryear?>" <?=$selectedYear==$curryear?' SELECTED':''?> ><?=$curryear?></option>
   <option value="<?=$curryear+1?>" <?=$selectedYear==$curryear+1?' SELECTED':''?> ><?=$curryear+1?></option>
   <option value="<?=$curryear+2?>" <?=$selectedYear==$curryear+2?' SELECTED':''?> ><?=$curryear+2?></option>
</select>
