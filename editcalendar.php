<?php
/**
 * editcalendar.php
 *
 * Displays the edit calendar dialog
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
 * Includes
 */
require_once ("config.tcpro.php");
require_once ("includes/functions.tcpro.php");
getOptions();
if (strlen($CONF['options']['lang'])) require ("includes/lang/" . $CONF['options']['lang'] . ".tcpro.php");
else require ("includes/lang/english.tcpro.php");

require_once( "includes/tcabsence.class.php" );
require_once( "includes/tcabsencegroup.class.php" );
require_once( "includes/tcconfig.class.php" );
require_once( "includes/tcdaynote.class.php" );
require_once( "includes/tcgroup.class.php" );
require_once( "includes/tcholiday.class.php" );
require_once( "includes/tclogin.class.php" );
require_once( "includes/tclog.class.php" );
require_once( "includes/tcmonth.class.php" );
require_once( "includes/tctemplate.class.php" );
require_once( "includes/tcuser.class.php" );
require_once( "includes/tcusergroup.class.php" );

$A   = new tcAbsence;
$AG  = new tcAbsenceGroup;
$C   = new tcConfig;
$G   = new tcGroup;
$H   = new tcHoliday;
$L   = new tcLogin;
$LOG = new tcLog;
$M   = new tcMonth;
$N   = new tcDaynote;
$T   = new tcTemplate;
$TT  = new tcTemplate; // used for template user loop
$U   = new tcUser;
$UL  = new tcUser; // User logged in
$UT  = new tcUser; // used for template user loop
$UG  = new tcUserGroup;

$error=FALSE;
$error_decl=FALSE;
$warning=FALSE;

/**
 * Get the user that is logged in
 */
$user=$L->checkLogin();
$UL->findByName($user);
if (isset($_REQUEST['Member'])) $Member=$_REQUEST['Member'];

/**
 * Check authorization
 */
$allowed=FALSE;
if ( $user == $Member ) {
   if (isAllowed("editOwnUserCalendars")) $allowed=TRUE;
}
else if ( $UG->shareGroups($user, $Member) ) {
   if (isAllowed("editGroupUserCalendars")) $allowed=TRUE;
}
else {
   if (isAllowed("editAllUserCalendars")) $allowed=TRUE;
}

if (!$allowed) showError("notallowed");

if (isset($_REQUEST['Year']))   $Year=$_REQUEST['Year'];
if (isset($_REQUEST['Month']))  $Month=$_REQUEST['Month'];
if (isset($_REQUEST['region'])) $region=$_REQUEST['region']; else $region = $CONF['options']['region'];

/**
 * =========================================================================
 * BACKWARD
 */
if ( isset($_POST['btn_bwd']) ) {
   $Year=$_POST['hid_bwdYear'];
   $Month=$_POST['hid_bwdMonth'];
   $Member=$_POST['hid_Member'];
}

/**
 * =========================================================================
 * FORWARD
 */
if ( isset($_POST['btn_fwd']) ) {
   $Year=$_POST['hid_fwdYear'];
   $Month=$_POST['hid_fwdMonth'];
   $Member=$_POST['hid_Member'];
}

/**
 * Get month info
 */
$weekdays = $LANG['weekdays'];
$mi = getMonthInfo($Year, $Month);
$nofdays = $mi['nofdays'];
$monthno = $mi['monthno'];
$weekday1 = $mi['weekday1'];
if ($weekday1=="0") $weekday1="7";
$dayofweek = intval($weekday1);

/**
 * Prepare the Fwd/Bwd buttons
 */
if (intval($monthno)==12) {
   $fwdMonth=$CONF['monthnames'][1];
   $fwdYear=$Year+1;
}
else {
   $fwdMonth=$CONF['monthnames'][intval($monthno)+1];
   $fwdYear=$Year;
}

if (intval($monthno)==1) {
   $bwdMonth=$CONF['monthnames'][12];
   $bwdYear=$Year-1;
}
else {
   $bwdMonth=$CONF['monthnames'][intval($monthno)-1];
   $bwdYear=$Year;
}

if ( $UL->checkUserType($CONF['UTADMIN']) ) $isAdmin = TRUE; else $isAdmin = FALSE;
if ( $UL->checkUserType($CONF['UTDIRECTOR']) ) $isDirector = TRUE; else $isDirector = FALSE;
if ( $UL->checkUserType($CONF['UTMANAGER']) ) $isManager = TRUE; else $isManager = FALSE;

/**
 * Read Month Template
 */
$found = $M->findByName($region, $Year.$monthno);
if ( !$found ) {
   /**
    * Seems there is no default template for this month yet.
    * Let's create a default one.
    */
   $M->region = $region;
   $M->yearmonth = $Year.$monthno;
   $M->template = createMonthTemplate($Year,$Month);
   $M->create();
}
else if ( empty($M->template) ) {
   /**
    * Seems there is an empty default template. That can't be.
    * Let's create a default one.
    */
   $M->template = createMonthTemplate($Year,$Month);
   $M->update($region, $Year.$monthno);
}

/**
 * Get the user for this calendar
 */
$U->findByName($Member);
$notify = $U->notify;
$notifygroup = $U->notify_group;

/**
 * Try to find this user's current template for this month
 */
$found = $T->findTemplate($U->username,$Year,$monthno);
if (!$found) {
   /**
    * No template found for this user and month yet.
    * Create a default one.
    */
   $T->username = $U->username;
   $T->year = $Year;
   $T->month = $monthno;
   $T->template = "";
   for ($i=0; $i<intval($nofdays); $i++ ) {
      $T->template .= $CONF['present'];
   }
   $T->create();
   /**
    * Log this event
    */
   $LOG->log("logUser",$L->checkLogin(),"User default template created: ".$T->year.$T->month);
}

/**
 * =========================================================================
 * APPLY
 */
