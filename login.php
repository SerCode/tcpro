<?php
/**
 * login.php
 *
 * Displays and runs the login dialog
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
else                                  require ("includes/lang/english.tcpro.php");

require_once ("models/announcement_model.php");
require_once ("models/config_model.php");
require_once ("models/login_model.php");
require_once ("models/log_model.php");
require_once ("models/user_model.php");
require_once ("models/user_announcement_model.php");
require_once ("includes/tcuseroption.class.php");

$AN = new Announcement_model;
$C = new Config_model;
$L = new Login_model;
$LOG = new Log_model;
$U = new User_model;
$UA = new User_announcement_model;
$UB = new User_model;
$UO = new tcUserOption;
$errors = "";

if ( isset($_POST['btn_login']) AND in_array($_POST['uname'],$U->getUsernames()) ) {
   $uname = $_POST['uname'];
   switch ($L->login()) {
      case 0 :
         /**
          * Successful login
          */
         $errors = $LANG['login_error_0'];
         $LOG->log("logLogin", $uname, "Login successful");

         if ($UO->true($uname,"notifybirthday")) {
            $bdayalarm = '';
            $query  = "SELECT * FROM `".$UB->table."` ORDER BY `lastname`,`firstname`;";
            $result = $UB->db->db_query($query);
            while ( $row = $UB->db->db_fetch_array($result,MYSQL_ASSOC) ){
               if ( $UO->true($row['username'],"showbirthday") ) {
                  if($UO->true($row['username'],"ignoreage")) {
                     $birthdate=date("d M",strtotime($row['birthday']));
                     if ($birthdate==date("d M")) {
                        $bdayalarm.= $row['firstname']." ".$row['lastname'].": ".$birthdate ."<br>";
                     }
                  } else {
                     $birthdate=date("d M Y",strtotime($row['birthday']));
                     $dayofbirth=date("d M",strtotime($row['birthday']));
                     $age=intval(date("Y"))-intval(substr($row['birthday'],0,4));
                     if ($dayofbirth==date("d M")) {
                        $bdayalarm.= $row['firstname']." ".$row['lastname'].": ".$birthdate ." (".$LANG['cal_age'].": ".$age.")<br>";
                     }
                  }
               }
            }
            if (strlen($bdayalarm)) {
               $bdayalarm = "<strong>".$LANG['ann_bday_title'].date("d. F")."</strong><br><br>".$bdayalarm."<br>&nbsp;";
               $tstamp = date("Ymd")."000000";
               if ($AN->read($tstamp)) {
                  $UA->unassign($tstamp,$uname);
                  $AN->delete($tstamp);
               }
               $AN->save($tstamp,$bdayalarm,1,0);
               $UA->assign($tstamp,$uname);
            }
            if ( file_exists("installation.php") && $uname=="admin" ) {
               $tstamp = date("YmdHis");
               $message = "<span style=\"background-color: #AA0000; color: #FFFFFF; font-weight: bold; padding: 4px;\">".$LANG['err_instfile_title']."</span><br><br>" .
                          "<span style=\"color: #0000AA;\">".$LANG['err_instfile']."<br><br>".
                          "[TeamCal Pro Installation]</span>";
               $popup=1;
               $silent=0;
               $AN->save($tstamp,$message,$popup,$silent);
               $UA->assign($tstamp,$uname);
            }
         }
         jsCloseAndReload("index.php?action=".$C->readConfig("homepage"));
         break;
      case 1 :
         /**
          * Username or password missing
          */
         $errors = $LANG['login_error_1'];
         $LOG->log("logLogin", $uname, "Login: Username or password missing");
         break;
      case 2 :
         /**
          * Username unknown
          */
         $errors = $LANG['login_error_2'];
         $LOG->log("logLogin", $uname, "Login: Username unknown");
         break;
      case 3 :
         /**
          * Account is locked
          */
         $errors = $LANG['login_error_3'];
         $LOG->log("logLogin", $uname, "Login: Account locked");
         break;
      case 4 :
      case 5 :
         /**
          * 4: Password incorrect 1st time
          * 5: Password incorrect 2nd or higher time
          */
         $U->findByName($uname);
         $errors = $LANG['login_error_4a'];
         $errors .= strval($U->bad_logins);
         $errors .= $LANG['login_error_4b'];
         $errors .= $C->readConfig("badLogins");
         $errors .= $LANG['login_error_4c'];
         $errors .= $C->readConfig("gracePeriod");
         $errors .= $LANG['login_error_4d'];
         $LOG->log("logLogin", $uname, "Login: Password incorrect");
         break;
      case 6 :
         /**
          * Login is locked due to too many bad login attempts
          */
         $now = date("U");
         $U->findByName($uname);
         $errors = $LANG['login_error_6a'];
         $errors .= strval(intval($C->readConfig("gracePeriod")) - ($now - $U->bad_logins_start));
         $errors .= $LANG['login_error_6b'];
         $LOG->log("logLogin", $uname, "Login: Too many bad login attempts");
         break;
      case 7 :
         /**
          * Password incorrect (no bad login count)
          */
         $errors = $LANG['login_error_7'];
         $LOG->log("logLogin", $uname, "Login: Password incorrect");
         break;
      case 8 :
         /**
          * Account not verified
          */
         $errors = $LANG['login_error_8'];
         $LOG->log("logLogin", $uname, "Login: Account not verified");
         break;
      case 91 :
         /**
          * LDAP error: password missing
          */
         $errors = $LANG['login_error_91'];
         $LOG->log("logLogin", $uname, "Login: LDAP password missing");
         break;
      case 92 :
         /**
          * LDAP error: bind failed
          */
         $errors = $LANG['login_error_92'];
         $LOG->log("logLogin", $uname, "Login: LDAP bind failed");
         break;
      case 93 :
         /**
          * LDAP error: Unable to connect
          */
         $errors = $LANG['login_error_93'];
         $LOG->log("logLogin", $uname, "Login: LDAP unable to connect to server");
         break;
      case 94 :
         /**
          * LDAP error: Start of TLS encryption failed
          */
         $errors = $LANG['login_error_94'];
         $LOG->log("logLogin", $uname, "Login: LDAP start TLS failed");
         break;
      case 95 :
         /**
          * LDAP error: Username not found
          */
         $errors = $LANG['login_error_95'];
         $LOG->log("logLogin", $uname, "Login: LDAP username not found");
         break;
      case 96 :
         /**
          * LDAP error: LDAP search bind failed
          */
         $errors = $LANG['login_error_96'];
         $LOG->log("logLogin", $uname, "Login: LDAP search bind failed");
         break;
   }
}

