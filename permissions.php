<?php
/**
 * permissions.php
 *
 * Displays the permissions configuration page
 *
 * @package TeamCalPro
 * @version 3.6.000
 * @author George Lewe
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
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
require_once ("helpers/global_helper.php");
getOptions();
if (strlen($CONF['options']['lang'])) require ("languages/" . $CONF['options']['lang'] . ".tcpro.php");
else require ("languages/english.tcpro.php");

require_once("models/permission_model.php");
require_once("models/log_model.php");
require_once("models/login_model.php");
require_once("models/user_model.php");

$C = new Config_model;
$L = new Login_model;
$LOG = new Log_model;
$P = new Permission_model;
$U = new User_model;

/**
 * HTML title. Will be shown in browser tab.
 */
$CONF['html_title'] = $LANG['html_title_permissions'];

/**
 * Check if allowed
 */
if (!isAllowed("editPermissionScheme")) showError("notallowed");

/**
 * Default permission array
 */
$perms = array (
            array ("p"=>"editConfig",              "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"editPermissionScheme",    "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"manageUsers",             "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"manageGroups",            "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"manageGroupMemberships",  "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"editAbsenceTypes",        "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"editRegions",             "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"editHolidays",            "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"editDeclination",         "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"manageDatabase",          "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"viewSystemLog",           "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"editSystemLog",           "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"viewEnvironment",         "type"=>"admin", "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"editGlobalCalendar",      "type"=>"cal",   "admin"=>1, "director"=>0, "manager"=>1, "user"=>0, "public"=>0),
            array ("p"=>"editGlobalDaynotes",      "type"=>"cal",   "admin"=>1, "director"=>0, "manager"=>1, "user"=>0, "public"=>0),
            array ("p"=>"viewUserProfiles",        "type"=>"user",  "admin"=>1, "director"=>1, "manager"=>1, "user"=>1, "public"=>0),
            array ("p"=>"viewUserAbsenceCounts",   "type"=>"user",  "admin"=>1, "director"=>1, "manager"=>1, "user"=>0, "public"=>0),
            array ("p"=>"editAllUserAllowances",   "type"=>"user",  "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"editGroupUserAllowances", "type"=>"user",  "admin"=>1, "director"=>0, "manager"=>1, "user"=>0, "public"=>0),
            array ("p"=>"editAllUserProfiles",     "type"=>"user",  "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"editGroupUserProfiles",   "type"=>"user",  "admin"=>1, "director"=>0, "manager"=>1, "user"=>0, "public"=>0),
            array ("p"=>"editAllUserCalendars",    "type"=>"user",  "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"editGroupUserCalendars",  "type"=>"user",  "admin"=>1, "director"=>0, "manager"=>1, "user"=>0, "public"=>0),
            array ("p"=>"editOwnUserCalendars",    "type"=>"user",  "admin"=>1, "director"=>1, "manager"=>1, "user"=>1, "public"=>0),
            array ("p"=>"editAllUserDaynotes",     "type"=>"user",  "admin"=>1, "director"=>0, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"editGroupUserDaynotes",   "type"=>"user",  "admin"=>1, "director"=>0, "manager"=>1, "user"=>0, "public"=>0),
            array ("p"=>"viewCalendar",            "type"=>"view",  "admin"=>1, "director"=>1, "manager"=>1, "user"=>1, "public"=>1),
            array ("p"=>"viewAllUserCalendars",    "type"=>"view",  "admin"=>1, "director"=>1, "manager"=>0, "user"=>0, "public"=>1),
            array ("p"=>"viewGroupUserCalendars",  "type"=>"view",  "admin"=>1, "director"=>0, "manager"=>1, "user"=>1, "public"=>0),
            array ("p"=>"viewYearCalendar",        "type"=>"view",  "admin"=>1, "director"=>1, "manager"=>1, "user"=>1, "public"=>0),
            array ("p"=>"viewAnnouncements",       "type"=>"view",  "admin"=>1, "director"=>1, "manager"=>1, "user"=>1, "public"=>0),
            array ("p"=>"useMessageCenter",        "type"=>"view",  "admin"=>1, "director"=>1, "manager"=>1, "user"=>1, "public"=>0),
            array ("p"=>"viewStatistics",          "type"=>"view",  "admin"=>1, "director"=>1, "manager"=>1, "user"=>0, "public"=>0),
            array ("p"=>"viewAllGroups",           "type"=>"view",  "admin"=>1, "director"=>1, "manager"=>0, "user"=>0, "public"=>0),
            array ("p"=>"viewFastEdit",            "type"=>"view",  "admin"=>1, "director"=>1, "manager"=>1, "user"=>0, "public"=>0),
         );

$types = array (
            "admin",
            "cal",
            "user",
            "view",
         );

$roles = array (
            "admin",
            "director",
            "manager",
            "user",
            "public",
         );

/**
 * Check whether a different scheme was selected
 */
if ( !isset($_REQUEST['scheme']) ) $scheme="Default";
else $scheme = $_REQUEST['scheme'];

if ( isset($_POST['sel_scheme']) ) {

   /**
    * ========================================================================
    * ACTIVATE
    */
   if ( isset($_POST['btn_activate']) ) {

      $C->saveConfig("permissionScheme",$_POST['sel_scheme']);
      /**
       * Log this event
       */
      $LOG->log("logPermission",$L->checkLogin(),"Permission scheme '".$_POST['sel_scheme']."' activated");
      header("Location: ".$_SERVER['PHP_SELF']."?scheme=".$_POST['sel_scheme']."&lang=".$CONF['options']['lang']);
   }
   /**
    * ========================================================================
    * DELETE
    */
   else if ( isset($_POST['btn_delete']) ) {

      if ($_POST['sel_scheme']!="Default") {
         $P->deleteScheme($_POST['sel_scheme']);
         $C->saveConfig("permissionScheme","Default");
         /**
          * Log this event
          */
         $LOG->log("logPermission",$L->checkLogin(),"Permission scheme '".$_POST['sel_scheme']."' deleted");
         header("Location: ".$_SERVER['PHP_SELF']."?scheme=Default&lang=".$CONF['options']['lang']);
      }
   }
   else {
      header("Location: ".$_SERVER['PHP_SELF']."?scheme=".$_POST['sel_scheme']."&lang=".$CONF['options']['lang']);
   }
}

/**
 * ========================================================================
 * RESET, CREATE
 * Reset Default permission scheme or create a new with standard settings
 */
else if ( isset($_POST['btn_reset']) OR isset($_POST['btn_create']) ) {

   if ( isset($_POST['btn_create']) ) {
      if (!preg_match('/^[a-zA-Z0-9-]*$/', $_POST['txt_newScheme'])) {
         $error=TRUE;
         $err_short=$LANG['err_input_caption'];
         $err_long=$LANG['err_input_perm_invalid_1'];
         $err_long.=$_POST['txt_newScheme'];
         $err_long.=$LANG['err_input_perm_invalid_2'];
         $err_module=$_SERVER['SCRIPT_NAME'];
         $err_btn_close=FALSE;
      }
      else {
         $scheme = $_POST['txt_newScheme'];
         if ($P->schemeExists($scheme)) {
            $error=TRUE;
            $err_short=$LANG['err_input_caption'];
            $err_long=$LANG['err_input_perm_exists_1'];
            $err_long.=$_POST['txt_newScheme'];
            $err_long.=$LANG['err_input_perm_exists_2'];
            $err_module=$_SERVER['SCRIPT_NAME'];
            $err_btn_close=FALSE;
         }
      }
   }

   if (!$error) {
      /**
       * First, delete the existing scheme entries
       */
      $P->deleteScheme($scheme);

      /**
       * Then create new entries based on default array
       */
      foreach($perms as $perm) {
         foreach($roles as $role) {
            $P->setPermission($scheme,$perm['p'],$role,$perm[$role]);
         }
      }

      /**
       * Log this event
       */
      $LOG->log("logPermission",$L->checkLogin(),"Permission scheme '".$scheme."' was created or reset");
      header("Location: ".$_SERVER['PHP_SELF']."?scheme=".$scheme."&lang=".$CONF['options']['lang']);
   }
}

/**
 * ========================================================================
 * APPLY
 */
else if ( isset($_POST['btn_apply']) ) {

   foreach($perms as $perm) {
      foreach($roles as $role) {
         if ( isset($_POST['chk_'.$perm['p'].'_'.$role]) && $_POST['chk_'.$perm['p'].'_'.$role] )
            $P->setPermission($scheme,$perm['p'],$role,1);
         else
            $P->setPermission($scheme,$perm['p'],$role,0);
      }
   }
   /**
    * Make sure no admin locks himself out of editing the permission scheme
    */
   $P->setPermission($scheme,"editPermissionScheme","admin",1);
   /**
    * Log this event
    */
   $LOG->log("logPermission",$L->checkLogin(),"Permission scheme '".$scheme."' changed");
   header("Location: ".$_SERVER['PHP_SELF']."?scheme=".$scheme."&lang=".$CONF['options']['lang']);
}
require("includes/header_html_inc.php");
echo "<body>\r\n";
require("includes/header_app_inc.php");
require("includes/menu_inc.php");
?>
<div id="content">
   <div id="content-content">
      <table class="dlg">
         <tr>
            <td style="padding: 8px 14px 8px 14px; border-right: 1px solid #333333;">
               <form name="form-sel-scheme" class="form" method="POST" action="<?=$_SERVER['PHP_SELF']."?scheme=".$scheme."&amp;lang=".$CONF['options']['lang']?>">
                  <?=$LANG['perm_sel_scheme']?>&nbsp;
                  <script type="text/javascript">var sel_scheme_cache;</script>
                  <select id="sel_scheme" name="sel_scheme" class="select" onclick="sel_scheme_cache=this.value" onchange="if (confirm('<?=$LANG['perm_select_confirm']?>')) this.form.submit(); else this.value=sel_scheme_cache;">
                     <?php
                        $schemes = $P->getSchemes();
                        foreach ($schemes as $sch) {
                           if ($sch==$scheme)
                              echo ("<option value=\"".$sch."\" SELECTED=\"selected\">".$sch."</option>");
                           else
                              echo ("<option value=\"".$sch."\" >".$sch."</option>");
                        }
                     ?>
                  </select>
                  &nbsp;&nbsp;<input name="btn_activate" type="submit" class="button" value="<?=$LANG['btn_activate']?>" onclick="return confirmSubmit('<?=$LANG['perm_activate_confirm']?>')">
                  <?php if ($scheme != "Default") { ?>
                  &nbsp;&nbsp;<input name="btn_delete" type="submit" class="button" value="<?=$LANG['btn_delete']?>" onclick="return confirmSubmit('<?=$LANG['perm_delete_confirm']?>')">
                  <?php } ?>
               </form>
            </td>
            <td style="padding: 8px 14px 8px 14px;">
               <form name="form-create-scheme" class="form" method="POST" action="<?=$_SERVER['PHP_SELF']."?scheme=".$scheme."&amp;lang=".$CONF['options']['lang']?>">
                  &nbsp;&nbsp;<?=$LANG['perm_create_scheme']?>&nbsp;
                  <input name="txt_newScheme" id="txt_newScheme" maxlength="80" size="40" type="text" class="text" value="">
                  &nbsp;&nbsp;<input name="btn_create" type="submit" class="button" value="<?=$LANG['btn_create']?>">
               </form>
            </td>
         </tr>
      </table>
      <br>

      <form class="form" name="form-permissions" method="POST" action="<?=$_SERVER['PHP_SELF']."?scheme=".$scheme."&amp;lang=".$CONF['options']['lang']?>">
      <table class="dlg">
         <tr>
            <td class="dlg-header" colspan="<?=count($roles)+1?>">
               <?php printDialogTop($LANG['perm_title'].$scheme,"permissions.html","ico_locked.png"); ?>
            </td>
         </tr>
         <tr>
            <td class="dlg-menu" colspan="<?=count($roles)+1?>" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">&nbsp;
               <input name="btn_reset" type="submit" class="button" value="<?=$LANG['btn_reset']?>" onclick="return confirmSubmit('<?=$LANG['perm_reset_confirm']?>')">&nbsp;
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?permissions.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
            </td>
         </tr>

         <?php $style="2";
         foreach ($types as $type) { ?>
         <!-- PERMISSION GROUP: <?=$type?> -->
            <tr>
               <td class="dlg-caption" style="text-align: left;">
                  <img id="<?=$type?>.img" class="noprint" alt="Toggle" title="Toggle section..." src="themes/<?=$theme?>/img/hide_section.gif" style="vertical-align: middle;" border="0" onclick="toggletr('<?=$type?>',<?=count($perms)?>);">
                  <?=$LANG['perm_col_perm_'.$type]?>
               </td>
               <?php foreach ($roles as $role) { ?>
               <td class="dlg-caption-tt" style="text-align: center;" title="<?=$LANG['perm_col_'.$role.'_tt']?>"><?=$LANG['perm_col_'.$role]?></td>
               <?php } ?>
            </tr>

            <?php
            $i=0;
            foreach ($perms as $perm) {
               if ($style=="1") $style="2"; else $style="1";
               if ($perm['type']==$type) {
                  $i++;
                  ?>
                  <tr id="<?=$type?>-<?=$i?>">
                     <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
                        <span class="config-key"><?=$LANG['perm_perm_'.$perm['p'].'_title']?></span><br>
                        <span class="config-comment"><?=$LANG['perm_perm_'.$perm['p'].'_desc']?></span>
                     </td>
                     <?php foreach ($roles as $role) { ?>
                     <td class="config-row<?=$style?>" style="text-align: center;">
                        <input name="chk_<?=$perm['p']?>_<?=$role?>" id="chk_<?=$perm['p']?>_<?=$role?>" value="chk_<?=$perm['p']?>_<?=$role?>" type="checkbox" <?=(($P->isAllowed($scheme,$perm['p'],$role))?"CHECKED":"")?>>
                     </td>
                     <?php } ?>
                  </tr>
               <?php } ?>
            <?php } ?>

            <tr>
               <td class="dlg-menu" colspan="<?=count($roles)+1?>" style="text-align: left;">
                  <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">&nbsp;
                  <input name="btn_reset" type="submit" class="button" value="<?=$LANG['btn_reset']?>" onclick="return confirmSubmit('<?=$LANG['perm_reset_confirm']?>')">&nbsp;
                  <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?permissions.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
               </td>
            </tr>
         <?php } ?>

      </table>
      </form>
   </div>
</div>
<?php require("includes/footer_inc.php"); ?>