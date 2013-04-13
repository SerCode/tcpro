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
<!-- Amount of months to show drop down -->
<select id="show_id" name="show_id" class="select">
   <option value="1" <?=$selectedAmount=="1"?' SELECTED':''?>><?=$LANG['drop_show_1_months']?></option>
   <option value="2" <?=$selectedAmount=="2"?' SELECTED':''?>><?=$LANG['drop_show_2_months']?></option>
   <option value="3" <?=$selectedAmount=="3"?' SELECTED':''?>><?=$LANG['drop_show_3_months']?></option>
   <option value="6" <?=$selectedAmount=="6"?' SELECTED':''?>><?=$LANG['drop_show_6_months']?></option>
   <option value="12" <?=$selectedAmount=="12"?' SELECTED':''?>><?=$LANG['drop_show_12_months']?></option>
</select>
