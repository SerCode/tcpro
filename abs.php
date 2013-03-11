<?php
/**
 * aps.php
 *
 * Displays the absence types configuration page
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

require_once("includes/tcabs.class.php");
require_once("includes/tclog.class.php");
require_once("includes/tclogin.class.php");
require_once("includes/tcuser.class.php");

$A = new tcAbs;
$C = new tcConfig;
$L = new tcLogin;
$LOG = new tcLog;
$U = new tcUser;

/**
 * Check if allowed
 */
if (!isAllowed("editAbsenceTypes")) showError("notallowed");

/**
 * Read all absence types and select first in array
 */
$absences = $A->getAll();
$absid = $absences[0]['id'];

/**
 * Check whether a different scheme was selected
 */
if ( isset($_REQUEST['absid']) ) $absid = $_REQUEST['absid'];
$A->get($absid);

if ( isset($_POST['sel_abs']) ) {
   header("Location: ".$_SERVER['PHP_SELF']."?absid=".$_POST['sel_abs']."&lang=".$CONF['options']['lang']);
}

/**
 * ========================================================================
 * CREATE
 */
if ( isset($_POST['btn_create']) ) {


   /**
    * Log this event
    */
   $LOG->log("logAbsence",$L->checkLogin(),"Absence type created '".$A->name." (".$A->id.")' was created or reset");
   header("Location: ".$_SERVER['PHP_SELF']."?absid=".$A->id."&lang=".$CONF['options']['lang']);
}

/**
 * ========================================================================
 * APPLY
 */
else if ( isset($_POST['btn_apply']) ) {


   /**
    * Log this event
    */
   $LOG->log("logAbsence",$L->checkLogin(),"Absence type created '".$A->name." (".$A->id.")' was created or reset");
   header("Location: ".$_SERVER['PHP_SELF']."?absid=".$A->id."&lang=".$CONF['options']['lang']);
}