if (isset($_POST['btn_apply'])) {
   /**
    * Create a new blank template
    */
   $newtemplate = "";
   for ($i=0; $i<intval($nofdays); $i++ ) $newtemplate .= $CONF['present'];

   /**
    * Create an array of all requested absences
    */
   $absarray = array();
   foreach($_POST as $key=>$value)
   {
      /**
       * First get the absence check marks in the upper portion.
       * Each key there ends with a number. No other key does.
       */
      if ( is_numeric(substr($key,-1)) ) $absarray[] = $key;

      /**
       * Then get the range input
       */
      if ( $key=="rangeabs" && strlen($_POST['rangefrom']) && strlen($_POST['rangeto']) ) {
	   	$yearfrom = substr($_POST['rangefrom'],0,4);
	   	$monthfrom = substr($_POST['rangefrom'],5,2);
	   	$dayfrom = substr($_POST['rangefrom'],8,2);
	   	$yearto = substr($_POST['rangeto'],0,4);
	   	$monthto = substr($_POST['rangeto'],5,2);
	   	$dayto = substr($_POST['rangeto'],8,2);
         if ( $yearfrom!=$Year || $monthfrom!=$monthno || $yearto!=$Year || $monthto!=$monthno ) {
            echo "<script type=\"text/javascript\">alert(\"".$LANG['cal_range_within']."\");</script>";
         }
         else if ( $_POST['rangefrom']>$_POST['rangeto']) {
            echo "<script type=\"text/javascript\">alert(\"".$LANG['cal_range_start']."\");</script>";
         }
         else {
            if (isset($_POST['range_only_business'])) {
               for ($i=intval($dayfrom);$i<=intval($dayto);$i++) {
                  if ( $H->findBySymbol($M->template[$i-1]) ) {
                     if ( $H->cfgname=='busi' OR $H->checkOptions($CONF['H_BUSINESSDAY']) ) $absarray[]=$_POST['rangeabs'].sprintf("%02d",$i);
                  }
               }
            }
            else {
	            for ($i=intval($dayfrom);$i<=intval($dayto);$i++) {
		            $absarray[]=$_POST['rangeabs'].sprintf("%02d",$i);
	            }
            }
         }
      }

      /**
       * Then get the recurring input
       */
      if ( $key=="monday" ) {
         $x = intval($weekday1);
         if (isset($_POST['recurring_only_business'])) {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ($x==1) {
                  if ( $H->findBySymbol($M->template[$i-1]) ) {
                     if ( $H->cfgname=='busi' OR $H->checkOptions($CONF['H_BUSINESSDAY']) ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
                  }
               }
               if($x<=6) $x+=1; else $x = 1;
            }
         }
         else {
	         for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ( $x==1 ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
	            if($x<=6) $x+=1; else $x = 1;
	         }
         }
      }
      if ( $key=="tuesday" ) {
         $x = intval($weekday1);
         if (isset($_POST['recurring_only_business'])) {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ($x==2) {
                  if ( $H->findBySymbol($M->template[$i-1]) ) {
                     if ( $H->cfgname=='busi' OR $H->checkOptions($CONF['H_BUSINESSDAY']) ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
                  }
               }
               if($x<=6) $x+=1; else $x = 1;
            }
         }
         else {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ( $x==2 ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
               if($x<=6) $x+=1; else $x = 1;
            }
         }
      }
      if ( $key=="wednesday" ) {
         $x = intval($weekday1);
         if (isset($_POST['recurring_only_business'])) {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ($x==3) {
                  if ( $H->findBySymbol($M->template[$i-1]) ) {
                     if ( $H->cfgname=='busi' OR $H->checkOptions($CONF['H_BUSINESSDAY']) ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
                  }
               }
               if($x<=6) $x+=1; else $x = 1;
            }
         }
         else {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ( $x==3 ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
               if($x<=6) $x+=1; else $x = 1;
            }
         }
      }
      if ( $key=="thursday" ) {
         $x = intval($weekday1);
         if (isset($_POST['recurring_only_business'])) {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ($x==4) {
                  if ( $H->findBySymbol($M->template[$i-1]) ) {
                     if ( $H->cfgname=='busi' OR $H->checkOptions($CONF['H_BUSINESSDAY']) ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
                  }
               }
               if($x<=6) $x+=1; else $x = 1;
            }
         }
         else {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ( $x==4 ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
               if($x<=6) $x+=1; else $x = 1;
            }
         }
      }
      if ( $key=="friday" ) {
         $x = intval($weekday1);
         if (isset($_POST['recurring_only_business'])) {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ($x==5) {
                  if ( $H->findBySymbol($M->template[$i-1]) ) {
                     if ( $H->cfgname=='busi' OR $H->checkOptions($CONF['H_BUSINESSDAY']) ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
                  }
               }
               if($x<=6) $x+=1; else $x = 1;
            }
         }
         else {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ( $x==5 ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
                  if($x<=6) $x+=1; else $x = 1;
            }
         }
      }
      if ( $key=="saturday" ) {
         $x = intval($weekday1);
         if (isset($_POST['recurring_only_business'])) {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ($x==6) {
                  if ( $H->findBySymbol($M->template[$i-1]) ) {
                     if ( $H->cfgname=='busi' OR $H->checkOptions($CONF['H_BUSINESSDAY']) ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
                  }
               }
               if($x<=6) $x+=1; else $x = 1;
            }
         }
         else {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ( $x==6 ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
               if($x<=6) $x+=1; else $x = 1;
            }
         }
      }
      if ( $key=="sunday" ) {
         $x = intval($weekday1);
         if (isset($_POST['recurring_only_business'])) {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ($x==7) {
                  if ( $H->findBySymbol($M->template[$i-1]) ) {
                     if ( $H->cfgname=='busi' OR $H->checkOptions($CONF['H_BUSINESSDAY']) ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
                  }
               }
               if($x<=6) $x+=1; else $x = 1;
            }
         }
         else {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ( $x==7 ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
               if($x<=6) $x+=1; else $x = 1;
            }
         }
      }
      if ( $key=="workdays" ) {
         $x = intval($weekday1);
         if (isset($_POST['recurring_only_business'])) {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ($x>=1 AND $x<=5) {
                  if ( $H->findBySymbol($M->template[$i-1]) ) {
                     if ( $H->cfgname=='busi' OR $H->checkOptions($CONF['H_BUSINESSDAY']) ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
                  }
               }
               if($x<=6) $x+=1; else $x = 1;
            }
         }
         else {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ( $x>=1 AND $x<=5 ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
               if($x<=6) $x+=1; else $x = 1;
            }
         }
      }
      if ( $key=="weekend" ) {
         $x = intval($weekday1);
         if (isset($_POST['recurring_only_business'])) {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ($x>=6 AND $x<=7) {
                  if ( $H->findBySymbol($M->template[$i-1]) ) {
                     if ( $H->cfgname=='busi' OR $H->checkOptions($CONF['H_BUSINESSDAY']) ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
                  }
               }
               if($x<=6) $x+=1; else $x = 1;
            }
         }
         else {
            for ($i=1; $i<=$nofdays; $i=$i+1) {
               if ( $x>=6 AND $x<=7 ) $absarray[]=$_POST['recurrabs'].sprintf("%02d",$i);
               if($x<=6) $x+=1; else $x = 1;
            }
         }
      }
   }

   /**
    * Now create the new requested template from absarray
    */
   foreach($absarray as $key) {
      $type = substr($key, 0, strlen($key)-2); // $type = requested cfgname
      $ixx = intval(substr($key,-2)); // $ixx = number of day in month, last two characters in $key
      $query  = "SELECT cfgsym FROM `".$A->table."` WHERE cfgname='".$type."'";
      $result = $A->db->db_query($query);
      if ($A->db->db_numrows($result)==1) {
         $row = $A->db->db_fetch_array($result,MYSQL_NUM);
         if ($ixx) $newtemplate[$ixx-1] = $row[0];
      }
      else {
         if ($ixx) $newtemplate[$ixx-1] = $CONF['present'];
      }
   }

   /**
    * We have the current template in $T->template
    * We have the new template in $newtemplate
    * Now we have to check each requested absence (removed or added) by comparison.
    * If any of them is not accepted we have to reject that one.
    * The valid ones will survive in the new template.
    *
    * Let's assume all is good. Set the corresponding flags.
    */
   $declined=FALSE;
   $errorarray = array();

   $oldtemplate = $T->template;
   $usergroups = $UG->getAllforUser($U->username);

   /**
    * Only go through this if the new template is different from the old
    * and if the user requesting this is not the Admin
    */
   if ($oldtemplate!=$newtemplate AND !$isAdmin) {
      /**
       * Create an array that will hold only groups affected by a declination.
       * This array is used late to send emails to only the affected managers.
       */
      $affectedgroups=array();
      /**
       * Loop through each day of the template
       */
      for ($i=0; $i<strlen($newtemplate); $i++) {
         /**
          * See if there was a change requested for this day
          */
         if ($oldtemplate[$i]!=$newtemplate[$i]) {

            /**
             * ABSENCE THRESHOLD
             */
            if ( $C->readConfig("declAbsence") AND $oldtemplate[$i]==$CONF['present'] ) {
               if ($C->readConfig("declBase")=="group") {
                  /**
                   * There is a declination threshold for groups.
                   * We have to go through each group of this user and see
                   * wether the threshold would be violated by this request.
                   */
                  $groups = "";
                  foreach ($usergroups as $row) {
                     if (declineThresholdReached($Year,$monthno,$i+1,"group",$row['groupname'])) {
                        /**
                         * Only add the affected group if user is not the group manager
                         */
                        if ( !$UG->isGroupManagerOfGroup($UL->username,$row['groupname']) ) {
                           $affectedgroups[] = $row['groupname'];
                           $groups .= $row['groupname'].", ";
                        }
                     }
                  }
                  if (strlen($groups)) {
                     /**
                      * Absence threshold for on or more groups is reached. Absence cannot be set.
                      */
                     $declined=TRUE;
                     $groups = substr($groups,0,strlen($groups)-2);
                     $errorarray[] = $T->year."-".$T->month."-".sprintf("%02d",($i+1)).$LANG['err_decl_group_threshold'].$groups;
                     $newtemplate[$i] = $oldtemplate[$i];
                  }
               }
               else {
                  if (declineThresholdReached($Year,$monthno,$ixx,"all")) {
                     /**
                      * Absence threshold for all is reached. Absence cannot be set.
                      */
                     $declined=TRUE;
                     $errorarray[] = $T->year."-".$T->month."-".sprintf("%02d",($i+1)).$LANG['err_decl_total_threshold'];
                     $newtemplate[$i] = $oldtemplate[$i];
                  }
               }
            }

            /**
             * MIN_PRESENT or MAX_ABSENT
             */
            if ( $oldtemplate[$i]==$CONF['present'] ) {
               $groups_min = "";
               $groups_max = "";
               foreach ($usergroups as $row) {
                  $G->findByName($row['groupname']);
                  if ($G->checkOptions($CONF['G_MIN_PRESENT'])) {
                     if (declineThresholdReached($Year,$monthno,$i+1,"min_present",$row['groupname'])) {
                        /**
                         * Only add the affected group if user is not the group manager
                         */
                        if ( !$UG->isGroupManagerOfGroup($UL->username,$row['groupname']) ) {
                           $affectedgroups[] = $row['groupname'];
                           $groups_min .= $row['groupname'].", ";
                        }
                     }
                  }
                  if ($G->checkOptions($CONF['G_MAX_ABSENT'])) {
                     if (declineThresholdReached($Year,$monthno,$i+1,"max_absent",$row['groupname'])) {
                        /**
                         * Only add the affected group if user is no not the group manager
                         */
                        if ( !$UG->isGroupManagerOfGroup($UL->username,$row['groupname']) ) {
                           $affectedgroups[] = $row['groupname'];
                           $groups_max .= $row['groupname'].", ";
                        }
                     }
                  }
               }
               if (strlen($groups_min)) {
                  /**
                   * Minimum presence of one or more groups is not given anymore. Absence cannot be set.
                   */
                  $declined=TRUE;
                  $groups_min = substr($groups_min,0,strlen($groups_min)-2);
                  $errorarray[] = $T->year."-".$T->month."-".sprintf("%02d",($i+1)).$LANG['err_decl_min_present'].$groups_min;
                  $newtemplate[$i] = $oldtemplate[$i];
               }
               if (strlen($groups_max)) {
                  /**
                   * Maximum absence of one or more groups is reached. Absence cannot be set.
                   */
                  $declined=TRUE;
                  $groups_max = substr($groups_max,0,strlen($groups_max)-2);
                  $errorarray[] = $T->year."-".$T->month."-".sprintf("%02d",($i+1)).$LANG['err_decl_max_absent'].$groups_max;
                  $newtemplate[$i] = $oldtemplate[$i];
               }
            }

            /**
             * DECLINE BEFORE
             */
            $declineBefore = FALSE;
            if ( $C->readConfig("declBefore")!="0") {

               $iDate = intval($Year.$monthno.sprintf("%02d",$i+1));
               $todayDate=date("Ymd", time());
               $yesterdayDate=date("Ymd", time()-86400);

               if ( $C->readConfig("declBefore")=="Today" ) $blockBeforeDate = intval($todayDate);
               else $blockBeforeDate = intval($C->readConfig("declBeforeDate"));

               if ( $iDate<$blockBeforeDate AND !$isDirector ) {
                  //echo "<script type=\"text/javascript\">alert(\"Debug: ".$iDate."|".$blockBeforeDate."\");</script>";
                  $declineBefore = TRUE;
                  if ( $isManager ) {
                     foreach ($usergroups as $row) {
                        if ($UG->isGroupManagerOfGroup($UL->username,$row['groupname'])) {
                           $declineBefore = FALSE;
                        }
                        else {
                           $affectedgroups[] = $row['groupname'];
                        }
                     }
                  }
                  if ($declineBefore) {
                     /**
                      * Absences before this date are not allowed. Absence cannot be set.
                      */
                     $declined=TRUE;
                     $dspDate = substr($blockBeforeDate,0,4)."-".substr($blockBeforeDate,4,2)."-".substr($blockBeforeDate,6,2);
                     $errorarray[] = $T->year."-".$T->month."-".sprintf("%02d",($i+1)).$LANG['err_decl_before'].$dspDate.".";
                     $newtemplate[$i] = $oldtemplate[$i];
                  }
               }
            }

            /**
             * DECLINATION PERIOD
             */
            $declinationPeriod = FALSE;
            if ( $C->readConfig("declPeriod") ) {
               $iDate = intval($Year.$monthno.sprintf("%02d",$i+1));
               $startDate = intval($C->readConfig("declPeriodStart"));
               $endDate = intval($C->readConfig("declPeriodEnd"));

               if ( $iDate >= $startDate AND $iDate <= $endDate ) {
                  // echo "<script type=\"text/javascript\">alert(\"".$iDate."|".$startDate."|".$endDate."\");</script>";
                  $declinationPeriod = TRUE;
                  if ( $isManager ) {
                     foreach ($usergroups as $row) {
                        if ($UG->isGroupManagerOfGroup($UL->username,$row['groupname'])) {
                           $declinationPeriod = FALSE;
                        }
                        else {
                           $affectedgroups[] = $row['groupname'];
                        }
                     }
                  }
                  if ($declinationPeriod) {
                     /**
                      * Absences is in declination period. Absence cannot be set.
                      */
                     $declined=TRUE;
                     $dspStartDate = substr($startDate,0,4)."-".substr($startDate,4,2)."-".substr($startDate,6,2);
                     $dspEndDate = substr($endDate,0,4)."-".substr($endDate,4,2)."-".substr($endDate,6,2);
                     $errorarray[] = $T->year."-".$T->month."-".sprintf("%02d",($i+1)).$LANG['err_decl_period'].$dspStartDate.$LANG['err_decl_and'].$dspEndDate.".";
                     $newtemplate[$i] = $oldtemplate[$i];
                  }
               }
            }

            /**
             * ABSENCE APPROVAL
             */
            $approvalRequired=FALSE;
            if ($oldtemplate[$i]!=$CONF['present']) {
               $A->findBySymbol($oldtemplate[$i]);
               if ( $A->checkOptions($CONF['A_APPROVAL']) ) {
                  if  ( !$isDirector AND !$isManager ) {
                     $approvalRequired=TRUE;
                  }
               }
               if ($approvalRequired) {
                  /**
                   * The old absence type requires approval and cannot be changed
                   */
                  $declined=TRUE;
                  $errorarray[] = $T->year."-".$T->month."-".sprintf("%02d",($i+1)).$LANG['err_decl_abs'].$A->dspname.$LANG['err_decl_approval'];
                  $newtemplate[$i] = $oldtemplate[$i];
               }
            }
            if ($newtemplate[$i]!=$CONF['present']) {
               $A->findBySymbol($newtemplate[$i]);
               if ( $A->checkOptions($CONF['A_APPROVAL']) ) {
                  // echo "<script type=\"text/javascript\">alert(\"".$newtemplate[$i]."\");</script>";
                  if  ( !$isDirector AND !$isManager ) {
                     $approvalRequired=TRUE;
                  }
               }
               if ($approvalRequired) {
                  /**
                   * The new absence type requires approval and cannot be set
                   */
                  $declined=TRUE;
                  $errorarray[] = $T->year."-".$T->month."-".sprintf("%02d",($i+1)).$LANG['err_decl_abs'].$A->dspname.$LANG['err_decl_approval'];
                  $newtemplate[$i] = $oldtemplate[$i];
               }
            }

         } // if ($oldtemplate[$i]!=$newtemplate[$i]) {

      } // for ($i=0; $i<strlen($newtemplate); $i++) {

   } // if ($oldtemplate!=$newtemplate AND !$isAdmin) {


   if ($oldtemplate!=$newtemplate) {
      /**
       * One or more absence changes are valid after the previous check. Update the template.
       */
      if ( $U->checkUserType($CONF['UTTEMPLATE']) ) {
         /**
          * This is a template user. We must not overwrite his old template yet.
          * We need to compare it with the new template to find the differences.
          * Then we have to go through the templates of all other users in the same
          * group and adjust accordingly.
          *
          */
         $query  = "SELECT groupname FROM `".$UG->table."` WHERE username='".$U->username."'";
         $result = $UG->db->db_query($query);
         while ($row=$UG->db->db_fetch_array($result,MYSQL_ASSOC) ) {
            /**
             * Go through all users of the same group
             */
            $groupfilter = $row['groupname'];
            $query2 = "SELECT DISTINCT ".$CONF['db_table_users'].".*" .
                     " FROM ".$CONF['db_table_users'].",".$CONF['db_table_user_group'].",".$CONF['db_table_groups'].
                     " WHERE (".$CONF['db_table_users'].".username!='admin'" .
                     " AND ".$CONF['db_table_users'].".username=".$CONF['db_table_user_group'].".username".
                     " AND ".$CONF['db_table_users'].".username!='".$U->username."'".
                     " AND ".$CONF['db_table_user_group'].".groupname = '".$groupfilter."'" .
                     " AND ".$CONF['db_table_groups'].".groupname=".$CONF['db_table_user_group'].".groupname".
                     " AND (".$CONF['db_table_groups'].".options&1)=0 )";
            $result2 = $UT->db->db_query($query2);
            while ( $row2 = $UT->db->db_fetch_array($result2,MYSQL_ASSOC) ) {
               $UT->findByName($row2['username']);
               /**
                * Find the template for the current loop user, otherwise create a fresh one
                */
               if (!$rc=$TT->findTemplate($UT->username,$Year,$monthno)) {
                  $freshtemplate = "";
                  for ($i=0; $i<intval($nofdays); $i++ ) {
                     $freshtemplate .= $CONF['present'];
                  }
                  $TT->template = $freshtemplate;
               }
               /**
                * Loop through each day and compare the templates. Set new value from template
                * based on rules.
                * $T->template = old template of the template user
                * $newtemplate = new template of the template user
                * $TT->template = template of the current user in the loop
                *
                * Template User Old | Template User New | Current User | Action to Regular User
                * -----------------------------------------------------------------------------
                * absence x         | present           | absence x    | set present
                * present           | absence x         | present      | set absence x
                * absence x         | absence x         | present      | set absence x
                * absence x         | absence y         | present      | set absence y
                * absence x         | absence y         | absence x    | set absence y
                *
                */
               for ($it=0; $it<strlen($newtemplate); $it++) {
                  if ( $newtemplate[$it]==$CONF['present'] && $T->template[$it]==$TT->template[$it] ) {
                     $TT->template[$it] = $newtemplate[$it];
                  }
                  else if ( $newtemplate[$it]!=$CONF['present'] && $TT->template[$it]==$CONF['present'] ) {
                     $TT->template[$it] = $newtemplate[$it];
                  }
                  else if ( $newtemplate[$it]!=$CONF['present'] && $T->template[$it]!=$CONF['present'] &&
                            $newtemplate[$it]!=$T->template[$it] && $TT->template[$it]==$T->template[$it] ) {
                     $TT->template[$it] = $newtemplate[$it];
                  }
               }
               /**
                * Now update the current user's template
                */
               $TT->update($UT->username,$Year,$monthno);
            }
         }
      }

      /**
       * Template was changed. Update it.
       * Send notification e-Mails.
       * Then log the event.
       */
      $T->template=$newtemplate;
      $T->update($U->username,$Year,$monthno);

      /**
       * Since the database symbols of the absence types may not be the same
       * as the display symbols (e.g. due to an absence type update) we need
       * to replace them in the template string that is sent out per mail.
       */
      $mailtemplate="";
      for ($i=0; $i<strlen($T->template); $i++) {
         if ($T->template[$i]<>$CONF['present']) {
            $A->findBySymbol($T->template[$i]);
            $mailtemplate.=$A->dspsym;
         }
         else {
            $mailtemplate.=$CONF['present'];
         }
      }

      /**
       * Create an ASCII table for the template
       */
      $ninfo = $LANG['notification_new_template'].$T->year."-".$T->month."\n\n|";
      $j=1;
      for ($i=0; $i<strlen($mailtemplate); $i++) {
         $ninfo .= sprintf("%02d",$j++)."|";
      }
      $ninfo .= "\n|";
      for ($i=0; $i<strlen($mailtemplate)-1; $i++) {
         $ninfo .= "--+";
      }
      $ninfo .= "--|\n|";
      for ($i=0; $i<strlen($mailtemplate); $i++) {
         $ninfo .= " ".$mailtemplate[$i]."|";
      }
      $ninfo .= "\n\n";
      $ats = $A->getAll();
      foreach ($ats as $at) {
         $ninfo .= $at['cfgsym']." = ".$at['dspname']."\n";
      }

      /**
       * Send out the mails
       */
      $nobject = $U->firstname." ".$U->lastname;
      $ugroups = $UG->getAllforUser($U->username);
      foreach ($ugroups as $ugroup) {
         $ntype = "usercalchange";
         $naffectedgroup = $ugroup['groupname'];
         sendNotification($ntype, $nobject, $naffectedgroup, $ninfo);
      }

      /**
       * Log this event
       */
      $LOG->log("logUser",$L->checkLogin(),"User template changed: ".$U->username." ".$T->year.$T->month." ".$mailtemplate);

   }

   if ($declined) {
      /**
       * One or more absence requests have been declined.
       * Build javascript error message. Will be shown at bottom of page.
       */
      $error_decl = TRUE;
      $notificationerror = "";
      $errormessage = $LANG['err_decl_title'];
      $errormessage .= $LANG['err_decl_subtitle'];
      foreach($errorarray as $err) {
         $errormessage .= $err."\\n";
      }

      /**
       * Build notification message and send it to the appropriate receivers
       */
      if ($C->readConfig("emailNotifications")) {
         $subject = $LANG['notification_subject'];
         $notification =$LANG['notification_greeting'];
         $notification.=$LANG['notification_decl_msg'];
         $notification.=$LANG['notification_decl_user'].$U->firstname." ".$U->lastname."\n\n";
         $notification.=$LANG['notification_decl_msg_2'];
         foreach($errorarray as $err) {
            $notificationerror .= $err."\n";
         }
         $notification.=$notificationerror;
         $notification.=$LANG['notification_decl_sign'];

         /*
          * Send email to requesting user if configured so in Declination Management
          */
         if ( $C->readConfig("declNotifyUser") ) {
            $to = $U->email;
            sendEmail($to, $subject, $notification);
            /*
             * Set to TRUE for debug
             */
            if (FALSE) {
               echo "<textarea cols=\"100\" rows=\"12\">To: ".$to."\n\n".
                    "Subject: ".stripslashes($subject)."\n\n".
                    stripslashes($notification)."</textarea>";
            }
         }

         /*
          * Send email to group manager of requesting user if configured so in Declination Management
          */
         if ( $C->readConfig("declNotifyManager") ) {
            foreach($affectedgroups as $grp) {
               $query  = "SELECT DISTINCT ".$U->table.".email FROM ".$U->table.",".$UG->table." " .
                         "WHERE ".$U->table.".username=".$UG->table.".username " .
                         "AND ".$UG->table.".groupname='".trim($grp)."' " .
                         "AND ".$UG->table.".type='manager'";
               $result = $UG->db->db_query($query);
               while ($row=$UG->db->db_fetch_array($result,MYSQL_NUM) ) {
                  $to = $row[0];
                  sendEmail($to, $subject, $notification);
               }
            }
         }

         /*
          * Send email to director if configured so in Declination Management
          */
         if ( $C->readConfig("declNotifyDirector") ) {
            $query  = "SELECT username FROM `".$U->table."`";
            $result = $U->db->db_query($query);
            while ($row=$U->db->db_fetch_array($result,MYSQL_NUM) ) {
               $U->findByName($row[0]);
               if ($U->checkUserType($CONF['UTDIRECTOR'])) {
                  $to = $U->email;
                  sendEmail($to, $subject, $notification);
                  /*
                   * Set to TRUE for debug
                   */
                  if (FALSE) {
                     echo "<textarea cols=\"100\" rows=\"12\">To: ".$to."\n\n".
                          "Subject: ".$subject."\n\n".
                          $notification."</textarea>";
                  }
               }
            }
         }

         /*
          * Send email to admin if configured so in Declination Management
          */
         if ( $C->readConfig("declNotifyAdmin") ) {
            $users = $U->getAll();
            foreach ($users as $u) {
               $U->findByName($u['username']);
               if ($U->checkUserType($CONF['UTADMIN'])) {
                  $to = $U->email;
                  sendEmail($to, $subject, $notification);
                  /*
                   * Set to TRUE for debug
                   */
                  if (FALSE) {
                     echo "<textarea cols=\"100\" rows=\"12\">To: ".$to."\n\n".
                          "Subject: ".$subject."\n\n".
                          $notification."</textarea>";
                  }
               }
            }
         }
      }

      /*
       * Log this event
       */
      $LOG->log("logUser",$L->checkLogin(),"Calendar change request declined: ".$U->username."\n".$notificationerror);
   }
}

