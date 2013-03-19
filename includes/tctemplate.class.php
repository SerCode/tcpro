<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * tctemplate.class.php
 * 
 * Contains the class dealing with the template table
 * 
 * @package TeamCalPro
 * @version 3.5.002 
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */
/**
 * Make sure the class hasn't been loaded yet
 */
if (!class_exists("tcTemplate")) {
   /**
    * Requires the database class
    */
   require_once ("includes/db.class.php");

   /**
    * Provides objects and methods to manage the template table
    * @package TeamCalPro
    */
   class tcTemplate {
      var $db = '';
      var $table = '';
      var $log = '';
      var $logtype = '';
      var $username = ''; // link to username of user table
      var $year = '';
      var $month = '';
      var $abs1 = 0;
      var $abs2 = 0;
      var $abs3 = 0;
      var $abs4 = 0;
      var $abs5 = 0;
      var $abs6 = 0;
      var $abs7 = 0;
      var $abs8 = 0;
      var $abs9 = 0;
      var $abs10 = 0;
      var $abs11 = 0;
      var $abs12 = 0;
      var $abs13 = 0;
      var $abs14 = 0;
      var $abs15 = 0;
      var $abs16 = 0;
      var $abs17 = 0;
      var $abs18 = 0;
      var $abs19 = 0;
      var $abs20 = 0;
      var $abs21 = 0;
      var $abs22 = 0;
      var $abs23 = 0;
      var $abs24 = 0;
      var $abs25 = 0;
      var $abs26 = 0;
      var $abs27 = 0;
      var $abs28 = 0;
      var $abs29 = 0;
      var $abs30 = 0;
      var $abs31 = 0;
      
      /**
       * Constructor
       */
      function tcTemplate() {
         global $CONF;
         unset($CONF);
         require ("config.tcpro.php");
         $this->db = new myDB;
         $this->table = $CONF['db_table_templates'];
         $this->log = $CONF['db_table_log'];
      }

      /**
       * Creates a template from local variables
       */
      function create() {
         $query = "INSERT INTO `".$this->table."` (`username`,`year`,`month`,`abs1`,`abs2`,`abs3`,`abs4`,`abs5`,`abs6`,`abs7`,`abs8`,`abs9`,`abs10`,`abs11`,`abs12`,`abs13`,`abs14`,`abs15`,`abs16`,`abs17`,`abs18`,`abs19`,`abs20`,`abs21`,`abs22`,`abs23`,`abs24`,`abs25`,`abs26`,`abs27`,`abs28`,`abs29`,`abs30`,`abs31`) ";
         $query .= "VALUES ('";
         $query .= $this->username . "','";
         $query .= $this->year . "','";
         $query .= $this->month . "','";
         for ($i=1; $i<=31; $i++) {
            $prop='abs'.$i;
            $query .= $this->$prop."','";
         }
         $query = substr($query,0,-2);
         $query .= ")";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes a template by username, year and month
       *  
       * @param string $uname Username this template is for
       * @param string $year Year of the template (YYYY)
       * @param string $month Month of the template (MM)
       */
      function deleteTemplate($uname = '', $year = '', $month = '') {
         $query = "DELETE FROM `".$this->table."` WHERE `username`='".$uname."' AND `year`='".$year."' AND `month`='".$month."'";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes a template by ID
       * 
       * @param integer $id ID of record to delete
       */
      function deleteById($id = '') {
         $query = "DELETE FROM `".$this->table."` WHERE `id`='".$id."'";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes all templates for a username
       * 
       * @param string $uname Username to delete all records of
       */
      function deleteByUser($uname = '') {
         $query = "DELETE FROM `".$this->table."` WHERE `username`='".$uname."'";
         $result = $this->db->db_query($query);
      }

      /**
       * Gets the absence ID of a given username, year, month and day
       * 
       * @param string $uname Username to find
       * @param string $year Year to find (YYYY)
       * @param string $month Month to find (MM)
       * @param string $day Day of month to find (D)
       * @return integer 0 or absence ID
       */
      function getAbsence($uname='', $year='', $month='', $day='1') {
         $rc = 0;
         $query = "SELECT abs".$day." FROM `".$this->table."` WHERE `username`='".$uname."' AND `year`='".$year."' AND `month`='".$month."'";
         $result = $this->db->db_query($query);

         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            return $row['abs'.$day];
         }
         else {
            return $rc;
         }
      }

      /**
       * Counts the absences of one ID of a given username, year, month and day
       * 
       * @param string $uname Username to find
       * @param string $year Year to find (YYYY)
       * @param string $month Month to find (MM)
       * @param string $day Day of month to find (D)
       * @return integer 0 or absence ID count
       */
      function countAbsence($uname='%', $year='', $month='', $absid, $start=1, $end=0) {
         $rc = 0;
         $mytime = $month . " 1," . $year;
         $myts = strtotime($mytime);
         if (!$end OR $end>31) $end = date("t",$myts);
         $query = "SELECT * FROM `".$this->table."` WHERE `username` LIKE '".$uname."' AND `year`='".$year."' AND `month`='".sprintf("%02d",$month)."';";
         $result = $this->db->db_query($query);
         if ($uname!="%" AND $this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            for ($i=$start; $i<=$end; $i++) {
               if ($row['abs'.$i]==$absid) $rc++;
            }
         }
         else if ($this->db->db_numrows($result)) {
            while ($row = $this->db->db_fetch_array($result)) {
               for ($i=$start; $i<=$end; $i++) {
                  if ($row['abs'.$i]==$absid) $rc++;
               }
            }
         }
         return $rc;
      }

      /**
       * Gest a template by username, year and month
       * 
       * @param string $uname Username to find
       * @param string $year Year to find (YYYY)
       * @param string $month Month to find (MM)
       * @return integer Result of MySQL query
       */
      function getTemplate($uname='', $year='', $month='') {
         $rc = 0;
         $query = "SELECT * FROM `".$this->table."` WHERE `username`='".$uname."' AND `year`='".$year."' AND `month`='".$month."'";
         $result = $this->db->db_query($query);

         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $this->username = $row['username'];
            $this->year = $row['year'];
            $this->month = $row['month'];
            for ($i=1; $i<=31; $i++) {
               $prop='abs'.$i;
               $this->$prop = $row[$prop];
            }
            $rc = 1;
         }
         return $rc;
      }

      /**
       * Gets a template by ID
       * 
       * @param string $id Record ID to find
       * @return integer Result of MySQL query
       */
      function getTemplateById($id = '') {
         $rc = 0;
         $query = "SELECT * FROM `".$this->table."` WHERE `id`='".$id."'";
         $result = $this->db->db_query($query);

         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $this->username = $row['username'];
            $this->year = $row['year'];
            $this->month = $row['month'];
            for ($i=1; $i<=31; $i++) {
               $prop='abs'.$i;
               $this->$prop = $row[$prop];
            }
            $rc = 1;
         }
         return $rc;
      }

      /**
       * Updates a template for a given username, year and month
       * 
       * @param string $uname Username for update
       * @param string $year Year for update (YYYY)
       * @param string $month Month for update (MM)
       */
      function update($uname, $year, $month) {
         $query = "UPDATE `" . $this->table . "` SET ";
         $query .= "`username` = '".$this->username."', ";
         $query .= "`year`     = '".$this->year."', ";
         $query .= "`month`    = '".$this->month."', ";
         for ($i=1; $i<=31; $i++) {
            $prop='abs'.$i;
            $query .= "`".$prop."`='".$this->$prop."', ";
         }
         $query = substr($query,0,-2);
         $query .= " WHERE `username`='".$uname."' AND `year`='".$year."' AND `month`='".$month."'";
         $result = $this->db->db_query($query);
      }

      /**
       * Replaces an absence ID in all templates.
       * 
       * @param string $symopld Symbol to be replaced
       * @param string $symnew Symbol to replace with
       */
      function replaceAbsID($absidold, $absidnew) {
         $query = "SELECT * FROM `" . $this->table . "`";
         $result = $this->db->db_query($query);
         while ($row = $this->db->db_fetch_array($result)) {
            $qry = "UPDATE `".$this->table."` SET ";
            for ($i=1; $i<=31; $i++) {
               if ($row['abs'.$i]==$absidold) {
                  $prop='abs'.$i;
                  $row[$prop]=$absidnew;
                  $qry .= "`".$prop."`='".$row[$prop]."', ";
               }
            }
            $qry = substr($qry,0,-2);
            $qry.= " WHERE `id`='".$row['id']."';";
            $res = $this->db->db_query($qry);
         }
      }

   } // End Class tcTemplate

} // if ( !class_exists( "tcTemplate" ) ) {
?>