require("includes/header.html.inc.php");
echo "<body>\r\n";
require("includes/header.application.inc.php");
require("includes/menu.inc.php");
?>
<div id="content">
   <div id="content-content">
      <table class="dlg">
         <tr>
            <td style="padding: 8px 14px 8px 14px; border-right: 1px solid #333333;">
               <form name="form-sel-abs" class="form" method="POST" action="<?=$_SERVER['PHP_SELF']."?absid=".$absid."&amp;lang=".$CONF['options']['lang']?>">
                  <?=$LANG['perm_sel_scheme']?>&nbsp;
                  <script type="text/javascript">var sel_absid_cache;</script>
                  <select id="sel_abs" name="sel_abs" class="select" onclick="sel_absid_cache=this.value" onchange="if (confirm('<?=$LANG['abs_sel_confirm']?>')) this.form.submit(); else this.value=sel_absid_cache;" style="background-image: url(<?=$CONF['app_icon_dir'].$A->icon?>); background-size: 16px 16px; background-repeat: no-repeat; background-position: 2px 2px; padding: 2px 0px 0px 22px;">
                     <?php
                        foreach ($absences as $abs) { ?>
                           <option style="background-image: url(<?=$CONF['app_icon_dir'].$abs['icon']?>); background-size: 16px 16px; background-repeat: no-repeat; padding-left: 20px;" value="<?=$abs['id']?>" <?=(($abs['id']==$A->id)?"SELECTED":"")?>><?=$abs['name']?></option>
                     <?php } ?>
                  </select>
               </form>
            </td>
         </tr>
      </table>
      <br>

      <form class="form" name="form-abs" method="POST" action="<?=$_SERVER['PHP_SELF']."?absid=".$A->id."&amp;lang=".$CONF['options']['lang']?>">
      <table class="dlg">
         <tr>
            <td class="dlg-header" colspan="2">
               <?php printDialogTop($LANG['abs_title'].$A->name."\" (ID=".$A->id.")","abs.html","ico_absences.png"); ?>
            </td>
         </tr>
         
         <!-- Name -->
         <?php $style="2"; 
         if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="width: 60%;">
               <span class="config-key"><?=$LANG['abs_name']?></span><br>
               <span class="config-comment"><?=$LANG['abs_name_desc']?></span>
            </td>
            <td class="config-row<?=$style?>">
               <input class="text" name="txt_name" id="txt_name" type="text" size="50" maxlength="80" value="<?=$A->name?>">
            </td>
         </tr>
         
         <!-- Symbol -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="width: 60%;">
               <span class="config-key"><?=$LANG['abs_symbol']?></span><br>
               <span class="config-comment"><?=$LANG['abs_symbol_desc']?></span>
            </td>
            <td class="config-row<?=$style?>">
               <input class="text" name="txt_symbol" id="txt_symbol" type="text" size="2" maxlength="1" value="<?=$A->symbol?>">
            </td>
         </tr>
         
         <!-- Icon -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_icon']?></span><br>
               <span class="config-comment"><?=$LANG['abs_icon_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%; vertical-align: top;">
               <script type="text/javascript">function switchAbsIcon(image) { document.getElementById('sel_icon').style.backgroundImage="url('<?=$CONF['app_icon_dir']?>"+image+"')"; }</script>
               <select id="sel_icon" name="sel_icon" class="select" onchange="javascript: switchAbsIcon(this.value);" style="background-image: url(<?=$CONF['app_icon_dir'].$A->icon?>); background-size: 16px 16px; background-repeat: no-repeat; background-position: 2px 2px; padding: 2px 0px 0px 22px;">
                  <option value="No" <?=(($A->icon=="No")?"SELECTED":"")?>><?=$LANG['no']?></option>
                  <?php
                  $fileTypes = array ("gif", "jpg", "png");
                  $imgFiles = scanDirectory($CONF['app_icon_dir']);
                  foreach ($imgFiles as $file) { ?>
                     <option style="background-image: url(<?=$CONF['app_icon_dir'].$file?>); background-size: 16px 16px; background-repeat: no-repeat; padding-left: 20px;" value="<?=$file?>" <?=(($A->icon==$file)?"SELECTED":"")?>><?=$file?></option>
                  <?php } ?>
               </select>
               &nbsp;<input name="btn_upload" type="button" class="button" onclick="javascript:this.blur();openPopup('upload.php?target=icon','upload','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=500,height=400');" value="<?=$LANG['btn_upload']?>">
               <?php if($A->icon!="No") { ?>
               <img src="<?=$CONF['app_homepage_dir'].$A->icon?>" alt="" align="top" id="absIcon">
               <?php } ?>
            </td>
         </tr>

         <!-- Color -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_color']?></span><br>
               <span class="config-comment"><?=$LANG['abs_color_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_color" id="txt_color" type="text" size="6" maxlength="6" value="<?=$A->color?>">
               <span id="color_sample" style="background-color: #<?=$A->color?>; margin: 0px 0px 0px 10px; padding: 4px;"><img src="img/blank.png" style="width: 20px; height: 20px;"></span>
            </td>
         </tr>

         <!-- Bgcolor -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_bgcolor']?></span><br>
               <span class="config-comment"><?=$LANG['abs_bgcolor_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_bgcolor" id="txt_bgcolor" type="text" size="6" maxlength="6" value="<?=$A->bgcolor?>">
               <span id="bgcolor_sample" style=" background-color: #<?=$A->bgcolor?>; margin: 0px 0px 0px 10px; padding: 4px;"><img src="img/blank.png" style="width: 20px; height: 20px;"></span>
            </td>
         </tr>

         <!-- Factor -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_factor']?></span><br>
               <span class="config-comment"><?=$LANG['abs_factor_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_factor" id="txt_factor" type="text" size="1" maxlength="3" value="<?=$A->factor?>">
            </td>
         </tr>

         <!-- Allowance -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_allowance']?></span><br>
               <span class="config-comment"><?=$LANG['abs_allowance_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input class="text" name="txt_allowance" id="txt_allowance" type="text" size="1" maxlength="3" value="<?=$A->allowance?>">
            </td>
         </tr>

         <!-- Show in remainder -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_show_in_remainder']?></span><br>
               <span class="config-comment"><?=$LANG['abs_show_in_remainder_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_show_in_remainder" id="chk_show_in_remainder" value="chk_show_in_remainder" type="checkbox" <?=(intval($A->show_in_remainder)?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- Show totals -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_show_totals']?></span><br>
               <span class="config-comment"><?=$LANG['abs_show_totals_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_show_totals" id="chk_show_totals" value="chk_show_totals" type="checkbox" <?=(intval($A->show_totals)?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- Approval required -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_approval_required']?></span><br>
               <span class="config-comment"><?=$LANG['abs_approval_required_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_approval_required" id="chk_approval_required" value="chk_approval_required" type="checkbox" <?=(intval($A->approval_required)?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- Counts as present -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_counts_as_present']?></span><br>
               <span class="config-comment"><?=$LANG['abs_counts_as_present_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_counts_as_present" id="chk_counts_as_present" value="chk_counts_as_present" type="checkbox" <?=(intval($A->counts_as_present)?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- Manager only -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_manager_only']?></span><br>
               <span class="config-comment"><?=$LANG['abs_manager_only_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_manager_only" id="chk_manager_only" value="chk_manager_only" type="checkbox" <?=(intval($A->manager_only)?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- Hide in profile -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_hide_in_profile']?></span><br>
               <span class="config-comment"><?=$LANG['abs_hide_in_profile_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_hide_in_profile" id="chk_hide_in_profile" value="chk_hide_in_profile" type="checkbox" <?=(intval($A->hide_in_profile)?"CHECKED":"")?>>
            </td>
         </tr>

         <!-- Confidential -->
         <?php if ($style=="1") $style="2"; else $style="1"; ?>
         <tr>
            <td class="config-row<?=$style?>" style="text-align: left; width: 60%;">
               <span class="config-key"><?=$LANG['abs_confidential']?></span><br>
               <span class="config-comment"><?=$LANG['abs_confidential_desc']?></span>
            </td>
            <td class="config-row<?=$style?>" style="text-align: left; width: 40%;">
               <input name="chk_confidential" id="chk_confidential" value="chk_confidential" type="checkbox" <?=(intval($A->confidential)?"CHECKED":"")?>>
            </td>
         </tr>

         <tr>
            <td class="dlg-menu" colspan="2" style="text-align: left;">
               <input name="btn_apply" type="submit" class="button" value="<?=$LANG['btn_apply']?>">&nbsp;
               <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?permissions.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
            </td>
         </tr>

      </table>
      </form>
   </div>
</div>
<script type="text/javascript">$(function() { $( "#txt_color, #txt_bgcolor" ).ColorPicker({ onSubmit: function(hsb, hex, rgb, el) { $(el).val(hex.toUpperCase()); $(el).ColorPickerHide(); }, onBeforeShow: function () { $(this).ColorPickerSetColor(this.value); } }) .bind('keyup', function(){ $(this).ColorPickerSetColor(this.value); }); });</script>
<?php require("includes/footer.html.inc.php"); ?>