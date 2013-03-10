<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * functions.tcpro.php
 *
 * Collection of global functions for TeamCal Pro
 *
 * @package TeamCalPro
 * @version 3.5.002
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */

//echo "<script type=\"text/javascript\">alert(\"Debug: \");</script>";

/**
 * Checks whether a user is authorized in the active permission scheme
 *
 * @param string $scheme Permission scheme to check
 * @param string $permission Permission to check
 * @param string $targetuser Some features reference data of other users. This is the target
 * @return boolean True if allowed, false if not.
 */
function isAllowed($permission='') {

   global $CONF;

   require_once ($CONF['app_root'] . "includes/tcconfig.class.php");
   require_once ($CONF['app_root'] . "includes/tclogin.class.php");
   require_once ($CONF['app_root'] . "includes/tcpermission.class.php");
   require_once ($CONF['app_root'] . "includes/tcuser.class.php");

   $C = new tcConfig;
   $L = new tcLogin;
   $P = new tcPermission;
   $UL = new tcUser;

   $pscheme = $C->readConfig("permissionScheme");

   if ($currentuser = $L->checkLogin()) {
      /**
       * Someone is logged in. Check permission by role.
       */
      $UL->findByName($currentuser);
      if ($UL->checkUserType($CONF['UTADMIN'])) {
         //echo "<script type=\"text/javascript\">alert(\"Admin: ".$permission."=".$P->isAllowed($pscheme, $permission, "admin")."\");</script>";
         return $P->isAllowed($pscheme, $permission, "admin");
      }
      else if ($UL->checkUserType($CONF['UTDIRECTOR'])) {
         //echo "<script type=\"text/javascript\">alert(\"Director: ".$permission."=".$P->isAllowed($pscheme, $permission, "director")."\");</script>";
         return $P->isAllowed($pscheme, $permission, "director");
      }
      else if ($UL->checkUserType($CONF['UTMANAGER'])) {
         //echo "<script type=\"text/javascript\">alert(\"Manager: ".$permission."=".$P->isAllowed($pscheme, $permission, "manager")."\");</script>";
         return $P->isAllowed($pscheme, $permission, "manager");
      }
      else {
         //echo "<script type=\"text/javascript\">alert(\"User: ".$permission."=".$P->isAllowed($pscheme, $permission, "user")."\");</script>";
         return $P->isAllowed($pscheme, $permission, "user");
      }
   }
   else {
      /**
       * It's a public viewer
       */
      //echo "<script type=\"text/javascript\">alert(\"Public: ".$permission."=".$P->isAllowed($pscheme, $permission, "public")."\");</script>";
      return $P->isAllowed($pscheme, $permission, "public");
   }
}

/**
 * Builds the menu based on permissions
 *
 * @return array menu
 */
function buildMenu() {

   global $CONF;

   require_once ($CONF['app_root'] . "includes/tcconfig.class.php");
   require_once ($CONF['app_root'] . "includes/tclogin.class.php");
   require_once ($CONF['app_root'] . "includes/tcuser.class.php");
   require_once ($CONF['app_root'] . "includes/tcusergroup.class.php");
   require_once ($CONF['app_root'] . "includes/tcuseroption.class.php");

   $C = new tcConfig;
   $L = new tcLogin;
   $U = new tcUser; // represents the user the operation is for
   $UL = new tcUser; // represents the logged in user who wants to perform the operation
   $UG = new tcUserGroup;
   $UO = new tcUserOption;

   /**
    * Create empty menu
    */
    $mnu = array(
      "mnu_teamcal"=>TRUE,
      "mnu_teamcal_login"=>TRUE,
      "mnu_teamcal_logout"=>FALSE,
      "mnu_teamcal_register"=>FALSE,
      "mnu_view"=>TRUE,
      "mnu_view_homepage"=>TRUE,
      "mnu_view_calendar"=>FALSE,
      "mnu_view_yearcalendar"=>FALSE,
      "mnu_view_announcement"=>FALSE,
      "mnu_view_statistics"=>FALSE,
      "mnu_view_statistics_g"=>FALSE,
      "mnu_view_statistics_r"=>FALSE,
      "mnu_tools"=>FALSE,
      "mnu_tools_profile"=>FALSE,
      "mnu_tools_message"=>FALSE,
      "mnu_tools_webmeasure"=>FALSE,
      "mnu_tools_admin"=>FALSE,
      "mnu_tools_admin_config"=>FALSE,
      "mnu_tools_admin_perm"=>FALSE,
      "mnu_tools_admin_users"=>FALSE,
      "mnu_tools_admin_groups"=>FALSE,
      "mnu_tools_admin_usergroups"=>FALSE,
      "mnu_tools_admin_absences"=>FALSE,
      "mnu_tools_admin_regions"=>FALSE,
      "mnu_tools_admin_holidays"=>FALSE,
      "mnu_tools_admin_declination"=>FALSE,
      "mnu_tools_admin_database"=>FALSE,
      "mnu_tools_admin_systemlog"=>FALSE,
      "mnu_tools_admin_env"=>FALSE,
      "mnu_tools_admin_phpinfo"=>FALSE,
      "mnu_help"=>TRUE,
      "mnu_help_legend"=>FALSE,
      "mnu_help_help"=>TRUE,
      "mnu_help_help_manualbrowser"=>TRUE,
      "mnu_help_help_manualpdf"=>TRUE,
      "mnu_help_about"=>TRUE,
   );

   /**
    * Now enable entries based on permission
    */
   if ($user=$L->checkLogin()) {
      $UL->findByName($user);
      if ($UL->checkUserType($CONF['UTADMIN'])) $mnu['mnu_teamcal_register']=TRUE;
      $mnu['mnu_teamcal_login']=FALSE;
      $mnu['mnu_teamcal_logout']=TRUE;
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_profile']=TRUE;
   }
   else {
      $mnu['mnu_teamcal_login']=TRUE;
      $mnu['mnu_teamcal_logout']=FALSE;
      if ($C->readConfig("allowRegistration")) $mnu['mnu_teamcal_register']=TRUE;
   }

   if (isAllowed("viewCalendar")) {
      $mnu['mnu_view_calendar']=TRUE;
      $mnu['mnu_help_legend']=TRUE;
   }

   if (isAllowed("viewYearCalendar")) {
      $mnu['mnu_view_yearcalendar']=TRUE;
      $mnu['mnu_help_legend']=TRUE;
   }

   if (isAllowed("viewAnnouncements")) $mnu['mnu_view_announcement']=TRUE;

   if (isAllowed("viewStatistics")) {
      $mnu['mnu_view_statistics']=TRUE;
      $mnu['mnu_view_statistics_g']=TRUE;
      $mnu['mnu_view_statistics_r']=TRUE;
   }

   if (isAllowed("useMessageCenter")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_message']=TRUE;
   }

   if (isAllowed("editConfig")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_config']=TRUE;
   }

   if (isAllowed("editPermissionScheme")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_perm']=TRUE;
   }

   if (isAllowed("manageUsers")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_users']=TRUE;
   }

   if (isAllowed("manageGroups")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_groups']=TRUE;
   }

   if (isAllowed("manageGroupMemberships")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_usergroups']=TRUE;
   }

   if (isAllowed("editAbsenceTypes")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_absences']=TRUE;
   }

   if (isAllowed("editRegions")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_regions']=TRUE;
   }

   if (isAllowed("editHolidays")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_holidays']=TRUE;
   }

   if (isAllowed("editDeclination")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_declination']=TRUE;
   }

   if (isAllowed("manageDatabase")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_database']=TRUE;
   }

   if (isAllowed("viewSystemLog")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_systemlog']=TRUE;
   }

   if (isAllowed("viewEnvironment")) {
      $mnu['mnu_tools']=TRUE;
      $mnu['mnu_tools_admin']=TRUE;
      $mnu['mnu_tools_admin_env']=TRUE;
      $mnu['mnu_tools_admin_phpinfo']=TRUE;
   }

   if ($mnu['mnu_tools'] AND $C->readConfig("webMeasure")) {
      $mnu['mnu_tools_webmeasure']=TRUE;
   }

   return $mnu;
}

/**
 * Unsets a bit combination in a given bitmask
 *
 * @param   string $email     eMail address to validate
 * @return  boolean           True if correct, false if not
 */
