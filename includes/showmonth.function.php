<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * showmonth.function.php
 *
 * Displays a month with all users and abesences. Big enough to reside in a
 * seperate file.
 *
 * @package TeamCalPro
 * @version 3.5.002
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */

//echo "<script type=\"text/javascript\">alert(\"Debug: "\");</script>";

/**
 *  Displays a given month with users and absences based on the passed filters
 *
 * @param string $year Four digit year number
 * @param string $month Two digit month number
 * @param string $groupfilter Identifying the group (or 'All') to filter by
 * @param string $sortorder Indicating ascending or descending sort order
 */
function showMonth($year,$month,$groupfilter,$sortorder,$page=1)
{
   global $CONF;
   global $LANG;
   global $theme;

   require_once( "includes/tcabsence.class.php" );
   require_once( "includes/tcallowance.class.php" );
   require_once( "includes/tcconfig.class.php" );
   require_once( "includes/tcdaynote.class.php" );
   require_once( "includes/tcgroup.class.php" );
   require_once( "includes/tcholiday.class.php" );
   require_once( "includes/tclogin.class.php" );
   require_once( "includes/tcmonth.class.php" );
   require_once( "includes/tctemplate.class.php" );
   require_once( "includes/tcuser.class.php" );
   require_once( "includes/tcusergroup.class.php" );
   require_once( "includes/tcuseroption.class.php" );

   $A  = new tcAbsence;
   $AC = new tcAbsence; // for Absence Count Array
   $AL = new tcAllowance; // for Absence Count Array
   $C  = new tcConfig;
   $G  = new tcGroup;
   $H  = new tcHoliday;
   $L  = new tcLogin;
   $M  = new tcMonth;
   $N  = new tcDaynote;
   $N2 = new tcDaynote;
   $T  = new tcTemplate;
   $U  = new tcUser;
   $UG = new tcUserGroup;
   $UL = new tcUser; // user logged in
   $UO = new tcUserOption;

   $pscheme = $C->readConfig("permissionScheme");
   $weekdays = $LANG['weekdays'];

   $showmonthBody='';
   $loggedIn = false;
   /**
    * Create a timestamp for the given year and month (using day 1 of the
    * month) and use it to get some relevant information using date() and
    * getdate()
    */
   $mytime = $month . " 1," . $year;
   $myts = strtotime($mytime);
   // Get number of days in current month
   $nofdays = date("t",$myts);
   // Get first weekday of the current month
   $mydate = getdate($myts);
   $weekday1 = $mydate['wday'];
   if ($weekday1=="0") $weekday1="7";
   $monthno = sprintf("%02d",intval($mydate['mon']));
   // Set the friendly name of the month
   // $monthname = $month . " " . $year;
   $monthname = $LANG['monthnames'][intval($monthno)] . " " . $year;

   /**
    * Now find out what today is and if it lies in the month we are about
    * to display
    */
   $today     = getdate();
   $daytoday   = $today['mday'];  // Numeric representation of todays' day of the month
   $monthtoday = $today['mon'];   // Numeric representation of todays' month
   $yeartoday  = $today['year'];  // A full numeric representation of todays' year, 4 digits
   $todaysmonth = false;
   if ( $mydate['mon']==$today['mon'] && $mydate['year']==$today['year'] ) {
      $todaysmonth = true; // The current month is todays' month
   }

   /**
    * Set the repeat header count
    */
   $repHeadCnt = intval($C->readConfig("repeatHeaderCount"));
   if (!$repHeadCnt) $repHeadCnt = 10000;

   /**
    * See if someone is logged in and if so, what type?
    */
   $regularUser = TRUE;
   $userType = "regular";
   $userGroups = null;
   $managerOf = null;
   if ($user = $L->checkLogin()) {
      $UL->findByName($user);
      $loggedIn = true;
		switch (true) {
			case $UL->checkUserType($CONF['UTADMIN']):
				$regularUser = FALSE;
				$userType = "admin";
				break;
			case $UL->checkUserType($CONF['UTDIRECTOR']):
				$regularUser = FALSE;
				$userType = "director";
				break;
			case $UL->checkUserType($CONF['UTMANAGER']):
				$regularUser = FALSE;
				$userType = "manager";
				break;
		}
		$userGroups = $UG->getAllforUser2($user);
   }

   /**
    * See if this user is manager of one or more groups
    */
   if(!empty($userGroups)) {
      foreach($userGroups as $key=>$value) {
         if ($value == "manager") {
            $managerOf[]=$key;
         }
      }
   }

   /**
    * Read Month Template
    */
   $found = $M->findByName($CONF['options']['region'], $year.$monthno);
   if ( !$found ) {
      /**
       * Seems there is no default template for this month yet.
       * Let's create a default one.
       */
      $M->region = $CONF['options']['region'];
      $M->yearmonth = $year.$monthno;
      $M->template = createMonthTemplate($year,$month);
      $M->create();
   }
   else if ( empty($M->template) ) {
      /**
       * Seems there is an empty default template. That can't be.
       * Let's create a default one.
       */
      $M->template = createMonthTemplate($year,$month);
      $M->update($CONF['options']['region'], $year.$monthno);
   }

   if ($monthname && $nofdays && $M->template && $weekday1) {
      /**
       * Collect the month header into $monthHeader
       */
      $cols=0;
      echo "<table class=\"month\">\n\r";

      $monthHeader="<tr>\n\r";
      $monthHeader.="<td class=\"month\">" . trim($monthname) . "</td>\n\r";
      $cols++;

      if (isAllowed("editGlobalCalendar")) {
         $monthHeader.="<td class=\"month-button\"><a href=\"javascript:openPopup('editmonth.php?lang=".$CONF['options']['lang']."&amp;region=".$CONF['options']['region']."&amp;Year=".$year."&amp;Month=".$month."','shop','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=no,dependent=1,width=960,height=300');\"><img class=\"noprint\" src=\"themes/".$theme."/img/date.png\" width=\"16\" height=\"16\" border=\"0\" title=\"".$LANG['cal_img_alt_edit_month']."\" alt=\"".$LANG['cal_img_alt_edit_month']."\"></a></td>\n\r";
      }
      else {
         $monthHeader.="<td class=\"month-button\">&nbsp;</td>\n\r";
      }
      $cols++;

      /**
       * Print the Remainder section if configured
       */
      if (intval($C->readConfig("includeRemainder"))) {
         /**
          * Print the Remainder section title
          * Go through each absence type, see wether its option is set
          * to be shown in the remainders. We need the count for the COLSPAN.
          */
         $cntRemainders=0;
         $cntTotals=0;
         $queryAC  = "SELECT `cfgsym` FROM `".$AC->table."` ORDER BY `dspname`;";
         $resultAC = $AC->db->db_query($queryAC);
         while ( $rowAC = $AC->db->db_fetch_array($resultAC,MYSQL_ASSOC) ) {
            $AC->findBySymbol($rowAC['cfgsym']);
            if ( $AC->checkOptions($CONF['A_SHOWREMAIN']) ) $cntRemainders++;
         }
         if ( $CONF['options']['remainder']=="show" && $cntRemainders ) {
            $monthHeader.="<td class=\"remainder-title\" colspan=\"".$cntRemainders."\">".$LANG['remainder']."</td>\n\r";
            $cols+=$cntRemainders;
         }
      }

      /**
       * Print the individual totals in the Remainder section if configured
       */
      if (intval($C->readConfig("includeTotals"))) {
         /**
          * Go through each absence type, see wether its option is set
          * to be shown in the remainders. We need the count for the COLSPAN.
          */
         $cntTotals=0;
         $queryAC  = "SELECT `cfgsym` FROM `".$AC->table."` ORDER BY `dspname`;";
         $resultAC = $AC->db->db_query($queryAC);
         while ( $rowAC = $AC->db->db_fetch_array($resultAC,MYSQL_ASSOC) ) {
            $AC->findBySymbol($rowAC['cfgsym']);
            if ( $AC->checkOptions($CONF['A_SHOWTOTAL']) ) $cntTotals++;
         }
         if ( $CONF['options']['remainder']=="show" && $cntTotals ) {
            $monthHeader.="<td class=\"remainder-title\" colspan=\"".$cntTotals."\">".$LANG['totals']."</td>\n\r";
            $cols+=$cntTotals;
         }
      }

      /**
       * Print the day numbers
       */
      $businessDayCount = 0;
      for ($i=1; $i<=$nofdays; $i=$i+1) {
         if ( $H->findBySymbol($M->template[$i-1]) ) {
            if ($H->checkOptions($CONF['H_BUSINESSDAY'])) $businessDayCount++;
            if ( $H->cfgname=='busi' ) {
               // Regular business day
               if ( $todaysmonth && $i==intval($today['mday']) ) {
                  $monthHeader.="<td class=\"todaynum\" title=\"".$H->dspname."\">".$i."</td>\n\r";
               }
               else {
                  $monthHeader.="<td class=\"daynum\" title=\"".$H->dspname."\">".$i."</td>\n\r";
               }
            }
            else {
               // Holiday or any other non-busi day
               if ( $todaysmonth && $i==intval($today['mday']) ) {
                  $monthHeader.="<td class=\"todaynum-".$H->cfgname."\" title=\"".$H->dspname."\">".$i."</td>\n\r";
               }
               else {
                  $monthHeader.="<td class=\"daynum-".$H->cfgname."\" title=\"".$H->dspname."\">".$i."</td>\n\r";
               }
            }
         }
         else {
            if ( $todaysmonth && $i==intval($today['mday']) ) {
               $monthHeader.="<td class=\"todaynum\">".$i."</td>\n\r";
            }
            else {
               $monthHeader.="<td class=\"daynum\">".$i."</td>\n\r";
            }
         }
         $cols++;
      }
      $monthHeader.="</tr>\n\r";

      /**
       * Print the week numbers
       */
      if (intval($C->readConfig("showWeekNumbers"))) {
         $wd = intval($weekday1);

         $colspan=0;
         $monthHeader.="<tr>\n\r";
         $monthHeader.="<td class=\"title\">".$LANG['cal_caption_weeknumber']."</td>\n\r";
         if ( $CONF['options']['remainder']=="show" && $cntRemainders ) {
            $monthHeader.="<td class=\"title-button\" colspan=\"".($cntRemainders+$cntTotals+1)."\">&nbsp;</td>\n\r";
         }
         else {
            $monthHeader.="<td class=\"title-button\">&nbsp;</td>\n\r";
         }

         $firstDayOfWeeknumber = intval($C->readConfig("firstDayOfWeek"));
         if ($firstDayOfWeeknumber<1 || $firstDayOfWeeknumber>7) $firstDayOfWeeknumber = 1;
         $lastDayOfWeeknumber = $firstDayOfWeeknumber-1;
         if ($lastDayOfWeeknumber==0) $lastDayOfWeeknumber = 7;
         for ($i=1; $i<=$nofdays; $i=$i+1) {
            if ($wd != $lastDayOfWeeknumber) {
               $colspan++;
               $wd++;
               if ($wd==8) $wd = 1;
            }
            else {
               $colspan++;
               $w=date("W",mktime(0,0,0,intval($mydate['mon']),$i,$year));
               $monthHeader.="<td class=\"weeknumber\" colspan=\"".$colspan."\">".sprintf("%d",$w)."</td>\n\r";
               $colspan=0;
               $wd++;
               if ($wd==8) $wd = 1;
            }
         }
         $w=date("W",mktime(0,0,0,intval($mydate['mon']),$i,$year));
         if ($colspan>0) $monthHeader.="<td class=\"weeknumber\" colspan=\"".$colspan."\">".sprintf("%d",$w)."</td>\n\r";
         $monthHeader.="</tr>\n\r";
      }

      /**
       * Print the weekday row: Name
       */
      $x = intval($weekday1);
      $monthHeader.="<tr>\n\r";
      $monthHeader.="<td class=\"title\">";
      if ($sortorder=="ASC") {
         $request = setRequests();
         $request .= "sort=DESC";
         $monthHeader.="<a href=\"".$_SERVER['PHP_SELF']."?".$request."\">";
         $monthHeader.="<img class=\"noprint\" alt=\"".$LANG['log_sort_desc']."\" title=\"".$LANG['log_sort_desc']."\" src=\"themes/".$theme."/img/desc.png\" align=\"middle\" border=\"0\"></a>";
      }
      else {
         $request = setRequests();
         $request .= "sort=ASC";
         $monthHeader.="<a href=\"".$_SERVER['PHP_SELF']."?".$request."\">";
         $monthHeader.="<img class=\"noprint\" alt=\"".$LANG['log_sort_asc']."\" title=\"".$LANG['log_sort_asc']."\" src=\"themes/".$theme."/img/asc.png\" align=\"middle\" border=\"0\"></a>";
      }
      $monthHeader.="&nbsp;".$LANG['cal_caption_name'];
      $monthHeader.="</td>\n\r";
      $monthHeader.="<td class=\"title-button\">";

      /**
       * Print the weekday row: Remainder section
       */
      if ( intval($C->readConfig("includeRemainder")) && $cntRemainders ) {
         if ( $CONF['options']['remainder']=="show" ) {
            /**
             * The remainder section is expanded. Display the collapse button.
             */
            $request = setRequests();
            $request=str_replace("remainder=show","remainder=hide",$request);
            $monthHeader.="<a href=\"".$_SERVER['PHP_SELF']."?".$request."\">";
            $monthHeader.="<img class=\"noprint\" alt=\"".$LANG['col_remainder']."\" title=\"".$LANG['col_remainder']."\" src=\"themes/".$theme."/img/hide_section.gif\" align=\"top\" border=\"0\"></a>";
            $monthHeader.="</td>\n\r";
            /**
             * Go through each absence type, see wether its option is set
             * to be shown in the remainders. Then display the remainder
             * title column.
             */
            $queryAC  = "SELECT `cfgsym` FROM `".$AC->table."` ORDER BY `dspname`;";
            $resultAC = $AC->db->db_query($queryAC);
            while ( $rowAC = $AC->db->db_fetch_array($resultAC,MYSQL_ASSOC) )
            {
               $AC->findBySymbol($rowAC['cfgsym']);
               if ( $AC->checkOptions($CONF['A_SHOWREMAIN']) )
               {
                  $monthHeader.="<td class=\"".$AC->cfgname."\" title=\"".$AC->dspname."\" style=\"border-right: 1px dotted #000000;\">";
                  if ($AC->iconfile) {
                     $monthHeader.="<img class=\"noprint\" align=\"top\" alt=\"\" src=\"".$CONF['app_icon_dir'].$AC->iconfile."\" width=\"16\" height=\"16\">";
                  }
                  else {
                     $monthHeader.=$AC->dspsym;
                  }
                  $monthHeader.="</td>\r\n";
               }
            }

            if ( intval($C->readConfig("includeTotals")) && $cntTotals ) {
               /**
                * Go through each absence type, see wether its option is set
                * to be shown in the totals. Then display the totals
                * title column.
                */
               $queryAC  = "SELECT `cfgsym` FROM `".$AC->table."` ORDER BY `dspname`;";
               $resultAC = $AC->db->db_query($queryAC);
               while ( $rowAC = $AC->db->db_fetch_array($resultAC,MYSQL_ASSOC) ) {
                  $AC->findBySymbol($rowAC['cfgsym']);
                  if ( $AC->checkOptions($CONF['A_SHOWTOTAL']) ) {
                     $monthHeader.="<td class=\"".$AC->cfgname."\" title=\"".$AC->dspname."\" style=\"border-right: 1px dotted #000000;\">";
                     if ($AC->iconfile) {
                        $monthHeader.="<img class=\"noprint\" align=\"top\" alt=\"\" src=\"".$CONF['app_icon_dir'].$AC->iconfile."\" width=\"16\" height=\"16\">";
                     }
                     else {
                        $monthHeader.=$AC->dspsym;
                     }
                     $monthHeader.="</td>\r\n";
                  }
               }
            }

         }
         else {
            /**
             * The remainder section is collapsed. Display the expand button.
             */
            $request = setRequests();
            $request=str_replace("remainder=hide","remainder=show",$request);
            $monthHeader.="<a href=\"".$_SERVER['PHP_SELF']."?".$request."\">";
            $monthHeader.="<img class=\"noprint\" alt=\"".$LANG['exp_remainder']."\" title=\"".$LANG['exp_remainder']."\" src=\"themes/".$theme."/img/show_section.gif\" align=\"top\" border=\"0\"></a>";
            $monthHeader.="</td>\n\r";
         }
      }
      else {
         $monthHeader.="&nbsp;</td>\n\r";
      }

      /**
       * Print the weekday row: Weekdays
       */
	   $title = "";
      for ($i=1; $i<=$nofdays; $i=$i+1) {
         //
         // Get general Daynote into $title if one exists
         //
         if ($i<10) $dd="0".strval($i); else $dd=strval($i);
         if ( $N->findAllByMonthUser($year,$monthno,$nofdays,"all",$CONF['options']['region']) ) {
            if (!empty($N->daynotes['all'][$year.$monthno.$dd])) {
               $title=htmlentities($N->daynotes['all'][$year.$monthno.$dd], ENT_QUOTES);
               $style="-note";
            }
            else {
               $title="";
               $style="";
            }
         }

         if ( $H->findBySymbol($M->template[$i-1]) ) {
            //
            // Display cell
            //
            if ( $H->cfgname=='busi' ) {
               if ( $todaysmonth && $i==intval($today['mday']) ) {
                  if (strlen($title)) {
                     $monthHeader.="<td class=\"toweekday".$style."\" onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$weekdays[$x]."</td>\n\r";
                  }
                  else {
                     $monthHeader.="<td class=\"toweekday\">".$weekdays[$x]."</td>\n\r";
                  }
               } else {
                  if (strlen($title)) {
                     $monthHeader.="<td class=\"weekday".$style."\" onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$weekdays[$x]."</td>\n\r";
                  }
                  else {
                     $monthHeader.="<td class=\"weekday\">".$weekdays[$x]."</td>\n\r";
                  }
               }
            } else {
               if ( $todaysmonth && $i==intval($today['mday']) ) {
                  if (strlen($title)) {
                     $monthHeader.="<td class=\"toweekday-".$H->cfgname."".$style."\" onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$weekdays[$x]."</td>\n\r";
                  }
                  else {
                     $monthHeader.="<td class=\"toweekday-".$H->cfgname."\">".$weekdays[$x]."</td>\n\r";
                  }
               } else {
                  if (strlen($title)) {
                     $monthHeader.="<td class=\"weekday-".$H->cfgname."".$style."\" onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$weekdays[$x]."</td>\n\r";
                  }
                  else {
                     $monthHeader.="<td class=\"weekday-".$H->cfgname."\">".$weekdays[$x]."</td>\n\r";
                  }
               }
            }
         } else {
            if ( $todaysmonth && $i==intval($today['mday']) ) {
               if (strlen($title)) {
                  $monthHeader.="<td class=\"toweekday".$style."\" onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$weekdays[$x]."</td>\n\r";
               }
               else {
                  $monthHeader.="<td class=\"toweekday\">".$weekdays[$x]."</td>\n\r";
               }
            } else {
               if (strlen($title)) {
                  $monthHeader.="<td class=\"weekday".$style."\" onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$weekdays[$x]."</td>\n\r";
               }
               else {
                  $monthHeader.="<td class=\"weekday\">".$weekdays[$x]."</td>\n\r";
               }
            }
         }
         if($x<=6) $x+=1; else $x = 1;
      }
      $monthHeader.="</tr>\n\r";

      /**
       * Write header into the output buffer
       */
      $showmonthBody .= $monthHeader;

      /**
       * Select usernames based on filter requests and put them in an array
       */
      $users = array();
      $groupfilter = $CONF['options']['groupfilter'];

      if ($groupfilter=="All") {
         $query = "SELECT DISTINCT ".$CONF['db_table_users'].".*" .
                  " FROM ".$CONF['db_table_users'].",".$CONF['db_table_user_group'].",".$CONF['db_table_groups'].
                  " WHERE ".$CONF['db_table_users'].".username != 'admin'" .
                  " AND ".$CONF['db_table_users'].".username=".$CONF['db_table_user_group'].".username" .
                  " AND (".$CONF['db_table_groups'].".groupname=".$CONF['db_table_user_group'].".groupname AND (".$CONF['db_table_groups'].".options&1)=0 )" .
                  " ORDER BY ".$CONF['db_table_users'].".lastname ".$sortorder.",".$CONF['db_table_users'].".firstname ASC";
         $result = $U->db->db_query($query);
         $i=0;
         while ( $row = $U->db->db_fetch_array($result,MYSQL_ASSOC) ) {
            $users[$i]['group']=$row['group'];
            $users[$i]['user']=$row['username'];
            $i++;
         }
      }
      else if ($groupfilter=="Allbygroup") {
         if (intval($C->readConfig("hideManagers"))) {
            $query = "SELECT DISTINCT ".$CONF['db_table_user_group'].".groupname, ".$CONF['db_table_user_group'].".username " .
                     " FROM ".$CONF['db_table_user_group'].", ".$CONF['db_table_users'].", ".$CONF['db_table_groups'].
                     " WHERE ".$CONF['db_table_users'].".username != 'admin'" .
                     " AND (".$CONF['db_table_groups'].".groupname=".$CONF['db_table_user_group'].".groupname AND (".$CONF['db_table_groups'].".options&1)=0 )" .
                     " AND ".$CONF['db_table_user_group'].".username=".$CONF['db_table_users'].".username" .
                     " AND ".$CONF['db_table_user_group'].".type!='manager'" .
                     " ORDER BY ".$CONF['db_table_user_group'].".groupname ASC, ".$CONF['db_table_users'].".lastname ".$sortorder.",".$CONF['db_table_users'].".firstname ASC";
         }
         else {
            $query = "SELECT DISTINCT ".$CONF['db_table_user_group'].".groupname, ".$CONF['db_table_user_group'].".username " .
                     " FROM ".$CONF['db_table_user_group'].", ".$CONF['db_table_users'].", ".$CONF['db_table_groups'].
                     " WHERE ".$CONF['db_table_users'].".username != 'admin'" .
                     " AND (".$CONF['db_table_groups'].".groupname=".$CONF['db_table_user_group'].".groupname AND (".$CONF['db_table_groups'].".options&1)=0 )" .
                     " AND ".$CONF['db_table_user_group'].".username=".$CONF['db_table_users'].".username" .
                     " ORDER BY ".$CONF['db_table_user_group'].".groupname ASC, ".$CONF['db_table_users'].".lastname ".$sortorder.",".$CONF['db_table_users'].".firstname ASC";
         }
         $result = $UG->db->db_query($query);
         $i=0;
         while ( $row = $UG->db->db_fetch_array($result,MYSQL_ASSOC) ) {
            $users[$i]['group']=$row['groupname'];
            $users[$i]['user']=$row['username'];
            $i++;
         }
      }
      else {
         /*
          * Get regular group members
          */
         if (intval($C->readConfig("hideManagers"))) {
            $query = "SELECT DISTINCT ".$CONF['db_table_users'].".*" .
                     " FROM ".$CONF['db_table_users'].",".$CONF['db_table_user_group'].",".$CONF['db_table_groups'].
                     " WHERE ".$CONF['db_table_users'].".username != 'admin'" .
                     " AND ".$CONF['db_table_users'].".username=".$CONF['db_table_user_group'].".username" .
                     " AND ".$CONF['db_table_groups'].".groupname='".$groupfilter."'" .
                     " AND (".$CONF['db_table_groups'].".groupname=".$CONF['db_table_user_group'].".groupname AND (".$CONF['db_table_groups'].".options&1)=0 )" .
                     " AND ".$CONF['db_table_user_group'].".type!='manager'" .
                     " ORDER BY ".$CONF['db_table_users'].".lastname ".$sortorder.",".$CONF['db_table_users'].".firstname ASC";
         }
         else {
            $query = "SELECT DISTINCT ".$CONF['db_table_users'].".*" .
                     " FROM ".$CONF['db_table_users'].",".$CONF['db_table_user_group'].",".$CONF['db_table_groups'].
                     " WHERE ".$CONF['db_table_users'].".username != 'admin'" .
                     " AND ".$CONF['db_table_users'].".username=".$CONF['db_table_user_group'].".username" .
                     " AND ".$CONF['db_table_groups'].".groupname='".$groupfilter."'" .
                     " AND (".$CONF['db_table_groups'].".groupname=".$CONF['db_table_user_group'].".groupname AND (".$CONF['db_table_groups'].".options&1)=0 )" .
                     " ORDER BY ".$CONF['db_table_users'].".lastname ".$sortorder.",".$CONF['db_table_users'].".firstname ASC";
         }
         $result = $U->db->db_query($query);
         $i=0;
         while ( $row = $U->db->db_fetch_array($result,MYSQL_ASSOC) ) {
            $users[$i]['group']=$row['group'];
            $users[$i]['user']=$row['username'];
            $users[$i]['mship']="real";
            $i++;
         }
         /*
          * Get related user to this group (user option: show in other groups)
          */
         if (intval($C->readConfig("hideManagers"))) {
            $query = "SELECT DISTINCT ".$CONF['db_table_users'].".*" .
                     " FROM ".$CONF['db_table_users'].",".$CONF['db_table_user_group'].",".$CONF['db_table_groups'].",".$CONF['db_table_user_options'].
                     " WHERE ".$CONF['db_table_users'].".username != 'admin'" .
                     " AND ".$CONF['db_table_users'].".username=".$CONF['db_table_user_group'].".username" .
                     " AND (".$CONF['db_table_groups'].".groupname!='".$groupfilter."' AND " .
                          "(".$CONF['db_table_users'].".username=".$CONF['db_table_user_options'].".username AND ".$CONF['db_table_user_options'].".option='showInGroups' AND ".$CONF['db_table_user_options'].".value LIKE '".$groupfilter."'))".
                     " AND (".$CONF['db_table_groups'].".groupname=".$CONF['db_table_user_group'].".groupname AND (".$CONF['db_table_groups'].".options&1)=0 )" .
                     " AND ".$CONF['db_table_user_group'].".type!='manager'" .
                     " ORDER BY ".$CONF['db_table_users'].".lastname ".$sortorder.",".$CONF['db_table_users'].".firstname ASC";
         }
         else {
            $query = "SELECT DISTINCT ".$CONF['db_table_users'].".*" .
                     " FROM ".$CONF['db_table_users'].",".$CONF['db_table_user_group'].",".$CONF['db_table_groups'].",".$CONF['db_table_user_options'].
                     " WHERE ".$CONF['db_table_users'].".username != 'admin'" .
                     " AND ".$CONF['db_table_users'].".username=".$CONF['db_table_user_group'].".username" .
                     " AND (".$CONF['db_table_groups'].".groupname!='".$groupfilter."' AND " .
                          "(".$CONF['db_table_users'].".username=".$CONF['db_table_user_options'].".username AND ".$CONF['db_table_user_options'].".option='showInGroups' AND ".$CONF['db_table_user_options'].".value LIKE '".$groupfilter."'))".
                     " AND (".$CONF['db_table_groups'].".groupname=".$CONF['db_table_user_group'].".groupname AND (".$CONF['db_table_groups'].".options&1)=0 )" .
                     " ORDER BY ".$CONF['db_table_users'].".lastname ".$sortorder.",".$CONF['db_table_users'].".firstname ASC";
         }
         $result = $U->db->db_query($query);
         while ( $row = $U->db->db_fetch_array($result,MYSQL_ASSOC) ) {
            $users[$i]['group']=$row['group'];
            $users[$i]['user']=$row['username'];
            $users[$i]['mship']="related";
            $i++;
         }
      }

      /**
       * Check whether an absence filter was requested
       */
      if ($CONF['options']['absencefilter']!="All" && $todaysmonth) {
         $j=0;
         for ($su=0; $su<count($users); $su++) {
            $found = $T->findTemplate(addslashes($users[$su]['user']),$year,$monthno);
            if (!$found) {
               /**
                * No template found for this user and month.
                * Create a default one.
                */
               $T->username = addslashes($users[$su]['user']);
               $T->year = $year;
               $T->month = $monthno;
               $T->template = "";
               for ($i=0; $i<intval($nofdays); $i++ ) {
                  $T->template .= $CONF['present'];
               }
               $T->create();
            }
            if ( $T->template[intval($today['mday'])-1]==$CONF['options']['absencefilter'] ) {
               $subusers[$j]['group']=$users[$su]['group'];
               $subusers[$j]['user']=$users[$su]['user'];
               $j++;
            }
         }
         $users = array();
         for ($su=0; $su<count($subusers); $su++) {
            $users[$su]['group']=$subusers[$su]['group'];
            $users[$su]['user']=$subusers[$su]['user'];
         }
      }


      $i=1;

      /**
       * Initialize the summary counts.
       * A) Create array $intSumPresentDay[], containing the sums of presents for each day of the month
       * B) Create array $intSumAbsentDay[], containing the sums of absences for each day of the month
       * C) Create array $arrAbsenceMonth[], one field for each absence type, containing the sum of it taken for the month
       * D) Create array $arrAbsenceDay[], one field for each absence type and day, containing the sum of it taken for the day
       */
      $intSumPresentMonth=0;
      $intSumAbsentMonth=0;
      for($x=0; $x<intval($nofdays); $x++) $intSumPresentDay[$x]=0; // Sum present per day
      for($x=0; $x<intval($nofdays); $x++) $intSumAbsentDay[$x]=0; // Sum absent per day
      $queryAC = "SELECT dspname FROM ".$AC->table." WHERE 1;";
      $resultAC = $AC->db->db_query($queryAC);
      while ( $rowAC=$AC->db->db_fetch_array($resultAC,MYSQL_ASSOC) ) {
         $arrAbsenceMonth[$rowAC['dspname']]=0;
         for($x=0; $x<intval($nofdays); $x++) {
            $arrAbsenceDay[$rowAC['dspname']][$x]=0;
         }
      }

      /**
       * Loop through all users previously selected into the array.
       * Initialize the row count which is per user. It is used to repeat the month header
       * later based on repeatHeaderRowCount.
       */
      $intSumUser = count($users);
      $intUsersPerPage = intval($C->readConfig("usersPerPage"));
      if (!$intUsersPerPage) $intUsersPerPage = 10000;
      $intNumPages = ceil($intSumUser/$intUsersPerPage);
      $intPageUserCount=-1;
      $intCurrentUserCount=-1;
      $intDisplayPage = $page;
      $currentgroup='';
      $newarray = array();

      /**
       * Get all usernames in an array
       */
		foreach($users as $value) {
			foreach($value as $key => $value2) {
				if ($key == 'user') {
					$newarray[] = mysql_real_escape_string($value2);
				}
			}
		}
		$userset = join("','", $newarray);

      /**
       * Get all daynotes for this userlist
       */
      $dayNotesExist = 0;
      if ( $N2->findAllByMonth($year,$monthno,$nofdays,$userset) ) {
         $dayNotesExist = 1;
      }

      foreach ($users as $usr) {
         $tempManager = false;
         $monthBody='';

         $U->findByName($usr['user']);

         /**
          * Permission to view this user?
          */
         $allowed=FALSE;
         if ( $user == $U->username ) {
            $allowed=TRUE;
         }
         else if ( $UG->shareGroups($user, $U->username) ) {
            if (isAllowed("viewGroupUserCalendars")) $allowed=TRUE;
         }
         else {
            if (isAllowed("viewAllUserCalendars")) $allowed=TRUE;
         }

         if ( $allowed AND !($U->status&$CONF['USHIDDEN']) ) {
            $intCurrentUserCount++;
            $intPageUserCount++;
            /**
             * Repeat month header if repeat count reached
             * $repHeadCnt = amount of user rows before new header to be inserted
             * $intPageUserCount = current amount of user rows we have on this page already
             * $intSumUser = how many users
             */
               if ( $intPageUserCount!=0 AND (($intPageUserCount)%$repHeadCnt)==0 ) {
                  $showmonthBody .= $monthHeader;
               }
               $intThisUsersPage = floor($intCurrentUserCount/$intUsersPerPage) + 1;
               if ( $intThisUsersPage < $intDisplayPage ) { $intPageUserCount=-1; continue; }
               if ( $intThisUsersPage > $intDisplayPage ) break;
               if ( $intPageUserCount == $intUsersPerPage ) break;

               if ( $groupfilter=="Allbygroup" ) {
               if (!strlen($currentgroup) OR $currentgroup!=$usr['group']) {
                  $currentgroup=$usr['group'];
                  $G->findByName($currentgroup);
                  $monthBody .= "<tr><td class=\"groupdelim\" colspan=\"".$cols."\">".$G->description."</td></tr>\n\r";
               }
            }

            if ( $U->firstname!="" ) $showname = stripslashes(ucwords(strtolower($U->lastname)).",&nbsp;".ucwords(strtolower($U->firstname)));
            else                     $showname = stripslashes($U->lastname);//user

            if (!strlen($showname)) $showname = $U->username;

            $monthBody .= "<tr>\n\r";
            $monthBody .= "<td class=\"name\">\n\r";

            /**
             * Get user avatar if configured
             */
            if ($C->readConfig("showUserIcons")) {
               $avatar_link='';
               $avatar_close='';
               if ($C->readConfig("showAvatars")) {
                  $avatar='';
                  $avatar_fullname=$U->title." ".$U->firstname." ".$U->lastname;
                  if( file_exists("img/avatar/".$U->username.".gif")) $avatar="<img src=img/avatar/".$U->username.".gif>";
                  else if( file_exists("img/avatar/".$U->username.".jpg")) $avatar="<img src=img/avatar/".$U->username.".jpg>";
                  else if( file_exists("img/avatar/".$U->username.".jpeg")) $avatar="<img src=img/avatar/".$U->username.".jpeg>";
                  else if( file_exists("img/avatar/".$U->username.".png")) $avatar="<img src=img/avatar/".$U->username.".png>";
                  if( strlen($avatar) ) {
                     $avatar_link="<a href=\"javascript:void(0);\" onmouseover=\"return overlib('$avatar', WIDTH, 100, HEIGHT, 140, BGCOLOR, '#FFB300', FGCOLOR, '#FEFEFE', CAPTION, '$avatar_fullname', MOUSEOFF);\" onmouseout=\"return nd();\">";
                     $avatar_close="</a>";
                  }
               }
            }

            if (!empty($managerOf)) {
               foreach ($managerOf as $value) {
                  if (($currentgroup == $value) || ($groupfilter == $value)) {
                     $tempManager = true;
                  }
               }
            }

            /**
             * Select user icon, make it female if necessary and put it in the body
             */
			   if ( $U->checkUserType($CONF['UTADMIN']) ) {
               $icon = "ico_usr_admin";
               $icon_tooltip = $LANG['icon_admin'];
            }
				else if ( $U->checkUserType($CONF['UTMANAGER']) ) {
               $icon = "ico_usr_manager";
               $icon_tooltip = $LANG['icon_manager'];
				}
				else {
               $icon = "ico_usr";
               $icon_tooltip = $LANG['icon_user'];
            }
            if ( !$U->checkUserType($CONF['UTMALE']) ) $icon .= "_f.png"; else $icon .= ".png";

            /**
             * If user is a related user (not member but shown in this group), just use grey icon
             */
            if ($groupfilter!="All" AND $groupfilter!="Allbygroup") {
               if ($usr['mship']=="related") {
                  $icon = "ico_usr_grey.png";
                  $icon_tooltip = $LANG['cal_tt_related_1'].$groupfilter.$LANG['cal_tt_related_2'];
               }
            }

            $monthBody .= "<img src=\"themes/".$theme."/img/".$icon."\" alt=\"img\" title=\"".$icon_tooltip."\" style=\"border: 0px; padding-right: 2px; vertical-align: top;\">";

            /**
             * Check permission to edit or view the profile
             */
            $editProfile=FALSE;
            if ( $UL->username == $U->username ) {
               $editProfile=TRUE;
            }
            else if ( $UG->shareGroups($UL->username, $U->username) ) {
               if (isAllowed("editGroupUserProfiles")) $editProfile=TRUE;
            }
            else {
               if (isAllowed("editAllUserProfiles")) $editProfile=TRUE;
            }

            $viewProfile=FALSE;
            if (isAllowed("viewUserProfiles")) $viewProfile=TRUE;

            if($editProfile) {
               $monthBody .= "&nbsp;<a class=\"name\" href=\"javascript:this.blur();openPopup('editprofile.php?referrer=index&amp;lang=".$CONF['options']['lang']."&amp;username=".addslashes($U->username)."','editprofile','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=600,height=680');\">".$showname."</a>\n\r";
            }
            else if($viewProfile) {
               $monthBody .= "&nbsp;<a class=\"name\" href=\"javascript:this.blur();openPopup('viewprofile.php?lang=".$CONF['options']['lang']."&amp;username=".addslashes($U->username)."','viewprofile','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=480,height=580');\">".$showname."</a>\n\r";
            }
            else {
               $monthBody .= $showname;
            }
			   $monthBody .= "</td>\n\r";

            /**
             * Show the custom popup if one exists
             */
            if (!strlen($U->customPopup) OR !$viewProfile)
               $monthBody .= "<td class=\"name-button\">\n\r";
            else
               $monthBody .= "<td class=\"name-button-note\" onmouseover=\"return overlib('".htmlentities($U->customPopup, ENT_QUOTES)."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">\n\r";

            /**
             * Check permission to edit or view the calendar
             */
            $editCalendar=FALSE;
            if ( $UL->username == $U->username ) {
               if (isAllowed("editOwnUserCalendars")) $editCalendar=TRUE;
            }
            else if ( $UG->shareGroups($UL->username, $U->username) ) {
               if (isAllowed("editGroupUserCalendars")) $editCalendar=TRUE;
            }
            else {
               if (isAllowed("editAllUserCalendars")) $editCalendar=TRUE;
            }

            if ($editCalendar) {
               if (!$thisregion = $UO->find($U->username,"defregion")) $thisregion = $CONF['options']['region'];
               $monthBody .= "<a href=\"javascript:openPopup('editcalendar.php?lang=".$CONF['options']['lang']."&amp;Year=".$year."&amp;Month=".$month."&amp;region=".$thisregion."&amp;Member=".addslashes($U->username)."','shop','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=no,dependent=1,width=980,height=640');\"><img class=\"noprint\" src=\"themes/".$theme."/img/btn_edit.gif\" width=\"16\" height=\"16\" border=\"0\" title=\"".$LANG['cal_img_alt_edit_cal']."\" alt=\"".$LANG['cal_img_alt_edit_cal']."\"></a>\n\r";
            }
            $monthBody .= "</td>\n\r";

            /**
             * Try to find this users template for this month
             */
            $found = $T->findTemplate(addslashes($U->username),$year,$monthno);
            if (!$found) {
               /**
                * No template found for this user and month.
                * Create a default one.
                */
               $T->username = addslashes($U->username);
               $T->year = $year;
               $T->month = $monthno;
               $T->template = "";
               for ($i=0; $i<intval($nofdays); $i++ ) {
                  $T->template .= $CONF['present'];
               }
               $T->create();
            }

            /**
             * Show the remainder section for this user
             */
            if ( intval($C->readConfig("includeRemainder")) && $cntRemainders ) {
               if ( $CONF['options']['remainder']=="show" ) {
                  /**
                   * Go through each absence type, see wether its option is set
                   * to be shown in the remainders. Then display the remainder
                   * if the current user has editCalendar rights.
                   */
                  $queryAC  = "SELECT `cfgsym` FROM `".$AC->table."` ORDER BY `dspname`;";
                  $resultAC = $AC->db->db_query($queryAC);
                  while ( $rowAC = $AC->db->db_fetch_array($resultAC,MYSQL_ASSOC) )
                  {
                     $AC->findBySymbol($rowAC['cfgsym']);
                     if ( $AC->checkOptions($CONF['A_CONFIDENTIAL']) ) $isConfidential = TRUE; else $isConfidential = FALSE;
                     if ( $U->username == $UL->username ) $isSameUser = TRUE; else $isSameUser = FALSE;
                     if ( $AC->checkOptions($CONF['A_SHOWREMAIN']) ) {
                        if (isAllowed("editAllUserCalendars") OR isAllowed("editGroupUserCalendars") OR isAllowed("editOwnUserCalendars")) {
                           if ( $AL->findAllowance($U->username,$rowAC['cfgsym']) ) {
                              $lastYearAllowance = $AL->lastyear;
                              $thisYearAllowance = $AL->curryear;
                           }
                           else {
                              $lastYearAllowance = 0;
                              $thisYearAllowance = $AC->allowance;
                           }

                           $from = str_replace("-","",$C->readConfig("defperiodfrom"));
                           $to = str_replace("-","",$C->readConfig("defperiodto"));
                           $thisYearTaken     = countAbsence(addslashes($U->username),$rowAC['cfgsym'],$from,$to);
                           $thisYearRemainder = $lastYearAllowance+$thisYearAllowance-$thisYearTaken;
                           $totalAllowance    = $lastYearAllowance+$thisYearAllowance;

                           if ( $isConfidential AND $regularUser AND !$isSameUser )
                           {
                              $thisYearTaken     = "&nbsp;";
                              $thisYearRemainder = "&nbsp;";
                              $totalAllowance    = "&nbsp;";
                           }

                           if ( $thisYearRemainder<0 ) $addStyle=" style=\"color: #FF0000;\""; else $addStyle="";
                           if ( intval($C->readConfig("includeRemainderTotal")) ) {
                              $monthBody .= "<td class=\"remainder\"><span".$addStyle.">".$thisYearRemainder."</span>/".$totalAllowance."</td>\n\r";
                           }
                           else {
                              $monthBody .= "<td class=\"remainder\"><span ".$addStyle.">".$thisYearRemainder."</span></td>\n\r";
                           }
                        }
                        else {
                           $monthBody .= "<td class=\"remainder\">&nbsp;</td>\n\r";
                        }
                     }
                  }

                  if ( intval($C->readConfig("includeTotals")) && $cntTotals ) {
                     /**
                      * Go through each absence type, see wether its option is set
                      * to be shown in the totals. Then display the total
                      * if the current user has editCalendar rights.
                      */
                     $first=true;
                     $queryAC  = "SELECT `cfgsym` FROM `".$AC->table."` ORDER BY `dspname`;";
                     $resultAC = $AC->db->db_query($queryAC);
                     while ( $rowAC = $AC->db->db_fetch_array($resultAC,MYSQL_ASSOC) )
                     {
                        $AC->findBySymbol($rowAC['cfgsym']);
                        if ( $AC->checkOptions($CONF['A_CONFIDENTIAL']) ) $isConfidential = TRUE; else $isConfidential = FALSE;
                        if ( $U->username == $UL->username ) $isSameUser = TRUE; else $isSameUser = FALSE;
                        if ( $AC->checkOptions($CONF['A_SHOWTOTAL']) )
                        {
                           if (isAllowed("editAllUserCalendars") OR isAllowed("editGroupUserCalendars") OR isAllowed("editOwnUserCalendars")) {
                              $thisTotal = substr_count($T->template,$AC->cfgsym);
                              if ( $isConfidential AND $regularUser AND !$isSameUser ) $thisTotal = "&nbsp;";

                              $addStyle="";
                              if ( $first )
                              {
                                 $addStyle=" style=\"border-left: 1px solid #000000;\"";
                                 $first=false;
                              }
                              $monthBody .= "<td class=\"totals\"".$addStyle."\">".$thisTotal."</td>\n\r";
                           }
                           else {
                              $monthBody .= "<td class=\"totals\">&nbsp;</td>\n\r";
                           }
                        }
                     }
                  }
               }
            }

            /**
             * Go through each day now
             */
			   for($i=0; $i<intval($nofdays); $i++) {

               /**
                * Check Birthday
                */
               $title="";
               $style="";
               if (($i+1)<10) $dd="0".strval($i+1); else $dd=strval($i+1);
               if ( (substr($U->birthday,4)==$monthno.$dd) && ($UO->true($U->username,"showbirthday")) ) {
                  if($UO->true($U->username,"ignoreage")) {
                     $birthdate=date("d M",strtotime($U->birthday));
                     $title  = "* ".$LANG['cal_birthday'].": ".$birthdate." * ";
                  } else {
                     $birthdate=date("d M Y",strtotime($U->birthday));
                     $dayofbirth=date("d M",strtotime($U->birthday));
                     $age=intval($year)-intval(substr($U->birthday,0,4));
                     $title  = "* ".$LANG['cal_birthday'].": ".$birthdate;
                     $title .= " (".$LANG['cal_age'].": ".$age.") * ";
                  }
                  $style="-bday";
               }

               /**
                * Check Daynote
                */
               if ( !intval($C->readConfig("hideDaynotes")) || !$regularUser ) {
                  if ($dayNotesExist == 1) {
                     if(!empty($N2->daynotes[addslashes($U->username)][$year.$monthno.$dd])) {
                        /**
                         * The personal daynote is appended to $title because it might
                         * contain a birthday text already. The style is overwritten.
                         * There can only be one marker.
                         */
                     	$title.=htmlentities($N2->daynotes[addslashes($U->username)][$year.$monthno.$dd], ENT_QUOTES);
                     	$style="-note";
                     }
                  }
               }

               $A->findBySymbol($T->template[$i]);
               if ( $A->dspname!="present" ) $isAbsence = TRUE; else $isAbsence = FALSE;
               if ( !$A->checkOptions($CONF['A_PRESENCE']) ) $countsAsAbsence = TRUE; else $countsAsAbsence = FALSE;
               if ( $A->checkOptions($CONF['A_CONFIDENTIAL']) ) $isConfidential = TRUE; else $isConfidential = FALSE;
               if ( $U->username == $UL->username ) $isSameUser = TRUE; else $isSameUser = FALSE;

               if ( !$isAbsence OR ($isAbsence AND $isConfidential AND $regularUser AND !$isSameUser) ) {
                  /**
                   * This person is present or the viewer may not see this absence. Lets color the day as present.
                   * Also, add this to the presence count for the summary.
                   */
                  if (!$isAbsence) {
                     $intSumPresentMonth++;
                     $intSumPresentDay[$i]++;
                  }

                  $inner = "&nbsp;";
                  if ( $isAbsence AND $isConfidential AND $regularUser AND !$isSameUser AND $C->readConfig("markConfidential") ) $inner = "X";

                  if ( $H->findBySymbol($M->template[$i]) ) {
                     if ( $todaysmonth && $i+1<intval($today['mday']) ) {
                        /**
                         * Today's month and day is in the past
                         */
                        if (strlen($C->readConfig("pastDayColor"))) $pdcolor="style=\"background: #".$C->readConfig("pastDayColor").";\""; else $pdcolor="";
                        if (strlen($title) && isAllowed("viewUserProfiles")) {
                           $monthBody .= "<td class=\"day-".$H->cfgname.$style."\" ".$pdcolor." onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$inner."</td>\n\r";
                        }
                        else {
                           $monthBody .= "<td class=\"day-".$H->cfgname."\" ".$pdcolor.">".$inner."</td>\n\r";
                        }
                     }
                     else if ( $todaysmonth && $i+1==intval($today['mday']) ) {
                        /**
                         * Today's month and day is today
                         */
                        if (strlen($title) && isAllowed("viewUserProfiles")) {
                           $monthBody .= "<td class=\"today-".$H->cfgname.$style."\" onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$inner."</td>\n\r";
                        }
                        else {
                           $monthBody .= "<td class=\"today-".$H->cfgname."\">".$inner."</td>\n\r";
                        }
                     }
                     else {
                        /**
                         * All other days
                         */
                        if (strlen($title) && isAllowed("viewUserProfiles")) {
                           $monthBody .= "<td class=\"day-".$H->cfgname.$style."\" onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$inner."</td>\n\r";
                        }
                        else {
                           $monthBody .= "<td class=\"day-".$H->cfgname."\">".$inner."</td>\n\r";
                        }
                     }
                  }
                  else {
                     if ( $todaysmonth && $i+1<intval($today['mday']) ) {
                        /**
                         * Today's month and day is in the past
                         */
                        if (strlen($C->readConfig("pastDayColor"))) $pdcolor="style=\"background: #".$C->readConfig("pastDayColor").";\""; else $pdcolor="";
                        if (strlen($title) && isAllowed("viewUserProfiles")) {
                           $monthBody .= "<td class=\"day".$style."\" ".$pdcolor." onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$inner."</td>\n\r";
                        }
                        else {
                           $monthBody .= "<td class=\"day\" ".$pdcolor.">".$inner."</td>\n\r";
                        }
                     }
                     else if ( $todaysmonth && $i+1==intval($today['mday']) ) {
                        /**
                         * Today's month and day is today
                         */
                        if (strlen($title) && isAllowed("viewUserProfiles")) {
                           $monthBody .= "<td class=\"today".$style."\" onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$inner."</td>\n\r";
                        }
                        else {
                           $monthBody .= "<td class=\"today\">".$inner."</td>\n\r";
                        }
                     } else {
                        /**
                         * All other days
                         */
                        if (strlen($title) && isAllowed("viewUserProfiles")) {
                           $monthBody .= "<td class=\"day".$style."\" onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">".$inner."</td>\n\r";
                        }
                        else {
                           $monthBody .= "<td class=\"day\">".$inner."</td>\n\r";
                        }
                     }
                  }
               }
               else {
                  /**
                   * This person is not present. Let's add this absence to
                   * the counter for the summary. Then we gotta color the
                   * day according to the absence type and show
                   * its display symbol.
                   *
                   * Also, add this to the absence count for the summary if it does not count as 'present'.
                   * Otherwise we have to add this to the presence count.
                   */
                  if ( !$countsAsAbsence ) {
                     $intSumPresentMonth++;
                     $intSumPresentDay[$i]++;
                  }
                  else {
                     $intSumAbsentMonth++;
                     $intSumAbsentDay[$i]++;
                     $arrAbsenceMonth[$A->dspname]++;
                     $arrAbsenceDay[$A->dspname][$i]++;
                  }

                  if ( $todaysmonth && $i+1==intval($today['mday']) ) $cssclass="to".$A->cfgname.$style;
                  else $cssclass=$A->cfgname.$style;
                  if ( strlen($title) && isAllowed("viewUserProfiles")) {
                     $monthBody .= "<td class=\"".$cssclass."\" onmouseover=\"return overlib('".$title."', ".$CONF['ovl_tt_settings'].");\" onmouseout=\"return nd();\">";
                     if ($A->iconfile) {
                        $monthBody .= "<img align=\"top\" alt=\"\" src=\"".$CONF['app_icon_dir'].$A->iconfile."\" width=\"16\" height=\"16\">";
                     }
                     else {
                        $monthBody .= $A->dspsym;
                     }
                     $monthBody .= "</td>";
                  }
                  else {
                     $monthBody .= "<td class=\"".$cssclass."\" title=\"".$A->dspname."\">";
                     if ($A->iconfile) {
                        $monthBody .= "<img align=\"top\" alt=\"\" src=\"".$CONF['app_icon_dir'].$A->iconfile."\" width=\"16\" height=\"16\">";
                     }
                     else {
                        $monthBody .= $A->dspsym;
                     }
                     $monthBody .= "</td>";
                  }
               }

               $A->cfgsym='.';
               $A->cfgname='present';
               $A->dspsym='';
               $A->dspname='present';
            }
            $monthBody .= "</tr>\n\r";

            /**
             * Write body into output buffer
             */
            $showmonthBody .= $monthBody;
         }
         // end if ( !($U->status&$CONF['USHIDDEN']) )
      }
      // end foreach ($users as $usr)

      /**
       * Now print a summary row for this month
       * Summary Header
       */
      $summaryBody='';
      if ($C->readConfig("includeSummary")) {
         $summaryBody .= "<tr>\n\r";
         $summaryBody .= "   <td class=\"title\" colspan=\"3\">";
         $summaryBody .= "      <b>".$LANG['sum_summary'].":</b>&nbsp;";

         $request = setRequests();
         if ($CONF['options']['summary']=="show") {
            $request=str_replace("summary=show","summary=hide",$request);
            $summaryBody .= "<a href=\"".$_SERVER['PHP_SELF']."?".$request."\">";
            $summaryBody .= "<img alt=\"".$LANG['col_summary']."\" title=\"".$LANG['col_summary']."\" src=\"themes/".$theme."/img/hide_section.gif\" align=\"top\" border=\"0\"></a>";
         }
         else {
            $request=str_replace("summary=hide","summary=show",$request);
            $summaryBody .= "<a href=\"".$_SERVER['PHP_SELF']."?".$request."\">";
            $summaryBody .= "<img alt=\"".$LANG['exp_summary']."\" title=\"".$LANG['exp_summary']."\" src=\"themes/".$theme."/img/show_section.gif\" align=\"top\" border=\"0\"></a>";
         }

         $summaryBody .= "   </td>\n\r";
         $summaryBody .= "   <td class=\"title-button\" colspan=\"".($cols-3)."\">".$businessDayCount." ".$LANG['sum_business_day_count']."</td>\n\r";
         $summaryBody .= "</tr>\n\r";

         if ($CONF['options']['summary']=="show") {
            /**
             * Sum Present
             */
            $summaryBody .= "<tr>\n\r";
            $summaryBody .= "   <td class=\"name\"><b>".$LANG['sum_present']."</b></td>\n\r";
            $summaryBody .= "   <td class=\"name-button\">&nbsp;</td>\n\r";
            if ( intval($C->readConfig("includeRemainder")) && $CONF['options']['remainder']=="show" && $cntRemainders ) {
               $summaryColSpan = $cntRemainders;
               if ( intval($C->readConfig("includeTotals")) ) $summaryColSpan+=$cntTotals;
               $summaryBody .= "<td class=\"day\" colspan=\"".$summaryColSpan."\"></td>\r\n";
            }
            for($i=0; $i<intval($nofdays); $i++) {
               if ( $H->findBySymbol($M->template[$i]) ) {
                  if ($H->checkOptions($CONF['H_BUSINESSDAY'])) $summaryValue = $intSumPresentDay[$i];
                  else $summaryValue = "&nbsp;";
                  if ( $todaysmonth && $i+1==intval($today['mday']) ) {
                     $summaryBody .= "<td class=\"today-".$H->cfgname."-sum-present\">".$summaryValue."</td>\n\r";
                  } else {
                     $summaryBody .= "<td class=\"day-".$H->cfgname."-sum-present\">".$summaryValue."</td>\n\r";
                  }
               } else {
                  if ( $todaysmonth && $i+1==intval($today['mday']) ) {
                     $summaryBody .= "<td class=\"today-sum-present\">".$intSumPresentDay[$i]."</td>\n\r";
                  } else {
                     $summaryBody .= "<td class=\"day-sum-present\">".$intSumPresentDay[$i]."</td>\n\r";
                  }
               }
            }
            $summaryBody .= "</tr>\n\r";

            /**
             * Sum Absent
             */
            $summaryBody .= "<tr>\n\r";
            $summaryBody .= "   <td class=\"name\"><b>".$LANG['sum_absent']."</b></td>\n\r";
            $summaryBody .= "   <td class=\"name-button\">&nbsp;</td>\n\r";
            if ( intval($C->readConfig("includeRemainder")) && $CONF['options']['remainder']=="show" && $cntRemainders ) {
               $summaryColSpan = $cntRemainders;
               if ( intval($C->readConfig("includeTotals")) ) $summaryColSpan+=$cntTotals;
               $summaryBody .= "<td class=\"day\" colspan=\"".$summaryColSpan."\"></td>\r\n";
            }
            for($i=0; $i<intval($nofdays); $i++) {
               if ( $H->findBySymbol($M->template[$i]) ) {
                  if ($H->checkOptions($CONF['H_BUSINESSDAY'])) $summaryValue = $intSumAbsentDay[$i];
                  else $summaryValue = "&nbsp;";
                  if ( $todaysmonth && $i+1==intval($today['mday']) ) {
                     $summaryBody .= "<td class=\"today-".$H->cfgname."-sum-absent\">".$summaryValue."</td>\n\r";
                  } else {
                     $summaryBody .= "<td class=\"day-".$H->cfgname."-sum-absent\">".$summaryValue."</td>\n\r";
                  }
               } else {
                  if ( $todaysmonth && $i+1==intval($today['mday']) ) {
                     $summaryBody .= "<td class=\"today-sum-absent\">".$intSumAbsentDay[$i]."</td>\n\r";
                  } else {
                     $summaryBody .= "<td class=\"day-sum-absent\">".$intSumAbsentDay[$i]."</td>\n\r";
                  }
               }
            }
            $summaryBody .= "</tr>\n\r";

            /**
             * Delta: Present-Absent
             */
            $summaryBody .= "<tr>\n\r";
            $summaryBody .= "   <td class=\"name\"><b>".$LANG['sum_delta']."</b></td>\n\r";
            $summaryBody .= "   <td class=\"name-button\">&nbsp;</td>\n\r";
            if ( intval($C->readConfig("includeRemainder")) && $CONF['options']['remainder']=="show" && $cntRemainders ) {
               $summaryColSpan = $cntRemainders;
               if ( intval($C->readConfig("includeTotals")) ) $summaryColSpan+=$cntTotals;
               $summaryBody .= "<td class=\"day\" colspan=\"".$summaryColSpan."\"></td>\r\n";
            }
            for($i=0; $i<intval($nofdays); $i++) {
               if (($delta=$intSumPresentDay[$i]-$intSumAbsentDay[$i])>=0) $suffix="-positive";
               else $suffix="-negative";
               if ( $H->findBySymbol($M->template[$i]) ) {
                  if ($H->checkOptions($CONF['H_BUSINESSDAY'])) $summaryValue = $delta;
                  else $summaryValue = "&nbsp;";
                  if ( $todaysmonth && $i+1==intval($today['mday']) ) {
                     $summaryBody .= "<td class=\"today-".$H->cfgname."-sum-delta".$suffix."\">".$summaryValue."</td>\n\r";
                  } else {
                     $summaryBody .= "<td class=\"day-".$H->cfgname."-sum-delta".$suffix."\">".$summaryValue."</td>\n\r";
                  }
               } else {
                  if ( $todaysmonth && $i+1==intval($today['mday']) ) {
                     $summaryBody .= "<td class=\"today-sum-delta".$suffix."\">".$delta."</td>\n\r";
                  } else {
                     $summaryBody .= "<td class=\"day-sum-delta".$suffix."\">".$delta."</td>\n\r";
                  }
               }
            }
            $summaryBody .= "</tr>\n\r";

            /**
             * Absence Summary Header
             */
            $summaryBody .= "<tr>\n\r";
            $summaryBody .= "   <td class=\"title\" colspan=\"".($cols-1)."\"><b>".$LANG['sum_absence_summary'].":</b></td>\n\r";
            $summaryBody .= "   <td class=\"title-button\">&nbsp;</td>\n\r";
            $summaryBody .= "</tr>\n\r";

            /**
             * Day Absences, one per row. Hide confidential ones to regular users.
             */
            $queryAC = "SELECT dspname,options FROM ".$AC->table." ORDER BY dspname;";
            $resultAC = $AC->db->db_query($queryAC);
            while ( $rowAC=$AC->db->db_fetch_array($resultAC,MYSQL_ASSOC) ) {
               if ( $rowAC['dspname']!="present" ) $isAbsence = TRUE; else $isAbsence = FALSE;
               if ( !($rowAC['options'] & $CONF['A_PRESENCE']) ) $countsAsAbsence = TRUE; else $countsAsAbsence = FALSE;
               if ( ($rowAC['options'] & $CONF['A_CONFIDENTIAL']) ) $isConfidential = TRUE; else $isConfidential = FALSE;
               /**
                * Only show those that do not count as 'present'
                */
               if ( ($isAbsence AND $countsAsAbsence AND !$isConfidential)
                    OR
                    ($isAbsence AND $countsAsAbsence AND $isConfidential AND !$regularUser)
                  ) {
                  $summaryBody .= "<tr>\n\r";
                  $summaryBody .= "   <td class=\"name\">".$rowAC['dspname']."</td>\n\r";
                  $summaryBody .= "   <td class=\"name-button\">".$arrAbsenceMonth[$rowAC['dspname']]."</td>\n\r";
                  if ( intval($C->readConfig("includeRemainder")) && $CONF['options']['remainder']=="show" && $cntRemainders ) {
                     $summaryColSpan = $cntRemainders;
                     if ( intval($C->readConfig("includeTotals")) ) $summaryColSpan+=$cntTotals;
                     $summaryBody .= "<td class=\"day\" colspan=\"".$summaryColSpan."\"></td>\r\n";
                  }
                  for($i=0; $i<intval($nofdays); $i++) {
                     if ( $H->findBySymbol($M->template[$i]) ) {
                        if ($H->checkOptions($CONF['H_BUSINESSDAY'])) $summaryValue = $arrAbsenceDay[$rowAC['dspname']][$i];
                        else $summaryValue = "&nbsp;";
                        if ( $todaysmonth && $i+1==intval($today['mday']) ) {
                           $summaryBody .= "<td class=\"today-".$H->cfgname."-day-absent\">".$summaryValue."</td>\n\r";
                        }
                        else {
                           $summaryBody .= "<td class=\"day-".$H->cfgname."-day-absent\">".$summaryValue."</td>\n\r";
                        }
                     }
                     else {
                        if ( $todaysmonth && $i+1==intval($today['mday']) ) {
                           $summaryBody .= "<td class=\"today-day-absent\">".$arrAbsenceDay[$rowAC['dspname']][$i]."</td>\n\r";
                        }
                        else {
                           $summaryBody .= "<td class=\"day-day-absent\">".$arrAbsenceDay[$rowAC['dspname']][$i]."</td>\n\r";
                        }
                     }
                  }
                  $summaryBody .= "</tr>\n\r";
               }
            }
         }
      }
      $summaryBody .= "</table>\n\r";
      $summaryBody .= "<br>\n\r";

      /**
       * Write summary into output buffer
       */
      $showmonthBody .= $summaryBody;

      /**
       * Print paging buttons if required
       */
      if ($page) {
	      if ($intNumPages>1) {
	         if ($intDisplayPage==1) $showmonthBody .= '<input type="button" class="button" value="'.$LANG['btn_prev'].'">&nbsp;';
            else $showmonthBody .= '<input type="button" class="button" onclick="window.location.href('.$_SERVER['PHP_SELF'].'?action=calendar&amp;page='.($intDisplayPage-1).'&lang='.$CONF['options']['lang'].');" value="'.$LANG['btn_prev'].'">&nbsp;';

            for ($i=1; $i<=$intNumPages; $i++) {
               if ($intDisplayPage==$i) $showmonthBody .= '<span>'.$intDisplayPage.'</span>&nbsp;';
               else $showmonthBody .= '<input type="button" class="button" onclick="window.location.href('.$_SERVER['PHP_SELF'].'?action=calendar&amp;page='.($i).'&lang='.$CONF['options']['lang'].');" value="'.$i.'">&nbsp;';
            }

            if ($intDisplayPage==$intNumPages) $showmonthBody .= '<span class="button">'.$LANG['btn_next'].'</span>&nbsp;';
            else $showmonthBody .= '<input type="button" class="button" onclick="window.location.href('.$_SERVER['PHP_SELF'].'?action=calendar&amp;page='.($intDisplayPage+1).'&lang='.$CONF['options']['lang'].');" value="'.$LANG['btn_next'].'">&nbsp;';
	      }
	   }

   } // End if ($monthname && $nofdays && $M->template && $weekday1)

   /**
    * Ok, release the body...
    */
   return $showmonthBody;

} // End function showMonth
?>
