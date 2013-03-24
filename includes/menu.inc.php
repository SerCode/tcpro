<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * menu.inc.php
 *
 * Displays the TeamCal Pro menu on every main page
 *
 * @package TeamCalPro
 * @version 3.5.002
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */

/**
 * Includes
 */
require_once ("config.tcpro.php");
require_once( $CONF['app_root']."includes/functions.tcpro.php" );
getOptions();
require( $CONF['app_root']."includes/lang/".$CONF['options']['lang'].".tcpro.php");

require_once( $CONF['app_root']."models/group_model.php" );
require_once( $CONF['app_root']."models/region_model.php" );
require_once( $CONF['app_root']."models/user_announcement_model.php" );
require_once ($CONF['app_root']."includes/tcusergroup.class.php");
require_once ($CONF['app_root']."includes/tcuseroption.class.php");

$G = new Group_model;
$L = new Login_model;
$R = new Region_model;
$UA = new User_announcement_model;
$UG = new tcUserGroup;
$UL = new tcUser;
$UO = new tcUserOption;

$user=$L->checkLogin();
$UL->findByName($user);

/**
 * Build menu flags based on permissions
 */
$m = buildMenu();
?>
<div id="menubar">
   <!-- MENU START ======================================================== -->
   <div id="myMenuID" style="position: relative; left: 7px;"></div>
   <script type="text/javascript">
   <!--
   var myMenu =
   [
      [null,'<?=$LANG['mnu_teamcal']?>',null,null,null,
         <?php if ($m['mnu_teamcal_login']) { ?>
         ['<img src="themes/<?=$theme?>/img/menu/ico_login.png" />','<?=$LANG['mnu_teamcal_login']?>','javascript:openPopup(\'login.php?lang=<?=$CONF['options']['lang']?>\',\'login\',\'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=420,height=300\');',null,null],
         <?php }
         if ($m['mnu_teamcal_register']) { ?>
            ['<img src="themes/<?=$theme?>/img/menu/ico_register.png" />','<?=$LANG['mnu_teamcal_register']?>','javascript:openPopup(\'register.php?lang=<?=$CONF['options']['lang']?>\',\'login\',\'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=420,height=550\');',null,null],
         <?php }
         if ($m['mnu_teamcal_logout']) { ?>
         ['<img src="themes/<?=$theme?>/img/menu/ico_logout.png" />','<?=$LANG['mnu_teamcal_logout']?>','index.php?lang=<?=$CONF['options']['lang']?>&action=logout',null,null],
         <?php } ?>
      ],
      _cmSplit,
      [null,'<?=$LANG['mnu_view']?>',null,null,null,
         ['<img src="themes/<?=$theme?>/img/menu/ico_home.png" />','<?=$LANG['mnu_view_homepage']?>','index.php?action=welcome&lang=<?=$CONF['options']['lang']?>',null,null],
         <?php if ($m['mnu_view_calendar']) { ?>
         ['<img src="themes/<?=$theme?>/img/menu/ico_calendar.png" />','<?=$LANG['mnu_view_calendar']?>','index.php?action=calendar&lang=<?=$CONF['options']['lang']?>',null,null],
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
         ['<img src="themes/<?=$theme?>/img/menu/ico_message.png" />','<?=$LANG['mnu_tools_message']?>','javascript:openPopup(\'message.php?lang=<?=$CONF['options']['lang']?>\',\'message\',\'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=500,height=600\');',null,null],
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
         ['<img src="themes/<?=$theme?>/img/menu/ico_legend.png" />','<?=$LANG['mnu_help_legend']?>','javascript:openPopup(\'legend.php?lang=<?=$CONF['options']['lang']?>\',\'legend\',\'toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=500,height=500\');',null,null],
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
   <!-- MENU END ======================================================== -->
</div>

<?php
/**
 * ============================================================================
 * OPTIONS BAR
 */
$optionitems=0;
if ( $C->readConfig("showLanguage") OR
     $C->readConfig("showGroup") OR
     $C->readConfig("showToday") OR
     $C->readConfig("showStart") OR
     substr_count($_SERVER['PHP_SELF'],"showyear.php")
   ) {
?>
<div id="optionsbar">
   <form class="form" method="POST" name="form_teamcal" action="<?=$_SERVER['PHP_SELF']."?action=calendar&amp;".setRequests()?>">
      <span id="optionsbar-content">
      <?php
      /**
       * The language drop down is on all pages
       */
      if ($C->readConfig("showLanguage")) { ?>
         <!-- Language Drop Down -->
         <?=$LANG['nav_language']?>&nbsp;
         <select id="user_lang" name="user_lang" class="select" onchange="javascript:">
         <?php
         $array = getLanguages(); // Collects language name of all installed language files
         foreach( $array as $langfile ) {
            if ($langfile==$CONF['options']['lang']) { ?>
               <option value="<?=$CONF['options']['lang']?>" selected><?=$CONF['options']['lang']?></option>
            <?php } else { ?>
               <option value="<?=$langfile?>"><?=$langfile?></option>
            <?php }
         } ?>
         </select>
      <?php
         $optionitems++;
      } ?>

   	<?php if ( substr_count($_SERVER['PHP_SELF'],"index.php") ) {
         /**
          * The group drop down is only shown on the month view page and userlist page
          */
         if ($C->readConfig("showGroup") && isAllowed("viewAllGroups")) { ?>
            <!-- Group filter drop down -->
            &nbsp;&nbsp;<?=$LANG['nav_groupfilter']?>&nbsp;
            <select id="groupfilter" name="groupfilter" class="select" onchange="javascript:">
               <option value="All" <?=($CONF['options']['groupfilter']=="All"?"SELECTED":"")?>><?=$LANG['drop_group_all']?></option>
               <option value="Allbygroup" <?=($CONF['options']['groupfilter']=="Allbygroup"?"SELECTED":"")?>><?=$LANG['drop_group_allbygroup']?></option>
               <?php
               $groups=$G->getAll(TRUE); // TRUE = exclude hidden
               foreach( $groups as $group ) {
                  if (!isAllowed("viewAllGroups")) {
                     if ($UO->true($user, "owngroupsonly") OR $UG->isMemberOfGroup($user, $group['groupname']) OR $UG->isGroupManagerOfGroup($user, $group['groupname'])) {
                        if ($CONF['options']['groupfilter']==$group['groupname']) { ?>
                           <option value="<?=$group['groupname']?>" selected><?=$group['groupname']?></option>
                        <?php } else { ?>
                           <option value="<?=$group['groupname']?>"><?=$group['groupname']?></option>
                        <?php }
                     }
                  }
                  else {
                     if ($CONF['options']['groupfilter']==$group['groupname']) { ?>
                        <option value="<?=$group['groupname']?>" selected><?=$group['groupname']?></option>
                     <?php } else { ?>
                        <option value="<?=$group['groupname']?>"><?=$group['groupname']?></option>
                     <?php }
                  }
               }
               ?>
            </select>
         <?php
            $optionitems++;
         } ?>

         <?php if ($C->readConfig("showRegion")) { ?>
            <!-- Region drop down -->
            &nbsp;&nbsp;<?=$LANG['nav_regionfilter']?>&nbsp;
            <select name="regionfilter" id="regionfilter" class="select" onchange="javascript:">
               <option class="option" value="default" <?=($CONF['options']['region']=="default"?"SELECTED":"")?>>default</option>
               <?php
               $query  = "SELECT `regionname` FROM `".$R->table."` ORDER BY `regionname`;";
               $result = $R->db->db_query($query);
               while ( $row = $R->db->db_fetch_array($result,MYSQL_ASSOC) ){
                  $R->findByName(stripslashes($row['regionname']));
                  if ($R->regionname!="default") {
                     if ($R->regionname==$CONF['options']['region']) { ?>
                        <option value="<?=$R->regionname?>" selected><?=$R->regionname?></option>
                     <?php } else { ?>
                        <option value="<?=$R->regionname?>"><?=$R->regionname?></option>
                     <?php }
                  }
               } ?>
            </select>
         <?php $optionitems++; } ?>

         <?php if ($C->readConfig("showToday")) { ?>
            <!-- Absence filter drop down -->
            &nbsp;&nbsp;<?=$LANG['nav_absencefilter']?>&nbsp;
            <select id="absencefilter" name="absencefilter" class="select" onchange="javascript:">
               <option value="All" <?=($CONF['options']['absencefilter']=="All"?"SELECTED":"")?>><?=$LANG['drop_group_all']?></option>
               <?php
               require_once( $CONF['app_root']."models/absence_model.php" );
               $A = new Absence_model;
               $absences = $A->getAll();
               foreach ($absences as $abs){
                  if ($CONF['options']['absencefilter']==$abs['id']) { ?>
                     <option value="<?=$abs['id']?>" selected><?=$abs['name']?></option>
                  <?php } else { ?>
                     <option value="<?=$abs['id']?>"><?=$row['name']?></option>
                  <?php }
               } ?>
            </select>
         <?php
            $optionitems++;
         } ?>

         <?php if ($C->readConfig("showStart")) { ?>

            <!-- Start month drop down -->
            &nbsp;&nbsp;<?=$LANG['nav_start_with']?>&nbsp;
            <select id="month_id" name="month_id" class="select" onchange="javascript:">
               <option value="1" <?=$CONF['options']['month_id']== "1"?' SELECTED':''?> ><?=$LANG['monthnames'][1]?></option>
               <option value="2" <?=$CONF['options']['month_id']== "2"?' SELECTED':''?> ><?=$LANG['monthnames'][2]?></option>
               <option value="3" <?=$CONF['options']['month_id']== "3"?' SELECTED':''?> ><?=$LANG['monthnames'][3]?></option>
               <option value="4" <?=$CONF['options']['month_id']== "4"?' SELECTED':''?> ><?=$LANG['monthnames'][4]?></option>
               <option value="5" <?=$CONF['options']['month_id']== "5"?' SELECTED':''?> ><?=$LANG['monthnames'][5]?></option>
               <option value="6" <?=$CONF['options']['month_id']== "6"?' SELECTED':''?> ><?=$LANG['monthnames'][6]?></option>
               <option value="7" <?=$CONF['options']['month_id']== "7"?' SELECTED':''?> ><?=$LANG['monthnames'][7]?></option>
               <option value="8" <?=$CONF['options']['month_id']== "8"?' SELECTED':''?> ><?=$LANG['monthnames'][8]?></option>
               <option value="9" <?=$CONF['options']['month_id']== "9"?' SELECTED':''?> ><?=$LANG['monthnames'][9]?></option>
               <option value="10" <?=$CONF['options']['month_id']== "10"?' SELECTED':''?> ><?=$LANG['monthnames'][10]?></option>
               <option value="11" <?=$CONF['options']['month_id']== "11"?' SELECTED':''?> ><?=$LANG['monthnames'][11]?></option>
               <option value="12" <?=$CONF['options']['month_id']== "12"?' SELECTED':''?> ><?=$LANG['monthnames'][12]?></option>
            </select>

            <!-- Year drop down -->
            <select id="year_id" name="year_id" class="select" onchange="javascript:">
               <?php
               $today = getdate();
               $curryear = $today['year'];
               ?>
               <option value="<?=$curryear-1?>" <?=$CONF['options']['year_id']==$curryear-1?' SELECTED':''?> ><?=$curryear-1?></option>
               <option value="<?=$curryear?>" <?=$CONF['options']['year_id']==$curryear?' SELECTED':''?> ><?=$curryear?></option>
               <option value="<?=$curryear+1?>" <?=$CONF['options']['year_id']==$curryear+1?' SELECTED':''?> ><?=$curryear+1?></option>
               <option value="<?=$curryear+2?>" <?=$CONF['options']['year_id']==$curryear+2?' SELECTED':''?> ><?=$curryear+2?></option>
            </select>

            <!-- Months to show drop down -->
            <select id="show_id" name="show_id" class="select" onchange="javascript:">
               <option value="1" <?=$CONF['options']['show_id']=="1"?' SELECTED':''?>><?=$LANG['drop_show_1_months']?></option>
               <option value="2" <?=$CONF['options']['show_id']=="2"?' SELECTED':''?>><?=$LANG['drop_show_2_months']?></option>
               <option value="3" <?=$CONF['options']['show_id']=="3"?' SELECTED':''?>><?=$LANG['drop_show_3_months']?></option>
               <option value="6" <?=$CONF['options']['show_id']=="6"?' SELECTED':''?>><?=$LANG['drop_show_6_months']?></option>
               <option value="12" <?=$CONF['options']['show_id']=="12"?' SELECTED':''?>><?=$LANG['drop_show_12_months']?></option>
            </select>
            &nbsp;
         <?php
            $optionitems++;
         }
      } ?>
      </span>

      <span id="optionsbar-buttons">
         <?php if ( $optionitems ) { ?>
            <input name="btn_apply" type="button" class="button" onclick="document.forms.form_teamcal.submit();" value="<?=$LANG['btn_apply']?>">
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
            require_once( $CONF['app_root']."includes/tcusergroup.class.php" );
            $UG = new tcUserGroup;
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