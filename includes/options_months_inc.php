<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * options_months_inc.php
 *
 * Displays the month selection in the options bar
 *
 * @package TeamCalPro
 * @version 3.6.001 Dev
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */
?>
<select id="month_id" name="month_id" class="select">
   <option value="1" <?=$selectedMonth == "1"?' SELECTED':''?> ><?=$LANG['monthnames'][1]?></option>
   <option value="2" <?=$selectedMonth == "2"?' SELECTED':''?> ><?=$LANG['monthnames'][2]?></option>
   <option value="3" <?=$selectedMonth == "3"?' SELECTED':''?> ><?=$LANG['monthnames'][3]?></option>
   <option value="4" <?=$selectedMonth == "4"?' SELECTED':''?> ><?=$LANG['monthnames'][4]?></option>
   <option value="5" <?=$selectedMonth == "5"?' SELECTED':''?> ><?=$LANG['monthnames'][5]?></option>
   <option value="6" <?=$selectedMonth == "6"?' SELECTED':''?> ><?=$LANG['monthnames'][6]?></option>
   <option value="7" <?=$selectedMonth == "7"?' SELECTED':''?> ><?=$LANG['monthnames'][7]?></option>
   <option value="8" <?=$selectedMonth == "8"?' SELECTED':''?> ><?=$LANG['monthnames'][8]?></option>
   <option value="9" <?=$selectedMonth == "9"?' SELECTED':''?> ><?=$LANG['monthnames'][9]?></option>
   <option value="10" <?=$selectedMonth == "10"?' SELECTED':''?> ><?=$LANG['monthnames'][10]?></option>
   <option value="11" <?=$selectedMonth == "11"?' SELECTED':''?> ><?=$LANG['monthnames'][11]?></option>
   <option value="12" <?=$selectedMonth == "12"?' SELECTED':''?> ><?=$LANG['monthnames'][12]?></option>
</select>
