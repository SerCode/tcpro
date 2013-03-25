<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * header.application.inc.php
 *
 * Included on the main pages to display the application header. This file can
 * be used to display an individual logo.
 *
 * @package TeamCalPro
 * @version 3.6.000
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */
?>
<div id="header">
   <img src="themes/<?=$theme?>/img/logo.gif" alt="">
</div>

<div id="subheader">
   <div id="subheader-content"><?=html_entity_decode($C->readConfig("appSubTitle"))?></div>
</div>

<table class="noscreen" style="width: 100%; border-bottom: 1px solid #555555;">
   <tr>
      <td style="text-align: left; font-size: 18px;"><strong><?=$LANG['print_title']?></strong>&nbsp;&nbsp;(<?=date("j. F Y, H:i")?>)</td>
   </tr>
</table>
