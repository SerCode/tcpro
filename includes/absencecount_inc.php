<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * absencecount_inc.php
 *
 * Fieldset showing absence counts. Used in editprofile and viewprofile.
 *
 * @package TeamCalPro
 * @version 3.6.000
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */

//echo "<script type=\"text/javascript\">alert(\"Debug: "\");</script>";
?>
<fieldset><legend><?=$LANG['show_absence']?></legend>
   <table style="width: 100%;">
      <tr>
         <td class="dlg-frame-body" colspan="2">
            <script type="text/javascript">
               $(function() { $( "#cntfrom" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd" }); });
               $(function() { $( "#cntto" ).datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd" }); });
            </script>
            <?=$LANG['show_absence_from']?>&nbsp;
            <input name="cntfrom" id="cntfrom" size="10" maxlength="10" type="text" class="text" style="text-align: center;" value="<?=$countfrom?>">
            &nbsp;<?=$LANG['show_absence_to']?>:&nbsp;
            <input name="cntto" id="cntto" size="10" maxlength="10" type="text" class="text" style="text-align: center;" value="<?=$countto?>">
            <hr size="1">
         </td>
      </tr>
      <tr>
         <td class="dlg-frame-body" colspan="2">
            <table style="width: 100%;">
               <tr>
                  <td class="dlg-caption-gray"><?=$LANG['show_absence_type']?></td>
                  <td class="dlg-caption-gray" style="text-align: center;"><?=$LANG['show_absence_lastyear']?></td>
                  <td class="dlg-caption-gray" style="text-align: center;"><?=$LANG['show_absence_allowance']?></td>
                  <td class="dlg-caption-gray" style="text-align: center;"><?=$LANG['show_absence_taken']?></td>
                  <td class="dlg-caption-gray" style="text-align: center;"><?=$LANG['show_absence_factor']?></td>
                  <td class="dlg-caption-gray" style="text-align: center;"><?=$LANG['show_absence_remainder']?></td>
               </tr>
               <?php
               $countfrom = str_replace("-","",$countfrom);
               $countto = str_replace("-","",$countto);
               $rowstyle=0;
               $absences=$A->getAll();
               foreach ($absences as $abs) {
                  $A->get($abs['id']);
                  if ( $A->factor ) {
                     if ( !$A->hide_in_profile ||
                          ($UL->checkUserType($CONF['UTADMIN']) || $UL->checkUserType($CONF['UTDIRECTOR']) || $UL->checkUserType($CONF['UTMANAGER']) )
                        ) {
                        if ( $B->find($U->username,$A->id)) {
                           $lstyr = $B->lastyear;
                           $allow = $B->curryear;
                        }else{
                           $lstyr = 0;
                           $allow = $A->allowance;
                        }
                        //echo "<script type=\"text/javascript\">alert(\"Debug: ".$countfrom."|".$countto." \");</script>";
                        $taken=countAbsence($U->username,$A->id,$countfrom,$countto);
                        $remain = $lstyr + $allow - $taken;
                        if ($remain<0) $stylesuffix="r"; else $stylesuffix="";
                        if ($rowstyle==1) $rowstyle=0; else $rowstyle=1;

                        $allowed=FALSE;
                        if ( $UG->shareGroups($user, $U->username) ) {
                           if (isAllowed("editGroupUserAllowances")) $allowed=TRUE;
                        }
                        else {
                           if (isAllowed("editAllUserAllowances")) $allowed=TRUE;
                        } ?>

                        <tr class="row<?=$rowstyle?>">
                          <td class="dlg-frame-bodyc" style="text-align: left; vertical-align: middle;">
                              <div style="border: 1px solid #000000; height: 24px; width: 24px; float: left; background-color: #<?=$A->dspbgcolor?>; text-align: center; vertical-align: middle; margin-right: 4px;">
                                 <?php if ($A->icon!='No') { ?>
                                    <img style="padding-top: 4px;" alt="" src="<?=$CONF['app_icon_dir'].$A->icon?>">
                                 <?php
                                 }
                                 else { ?>
                                    <?=$A->symbol?>
                                 <?php } ?>
                              </div>
                              <?=$A->name?>&nbsp;(<?=$A->symbol?>)
                          </td>

                          <?php if ($allowed) { ?>
                          <td class="dlg-frame-bodyc" style="text-align: center;"><input name="lastyear-<?=$A->id?>" id="lastyear-<?=$A->id?>" size="2" maxlength="4" type="text" class="text" style="text-align: center;" value="<?=$lstyr?>"></td>
                          <td class="dlg-frame-bodyc" style="text-align: center;"><input name="allowance-<?=$A->id?>" id="allowance-<?=$A->id?>" size="2" maxlength="4" type="text" class="text" style="text-align: center;" value="<?=$allow?>"></td>
                          <?php } else { ?>
                          <td class="dlg-frame-bodyc" style="text-align: center;" ><?=$lstyr?></td>
                          <td class="dlg-frame-bodyc" style="text-align: center;" ><?=$allow?></td>
                          <?php } ?>

                          <td class="dlg-frame-bodyc" style="text-align: center;"><?=$taken?></td>
                          <td class="dlg-frame-bodyc" style="text-align: center;"><?=$A->factor?></td>
                          <td class="dlg-frame-bodyc<?=$stylesuffix?>" style="text-align: center;"><?=$remain?></td>
                        </tr>
                     <?php }
                  }
               }
               ?>
               <tr>
                  <td>&nbsp;</td>
                  <td colspan="2" style="text-align: center;">
                     <?php if ($allowed) { ?>
                        <input name="btn_abs_update" type="submit" class="button" value="<?=$LANG['btn_update']?>">
                     <?php } ?>
                  </td>
                  <td colspan="3">&nbsp;</td>
               </tr>
            </table>
         </td>
      </tr>
   </table>
</fieldset>