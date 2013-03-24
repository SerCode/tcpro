<?php
/**
 * about.php
 *
 * This file displays the About window.
 * You may not alter, disable or remove the About dialog information nor
 * the corresponding $CONF variables.
 *
 * @package TeamCalPro
 * @version 3.5.002
 * @author George Lewe
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */

/**
 * Set parent flag to control access to child scripts
 */
define( '_VALID_TCPRO', 1 );

/**
 * Include configuration
 */
require_once ("config.tcpro.php");
require_once ("helpers/global_helper.php");
getOptions();
if (strlen($CONF['options']['lang'])) require ("includes/lang/" . $CONF['options']['lang'] . ".tcpro.php");
else                                  require ("includes/lang/english.tcpro.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
   <head>
      <title>Lewe Team Calendar Pro</title>
      <meta http-equiv="Content-type" content="text/html;charset=iso-8859-1">
      <meta http-equiv="Content-Style-Type" content="text/css">
      <style type="text/css" media="screen">
         body           { background-color: #F0F0F0; color: #000000; font-family: "segoe ui", arial, helvetica, sans-serif; font-size: 13px; margin: 0px; padding: 0px; width: 100%; height: 100%; }
         .button        { -moz-box-shadow:inset 0px 1px 0px 0px #ffffff; -webkit-box-shadow:inset 0px 1px 0px 0px #ffffff; box-shadow:inset 0px 1px 0px 0px #ffffff; background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #ededed), color-stop(1, #cecece) ); background:-moz-linear-gradient( center top, #ededed 5%, #cecece 100% ); filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#cecece'); background-color:#ededed; -moz-border-radius:4px; -webkit-border-radius:4px; border-radius:4px; border:1px solid #dcdcdc; display:inline-block; color:#000000; font-family:Tahoma, Arial, Helvetica, sans-serif; font-size:8pt; font-weight:normal; padding:2px 14px; text-decoration:none; text-shadow:1px 1px 0px #ffffff; }
         .button:hover  { background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #cecece), color-stop(1, #ededed) ); background:-moz-linear-gradient( center top, #cecece 5%, #ededed 100% ); filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#cecece', endColorstr='#ededed'); background-color:#dfdfdf; }
         .button:active { position:relative; top:1px; }
      </style>
   </head>
   <body>
      <table style="border-collapse: collapse; margin: 0px; padding: 0px; width: 100%; height: 100%;">
         <tr>
            <td style="background-color: #F7F7F7; width: 260px; padding-top: 16px; vertical-align: top; text-align: center;"><img src="img/Calendar-icon-200.png" alt="TeamCal Pro"></td>
            <td style="background-color: #F7F7F7;">
               <p style="font-weight: bold; font-size: 30px; margin: 10px 0px 10px 0px;">TeamCal Pro</p>
               <p>
               <strong>Version:</strong>&nbsp;&nbsp;<?=$CONF['app_version']?><br />
               <strong>Copyright:</strong>&nbsp;&nbsp;&copy;2004-<?=$CONF['app_curr_year']?> by <a class="about" href="http://www.lewe.com/" target="_blank"><?=$CONF['app_author']?></a><br />
               <strong>License:</strong>&nbsp;&nbsp;GNU General Public License (GPL)<br />
               <br />
               <strong>Credits:</strong><br />
               &#8226;&nbsp;jQuery UI Team for <a href="http://www.jqueryui.com/" target="_blank">jQuery UI</a><br />
               &#8226;&nbsp;Stefan Petre for <a href="http://www.eyecon.ro/colorpicker/" target="_blank">jQuery Color Picker</a><br />
               &#8226;&nbsp;Erik Bosrup for <a href="http://www.bosrup.com/web/overlib/" target="_blank">OverLIB</a><br />
               &#8226;&nbsp;Heng Yuan for <a href="http://www.cs.ucla.edu/~heng/JSCookMenu/" target="_blank">JSCookMenu</a><br />
               &#8226;&nbsp;David Vignoni for <a href="http://www.icon-king.com" target="_blank">Nuvola Icons</a><br />
               &#8226;&nbsp;dAKirby309 for <a href="http://www.iconarchive.com/show/windows-8-metro-icons-by-dakirby309.html" target="_blank">Windows 8 Metro Icons</a><br />
               &#8226;&nbsp;Custom Icon Design for <a href="http://www.customicondesign.com/free-icons/pretty-office-icon-set/pretty-office-icon-set-part-7/" target="_blank">the nice icon on the left</a><br />
               &#8226;&nbsp;many users for testing and suggesting...<br />
               </p>
            </td>
         </tr>
         <tr>
            <td colspan="2" style="text-align: right; padding: 20px; margin: 0px;">
               <input name="btn_close" type="button" class="button" onclick="javascript:window.close();" value="<?=$LANG['btn_close']?>">
            </td>
         </tr>
      </table>
   </body>
</html>
