<?php
/**
 * index.php
 *
 * Initial TeamCal Pro page
 *
 * @package TeamCalPro
 * @version 3.5.002
 * @author George Lewe
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */

//echo "<script type=\"text/javascript\">alert(\"Debug: \");</script>";

/**
 * Set parent flag to control access to child scripts
 */
define( '_VALID_TCPRO', 1 );

/**
 * Installation check
 */
if ( !file_exists("config.tcpro.php")) {
   if ( file_exists("installation.php")) {
      header( 'Location: installation.php' );
   }
   else {
      header( 'Location: index_corrupt.html' );
   }
}

/**
 * Get all $_REQUEST and $_POST parameters into $CONF['options'][]
 * and overwrite defaults accordingly.
 */
require_once ("config.tcpro.php");
require_once ("includes/functions.tcpro.php");
getOptions();
if (strlen($CONF['options']['lang'])) require ("includes/lang/" . $CONF['options']['lang'] . ".tcpro.php");
else                                  require ("includes/lang/english.tcpro.php");

/**
 * Includes
 */
require_once ("includes/showmonth.function.php");
require_once ("models/announcement_model.php" );
require_once ("models/config_model.php");
require_once ("models/login_model.php");
require_once ("models/log_model.php");
require_once ("includes/tcuser.class.php");
require_once ("models/user_announcement_model.php" );
require_once ("includes/tcuseroption.class.php");

$AN = new Announcement_model;
$C = new Config_model;
$L = new Login_model;
$LOG = new Log_model;
$U = new tcUser;
$U2 = new tcUser;
$UA = new User_announcement_model;
$UO = new tcUserOption;

if ($L->checkLogin()) $logged_out=FALSE; else $logged_out=TRUE;
$display = $C->readConfig("homepage");

if (isset ($_REQUEST['action'])) {
   switch ($_REQUEST['action']) {

      case 'welcome' :
      $display = "welcome";
      break;

      case 'calendar' :
      $display = "calendar";
      break;

      case 'logout' :
      $logged_out = true;
      $display = $C->readConfig("homepage");
      $LOG->log("logLogin", $L->checkLogin(), "Logout");
      $L->logout();
      exit;
      break;

      default:
      $display = $C->readConfig("homepage");
      break;
   }
}

/**
 * Show HTML header
 * Use this file to adjust your meta tags and such
 */
require("includes/header.html.inc.php");

/**
 * Body Tag
 * The following <div> tag is necessary for overLIB tool tips
 */
echo "<body>\r\n";

/**
 * Show application header
 * This is the file to change in order to put different images at the top
 * of the main page.
 */
require("includes/header.application.inc.php");

/**
 * Show menu
 * This is the file containing the TeamCal menu
 */
require("includes/menu.inc.php");

/**
 * Let's check what we can show this user
 */
if ( $display == "calendar" AND isAllowed("viewCalendar")) {
   /**
    * Show calendar
    */
   include("includes/calendar.html.inc.php");
}
else {
   /**
    * Show homepage
    */
   include("includes/homepage.html.inc.php");
}

if ( !$logged_out ) {
   /**
    * Now check for popup announcements for the logged in user
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
          * Found popup announcements. Open popup if not more than 20 seconds
          * have passed since login. (Otherwise the popup would be shown everytime
          * the calendar or homepage is displayed.)
          */
         $U2->findByName($user);
         $nowstamp = date("YmdHis");
         $userstamp=$U2->last_login;
         $userstamp=str_replace("-",'',$userstamp);
         $userstamp=str_replace(" ",'',$userstamp);
         $userstamp=str_replace(":",'',$userstamp);
         //echo "<script type=\"text/javascript\">alert(\"Debug: ".$nowstamp."|".$userstamp."\");</script>";
         if ( (floatval($nowstamp)-20) < floatval($userstamp) AND isAllowed("viewAnnouncements")) {
         ?>
         <script type="text/javascript">
            <!--
            this.blur();
            openPopup('popup.php?uname=<?php echo $user; ?>','popup','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=500,height=400');
            -->
         </script>
         <?php
         }
      }
   }
}

/**
 * Show HTML page footer
 */
require("includes/footer.html.inc.php");
?>
