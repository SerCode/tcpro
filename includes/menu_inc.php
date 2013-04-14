<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * menu_inc.php
 *
 * Displays the TeamCal Pro menu on every main page
 *
 * @package TeamCalPro
 * @version 3.6.001 Dev
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */

/**
 * Includes
 */
require_once ("config.tcpro.php");
require_once ($CONF['app_root']."helpers/global_helper.php");
require_once ($CONF['app_root']."languages/".$CONF['options']['lang'].".tcpro.php");

require_once ($CONF['app_root']."models/absence_model.php");
require_once ($CONF['app_root']."models/group_model.php");
require_once ($CONF['app_root']."models/region_model.php");
require_once ($CONF['app_root']."models/user_announcement_model.php");
require_once ($CONF['app_root']."models/user_group_model.php");
require_once ($CONF['app_root']."models/user_option_model.php");

$A = new Absence_model;
$G = new Group_model;
$L = new Login_model;
$R = new Region_model;
$UA = new User_announcement_model;
$UG = new User_group_model;
$UL = new User_model;
$UO = new User_option_model;

$user=$L->checkLogin();
$UL->findByName($user);

/**
 * Build menu flags based on permissions
 */
$m = buildMenu();
?>

<!-- MENU BAR ============================================================= -->
<div id="menubar">
   <div id="menubar-content">
      <div id="myMenuID" style="position: relative; left: 7px;"></div>
      <script type="text/javascript">
      <!--
      var myMenu =
      [
         [null,'<?=$LANG['mnu_teamcal']?>',null,null,null,
            <?php if ($m['mnu_teamcal_login']) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_login.png" />','<?=$LANG['mnu_teamcal_login']?>','login.php?lang=<?=$CONF['options']['lang']?>',null,null],
            <?php }
            if ($m['mnu_teamcal_register']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_register.png" />','<?=$LANG['mnu_teamcal_register']?>','javascript:openPopup(\'register.php?lang=<?=$CONF['options']['lang']?>\',\'login\',\'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=420,height=550\');',null,null],
            <?php }
            if ($m['mnu_teamcal_logout']) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_logout.png" />','<?=$LANG['mnu_teamcal_logout']?>','index.php?action=logout&lang=<?=$CONF['options']['lang']?>',null,null],
            <?php } ?>
         ],
         _cmSplit,
         [null,'<?=$LANG['mnu_view']?>',null,null,null,
            ['<img src="themes/<?=$theme?>/img/menu/ico_home.png" />','<?=$LANG['mnu_view_homepage']?>','index.php?action=welcome&lang=<?=$CONF['options']['lang']?>',null,null],
            <?php if ($m['mnu_view_calendar']) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_calendar.png" />','<?=$LANG['mnu_view_calendar']?>','calendar.php?lang=<?=$CONF['options']['lang']?>',null,null],
            <?php }
            if ($m['mnu_view_yearcalendar']) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_calendar.png" />','<?=$LANG['mnu_view_yearcalendar']?>','showyear.php?lang=<?=$CONF['options']['lang']?>',null,null],
            <?php }
            if ($m['mnu_view_announcement']) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_announcement.png" />','<?=$LANG['mnu_view_announcement']?>','announcement.php?lang=<?=$CONF['options']['lang']?>',null,null],
            <?php }
            if ($m['mnu_view_statistics']) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_statistics.png" />','<?=$LANG['mnu_view_statistics']?>...',null,null,null,
               ['<img src="themes/<?=$theme?>/img/menu/ico_statistics.png" />','<?=$LANG['mnu_view_statistics_g']?>','statistics.php?lang=<?=$CONF['options']['lang']?>',null,null],
               ['<img src="themes/<?=$theme?>/img/menu/ico_statistics.png" />','<?=$LANG['mnu_view_statistics_r']?>','statisticsu.php?lang=<?=$CONF['options']['lang']?>',null,null],
            ]
            <?php } ?>
         ],
         _cmSplit,
         <?php if ($m['mnu_tools'] ) { ?>
         [null,'<?=$LANG['mnu_tools']?>',null,null,null,
            <?php if ($m['mnu_tools_profile'] ) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_usr.png" />','<?=$LANG['mnu_tools_profile']?>','javascript:openPopup(\'editprofile.php?referrer=index&username=<?=addslashes($UL->username)?>&lang=<?=$CONF['options']['lang']?>\',\'profile\',\'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=600,height=700\');',null,null],
            <?php }
            if ($m['mnu_tools_message']) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_message.png" />','<?=$LANG['mnu_tools_message']?>','message.php?lang=<?=$CONF['options']['lang']?>',null,null],
            <?php }
            if ($m['mnu_tools_webmeasure']) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_calc.png" />','<?=$LANG['mnu_tools_webmeasure']?>','javascript:openPopup(\'http://measure.lewe.com\',\'message\',\'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=820,height=480\');',null,null],
            <?php }
            if ($m['mnu_tools_admin']) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_configure.png" />','<?=$LANG['mnu_tools_admin']?>...',null,null,null,
               <?php if ($m['mnu_tools_admin_config']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_configure.png" />','<?=$LANG['mnu_tools_admin_config']?>','config.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php }
               if ($m['mnu_tools_admin_perm']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_permissions.png" />','<?=$LANG['mnu_tools_admin_perm']?>','permissions.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php }
               if ( ($m['mnu_tools_admin_config'] OR $m['mnu_tools_admin_perm'])
                     AND
                    ($m['mnu_tools_admin_users'] OR
                     $m['mnu_tools_admin_groups'] OR
                     $m['mnu_tools_admin_usergroups'] OR
                     $m['mnu_tools_admin_absences'] OR
                     $m['mnu_tools_admin_regions'] OR
                     $m['mnu_tools_admin_holidays'] OR
                     $m['mnu_tools_admin_declination'] OR
                     $m['mnu_tools_admin_database'])
                  ) { ?>
               _cmSplit,
               <?php }
               if ($m['mnu_tools_admin_users']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_usr.png" />','<?=$LANG['mnu_tools_admin_users']?>','userlist.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php }
               if ($m['mnu_tools_admin_groups']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_usr_member.png" />','<?=$LANG['mnu_tools_admin_groups']?>','groups.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php }
               if ($m['mnu_tools_admin_usergroups']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_usr_member.png" />','<?=$LANG['mnu_tools_admin_usergroups']?>','groupassign.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php }
               if ($m['mnu_tools_admin_absences']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_absences.png" />','<?=$LANG['mnu_tools_admin_absences']?>','absences.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php }
               if ($m['mnu_tools_admin_regions']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_region.png" />','<?=$LANG['mnu_tools_admin_regions']?>','regions.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php }
               if ($m['mnu_tools_admin_holidays']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_calendar.png" />','<?=$LANG['mnu_tools_admin_holidays']?>','holidays.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php }
               if ($m['mnu_tools_admin_declination']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_declination.png" />','<?=$LANG['mnu_tools_admin_declination']?>','declination.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php }
               if ($m['mnu_tools_admin_database']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_database.png" />','<?=$LANG['mnu_tools_admin_database']?>','database.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php }
               if ( ($m['mnu_tools_admin_users'] OR
                     $m['mnu_tools_admin_groups'] OR
                     $m['mnu_tools_admin_usergroups'] OR
                     $m['mnu_tools_admin_absences'] OR
                     $m['mnu_tools_admin_regions'] OR
                     $m['mnu_tools_admin_holidays'] OR
                     $m['mnu_tools_admin_declination'] OR
                     $m['mnu_tools_admin_database'])
                     AND
                     ($m['mnu_tools_admin_systemlog'] OR
                      $m['mnu_tools_admin_env'])
                  ) { ?>
               _cmSplit,
               <?php }
               if ($m['mnu_tools_admin_systemlog']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_log.png" />','<?=$LANG['mnu_tools_admin_systemlog']?>','log.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php }
               if ($m['mnu_tools_admin_env']) { ?>
               ['<img src="themes/<?=$theme?>/img/menu/ico_env.png" />','<?=$LANG['mnu_tools_admin_env']?>','environment.php?lang=<?=$CONF['options']['lang']?>',null,null],
               ['<img src="themes/<?=$theme?>/img/menu/ico_php.png" />','<?=$LANG['mnu_tools_admin_phpinfo']?>','phpinfo.php?lang=<?=$CONF['options']['lang']?>',null,null],
               <?php } ?>
            ],
            <?php } ?>
         ],
         <?php } ?>
         _cmSplit,
         [null,'<?=$LANG['mnu_help']?>',null,null,null,
            <?php if ($m['mnu_help_legend']) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_legend.png" />','<?=$LANG['mnu_help_legend']?>','javascript:openPopup(\'legend.php?lang=<?=$CONF['options']['lang']?>\',\'legend\',\'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=500,height=540\');',null,null],
            <?php } ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_help.png" />','<?=$LANG['mnu_help_help']?>',null,null,null,
               ['<img src="themes/<?=$theme?>/img/menu/ico_help.png" />','<?=$LANG['mnu_help_help_manualbrowser']?>','javascript:openPopup(\'help/<?=$CONF['options']['helplang']?>/html/index.html\',\'help\',\'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=800,height=500\');',null,null],
               ['<img src="themes/<?=$theme?>/img/menu/ico_help.png" />','<?=$LANG['mnu_help_help_manualpdf']?>','help/<?=$CONF['options']['helplang']?>/Pdf/tcpro.pdf',null,null],
            ],
            <?php
            /**
             * You may not disable or alter the About dialog nor its menu item here.
             */
            ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_calendar.png" />','<?=$LANG['mnu_help_about']?>','javascript:openPopup(\'about.php\',\'about\',\'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0,titlebar=0,resizable=0,dependent=1,width=580,height=370\');',null,null],
         ],
      ];
      cmDraw ('myMenuID', myMenu, 'hbr', cmThemeOffice, 'ThemeOffice');
      -->
      </script>
   </div>
</div>

<?php
/**
 * ============================================================================
 * OPTIONS BAR
 */
$optionitems=FALSE;
if ( $C->readConfig("showLanguage") OR
     $C->readConfig("showGroup") OR
     $C->readConfig("showToday") OR
     $C->readConfig("showStart") OR
     substr_count($_SERVER['PHP_SELF'],"calendar.php") OR
     substr_count($_SERVER['PHP_SELF'],"showyear.php")
   ) {
?>
<!-- OPTIONS BAR ========================================================== -->
<div id="optionsbar">
   <form class="form" method="POST" name="form_options" action="<?=$_SERVER['PHP_SELF']."?".setRequests()?>">
      <span id="optionsbar-content">
      <?php
      /**
       * ALL PAGES
       * Language
       */
      if ($C->readConfig("showLanguage")) { 
         include ($CONF['app_root']."includes/options_language_inc.php");
         $optionitems=TRUE;
      }
        
      /**
       * CALENDAR
       * Group, Region, Absence, Start-year, Start-month, Number of months
       */
   	if (substr_count($_SERVER['PHP_SELF'],"calendar.php") AND isAllowed("viewCalendar")) {
         include ($CONF['app_root']."includes/options_calendar_inc.php");
         $optionitems=TRUE;
      } 

      /**
       * YEAR CALENDAR
       * Year, User
       */
      if (substr_count($_SERVER['PHP_SELF'],"showyear.php") AND isAllowed("viewYearCalendar")) {
         include ($CONF['app_root']."includes/options_showyear_inc.php");
         $optionitems=TRUE;
      }
      
      /**
       * GLOBAL STATISTICS
       * Standard Period, Custom Period, Group, Absence
       */
      if (substr_count($_SERVER['PHP_SELF'],"statistics.php")AND isAllowed("viewStatistics")) {
         include ($CONF['app_root']."includes/options_statistics_inc.php");
         $optionitems=TRUE;
      }

      /**
       * REMAINDER STATISTICS
       * Group, User
       */
      if (substr_count($_SERVER['PHP_SELF'],"statisticsu.php")AND isAllowed("viewStatistics")) {
         include ($CONF['app_root']."includes/options_statisticsu_inc.php");
         $optionitems=TRUE;
      }

      ?>
      </span>

      <?php
      /**
       * Buttons and announcement icon on all pages
       */
      ?>
      <span id="optionsbar-buttons">&nbsp;
         <?php if ( $optionitems ) { ?>
            <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">
            <input name="btn_reset" type="button" class="button" onclick="javascript:document.location.href='<?=$_SERVER['PHP_SELF']?>'" value="<?=$LANG['btn_reset']?>">
         <?php }
         /**
          * Display announcement icon for this user
          */
         if (isAllowed("viewAnnouncements")) {
            $uas=$UA->getAllForUser($UL->username);
            if (count($uas)) { ?>
          	   <a href="announcement.php?uaname=<?=$UL->username?>"><img src="themes/<?=$theme?>/img/ico_bell.png" alt="" title="You got Announcements..." style="padding-left: 18px; vertical-align: middle;"></a> (<?=count($uas)?>)
        	   <?php }
         }
         ?>
      </span>
   </form>
</div>
<?php } ?>

<?php
/**
 * ============================================================================
 * STATUS BAR
 */
?>
<!-- STATUS BAR =========================================================== -->
<div id="statusbar">
   <div id="statusbar-content">
      <?php
      if ($user = $L->checkLogin()) {
         $UL->findByName($user);

         if( $UL->checkUserType($CONF['UTUSER']) ) {
            $utype = $LANG['status_ut_user'];
            $icon = "ico_usr";
            $icon_tooltip = $LANG['icon_user'];
         }

         if( $UL->checkUserType($CONF['UTMANAGER']) ) {
            require_once( $CONF['app_root']."models/user_group_model.php" );
            $UG = new User_group_model;
            $groups='';
            $queryUG  = "SELECT `groupname` FROM `".$CONF['db_table_user_group']."` WHERE `username`='".$UL->username."' AND `type`='manager' ORDER BY `groupname`;";
            $resultUG = $UG->db->db_query($queryUG);
            while ( $rowUG = $UG->db->db_fetch_array($resultUG) ){
               $groups.=stripslashes($rowUG['groupname']).", ";
               }
            $groups=substr($groups,0,strlen($groups)-2);
            $utype = $LANG['status_ut_manager']." ".$groups;
            $icon = "ico_usr_manager";
            $icon_tooltip = $LANG['icon_manager'];
         }

         if( $UL->checkUserType($CONF['UTDIRECTOR']) ) {
            $utype = $LANG['status_ut_director'];
            $icon = "ico_usr_director";
            $icon_tooltip = $LANG['icon_director'];
         }

         if( $UL->checkUserType($CONF['UTADMIN']) ) {
            $utype = $LANG['status_ut_admin'];
            $icon = "ico_usr_admin";
            $icon_tooltip = $LANG['icon_admin'];
         }
         if ( !$UL->checkUserType($CONF['UTMALE']) ) $icon .= "_f.png";
         else $icon .= ".png";
         ?>
         <span class="loggedin">
            <img src="themes/<?=$theme?>/img/<?=$icon?>" alt="" title="<?=$icon_tooltip?>" align="top" style="padding-right: 2px;">
            <?=$LANG['status_logged_in']?> <?=$user?> (<?=$utype?>)
         </span>
      <?php }
      else { ?>
         <span class="loggedout">
            <?=$LANG['status_logged_out']?>
         </span>
      <?php } ?>
   </div>
</div>

<!-- CONTENT ============================================================== -->