function checkEmail($email)
{
   /**
    * First, we check that there's one @ symbol, and that the lengths are right
    */
   if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) {
      /**
       * Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
       */
      return false;
   }

   /**
    * Split it into sections to make life easier
    */
   $email_array = explode("@", $email);
   $local_array = explode(".", $email_array[0]);
   for ($i = 0; $i < sizeof($local_array); $i++)
   {
      if (!preg_match("/^(([A-Za-z0-9!#$%&'*+=?^_`{|}~-][A-Za-z0-9!#$%&'*+=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i]))
      {
         return false;
      }
   }
   if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1]))
   {
      /**
       * Check if domain is IP. If not, it should be valid domain name
       */
      $domain_array = explode(".", $email_array[1]);
      if (sizeof($domain_array) < 2)
      {
         /**
          * Not enough parts to domain
          */
         return false;
      }
      for ($i = 0; $i < sizeof($domain_array); $i++)
      {
         if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i]))
         {
            return false;
         }
      }
   }
   return true;
}

/**
 * Unsets a bit combination in a given bitmask
 *
 * @param   integer $flagset     Target to change
 * @param   integer $bitmask     Bitmask to unset (0's in this bitmask will
 *                               become 0's in the target)
 * @return  integer              New target
 */
function clearFlag($flagset, $bitmask) {
   $newflagset = $flagset & (~$bitmask);
   return $newflagset;
}

/**
 * Counts all occurences of a given absence type for a given user in a given
 * time period
 *
 * @param   string $cntuser     User to count for
 * @param   string $cntabsence  Absence type to count
 * @param   string $cntfrom     Date to count from (including)
 * @param   string $cntto       Date to count to (including)
 * @return  integer             Result of the count
 */
function countAbsence($cntuser, $cntabsence, $cntfrom, $cntto) {
   global $CONF;
   require_once ($CONF['app_root'] . "includes/tcabsence.class.php");
   require_once ($CONF['app_root'] . "includes/tctemplate.class.php");

   $A = new tcAbsence;
   $T = new tcTemplate;

   // Figure out starting month and ending month
   $startyear = intval(substr($cntfrom, 0, 4));
   $startmonth = intval(substr($cntfrom, 4, 2));
   $startday = intval(substr($cntfrom, 6, 2));
   $endyear = intval(substr($cntto, 0, 4));
   $endmonth = intval(substr($cntto, 4, 2));
   $endday = intval(substr($cntto, 6, 2));

   // Get the count factor for this absence type
   $factor = $A->getFactor($cntabsence);

   // Now count
   $count = 0;
   $year = $startyear;
   $month = $startmonth;
   $firstday = $startday;
   if ($firstday < 1 || $firstday > 31)
      $firstday = 1;
   if ($cntuser == "*")
      $whereUser = "";
   else
      $whereUser = "`username`='" . $cntuser . "' AND ";

   while ($year . sprintf("%02d", $month) <= $endyear . sprintf("%02d", $endmonth))
   {
      $query2 = "SELECT * FROM `" . $T->table . "` WHERE " . $whereUser . " `year`='" . $year . "' AND `month`='" . sprintf("%02d", $month) . "';";
      $result2 = $T->db->db_query($query2);
      while ($row2 = $T->db->db_fetch_array($result2, MYSQL_ASSOC))
      {
         if ($year == $endyear && $month == $endmonth)
         {
            // This is the last template. Make sure we just read it up to the specified endday.
            if ($endday < strlen($row2['template']))
               $lastday = $endday;
            else
               $lastday = strlen($row2['template']);
         }
         else
         {
            $lastday = strlen($row2['template']);
         }
         for ($i = $firstday -1; $i < $lastday; $i++)
         {
            if ($row2['template'][$i] == $cntabsence)
               $count += 1 * $factor;
         }
         //echo "<script type=\"text/javascript\">alert(\"Debug: ".$row2['template']." | ".$count."\");</script>";
      }
      if ($month == 12)
      {
         $year++;
         $month = 1;
      }
      else
      {
         $month++;
      }
      $firstday = 1;
   }

   return $count;
}

/**
 * Counts all business days or man days in a given time period
 *
 * @param   boolean $cntManDays  Switch whether to multiply the business days by the
 *                               amount of users and return that value instead
 * @param   string $cntfrom      Date to count from (including)
 * @param   string $cntto        Date to count to (including)
 * @return  boolean              True if reached, false if not
 */
function countBusinessDays($cntfrom, $cntto, $cntManDays = 0) {
   global $CONF;
   require_once ($CONF['app_root'] . "includes/tcholiday.class.php");
   require_once ($CONF['app_root'] . "includes/tcmonth.class.php");
   require_once ($CONF['app_root'] . "includes/tcuser.class.php");

   $H = new tcHoliday;
   $M = new tcMonth;
   $U = new tcUser;

   // Figure out starting month and ending month
   $startyearmonth = intval(substr($cntfrom, 0, 6));
   $startday = intval(substr($cntfrom, 6, 2));
   $endyearmonth = intval(substr($cntto, 0, 6));
   $endday = intval(substr($cntto, 6, 2));

   // Now count
   $count = 0;
   $yearmonth = $startyearmonth;
   $firstday = $startday;
   if ($firstday < 1 || $firstday > 31) $firstday = 1;

   while ($yearmonth <= $endyearmonth) {
      $queryM = "SELECT * FROM `" . $M->table . "` WHERE `yearmonth`='" . $yearmonth . "';";
      $resultM = $M->db->db_query($queryM);
      while ($rowM = $M->db->db_fetch_array($resultM, MYSQL_ASSOC)) {
         if ($yearmonth == $endyearmonth) {
            // This is the last template. Make sure we just read it up to the specified endday.
            if ($endday < strlen($rowM['template']))
               $lastday = $endday;
            else
               $lastday = strlen($rowM['template']);
         }
         else {
            $lastday = strlen($rowM['template']);
         }
         for ($i = $firstday-1; $i < $lastday; $i++) {
            $H->findBySymbol($rowM['template'][$i]);
            if ($H->checkOptions($CONF['H_BUSINESSDAY'])) {
               /*
                * This daytype counts as a business day
                */
               $count++;
            }
         }
      }
      if (intval(substr($yearmonth, 4, 2)) == 12) {
         $year = intval(substr($yearmonth, 0, 4));
         $year++;
         $yearmonth = strval($year) . "01";
      }
      else {
         $year = intval(substr($yearmonth, 0, 4));
         $month = intval(substr($yearmonth, 4, 2));
         $month++;
         $yearmonth = strval($year) . sprintf("%02d",strval($month));
      }
      $firstday = 1;
   }

   if ($cntManDays) {
      /*
       * Now we know the remaining amount of business days left in this period.
       * In order to get the remaining man days we need to multiply that amount
       * with all user in the calendar (not the admin and not those who are hidden
       * from the calendar).
       */
      $queryU = "SELECT * FROM `" . $U->table . "` WHERE `username`!='admin';";
      $resultU = $U->db->db_query($queryU);
      $usercount = 0;
      while ($rowU = $U->db->db_fetch_array($resultU, MYSQL_ASSOC)) {
         if (!$U->checkStatus($CONF['USHIDDEN']))
            $usercount++;
      }
      return $count * $usercount;
   }
   else {
      return $count;
   }
}

/**
 * Reads the current theme default css (default.css) file and adds/replaces
 * the holiday and absence based styles in the database.
 *
 * @param   string $theme  Name of the TeamCal Pro theme to process
 *
 */