require("includes/header.html.inc.php");
?>
<body>
   <div id="content">
      <div id="content-content">
         <form name="login" method="POST" action="<?=$_SERVER['PHP_SELF']."?lang=".$CONF['options']['lang']?>">
            <table class="dlg">
               <tr>
                  <td class="dlg-header" colspan="3">
                     <?php printDialogTop($LANG['login_login'],"login.html","ico_login.png"); ?>
                  </td>
               </tr>
               <tr>
                  <td class="dlg-body">
                     <table class="dlg-frame">
                        <tr>
                           <td class="dlg-body" rowspan="<?php if (strlen($errors)) echo "3"; else echo "2"; ?>"><img src="themes/<?=$theme?>/img/login.png" alt="Login" title="Login" border="0"></td>
                           <td class="dlg-body" width="80"><strong><?=$LANG['login_username']?></strong></td>
                           <td class="dlg-body">
                              <input name="uname" id="uname" size="30" type="text" class="text" value="">
                           </td>
                        </tr>
                        <tr>
                           <td class="dlg-body" width="80"><strong><?=$LANG['login_password']?></strong></td>
                           <td class="dlg-body">
                              <input name="pword" id="pword" size="30" type="password" class="text" value="">
                           </td>
                        </tr>
                        <?php if ( strlen($errors) ) { ?>
                        <tr>
                           <td class="dlg-body" colspan="3">
                              <div class="erraction">
                              <?php
                              echo $errors;
                              ?>
                              </div>
                           </td>
                        </tr>
                        <?php } ?>
                     </table>
                  </td>
               </tr>
               <tr>
                  <td class="dlg-menu">
                      <input name="btn_login" type="submit" class="button" value="<?=$LANG['btn_login']?>">
                      <input name="btn_help" type="button" class="button" onclick="javascript:this.blur(); openPopup('help/<?=$CONF['options']['helplang']?>/html/index.html?login.html','help','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,titlebar=0,resizable=0,dependent=1,width=750,height=500');" value="<?=$LANG['btn_help']?>">
                      <input name="btn_close" type="button" class="button" onclick="javascript:window.close();" value="<?=$LANG['btn_close']?>">
                  </td>
               </tr>
            </table>
         </form>
      </div>
   </div>
<?php require("includes/footer.html.inc.php"); ?>