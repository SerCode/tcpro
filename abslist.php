<?php
/**
 * abslist.php
 *
 * Displays the absence type list
 *
 * @package TeamCalPro
 * @version 3.6.011
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
require_once ("languages/".$CONF['options']['lang'].".tcpro.php");

require_once("models/absence_model.php");
require_once("models/absence_group_model.php");
require_once("models/config_model.php");
require_once("models/group_model.php");
require_once("models/log_model.php");
require_once("models/login_model.php");
require_once("models/user_model.php");

$A = new Absence_model;
$AG = new Absence_group_model;
$C = new Config_model;
$G = new Group_model;
$L = new Login_model;
$LOG = new Log_model;
$U = new User_model;

$error=FALSE;

/**
 * Check if allowed
 */
if (!isAllowed("manageUsers")) showError("notallowed");

$confirmation = array (
   'header'  => "Header",
   'title'   => "Title",
   'show'    => false,
   'success' => false,
   'text'    => ""
);

/**
 * =========================================================================
 * DELETE
 */
if ( isset($_POST['btn_abs_del']) ) 
{
   $selected_absences = $_POST['chk_abs'];
   foreach($selected_absences as $sa=>$value) 
   {
      /**
       * Delete absence
       */
      $absname = $A->getName($value);
      $A->delete($value);
      
      /**
       * Log this event
       */
      $LOG->log("logAbsence",$L->checkLogin(),"log_abs_deleted", $absname." (".$value.")");
            
      /**
       * Prepare confirmation message
       */
      $confirmation['show']=true;
      $confirmation['success']=true;
      $confirmation['header'] = $LANG['confirmation_success'];
      $confirmation['title'] = $LANG['btn_delete_selected'];
      $confirmation['text'] = $LANG['confirmation_delete_selected_absences'];
   }
}

/**
 * HTML title. Will be shown in browser tab.
 */
$CONF['html_title'] = $LANG['html_title_absences'];

/**
 * User manual page
 */
$help = urldecode($C->readConfig("userManual"));
if (urldecode($C->readConfig("userManual"))==$CONF['app_help_root']) {
   $help .= 'Absence+Types';
}

require("includes/header_html_inc.php");
require("includes/header_app_inc.php");
require("includes/menu_inc.php");
?>
<div id="content">
   <div id="content-content">
      <form class="form" name="form-abslist" method="POST" action="<?=$_SERVER['PHP_SELF']?>">
         <!--  ABSENCE TYPES =========================================================== -->
         <?php $colspan="4"; ?>
         <table class="dlg">
            <tr>
               <td class="dlg-header" colspan="<?=$colspan?>">
                  <?php printDialogTop($LANG['abs_list_title'], $help, "ico_absences.png"); ?>
               </td>
            </tr>
            
            <!-- MESSAGE -->
            <?php if ($confirmation['show']) { ?>
               <?php $style="2"; ?>
               <tr>
                  <td class="dlg-caption-<?=($confirmation['success'])?"green":"red";?>" colspan="<?=$colspan?>" style="text-align: left;"><?=$confirmation['header']?></td>
               </tr>
   
               <?php if ($style=="1") $style="2"; else $style="1"; ?>
               <tr>
                  <td colspan="<?=$colspan?>" class="config-row<?=$style?>">
                     <span class="config-key"><?=$confirmation['title']?></span><br>
                     <span class="config-comment"><?=$confirmation['text']?></span>
                  </td>
               </tr>
            <?php } ?> 

            <tr>
               <td class="dlg-caption" style="text-align: left; padding-left: 8px;"></td>
               <td class="dlg-caption" style="text-align: left; padding-left: 8px;"><?=$LANG['abs_col_display']?></td>
               <td class="dlg-caption" style="text-align: left;"><?=$LANG['abs_col_name']?></td>
               <td class="dlg-caption" style="text-align: right; padding-right: 8px;"><?=$LANG['admin_user_action']?></td>
            </tr>
                        
            <?php 
            $absences = $A->getAll();
            $numabs = count($absences);
            $printrow = 2;
            
            foreach ($absences as $abs)
            {
               if ($printrow==1) $printrow=2; else $printrow=1;
            ?>
               <!-- <?=$abs['name']?> -->
               <tr>
                  <td class="dlg-row<?=$printrow?>" style="width: 20px; text-align: center;"><input type="checkbox" name="chk_abs[]" value="<?=$abs['id']?>"></td>
                  <td class="dlg-row<?=$printrow?>" style="width: 26px; padding-left: 12px;">
                     <div style="color: #<?=$abs['color']?>; background-color: #<?=$abs['bgcolor']?>; border: 1px solid #000000; width: 24px; height: 20px; text-align: center; padding: 4px 0px 0px 0px;">
                     <?php if ($abs['icon']=="No") {?>
                        <?=$abs['symbol']?>
                     <?php } else { ?>
                        <img src="<?=$CONF['app_icon_dir'].$abs['icon']?>" alt="" style="vertical-align: middle;">
                     <?php } ?>
                     </div>
                  </td>
                  <td class="dlg-row<?=$printrow?>" style="font-weight: bold; vertical-align: middle;"><?=$abs['name']?> (<?=$abs['symbol']?>)</td>
                  <td class="dlg-row<?=$printrow?>" style="text-align: right;">
                     <input name="btn_edit" type="button" class="button" value="<?=$LANG['btn_edit']?>" onclick="javascript:window.location.href='absences.php?absid=<?=$abs['id']?>';">&nbsp;
                  </td>
               </tr>
            <?php } ?>
            <tr>
               <td class="dlg-row<?=$printrow?>" style="border-bottom: 1px solid #000000;" colspan="<?=$colspan?>">
                  <input type="checkbox" name="select-all" id="select-all" style="margin-right: 8px; vertical-align: middle;"><?=$LANG['select_all']?>&nbsp;
                  <input name="btn_abs_del" type="submit" class="button" value="<?=$LANG['btn_delete_selected']?>" onclick="return confirmSubmit('<?=$LANG['abs_delete_confirm']?>')">&nbsp;
               </td>
            </tr>
         </table>
      </form>
   </div>
</div>
<script type="text/javascript">
$('#select-all').click(function(event) {   
   if(this.checked) {
      // Tick each checkbox
      $(':checkbox').each(function() {
         this.checked = true;                        
      });
   }
   else {
      // Untick each checkbox
      $(':checkbox').each(function() {
         this.checked = false;                        
      });
   }
});
</script>
<?php require("includes/footer_inc.php"); ?>