/**
 * =========================================================================
 * CLEAR
 */
else if (isset($_POST['btn_clear'])) {
   /**
    * First create default template
    */
   $T->template = "";
   for ($i=0; $i<intval($nofdays); $i++ ) {
      $T->template .= $CONF['present'];
   }
   /**
    * Update his template for this month
    */
   $T->update($U->username,$Year,$monthno);
   $query  = "SELECT groupname FROM `".$UG->table."` WHERE username='".$U->username."'";
   $result = $UG->db->db_query($query);
   while ($row=$UG->db->db_fetch_array($result,MYSQL_NUM) ) {
      sendNotification("usercalchange",$U->firstname." ".$U->lastname, $row[0],$T->year.$T->month." ".$T->template);
   }
   /**
    * Log this event
    */
   $LOG->log("logUser",$L->checkLogin(),"User template cleared: ".$U->username." ".$T->year.$T->month." ".$T->template);
}

$currlang = $CONF['options']['lang'];
require("includes/header.html.inc.php");
$CONF['options']['lang']=$currlang;
?>
<body>
<div id="content">
   <div id="content-content">
      <form  name="monthform" method="POST" action="<?=($_SERVER['PHP_SELF']."?Year=".$Year."&amp;Month=".$Month."&amp;Member=".$Member."&amp;lang=".$CONF['options']['lang'])."&amp;region=".$region?>">
      <table class="dlg">
         <tr>
            <td class="dlg-header">
               <?php printDialogTop($LANG['member_edit']." ".$U->firstname." ".$U->lastname." (".$LANG['month_region'].": ".$region.")","user_calendar.html","ico_calendar.png"); ?>
            </td>
         </tr>
         <tr>
            <td class="dlg-body"><br>
               <table class="month">
                  <tr>
                     <td class="month"><?=$LANG['monthnames'][intval($monthno)]."&nbsp;".trim($Year)?></td>
                     <td class="month-button">&nbsp;</td>
                     <?php
                     /**
                      * Month frame: Day of month
                      */
                     for ($i=1; $i<=$nofdays; $i=$i+1) {
                        if ( $H->findBySymbol($M->template[$i-1]) ) {
                           if ( $H->cfgname=='busi' ) {
                              /**
                               * A holiday but business day => business day color
                               */
                              echo "<td class=\"daynum\">".$i."</td>";
                           }
                           else {
                              /**
                               * A holiday and not business day => holiday color
                               */
                              echo "<td class=\"daynum-".$H->cfgname."\">".$i."</td>";
                           }
                        }
                        else {
                           echo "<td class=\"daynum\">".$i."</td>";
                        }
                     }

                     /**
                      * Month frame: Weekday
                      */
                     $x = intval($weekday1);
                     ?>
                  </tr>
                  <tr>
                     <td class="title" style="font-size: 8pt;">
                        <input title="<?=$LANG['tt_page_bwd']?>" name="btn_bwd" type="submit" class="button" value="&lt;&lt;">
                        <input title="<?=$LANG['tt_page_fwd']?>" name="btn_fwd" type="submit" class="button" value="&gt;&gt;">
                        <input type="hidden" name="hid_fwdMonth" value="<?=$fwdMonth?>">
                        <input type="hidden" name="hid_fwdYear" value="<?=$fwdYear?>">
                        <input type="hidden" name="hid_bwdMonth" value="<?=$bwdMonth?>">
                        <input type="hidden" name="hid_bwdYear" value="<?=$bwdYear?>">
                        <input type="hidden" name="hid_Member" value="<?=$Member?>">
                     </td>
                     <td class="title-button">&nbsp;</td>
                     <?php
                     for ($i=1; $i<=$nofdays; $i=$i+1) {
                        if ( $H->findBySymbol($M->template[$i-1]) ) {
                           if ( $H->cfgname=='busi' ) {
                              /**
                               * A holiday but business day => business day color
                               */
                              echo "<td class=\"weekday\">".$weekdays[$x]."</td>";
                           }
                           else {
                              /**
                               * A holiday and not business day => holiday color
                               */
                              echo "<td class=\"weekday-".$H->cfgname."\">".$weekdays[$x]."</td>";
                           }
                        }
                        else {
                           echo "<td class=\"weekday\">".$weekdays[$x]."</td>";
                        }
                        if($x<=6) $x+=1; else $x = 1;
                     }
                     ?>
                  </tr>
                  <?php
                  /**
                   * Show Daynote row
                   */
                  if ($user = $L->checkLogin()) $UL->findByName($user);
                  if ( !intval($C->readConfig("hideDaynotes")) ||
                       ($UL->checkUserType($CONF['UTADMIN']) || $UL->checkUserType($CONF['UTDIRECTOR']) || $UL->checkUserType($CONF['UTMANAGER']) )
                     ) {
                     $x = intval($weekday1);
                     echo "
                     <tr>
                        <td class=\"name\">".$LANG['month_daynote']."</td>
                        <td class=\"name-button\">&nbsp;</td>";
                     for ($i=1; $i<=$nofdays; $i=$i+1) {
                        if ($i<10) $dd="0".strval($i); else $dd=strval($i);
                        if ( $H->findBySymbol($M->template[$i-1]) ) {
                           if ( $H->cfgname=='busi' ) {
                              if ( $N->findByDay($Year.$monthno.$dd,$U->username) ) $style="weekday-note"; else $style="weekday";
                           }else{
                              if ( $N->findByDay($Year.$monthno.$dd,$U->username) ) $style="weekday-".$H->cfgname."-note"; else $style="weekday-".$H->cfgname;
                           }
                         }else{
                            if ( $N->findByDay($Year.$monthno.$dd,$U->username) ) $style="weekday-note"; else $style="weekday";
                         }
                        echo "
                            <td class=\"".$style."\">
                                <a href=\"javascript:this.blur();openPopup('daynote.php?lang=".$CONF['options']['lang']."&amp;date=".$Year.$monthno.$dd."&amp;daynotefor=".$U->username."&amp;region=default&amp;datestring=".$dd."%20".$LANG['monthnames'][intval($monthno)]."%20".$Year."','daynote','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=600,height=340');\">
                                    <img src=\"themes/".$theme."/img/ico_daynote.png\" alt=\"\" title=\"".$LANG['month_daynote_tooltip']."\" border=\"0\">
                                </a>
                            </td>\n";
                        if($x<=6) $x+=1; else $x = 1;
                     }
                     echo "
                     </tr>";
                  }

                  /**
                   * Absence type loop
                   */
                  $approvalNeeded=false;
                  $absences = $A->getAll();
                  foreach ($absences as $row) {
                     /**
                      * Make sure this absence type is allowed for this group
                      */
                     $A->findBySymbol($row['cfgsym']);
                     $showthisabsence=false;
                     $showdisabled=false;
                     $groups = $G->getAll();
                     foreach ($groups as $Grow) {
                        if ($UG->isMemberOfGroup($U->username,$Grow['groupname']) &&
                            $AG->isAssigned($row['cfgsym'],$Grow['groupname'])
                           ) {
                           $showthisabsence=true;
                           if ($A->checkOptions($CONF['A_MGR_ONLY'])) {
                              if ( ($UL->checkUserType($CONF['UTADMIN']) || $UL->checkUserType($CONF['UTDIRECTOR'])) || ($UL->checkUserType($CONF['UTMANAGER']) && $UG->isMemberOfGroup($UL->username,$Grow['groupname'])) ) {
                                 $showdisabled=false;
                              }
                              else {
                                 $showdisabled=true;
                              }
                           }
                           if ( $A->checkOptions($CONF['A_APPROVAL']) ) $approvalNeeded=true;
                        }
                     }

                     /**
                      * Show the absence row
                      */
                     if ($showthisabsence) {
                        if ($row['cfgname'] != "present" ) {
                           echo "
                           <tr>
                              <td class=\"name\">".str_replace(" ","&nbsp;",$row['dspname'])."</td>
                              <td class=\"name-button\">&nbsp;</td>";
                           $count=0;
                           /**
                            * Show a line for this absence type covering each day of the month
                            */
                           for($idx=0; $idx<strlen($M->template); $idx++) {
                              if ( ($count+1)<10 ) $strcount = "0".strval($count+1); else $strcount = strval($count+1);
                              if ( $T->template[$idx] == $row['cfgsym'] ) {
                                 echo "
                                 <td class=\"".$row['cfgname']."\">
                                    <input name=\"".$row['cfgname'].$strcount."\" type=\"checkbox\" id=\"".$row['cfgname'].$strcount."\" value=\"".$row['cfgname'].$strcount."\" CHECKED ".($showdisabled?'DISABLED':'').">";
                              }else{
                                 if ( $H->findBySymbol($M->template[$count]) ) {
                                    echo "<td class=\"day-".$H->cfgname."\">";
                                 }else{
                                    echo "<td class=\"day\">";
                                 }
                                 echo "<input name=\"".$row['cfgname'].$strcount."\" type=\"checkbox\" id=\"".$row['cfgname'].$strcount."\" value=\"".$row['cfgname'].$strcount."\" ".($showdisabled?'DISABLED':'').">";
                              }
                              $count+=1;
                              echo "</td>";
                           }
                           echo "</tr>\n";
                        }
                     }
                  }
                  ?>
               </table>
               <table style="width: 100%;">
                  <tr>
                     <td width="50%" style="vertical-align: top;">
                        <fieldset><legend><?=$LANG['cal_range_title']?></legend>
         			         <table>
         			            <tr>
         			               <td><?=$LANG['cal_range_type']?></td>
         			               <td><?=$LANG['cal_range_from']?></td>
         				           <td><?=$LANG['cal_range_to']?></td>
         				           <td>&nbsp;</td>
         				        </tr>
         				        <tr>
                                 <td style="padding-right: 4px;">
                                    <select name="rangeabs" id="rangeabs" class="select">
                                    <?php
                                    $absences = $A->getAll();
                                    foreach ($absences as $row) {
                                       /**
                                        * Make sure this users calendar only contains those absence types
                                        * that his group(s) is(are) entitled for
                                        */
                                       $A->findBySymbol($row['cfgsym']);
                                       $showthisabsence=false;
                                       $groups = $G->getAll();
                                       foreach ($groups as $Grow) {
                                          if ($UG->isMemberOfGroup($U->username,$Grow['groupname']) &&
                                              $AG->isAssigned($row['cfgsym'],$Grow['groupname'])
                                             ) {
                                             $showthisabsence=true;
                                             if ($A->checkOptions($CONF['A_MGR_ONLY'])) {
                                                if ( ($UL->checkUserType($CONF['UTADMIN']) || $UL->checkUserType($CONF['UTDIRECTOR'])) || ($UL->checkUserType($CONF['UTMANAGER']) && $UG->isMemberOfGroup($UL->username,$Grow['groupname'])) ) {
                                                   $showthisabsence=true;
                                                }
                                                else {
                                                   $showthisabsence=false;
                                                }
                                             }
                                             if ( $A->checkOptions($CONF['A_APPROVAL']) ) $approvalNeeded=true;
                                          }
                                       }
                                       if ($showthisabsence && $row['cfgname']!="present") {
                                          echo "            <option class=\"option\" value=\"".$row['cfgname']."\">".$row['dspname']."</option>\n";
                                       }
                                    }
                                    ?>
                                    </select>
                                 </td>
                                 <td style="padding-right: 4px;">
                                    <script>
                                       $(function() { $( "#rangefrom" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd" }); });
                                       $(function() { $( "#rangeto" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd" }); });
                                    </script>
                                    <input name="rangefrom" id="rangefrom" size="10" maxlength="10" type="text" class="text" value="">
                                 </td>
                                 <td style="padding-right: 4px;">
                                    <input name="rangeto" id="rangeto" size="10" maxlength="10" type="text" class="text" value="">
                                 </td>
                                 <td>&nbsp;</td>
                              </tr>
                              <tr>
                                 <td colspan="4" style="padding-bottom: 7px;"><input name="range_only_business" type="checkbox" id="range_only_business" value="range_only_business" checked="checked"><?=$LANG['cal_only_business']?></td>
                              </tr>
                           </table>
                        </fieldset>
                     </td>
                     <td width="50%" style="vertical-align: top;">
                        <fieldset><legend><?=$LANG['cal_recurring_title']?></legend>
                        <table>
                           <tr>
                              <td><input name="monday" type="checkbox" id="monday" value="monday"></td>
                              <td><?=$LANG['weekdays_long'][1]?></td>
                              <td><input name="thursday" type="checkbox" id="thursday" value="thursday"></td>
                              <td><?=$LANG['weekdays_long'][4]?></td>
                              <td><input name="sunday" type="checkbox" id="sunday" value="sunday"></td>
                              <td><?=$LANG['weekdays_long'][7]?></td>
                              <td style="padding-left: 10px; vertical-align: top;"><?=$LANG['cal_range_type']?></td>
                           </tr>
                           <tr>
                              <td><input name="tuesday" type="checkbox" id="tuesday" value="tuesday"></td>
                              <td><?=$LANG['weekdays_long'][2]?></td>
                              <td><input name="friday" type="checkbox" id="friday" value="friday"></td>
                              <td><?=$LANG['weekdays_long'][5]?></td>
                              <td><input name="workdays" type="checkbox" id="workdays" value="workdays"></td>
                              <td><?=$LANG['cal_recurring_workdays']?></td>
                              <td rowspan="2" style="padding-left: 10px; vertical-align: top;">
                                 <select name="recurrabs" id="recurrabs" class="select">
                                 <?php
                                 $absences = $A->getAll();
                                 foreach ($absences as $row) {
                                    /*
                                     * Make sure this users calendar only contains those absence types
                                     * that his group(s) is(are) entitled for
                                     */
                                    $A->findBySymbol($row['cfgsym']);
                                    $showthisabsence=false;
                                    $groups = $G->getAll();
                                    foreach ($groups as $Grow) {
                                       if ($UG->isMemberOfGroup($U->username,$Grow['groupname']) &&
                                           $AG->isAssigned($row['cfgsym'],$Grow['groupname'])
                                          ) {
                                          $showthisabsence=true;
                                          if ($A->checkOptions($CONF['A_MGR_ONLY'])) {
                                             if ( ($UL->checkUserType($CONF['UTADMIN']) || $UL->checkUserType($CONF['UTDIRECTOR'])) || ($UL->checkUserType($CONF['UTMANAGER']) && $UG->isMemberOfGroup($UL->username,$Grow['groupname'])) ) {
                                                $showthisabsence=true;
                                             }
                                             else {
                                                $showthisabsence=false;
                                             }
                                          }
                                          if ( $A->checkOptions($CONF['A_APPROVAL']) ) $approvalNeeded=true;
                                       }
                                    }
                                    if ($showthisabsence && $row['cfgname']!="present") {
                                       echo "            <option class=\"option\" value=\"".$row['cfgname']."\">".$row['dspname']."</option>\n";
                                    }
                                 }
                                 ?>
                                 </select><br>
                                 <input name="recurring_only_business" type="checkbox" id="recurring_only_business" value="rcurring_only_business" checked="checked"><?=$LANG['cal_only_business']?>
                              </td>
                           </tr>
                           <tr>
                              <td><input name="wednesday" type="checkbox" id="wednesday" value="wednesday"></td>
                              <td><?=$LANG['weekdays_long'][3]?></td>
                              <td><input name="saturday" type="checkbox" id="saturday" value="saturday"></td>
                              <td><?=$LANG['weekdays_long'][6]?></td>
                              <td><input name="weekend" type="checkbox" id="weekend" value="weekend"></td>
                              <td><?=$LANG['cal_recurring_weekend']?></td>
                           </tr>
                        </table>
                        </fieldset>
                     </td>
                  </tr>
                  <tr>
                     <td style="vertical-align: top; width: 100%;" colspan="2">
                        <fieldset><legend><?=$LANG['cal_reason_title']?></legend>
                           <input class="text" name="txtReason" id="txtReason" type="text" size="130" maxlength="130" value="<?=$LANG['cal_reason_dummy']?>">
                        </fieldset>
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
         <tr>
            <td class="dlg-menu">
               <input name="btn_clear" type="submit" class="button" value="<?=$LANG['btn_clear']?>" onclick="return confirmSubmit('<?=$LANG['cal_clear_confirm']?>')">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?user_calendar.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
               <input name="btn_close" type="button" class="button" onclick="javascript:window.close();" value="<?=$LANG['btn_close']?>">
               <input name="btn_done"  type="button" class="button" onclick="javascript:closeme();" value="<?=$LANG['btn_done']?>">
            </td>
         </tr>
      </table>
      </form>
   </div>
</div>

<?php
require( "includes/footer.html.inc.php" );
//
// Show javascript error message to user if there is one
//
if ($error_decl) echo "<script type=\"text/javascript\">alert(\"".$errormessage."\");</script>";
?>
