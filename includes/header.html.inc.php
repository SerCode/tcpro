<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * header.html.inc.php
 *
 * Included on each page holding the HTML header information.
 * You may not to alter or remove these header information nor their
 * corresponding $CONF variables. Giving credits is a matter of good
 * manners. However, you may add your own information to it if you like.
 *
 * @package TeamCalPro
 * @version 3.5.002
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--
===============================================================================
TEAMCAL PRO
___________________________________________________________________________

Application: <?=$CONF['app_name']." ".$CONF['app_version']."\n"?>
Date:        <?=$CONF['app_date']."\n"?>
Author:      <?=$CONF['app_author']."\n"?>
Copyright:   <?=$CONF['app_copyright_html']."\n"?>
             All rights reserved.
___________________________________________________________________________

<?php echo $CONF['app_license_html']; ?>

===============================================================================
-->
<?php
/**
 * Includes
 */
require_once( $CONF['app_root']."models/config_model.php" );
require_once( $CONF['app_root']."includes/tclogin.class.php" );
require_once( $CONF['app_root']."includes/tcstyles.class.php" );
require_once( $CONF['app_root']."includes/tcuseroption.class.php" );
$C = new Config_model;
$L = new tcLogin;
$S = new tcStyles;
$UO = new tcUserOption;

/**
 * HELP FILE
 * If there is a manual document in that language make it the default help
 * file. If not take the english one:
 */
if (file_exists($CONF['app_root'] . "manual/" . $CONF['options']['lang'] . ".manual.php")) {
   $CONF['help_file'] = $CONF['options']['lang'] . ".manual.php";
}
else {
   $CONF['help_file'] = "english.manual.php";
}

/**
 * Select the theme to use
 */
if ($thisuser=$L->checkLogin()) {
   /**
    * A user is logged in
    */
   if ($C->readConfig("allowUserTheme")) {
      /**
       * User theme selection is allowed. If none is found set it to 'default'
       * and load default theme
       */
      if (!$theme=$UO->find($thisuser,"deftheme")) {
         $UO->create($thisuser,"deftheme","default");
         $theme = $C->readConfig("theme");
      }
      else {
         /**
          * If user theme selection is set it to 'default', use it.
          */
         if ($theme=="default") $theme = $C->readConfig("theme");
      }
   }
   else {
      /**
       * User theme selection not allowed. Use default theme
       */
      $theme = $C->readConfig("theme");
   }
}
else {
   /**
    * No user logged in. Use default theme
    */
   $theme = $C->readConfig("theme");
}

/**
 * If by now nothing is in $theme set it to 'tcpro'
 */
if (!strlen($theme)) {
   $theme="tcpro";
   $C->saveConfig("theme","tcpro");
}
if (!$S->getStyle($theme)) createCSS($theme);
?>
<html>
   <head>
      <title>Lewe TeamCal Pro</title>
      <meta http-equiv="Pragma" content="no-cache">
      <meta http-equiv="Cache-Control" content="no-cache, must-revalidate, max_age=0">
      <meta http-equiv="Expires" content="0">
      <meta http-equiv="Content-type" content="text/html;charset=utf-8">
      <meta http-equiv="Content-Style-Type" content="text/css">
      <meta name="copyright" content="<?=$CONF['app_copyright_html']?>">
      <meta name="keywords" content="Lewe TeamCal Pro">
      <meta name="description" content="Lewe TeamCal Pro calendar">
      <script type="text/javascript" src="includes/js/tcpro.js"></script>
      <script type="text/javascript" src="includes/js/ajax.js"></script>
      <script type="text/javascript" src="includes/js/overlib.js"></script>
      <script type="text/javascript" src="includes/js/JSCookMenu.js"></script>
      <script type="text/javascript" src="includes/js/JSCookMenu/ThemeOffice/theme.js"></script>
   <?php if ($C->readConfig("jQueryCDN")) { ?>
   <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.js"></script>
      <script type="text/javascript" src="http://code.jquery.com/ui/1.10.1/jquery-ui.js"></script>
      <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css">
   <?php } else { ?>
   <script type="text/javascript" src="includes/js/jQuery/jquery-1.9.1.js"></script>
      <script type="text/javascript" src="includes/js/jQuery/jquery-ui-1.10.1.custom.js"></script>
      <link rel="stylesheet" href="includes/js/jQuery/themes/base/jquery-ui.css">
   <?php } ?>
   <link rel="stylesheet" media="screen" type="text/css" href="includes/js/colorpicker/css/colorpicker.css">
      <script type="text/javascript" src="includes/js/colorpicker/js/colorpicker.js"></script>
      <link rel="shortcut icon" href="themes/<?=$theme?>/img/favicon.ico">
      <link type="text/css" rel="stylesheet" href="themes/<?=$theme?>/css/menu.css">
      <link type="text/css" rel="stylesheet" href="themes/<?=$theme?>/css/calendar.css" media="All" title="Summer">

<!--
===============================================================================
This following stylesheet was created automatically and saved to/read from the database.
If you want to change styles, edit the core stylesheet file
"themes/<?php print $theme;?>/default.css"
Then navigate to the TeamCal Configuration page and click [Apply]. Applying the configuration
will always rebuild the stylesheet in the database based on the core file.
-->
<style type="text/css" media="screen">
<?php print $S->getStyle($theme);?>
</style>

<!--
===============================================================================
This following stylesheet was created automatically and saved to/read from the database.
If you want to change styles, edit the core stylesheet file
"themes/<?php print $theme;?>/default.css"
Then navigate to the TeamCal Configuration page and click [Apply]. Applying the configuration
will always rebuild the stylesheet in the database based on the core file.
-->
<style type="text/css" media="print">
<?php print $S->getStyle($theme."_print");?>
</style>

   <!-- jQuery Tooltip -->
   <script type="text/javascript">$(function() { $( document ).tooltip({ position: { my: "center bottom-20", at: "center top", using: function( position, feedback ) { $( this ).css( position ); $( "<div>" ) .addClass( "arrow" ) .addClass( feedback.vertical ) .addClass( feedback.horizontal ) .appendTo( this ); } } }); });</script>

   </head>
