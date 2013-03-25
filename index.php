<?php
/**
 * index.php
 *
 * Initial TeamCal Pro page
 *
 * @package TeamCalPro
 * @version 3.6.000
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
 * Load the user configuration file
 */
require_once ("config.tcpro.php");

/**
 * Load models that we need here
 */
require_once ("models/announcement_model.php" );
require_once ("models/config_model.php");
require_once ("models/login_model.php");
require_once ("models/log_model.php");
require_once ("models/user_model.php");
require_once ("models/user_announcement_model.php" );
require_once ("models/user_option_model.php");

/**
 * Load helpers that we need here
 */
require_once ("helpers/global_helper.php");
require_once ("helpers/showmonth_helper.php");

/**
 * Create model instances
 */
$AN = new Announcement_model;
$C = new Config_model;
$L = new Login_model;
$LOG = new Log_model;
$U = new User_model;
$UA = new User_announcement_model;
$UO = new User_option_model;

/**
 * Get other options
 */
getOptions();
if (strlen($CONF['options']['lang'])) 
   require ("includes/lang/".$CONF['options']['lang'].".tcpro.php");
else
   require ("includes/lang/english.tcpro.php");

/**
 * Get the URL action request
 */
if (isset ($_REQUEST['action'])) {
   switch ($_REQUEST['action']) {

      case 'welcome' :
         $display = "welcome";
         break;

      case 'calendar' :
         $display = "calendar";
         break;

      case 'logout' :
         $L->logout();
         $LOG->log("logLogin", $L->checkLogin(), "Logout");
         header("Location: ".$_SERVER['PHP_SELF']);
         break;

      default:
         $display = $C->readConfig("homepage");
         break;
   }
}
else {
   $display = $C->readConfig("homepage");
}

/**
 * If someone is logged in and there is a popup announcement for him then
 * this overrules the content request.
 */
if ($user=$L->checkLogin()) {
   $uas=$UA->getAllForUser($user);
   $foundpopup=false;
   foreach($uas as $ua) {
      $AN->read($ua['ats']);
      if ($AN->popup) {
         $foundpopup=true;
         break;
      }
   }
   if ($foundpopup) {
      /**
       * Found popup announcements. Show announcement page if not more than 20
       * seconds have passed since login. Otherwise, if the user does not 
       * remove his announcement, the popup would be shown everytime the 
       * calendar or homepage is displayed.
       */
      $U->findByName($user);
      $nowstamp = date("YmdHis");
      $userstamp=$U->last_login;
      $userstamp=str_replace("-",'',$userstamp);
      $userstamp=str_replace(" ",'',$userstamp);
      $userstamp=str_replace(":",'',$userstamp);
      if ( (floatval($nowstamp)-20) < floatval($userstamp) AND isAllowed("viewAnnouncements") ) {
         header("Location: announcement.php?uaname=".$user);
      }
   }
}

/**
 * Show HTML top section
 */
require("includes/header.html.inc.php");
echo "<body>\r\n";
require("includes/header.application.inc.php");
require("includes/menu.inc.php");

/**
 * Show content
 */
if ( $display=="calendar" AND isAllowed("viewCalendar")) {
   include("includes/calendar.html.inc.php");
}
else {
   include("includes/homepage.html.inc.php");
}

/**
 * Show HTML footer
 */
require("includes/footer.html.inc.php");
?>