function createCSS($theme) {
   global $CONF;
   require_once ($CONF['app_root'] . "includes/csshandler.class.php");
   require_once ($CONF['app_root'] . "includes/tcconfig.class.php");
   require_once ($CONF['app_root'] . "includes/tcabsence.class.php");
   require_once ($CONF['app_root'] . "includes/tcholiday.class.php");
   require_once ($CONF['app_root'] . "includes/tcstyles.class.php");

   $A   = new tcAbsence;
   $H   = new tcHoliday;
   $CSS = new cssHandler;
   $C   = new tcConfig;
   $S   = new tcStyles;

   /**
    * Read the theme css file into the CSS array
    */
   $CSS->parseFile("themes/".$theme."/css/default.css");
   $CSS->setKey(".noscreen","display: none;");

   /**
    * Create the today based styles in the array
    */
   $readkey=$CSS->getKeyProperties("td.daynum");
   $CSS->setKey("td.todaynum"," ".$readkey);
   $CSS->setProperty("td.todaynum","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.todaynum","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.weekday");
   $CSS->setKey("td.toweekday"," ".$readkey);
   $CSS->setProperty("td.toweekday","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.toweekday","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.weekday-note");
   $CSS->setKey("td.toweekday-note"," ".$readkey);
   $CSS->setProperty("td.toweekday-note","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.toweekday-note","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.weekday-bday");
   $CSS->setKey("td.toweekday-bday"," ".$readkey);
   $CSS->setProperty("td.toweekday-bday","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.toweekday-bday","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.weekday-bdaynote");
   $CSS->setKey("td.toweekday-bdaynote"," ".$readkey);
   $CSS->setProperty("td.toweekday-bdaynote","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.toweekday-bdaynote","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.day");
   $CSS->setKey("td.today"," ".$readkey);
   $CSS->setProperty("td.today","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.today","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.day-note");
   $CSS->setKey("td.today-note"," ".$readkey);
   $CSS->setProperty("td.today-note","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.today-note","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.day-bday");
   $CSS->setKey("td.today-bday"," ".$readkey);
   $CSS->setProperty("td.today-bday","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.today-bday","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.day-bdaynote");
   $CSS->setKey("td.today-bdaynote"," ".$readkey);
   $CSS->setProperty("td.today-bdaynote","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.today-bdaynote","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.day-sum-present");
   $CSS->setKey("td.today-sum-present"," ".$readkey);
   $CSS->setProperty("td.today-sum-present","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.today-sum-present","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.day-sum-absent");
   $CSS->setKey("td.today-sum-absent"," ".$readkey);
   $CSS->setProperty("td.today-sum-absent","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.today-sum-absent","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.day-sum-delta-negative");
   $CSS->setKey("td.today-sum-delta-negative"," ".$readkey);
   $CSS->setProperty("td.today-sum-delta-negative","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.today-sum-delta-negative","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.day-sum-delta-positive");
   $CSS->setKey("td.today-sum-delta-positive"," ".$readkey);
   $CSS->setProperty("td.today-sum-delta-positive","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.today-sum-delta-positive","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.day-day-absent");
   $CSS->setKey("td.today-day-absent"," ".$readkey);
   $CSS->setProperty("td.today-day-absent","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.today-day-absent","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   $readkey=$CSS->getKeyProperties("td.legend");
   $CSS->setKey("td.legend-today"," ".$readkey);
   $CSS->setProperty("td.legend-today","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   $CSS->setProperty("td.legend-today","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

   /**
    * Add/replace/change the holiday based styles in the array
    */
   $holidays = $H->getAll();
   foreach ($holidays as $hol) {
      $H->findByName($hol['cfgname']);

      $readkey=$CSS->getKeyProperties("td.daynum");
      $CSS->setKey("td.daynum-".$H->cfgname," ".$readkey);
      $CSS->setProperty("td.daynum-".$H->cfgname,"background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.daynum-".$H->cfgname,"color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.todaynum");
      $CSS->setKey("td.todaynum-".$H->cfgname," ".$readkey);
      $CSS->setProperty("td.todaynum-".$H->cfgname,"color","#".$H->dspcolor);
      $CSS->setProperty("td.todaynum-".$H->cfgname,"background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.todaynum-".$H->cfgname,"border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.todaynum-".$H->cfgname,"border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.weekday");
      $CSS->setKey("td.weekday-".$H->cfgname," ".$readkey);
      $CSS->setProperty("td.weekday-".$H->cfgname,"background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.weekday-".$H->cfgname,"color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.weekday-note");
      $CSS->setKey("td.weekday-".$H->cfgname."-note"," ".$readkey);
      $CSS->setProperty("td.weekday-".$H->cfgname."-note","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.weekday-".$H->cfgname."-note","color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.weekday-bdaynote");
      $CSS->setKey("td.weekday-".$H->cfgname."-bdaynote"," ".$readkey);
      $CSS->setProperty("td.weekday-".$H->cfgname."-bdaynote","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.weekday-".$H->cfgname."-bdaynote","color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.toweekday");
      $CSS->setKey("td.toweekday-".$H->cfgname," ".$readkey);
      $CSS->setProperty("td.toweekday-".$H->cfgname,"background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.toweekday-".$H->cfgname,"color","#".$H->dspcolor);
      $CSS->setProperty("td.toweekday-".$H->cfgname,"border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.toweekday-".$H->cfgname,"border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.toweekday-note");
      $CSS->setKey("td.toweekday-".$H->cfgname."-note"," ".$readkey);
      $CSS->setProperty("td.toweekday-".$H->cfgname."-note","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.toweekday-".$H->cfgname."-note","color","#".$H->dspcolor);
      $CSS->setProperty("td.toweekday-".$H->cfgname."-note","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.toweekday-".$H->cfgname."-note","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.toweekday-bday");
      $CSS->setKey("td.toweekday-".$H->cfgname."-bday"," ".$readkey);
      $CSS->setProperty("td.toweekday-".$H->cfgname."-bday","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.toweekday-".$H->cfgname."-bday","color","#".$H->dspcolor);
      $CSS->setProperty("td.toweekday-".$H->cfgname."-bday","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.toweekday-".$H->cfgname."-bday","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.toweekday-bdaynote");
      $CSS->setKey("td.toweekday-".$H->cfgname."-bdaynote"," ".$readkey);
      $CSS->setProperty("td.toweekday-".$H->cfgname."-bdaynote","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.toweekday-".$H->cfgname."-bdaynote","color","#".$H->dspcolor);
      $CSS->setProperty("td.toweekday-".$H->cfgname."-bdaynote","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.toweekday-".$H->cfgname."-bdaynote","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.day");
      $CSS->setKey("td.day-".$H->cfgname," ".$readkey);
      $CSS->setProperty("td.day-".$H->cfgname,"background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.day-".$H->cfgname,"color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.day-note");
      $CSS->setKey("td.day-".$H->cfgname."-note"," ".$readkey);
      $CSS->setProperty("td.day-".$H->cfgname."-note","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.day-".$H->cfgname."-note","color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.day-bday");
      $CSS->setKey("td.day-".$H->cfgname."-bday"," ".$readkey);
      $CSS->setProperty("td.day-".$H->cfgname."-bday","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.day-".$H->cfgname."-bday","color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.day-bdaynote");
      $CSS->setKey("td.day-".$H->cfgname."-bdaynote"," ".$readkey);
      $CSS->setProperty("td.day-".$H->cfgname."-bdaynote","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.day-".$H->cfgname."-bdaynote","color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.day-sum-present");
      $CSS->setKey("td.day-".$H->cfgname."-sum-present"," ".$readkey);
      $CSS->setProperty("td.day-".$H->cfgname."-sum-present","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.day-".$H->cfgname."-sum-present","color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.day-sum-absent");
      $CSS->setKey("td.day-".$H->cfgname."-sum-absent"," ".$readkey);
      $CSS->setProperty("td.day-".$H->cfgname."-sum-absent","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.day-".$H->cfgname."-sum-absent","color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.day-sum-delta-negative");
      $CSS->setKey("td.day-".$H->cfgname."-sum-delta-negative"," ".$readkey);
      $CSS->setProperty("td.day-".$H->cfgname."-sum-delta-negative","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.day-".$H->cfgname."-sum-delta-negative","color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.day-sum-delta-positive");
      $CSS->setKey("td.day-".$H->cfgname."-sum-delta-positive"," ".$readkey);
      $CSS->setProperty("td.day-".$H->cfgname."-sum-delta-positive","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.day-".$H->cfgname."-sum-delta-positive","color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.day-day-absent");
      $CSS->setKey("td.day-".$H->cfgname."-day-absent"," ".$readkey);
      $CSS->setProperty("td.day-".$H->cfgname."-day-absent","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.day-".$H->cfgname."-day-absent","color","#".$H->dspcolor);

      $readkey=$CSS->getKeyProperties("td.today");
      $CSS->setKey("td.today-".$H->cfgname," ".$readkey);
      $CSS->setProperty("td.today-".$H->cfgname,"background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.today-".$H->cfgname,"color","#".$H->dspcolor);
      $CSS->setProperty("td.today-".$H->cfgname,"border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.today-".$H->cfgname,"border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.today-note");
      $CSS->setKey("td.today-".$H->cfgname."-note"," ".$readkey);
      $CSS->setProperty("td.today-".$H->cfgname."-note","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-note","color","#".$H->dspcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-note","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.today-".$H->cfgname."-note","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.today-bday");
      $CSS->setKey("td.today-".$H->cfgname."-bday"," ".$readkey);
      $CSS->setProperty("td.today-".$H->cfgname."-bday","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-bday","color","#".$H->dspcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-bday","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.today-".$H->cfgname."-bday","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.today-bdaynote");
      $CSS->setKey("td.today-".$H->cfgname."-bdaynote"," ".$readkey);
      $CSS->setProperty("td.today-".$H->cfgname."-bdaynote","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-bdaynote","color","#".$H->dspcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-bdaynote","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.today-".$H->cfgname."-bdaynote","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.today-sum-present");
      $CSS->setKey("td.today-".$H->cfgname."-sum-present"," ".$readkey);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-present","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-present","color","#".$H->dspcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-present","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.today-".$H->cfgname."-sum-present","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.today-sum-absent");
      $CSS->setKey("td.today-".$H->cfgname."-sum-absent"," ".$readkey);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-absent","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-absent","color","#".$H->dspcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-absent","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.today-".$H->cfgname."-sum-absent","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.today-sum-delta-negative");
      $CSS->setKey("td.today-".$H->cfgname."-sum-delta-negative"," ".$readkey);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-delta-negative","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-delta-negative","color","#".$H->dspcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-delta-negative","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.today-".$H->cfgname."-sum-delta-negative","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.today-sum-delta-positive");
      $CSS->setKey("td.today-".$H->cfgname."-sum-delta-positive"," ".$readkey);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-delta-positive","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-delta-positive","color","#".$H->dspcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-sum-delta-positive","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.today-".$H->cfgname."-sum-delta-positive","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.today-day-absent");
      $CSS->setKey("td.today-".$H->cfgname."-day-absent"," ".$readkey);
      $CSS->setProperty("td.today-".$H->cfgname."-day-absent","background-color","#".$H->dspbgcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-day-absent","color","#".$H->dspcolor);
      $CSS->setProperty("td.today-".$H->cfgname."-day-absent","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.today-".$H->cfgname."-day-absent","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   }

   /**
    * Add/replace/change the absence based styles in the array
    */
   $absences = $A->getAll();
   foreach ($absences as $abs) {
      $A->findByName($abs['cfgname']);
      
      $readkey=$CSS->getKeyProperties("td.day");
      $CSS->setKey("td.".$A->cfgname," ".$readkey);
      $CSS->setProperty("td.".$A->cfgname,"background-color","#".$A->dspbgcolor);
      $CSS->setProperty("td.".$A->cfgname,"color","#".$A->dspcolor);

      $readkey=$CSS->getKeyProperties("td.day-note");
      $CSS->setKey("td.".$A->cfgname."-note"," ".$readkey);
      $CSS->setProperty("td.".$A->cfgname."-note","background-color","#".$A->dspbgcolor);
      $CSS->setProperty("td.".$A->cfgname."-note","color","#".$A->dspcolor);

      $readkey=$CSS->getKeyProperties("td.day-bday");
      $CSS->setKey("td.".$A->cfgname."-bday"," ".$readkey);
      $CSS->setProperty("td.".$A->cfgname."-bday","background-color","#".$A->dspbgcolor);
      $CSS->setProperty("td.".$A->cfgname."-bday","color","#".$A->dspcolor);

      $readkey=$CSS->getKeyProperties("td.day-bdaynote");
      $CSS->setKey("td.".$A->cfgname."-bdaynote"," ".$readkey);
      $CSS->setProperty("td.".$A->cfgname."-bdaynote","background-color","#".$A->dspbgcolor);
      $CSS->setProperty("td.".$A->cfgname."-bdaynote","color","#".$A->dspcolor);

      $readkey=$CSS->getKeyProperties("td.today");
      $CSS->setKey("td.to".$A->cfgname," ".$readkey);
      $CSS->setProperty("td.to".$A->cfgname,"background-color","#".$A->dspbgcolor);
      $CSS->setProperty("td.to".$A->cfgname,"color","#".$A->dspcolor);
      $CSS->setProperty("td.to".$A->cfgname,"border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.to".$A->cfgname,"border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.today-note");
      $CSS->setKey("td.to".$A->cfgname."-note"," ".$readkey);
      $CSS->setProperty("td.to".$A->cfgname."-note","background-color","#".$A->dspbgcolor);
      $CSS->setProperty("td.to".$A->cfgname."-note","color","#".$A->dspcolor);
      $CSS->setProperty("td.to".$A->cfgname."-note","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.to".$A->cfgname."-note","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.today-bday");
      $CSS->setKey("td.to".$A->cfgname."-bday"," ".$readkey);
      $CSS->setProperty("td.to".$A->cfgname."-bday","background-color","#".$A->dspbgcolor);
      $CSS->setProperty("td.to".$A->cfgname."-bday","color","#".$A->dspcolor);
      $CSS->setProperty("td.to".$A->cfgname."-bday","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.to".$A->cfgname."-bday","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));

      $readkey=$CSS->getKeyProperties("td.today-bdaynote");
      $CSS->setKey("td.to".$A->cfgname."-bdaynote"," ".$readkey);
      $CSS->setProperty("td.to".$A->cfgname."-bdaynote","background-color","#".$A->dspbgcolor);
      $CSS->setProperty("td.to".$A->cfgname."-bdaynote","color","#".$A->dspcolor);
      $CSS->setProperty("td.to".$A->cfgname."-bdaynote","border-right",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
      $CSS->setProperty("td.to".$A->cfgname."-bdaynote","border-left",$C->readConfig("todayBorderSize")."px solid #".$C->readConfig("todayBorderColor"));
   }

   /**
    * Put the whole thing together
    */
   $buffer  = "/**\r\n";
   $buffer .= " * Stylesheet created by TeamCalPro at: ".date('Y-m-d H:i:s')."\r\n";
   $buffer .= " */\r\n";
   $buffer .= $CSS->printCSS();

   /**
    * Save sylesheet to database
    */
   $buffer=str_replace("url(../img/", "url(themes/".$theme."/img/", $buffer);
   $S->saveStyle($theme,$buffer);

   /**
    * Adjust and write the new CSS file for print output
    */
   $CSS->setKey(".noprint","display: none;");
   $CSS->setKey(".noscreen","display: block;");
   $CSS->setProperty("body","size","landscape");
   $CSS->setProperty("body","background-color","#FFFFFF");
   $CSS->setProperty("body","color","#000000");
   $CSS->setProperty("table.header","display","none");
   $CSS->setProperty("table.menubar","display","none");
   $CSS->setProperty("table.menu","display","none");
   $CSS->setProperty("table.statusbar","display","none");
   $buffer  = "/**\r\n";
   $buffer .= " * Stylesheet created by TeamCalPro at: ".date('Y-m-d H:i:s')."\r\n";
   $buffer .= " */\r\n";
   $buffer .= $CSS->printCSS();

   /**
    * Save sylesheet to database
    */
   $buffer=str_replace("url(../img/", "url(themes/".$theme."/img/", $buffer);
   $S->saveStyle($theme."_print",$buffer);

}

/**
 * Creates an empty month template marking Saturdays and Sundays as weekend
 *
 * @param   string $yr  Four character string representing the year
 * @param   string $mt  Two character string representing the month
 *
 * @return  string      The template string of the month
 */
function createMonthTemplate($yr, $mt) {
   global $CONF;
   global $LANG;

   require_once ($CONF['app_root'] . "includes/tcholiday.class.php");

   $C = new tcConfig;
   $H = new tcHoliday;
   $H->findByName('busi');
   $busisym = $H->cfgsym;
   $H->findByName('wend');
   $wendsym = $H->cfgsym;

   /*
    * Create a timestamp for the given year and month (using day 1 of the
    * month) and use it to get some relevant information using date() and
    * getdate()
    */
   $mytime = $mt." 1,".$yr;
   $myts = strtotime($mytime);
   // Get number of days in month
   $nofdays = date("t", $myts);
   // Get first weekday of the month
   $mydate = getdate($myts);
   $monthno = sprintf("%02d", intval($mydate['mon']));
   $weekday1 = $mydate['wday'];
   if ($weekday1 == "0") $weekday1 = "7";
   $dayofweek = intval($weekday1);

   $template = "";
   for ($i = 1; $i <= $nofdays; $i++) {
      switch ($dayofweek) {
         case 1 : // Monday
            $template .= $busisym;
            break;
         case 2 : // Tuesday
            $template .= $busisym;
            break;
         case 3 : // Wednesday
            $template .= $busisym;
            break;
         case 4 : // Thursday
            $template .= $busisym;
            break;
         case 5 : // Friday
            $template .= $busisym;
            break;
         case 6 : // Saturday
            if ($C->readConfig("satBusi")) $template .= $busisym; else $template .= $wendsym;
            break;
         case 7 : // Sunday
            if ($C->readConfig("sunBusi")) $template .= $busisym; else $template .= $wendsym;
            break;
         default :
            $template .= $busisym;
            break;
      }
      $dayofweek += 1;
      if ($dayofweek == 8) {
         $dayofweek = 1;
      }
   }
   // Return the template
   return $template;
}

/**
 * Checks wether the maximum absences threshold is reached
 *
 * @param   string $year   Year of the day to count for
 * @param   string $month  Month of the day to count for
 * @param   string $day    Day to count for
 * @param   string $base   Threshold base: user or group
 * @param   string $group  Group to refer to in case of base=group
 * @return  boolean        True if reached, false if not
 */
function declineThresholdReached($year, $month, $day, $base, $group = '') {
   global $CONF;
   require_once ($CONF['app_root'] . "includes/tcconfig.class.php");
   require_once ($CONF['app_root'] . "includes/tcgroup.class.php");
   require_once ($CONF['app_root'] . "includes/tctemplate.class.php");
   require_once ($CONF['app_root'] . "includes/tcuser.class.php");
   require_once ($CONF['app_root'] . "includes/tcusergroup.class.php");

   $C = new tcConfig;
   $G = new tcGroup;
   $T = new tcTemplate;
   $U = new tcUser;
   $UG = new tcUserGroup;

   if ($base=="group") {
      /*
       * Count group members
       */
      $query = "SELECT * FROM " . $UG->table . " WHERE " . $UG->table . ".groupname='" . $group . "'";
      $result = $UG->db->db_query($query);
      $users = $UG->db->db_numrows($result);

      /*
       *  Count all group absences for this day
       */
      $query = "SELECT " . $T->table . ".template FROM " . $T->table . "," . $UG->table . " " .
      "WHERE " .
      "(" . $T->table . ".year='" . $year . "' AND " . $T->table . ".month='" . sprintf("%02d", $month) . "') " .
      "AND " .
      "(" . $T->table . ".username=" . $UG->table . ".username AND " . $UG->table . ".groupname='" . $group . "');";
      $result = $T->db->db_query($query);
      $absences = 0;
      while ($row = $T->db->db_fetch_array($result, MYSQL_ASSOC)) {
         if ($row['template'][$day -1] != '.')
            $absences++;
      }
   }
   else if ($base=="min_present") {
      /*
       * Count group members
       */
      $query = "SELECT * FROM " . $UG->table . " WHERE " . $UG->table . ".groupname='" . $group . "'";
      $result = $UG->db->db_query($query);
      $users = $UG->db->db_numrows($result);

      /*
       *  Count all group absences for this day
       */
      $query = "SELECT ".$T->table.".template FROM ".$T->table.",".$UG->table." " .
      "WHERE (".$T->table.".year='".$year."' AND ".$T->table.".month='".sprintf("%02d", $month)."') " .
      "AND   (".$T->table.".username=".$UG->table.".username AND ".$UG->table.".groupname='".$group."');";
      $result = $T->db->db_query($query);
      $absences = 0;
      while ($row = $T->db->db_fetch_array($result, MYSQL_ASSOC)) {
         if ($row['template'][$day-1] != '.') $absences++;
      }

      $G->findByName($group);
      if ($users-$absences < $G->min_present) return true; else return false;
   }
   else if ($base=="max_absent") {
      /*
       *  Count all group absences for this day
       */
      $query = "SELECT ".$T->table.".template FROM ".$T->table.",".$UG->table." " .
      "WHERE (".$T->table.".year='".$year."' AND ".$T->table.".month='".sprintf("%02d", $month)."') " .
      "AND (".$T->table.".username=".$UG->table.".username AND ".$UG->table.".groupname='".$group."');";
      $result = $T->db->db_query($query);
      $absences = 0;
      while ($row = $T->db->db_fetch_array($result, MYSQL_ASSOC)) {
         if ($row['template'][$day -1] != '.') $absences++;
      }

      $G->findByName($group);
      if ($absences+1 > $G->max_absent) return true; else return false;
   }
   else {
      /*
       * Count all members
       */
      $query = "SELECT * FROM " . $U->table . ";";
      $result = $U->db->db_query($query);
      $users = $U->db->db_numrows($result) - 1; // Subtract Admin

      /*
       *  Count all absences for this day
       */
      $query = "SELECT * FROM `" . $T->table . "` " .
      "WHERE `year`='" . $year . "' " .
      "AND `month`='" . sprintf("%02d", $month) . "';";
      $result = $T->db->db_query($query);
      $absences = 0;
      while ($row = $T->db->db_fetch_array($result, MYSQL_ASSOC)) {
         if ($row['template'][$day -1] != '.')
            $absences++;
      }
   }

   /*
    *  Ccheck absences against threshold
    */
   $absencerate = ((100 * $absences) / $users);
   $threshold = intval($C->readConfig("declThreshold"));
   //echo "<script type=\"text/javascript\">alert(\"Threshold ".$absencerate." : ".$threshold."\");</script>";
   if ($absencerate >= $threshold) {
      return true;
   }
   else {
      return false;
   }

}

/**
 * Generates a password
 *
 * @param   integer $length    Desired password length
 * @return  string             Password
 */
function generatePassword($length=9)
{
   $characters = 'abcdefghjklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789@#$%';

   $password = '';
   for ($i = 0; $i < $length; $i++) {
         $password .= $characters[(rand() % strlen($characters))];
   }
   return $password;
}

/**
 * Extracts the file extension from a given file name
 *
 * @param string str String containing the path or filename
 * @return string File extension of the string passed
 */
function getFileExtension($str) {
   $i = strrpos($str,".");
   if (!$i) return "";
   $l = strlen($str) - $i;
   $ext = substr($str,$i+1,$l);
   return $ext;
}

/**
 * Gets the number of days in a given month
 *
 * @param   string $yr  Four character string representing the year
 * @param   string $mt  Two character string representing the month
 *
 * @return  array       [monthno, days, weekday1]
 */
function getMonthInfo($yr, $mt) {
   $mytime = $mt . " 1," . $yr;
   $myts = strtotime($mytime);
   $mydate = getdate($myts);
   $mi['monthno']  = sprintf("%02d",intval($mydate['mon']));
   $mi['nofdays']  = date("t", $myts);
   $mi['weekday1'] = $mydate['wday'];
   return $mi;
}

/**
 * Gets all language directory names from the TeamCal Pro language directory
 *
 * @return array Array containing the names
 */
function getLanguages() {
   $mydir = "includes/lang/";
   $handle = opendir($mydir); // open directory
   $fileidx = 0;
   while (false !== ($file = readdir($handle))) {
      if (!is_dir($mydir . "/$file") && $file != "." && $file != "..") {
         $filearray[$fileidx]["name"] = $file;
         $fileidx++;
      }
   }
   closedir($handle);

   // If there are language files
   if ($fileidx > 0) {
      // Extract the language name
      // Filename mus follow the format "english.tcpro.php"
      for ($i = 0; $i < $fileidx; $i++) {
         $langName = explode(".", $filearray[$i]["name"]);
         if ($langName[1] == "tcpro" && $langName[2] == "php") {
            $langarray[$i] = $langName[0];
         }
      }
   }
   return $langarray;
}

/**
 * Gets all $_REQUEST and $_POST parameters and fills the $CONF['options'][] array
 *
 */
function getOptions() {
   global $CONF;
   global $_REQUEST;
   global $_POST;

   require_once ($CONF['app_root'] . "includes/tcconfig.class.php");
   require_once ($CONF['app_root'] . "includes/tcabsence.class.php");
   require_once ($CONF['app_root'] . "includes/tcgroup.class.php");
   require_once ($CONF['app_root'] . "includes/tclogin.class.php");
   require_once ($CONF['app_root'] . "includes/tcregion.class.php");
   require_once ($CONF['app_root'] . "includes/tcuser.class.php");
   require_once ($CONF['app_root'] . "includes/tcuseroption.class.php");

   $A = new tcAbsence;
   $C = new tcConfig;
   $G = new tcGroup;
   $L = new tcLogin;
   $R = new tcRegion;
   $UL = new tcUser;
   $UO = new tcUserOption;
   $user = $L->checkLogin();

   /**
    * Set defaults
    */
   $today = getdate();
   $CONF['options'] = array(
      "lang"=>'english',
      "groupfilter"=>'All',
      "region"=>'default',
      "absencefilter"=>'All',
      "month_id"=>$today['mon'],
      "year_id"=>$today['year'],
      "show_id"=>1,
      "summary"=>'',
      "remainder"=>'',
   );

   if (!$C->readConfig("defgroupfilter")) $C->saveConfig("defgroupfilter","All");
   else $CONF['options']['groupfilter'] = $C->readConfig("defgroupfilter");

   if (!$C->readConfig("defregion")) $C->saveConfig("defregion","default");
   else $CONF['options']['region'] = $C->readConfig("defregion");

   $CONF['options']['show_id'] = $C->readConfig("showMonths");

   if ($C->readConfig("showRemainder")) $CONF['options']['remainder'] = "show";
   else                                 $CONF['options']['remainder'] = "hide";

   if ($C->readConfig("showSummary"))   $CONF['options']['summary'] = "show";
   else                                 $CONF['options']['summary'] = "hide";

   /**
    * Get user preferences
    */
   if ($userlang = $UO->find($user, "language")) $CONF['options']['lang'] = $userlang;

   if ($userprefgroup = $UO->find($user, "defgroup")) {
      if ($userprefgroup=="default") $CONF['options']['groupfilter'] = $C->readConfig("defgroupfilter");
      else                           $CONF['options']['groupfilter'] = $userprefgroup;
   }

   if ($userregion = $UO->find($user, "defregion")) {
      if ($userregion!="default") $CONF['options']['region'] = $userregion;
   }

   /**
    * DEBUG: Set to TRUE for debug info
    */
   if (FALSE) {
      $debug ="After Preferences\\r\\n";
      $debug.="tc_config['options']['lang'] = ".$CONF['options']['lang']."\\r\\n";
      $debug.="tc_config['options']['groupfilter'] = ".$CONF['options']['groupfilter']."\\r\\n";
      $debug.="tc_config['options']['region'] = ".$CONF['options']['region']."\\r\\n";
      $debug.="tc_config['options']['month_id'] = ".$CONF['options']['month_id']."\\r\\n";
      $debug.="tc_config['options']['year_id'] = ".$CONF['options']['year_id']."\\r\\n";
      $debug.="tc_config['options']['show_id'] = ".$CONF['options']['show_id']."\\r\\n";
      $debug.="tc_config['options']['summary'] = ".$CONF['options']['summary']."\\r\\n";
      $debug.="tc_config['options']['remainder'] = ".$CONF['options']['remainder']."\\r\\n";
      echo "<script type=\"text/javascript\">alert(\"".$debug."\");</script>";
   }

   /**
    * Get $_REQUEST (overwriting user preferences)
    */
   if (isset ($_REQUEST['lang']) AND strlen($_REQUEST['lang'])
       AND in_array($_REQUEST['lang'],getLanguages())
      )
      $CONF['options']['lang'] = trim($_REQUEST['lang']);

   if (isset ($_REQUEST['groupfilter']) AND strlen($_REQUEST['groupfilter'])
       AND (in_array($_REQUEST['groupfilter'],$G->getGroups())
            OR $_REQUEST['groupfilter']=="All"
            OR $_REQUEST['groupfilter']=="Allbygroup"
           )
      )
      $CONF['options']['groupfilter'] = trim($_REQUEST['groupfilter']);

   if (isset ($_REQUEST['region']) && strlen($_REQUEST['region'])
       AND in_array($_REQUEST['region'],$R->getRegions())
      )
      $CONF['options']['region'] = trim($_REQUEST['region']);

   if (isset ($_REQUEST['absencefilter']) && strlen($_REQUEST['absencefilter'])
       AND in_array($_REQUEST['absencefilter'],$A->getAbsences())
      )
      $CONF['options']['absencefilter'] = trim($_REQUEST['absencefilter']);

   $mo = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12");
   if (isset ($_REQUEST['month_id']) && strlen($_REQUEST['month_id'])
       AND in_array($_REQUEST['month_id'],$mo)
      )
      $CONF['options']['month_id'] = intval($_REQUEST['month_id']);

   if (isset ($_REQUEST['year_id']) && strlen($_REQUEST['year_id'])
       AND is_numeric($_REQUEST['year_id'])
      )
      $CONF['options']['year_id'] = intval($_REQUEST['year_id']);

   $sho = array("1", "2", "3", "6", "12");
   if (isset ($_REQUEST['show_id']) && strlen($_REQUEST['show_id'])
       AND in_array($_REQUEST['show_id'],$sho)
      )
      $CONF['options']['show_id'] = intval($_REQUEST['show_id']);

   $showhide = array("show", "hide");
   if (isset ($_REQUEST['summary']) && strlen($_REQUEST['summary'])
       AND in_array($_REQUEST['summary'],$showhide)
      )
      $CONF['options']['summary'] = trim($_REQUEST['summary']);

   if (isset ($_REQUEST['remainder']) && strlen($_REQUEST['remainder'])
       AND in_array($_REQUEST['remainder'],$showhide)
      )
      $CONF['options']['remainder'] = trim($_REQUEST['remainder']);

   /**
    * DEBUG: Set to TRUE for debug info
    */
   if (FALSE) {
      $debug ="After _REQUEST\\r\\n";
      $debug.="tc_config['options']['lang'] = ".$CONF['options']['lang']."\\r\\n";
      $debug.="tc_config['options']['groupfilter'] = ".$CONF['options']['groupfilter']."\\r\\n";
      $debug.="tc_config['options']['region'] = ".$CONF['options']['region']."\\r\\n";
      $debug.="tc_config['options']['month_id'] = ".$CONF['options']['month_id']."\\r\\n";
      $debug.="tc_config['options']['year_id'] = ".$CONF['options']['year_id']."\\r\\n";
      $debug.="tc_config['options']['show_id'] = ".$CONF['options']['show_id']."\\r\\n";
      $debug.="tc_config['options']['summary'] = ".$CONF['options']['summary']."\\r\\n";
      $debug.="tc_config['options']['remainder'] = ".$CONF['options']['remainder']."\\r\\n";
      echo "<script type=\"text/javascript\">alert(\"".$debug."\");</script>";
   }

   /**
    * Now get $_POST (overwrites $_REQUEST and user preferences)
    */
   if (isset ($_POST['user_lang']) && strlen($_POST['user_lang']) AND in_array($_POST['user_lang'],getLanguages()))
      $CONF['options']['lang'] = trim($_POST['user_lang']);

   if (isset ($_POST['groupfilter']) && strlen($_POST['groupfilter']) AND (in_array($_POST['groupfilter'],$G->getGroups()) OR $_POST['groupfilter']=="Allbygroup") )
      $CONF['options']['groupfilter'] = trim($_POST['groupfilter']);

   if (isset ($_POST['regionfilter']) && strlen($_POST['regionfilter']) AND in_array($_POST['regionfilter'],$R->getRegions()))
      $CONF['options']['region'] = trim($_POST['regionfilter']);

   if (isset ($_POST['absencefilter']) && strlen($_POST['absencefilter']) AND in_array($_POST['absencefilter'],$A->getAbsences()))
      $CONF['options']['absencefilter'] = trim($_POST['absencefilter']);

   if (isset ($_POST['month_id']) && strlen($_POST['month_id']) AND in_array($_POST['month_id'],$mo))
      $CONF['options']['month_id'] = intval($_POST['month_id']);

   if (isset ($_POST['year_id']) && strlen($_POST['year_id']) AND is_numeric($_POST['year_id']))
      $CONF['options']['year_id'] = intval($_POST['year_id']);

   if (isset ($_POST['show_id']) && strlen($_POST['show_id']) AND in_array($_POST['show_id'],$sho))
      $CONF['options']['show_id'] = intval($_POST['show_id']);

   /**
    * DEBUG: Set to TRUE for debug info
    */
   if (FALSE) {
      $debug ="After _POST\\r\\n";
      $debug.="tc_config['options']['lang'] = ".$CONF['options']['lang']."\\r\\n";
      $debug.="tc_config['options']['groupfilter'] = ".$CONF['options']['groupfilter']."\\r\\n";
      $debug.="tc_config['options']['region'] = ".$CONF['options']['region']."\\r\\n";
      $debug.="tc_config['options']['month_id'] = ".$CONF['options']['month_id']."\\r\\n";
      $debug.="tc_config['options']['year_id'] = ".$CONF['options']['year_id']."\\r\\n";
      $debug.="tc_config['options']['show_id'] = ".$CONF['options']['show_id']."\\r\\n";
      $debug.="tc_config['options']['summary'] = ".$CONF['options']['summary']."\\r\\n";
      $debug.="tc_config['options']['remainder'] = ".$CONF['options']['remainder']."\\r\\n";
      echo "<script type=\"text/javascript\">alert(\"".$debug."\");</script>";
   }

   /**
    * Now we have the language. Check if an according help file exists. If not
    * default to English
    */
   if (file_exists("includes/help/" . $CONF['options']['lang'] . "/html/index.html")) {
      $CONF['options']['helplang'] = $CONF['options']['lang'];
   }
   else {
      $CONF['options']['helplang'] = "english";
   }

   /**
    * Overlib Settings based on theme
    */
   $CONF['ovl_tt_bgbackground'] = 'BGBACKGROUND, \''.$CONF['app_url'].'/themes/'.$C->readConfig("theme").'/img/bg_tooltip.gif\', ';
   $CONF['ovl_tt_capicon'] = 'CAPICON, \''.$CONF['app_url'].'/themes/'.$C->readConfig("theme").'/img/ico_tt.png\', ';
   $CONF['ovl_tt_settings'] = $CONF['ovl_tt_snap'].$CONF['ovl_tt_cellpad'].$CONF['ovl_tt_bgbackground'].$CONF['ovl_tt_capicon'].$CONF['ovl_tt_capcolor'].$CONF['ovl_tt_caption'].$CONF['ovl_tt_captionfont'].$CONF['ovl_tt_captionsize'].$CONF['ovl_tt_fgcolor'];

   /**
    * Time Zone
    */
   if (($myTimeZone=$C->readConfig("timeZone"))!="default")
      putenv("TZ=".$myTimeZone);
   else
      putenv("TZ=");
}

/**
 * Gets all theme directory names from the TeamCal Pro theme directory
 *
 * @return array Array containing the names
 */
function getThemes() {
   $themedir = "themes/";
   $handle = opendir($themedir); // open directory
   $diridx = 0;
   while (false !== ($dir = readdir($handle))) {
      if (is_dir($themedir . "/$dir") && $dir != "." && $dir != "..") {
         $dirarray[$diridx]["name"] = $dir;
         $diridx++;
      }
   }
   closedir($handle);
   return $dirarray;
}

/**
 * Checks wether a bit combination is set in a given bitmask
 *
 * @param   integer $flagset  Target to check
 * @param   integer $bitmask  Bit combination to check
 * @return  boolean           True if set, false if not
 */
function isFlag($flagset, $bitmask) {
   if ($flagset & $bitmask)
      return true;
   else
      return false;
}

/**
 * Uses Javascript to close the current window and reload the calling page
 * without the previous POST parameters
 *
 * @param string $page URL to redirect to
 */
function jsCloseAndReload($page = 'index.php') {
   global $CONF;
   echo "<html>" .
   "<head></head>" .
   "<body>" .
   "   <script type=\"text/javascript\" type=\"javascript\">" .
   "      opener.location.href=\"" . $page . "\";" .
   "      self.close();" .
   "   </script>" .
   "</body>" .
   "</html>";
}

/**
 * Uses Javascript to reload a page without the previous POST parameters
 *
 * @param string $page URL to redirect to
 */
function jsReload($page = "index.php") {
   global $CONF;
   echo "<html>" .
   "<head></head>" .
   "<body>" .
   "   <script type=\"text/javascript\" type=\"javascript\">" .
   "      location.href=\"" . $page . "\";" .
   "   </script>" .
   "</body>" .
   "</html>";
}

/**
 * Sends a HTTP redirect instruction to the browser via http-equiv
 *
 * @param string $url URL to redirect to
 */
function jsReloadPage($url = '') {
   echo "<html>" .
   "   <head>" .
   "      <meta http-equiv=\"refresh\" content=\"0;URL=" . $url . "\">" .
   "   </head>" .
   "   <body></body>" .
   "</html>";
}

/**
 * Prints the top HTML code of a dialog
 *
 * @param  string $title     The title of the dialog
 * @param  string $helpfile  The name of the help file to be linked to from the help icon
 * @param  string $icon      The icon to appear left of the dialog title
 */
function printDialogTop($title = '', $helpfile = '', $icon = '') {
   global $CONF, $LANG, $theme;
   getOptions();
   echo '
         <table style="border-collapse: collapse; border: 0px; width: 100%;">
            <tr>
               <td class="dlg-header">';
   if (strlen($icon))
      echo '
                  <img src="themes/'.$theme.'/img/' . $icon . '" alt="" width="16" height="16" align="top">&nbsp;';
   echo $title . "</td>";
   if (strlen($helpfile))
      echo '
                  <td align="right" style="font-size: 9pt; background-color: inherit;">
                     <div align="right">
                        <a href="javascript:this.blur();openPopup(\'help/' . $CONF['options']['helplang'] . '/html/index.html?' . $helpfile . '\',\'help\',\'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=1024,height=640\');">
                        <img class="noprint" title="' . $title . ' ' . $LANG['mnu_help'] . '..." align="middle" alt="" src="themes/'.$theme.'/img/ico_help.png" width="16" height="16" border="0"></a>
                     </div>
                  </td>';
   echo '
            </tr>
         </table>
         ';
}

/**
 * Scans a given directory for files. Optionally you can specify an array of
 * extension to look for.
 *
 * @param   string $myDir     Directory name to scan
 * @param   string $myExt     Array of extensions to scan for
 * @return  array Array containing the names of the files (optionally matching one of the extension in myExt)
 */
function scanDirectory($myDir, $myExt = NULL) {

   $myDir = rtrim($myDir, "/");
   $dir = opendir($myDir);
   while (false !== ($filename = readdir($dir)))
      $files[] = strtolower($filename);

   foreach ($files as $pos => $file) {
      if (is_dir($file)) {
         $dirs[] = $file;
         unset ($files[$pos]);
      }
   }

   if (count($myExt)) {
      if (count($files)) {
         foreach ($files as $pos => $file) {
            $thisExt = explode(".", $file);
            if (in_array($thisExt[1], $myExt)) {
               $filearray[] = $file;
            }
         }
      }
      return $filearray;
   }
   else {
      return $files;
   }
}

/**
 * If a user was added or updated we send him an info to let him know.
 * Esepcially when he was added he needs to know what URL to navigate to and
 * how to login.
 *
 * @param  string $uname  The username created
 * @param  string $pwd    The password created
 */
function sendAccountCreatedMail($uname, $pwd) {
   global $CONF;
   global $LANG;

   require_once ($CONF['app_root'] . "includes/tcuser.class.php");
   require_once ($CONF['app_root'] . "includes/tcconfig.class.php");

   $C = new tcConfig;
   $U = new tcUser;

   if ($U->findByName($uname)) {
      $message = '';
      $subject = $LANG['user_add_subject'];
      $message = $LANG['user_add_greeting'];
      $message .= $LANG['user_add_info_1'];
      $message .= $U->username;
      $message .= $LANG['user_add_info_2'] . $pwd;
      $message .= $LANG['user_add_info_3'];
      $to = $U->email;
      $headers = "From: " . $C->readConfig("mailFrom") . "\r\n" . "Reply-To: " . $C->readConfig("mailReply") . "\r\n";
      if ($C->readConfig("emailNotifications")) {
         if ($C->readConfig("mailSMTP")) sendSMTPmail($C->readConfig("mailFrom"), $to, stripslashes($subject), stripslashes($message));
         else mail($to, stripslashes($subject), stripslashes($message), $headers);
      }
   }
}

/**
 * Sends a notification eMail to one ore more users based on the type given
 *
 * @param  string $type          Type of notification
 * @param  string $object        Object of the activity. Listed at the bottom of the message
 * @param  string $grouptouched  Affected group for group notification
 * @param  string $addlinfo      Additional info in case needed
 */
function sendNotification($type, $object, $grouptouched = '', $addlinfo = '') {
   global $CONF;
   global $LANG;

   require_once ($CONF['app_root'] . "includes/tcconfig.class.php");
   require_once ($CONF['app_root'] . "includes/tcuser.class.php");

   $C = new tcConfig;
   $U = new tcUser;

   /*
    * Now we're gonna send a mail to every user who wants to be notified
    * about this change. Each user can set that option in his profile
    */
   $query = "SELECT * FROM `" . $CONF['db_table_users'] . "` ORDER BY `username`;";
   $result = $U->db->db_query($query);
   $i = 1;
   while ($row = $U->db->db_fetch_array($result, MYSQL_ASSOC)) {
      $notify = $row['notify'];
      $notifygroup = $row['notify_group'];
      $sendmail = false;
      switch (strtolower($type)) {
         case "useradd" :
            if (($notify & $CONF['userchg']) == $CONF['userchg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_usr_msg'];
               $message .= $LANG['notification_usr_add_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "userchange" :
            if (($notify & $CONF['userchg']) == $CONF['userchg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_usr_msg'];
               $message .= $LANG['notification_usr_chg_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "userdelete" :
            if (($notify & $CONF['userchg']) == $CONF['userchg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_usr_msg'];
               $message .= $LANG['notification_usr_del_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "groupadd" :
            if (($notify & $CONF['groupchg']) == $CONF['groupchg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_grp_msg'];
               $message .= $LANG['notification_grp_add_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "groupchange" :
            if (($notify & $CONF['groupchg']) == $CONF['groupchg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_grp_msg'];
               $message .= $LANG['notification_grp_chg_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "groupdelete" :
            if (($notify & $CONF['groupchg']) == $CONF['groupchg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_grp_msg'];
               $message .= $LANG['notification_grp_del_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "monthchange" :
            if (($notify & $CONF['monthchg']) == $CONF['monthchg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_month_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "usercalchange" :
            if (($notify & $CONF['usercalchg']) == $CONF['usercalchg'] && ($notifygroup == $grouptouched || $notifygroup == "All")) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_usr_cal'];
               $message .= $LANG['notification_usr_cal_msg'];
               $message .= $object;
               $message .= "\r\n";
               $message .= $addlinfo;
               $message .= "\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "absenceadd" :
            if (($notify & $CONF['absencechg']) == $CONF['absencechg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_abs_msg'];
               $message .= $LANG['notification_abs_add_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "absencechange" :
            if (($notify & $CONF['absencechg']) == $CONF['absencechg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_abs_msg'];
               $message .= $LANG['notification_abs_chg_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "absencedelete" :
            if (($notify & $CONF['absencechg']) == $CONF['absencechg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_abs_msg'];
               $message .= $LANG['notification_abs_del_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "holidayadd" :
            if (($notify & $CONF['holidaychg']) == $CONF['holidaychg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_hol_msg'];
               $message .= $LANG['notification_hol_add_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "holidaychange" :
            if (($notify & $CONF['holidaychg']) == $CONF['holidaychg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_hol_msg'];
               $message .= $LANG['notification_hol_chg_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "holidaydelete" :
            if (($notify & $CONF['holidaychg']) == $CONF['holidaychg']) {
               $message = $LANG['notification_greeting'];
               $message .= $LANG['notification_hol_msg'];
               $message .= $LANG['notification_hol_del_msg'];
               $message .= $object . ".\r\n\r\n";
               $sendmail = true;
            }
            break;
         case "decline" :
            $message = $LANG['notification_greeting'];
            $message .= $LANG['notification_decl_msg'];
            $message .= $LANG['notification_hol_del_msg'];
            $message .= $object . ".\r\n\r\n";
            $sendmail = true;
            break;
         default :
            break;
      }

      if ($sendmail AND $C->readConfig("emailNotifications")) {
         $to = $row['email'];
         $subject = stripslashes($LANG['notification_subject']);
         $message .= stripslashes($LANG['notification_signature']);
         $headers = "From: " . $C->readConfig("mailFrom") . "\r\n" . "Reply-To: " . $C->readConfig("mailReply") . "\r\n";
         /*
          * Set to TRUE for Debug
          */
         if (FALSE) {
            echo "<textarea cols=\"100\" rows=\"12\">email:   " . $to . "\n\n".
                 "subject: " . $subject . "\n\n".
                 $type." - message: " . $message . "\n\n".
                 "headers: " . $headers . "</textarea>";
         }
         if ($C->readConfig("mailSMTP")) {
            sendSMTPmail($C->readConfig("mailFrom"), $to, $subject, $message);
         }
         else {
            mail($to, $subject, $message, $headers);
         }
      }
   }
   return;
}

/**
 * Sends an eMail via SMTP
 * Requires the PEAR Mail package installed on the server that Tcpro is run
 *
 * @param  string $from        eMail from address
 * @param  string $to          eMail to address
 * @param  string $subject     eMail subject
 * @param  string $body        eMail body
 * @return bool                SMTP success
 */
function sendSMTPmail($from, $to, $subject, $body) {
   global $CONF;
   require_once "Mail.php";

   $host     = $C->readConfig("mailSMTPHost");
   $port     = $C->readConfig("mailSMTPPort");
   $username = $C->readConfig("mailSMTPUser");
   $password = $C->readConfig("mailSMTPPassword");;

   $headers = array (
      'From' => $from,
      'To' => $to,
      'Subject' => $subject
   );

   $smtp = Mail::factory(
      'smtp',
      array (
         'host' => $host,
         'port' => $port,
         'auth' => true,
         'username' => $username,
         'password' => $password
      )
   );

   $mail = $smtp->send($to, $headers, $body);

   if (PEAR::isError($mail)) {
      echo("<p>" . $mail->getMessage() . "</p>");
      return FALSE;
   }
   else {
      return TRUE;
   }
}

/**
 * Sets a bit combination in a given bitmask
 *
 * @param   integer $flagset  Target to change
 * @param   integer $bitmask  Bitmask to set (1's in this bitmask will become 1's in the target)
 * @return  integer           New target
 */
function setFlag($flagset, $bitmask) {
   $newflagset = $flagset | $bitmask;
   return $newflagset;
}

/**
 * Builds the URL request parameters based on whats in the tc_config['options'][] array
 *
 * @return  string  URL request string
 */
function setRequests() {
   global $CONF;
   $requ = "";
   foreach ($CONF['options'] as $key => $value) {
      if (strlen($value)) $requ .= $key . "=" . $value . "&amp;";
   }
   return $requ;
}

/**
 * Shows the error page
 */
function showError($error="notallowed",$closeButton=FALSE) {
   global $CONF, $LANG, $U;
   switch($error) {
      case "notarget":
         $err_short=$LANG['err_notarget_short'];
         $err_long=$LANG['err_notarget_long'];
         $err_module=$_SERVER['SCRIPT_NAME'];
         $err_btn_close=$closeButton;
         break;
      case "notallowed":
         $err_short=$LANG['err_not_authorized_short'];
         $err_long=$LANG['err_not_authorized_long'];
         $err_module=$_SERVER['SCRIPT_NAME'];
         $err_btn_close=$closeButton;
         break;
      default:
         $err_short=$LANG['err_unspecified_short'];
         $err_long=$LANG['err_unspecified_long'];
         $err_module=$_SERVER['SCRIPT_NAME'];
         $err_btn_close=$closeButton;
         break;
   }
   require("includes/header.html.inc.php");
   echo "<body>\r\n";
   if (!$closeButton) {
      require("includes/header.application.inc.php");
      require("includes/menu.inc.php");
   }
   require("error.php");
   require("includes/footer.html.inc.php");
   die();
}

/**
 * Validate an email address.
 *
 * @param email The email address to validate
 * @return $isValid Boolean result
*/
function validEmail($email) {
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex) {
      $isValid = false;
   }
   else {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64) {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255) {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.') {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local)) {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain)) {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
         // character not valid in local part unless
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',str_replace("\\\\","",$local))) {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}
?>
