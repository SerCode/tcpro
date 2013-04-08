<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * allowance_model.php
 * 
 * Contains the class to interface with the allowance table
 *
 * @package TeamCalPro
 * @version 3.6.001 Dev 
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */

/**
 * Make sure the class hasn't been loaded yet
 */
if (!class_exists("Allowance_model")) {
   /**
    * Requires the database class
    */
   require_once ("models/db_model.php");
   
   /**
    * Provides objects and methods to interface with the allowance table
    * @package TeamCalPro
    */
   class Allowance_model {
      var $db = NULL;
      var $table = '';
      var $log = '';
      var $logtype = '';
      var $id = NULL;
      var $username = '';
      var $absid = 0;
      var $lastyear = 0;
      var $curryear = 0;

      // ---------------------------------------------------------------------
      /**
       * Constructor
       */
      function Allowance_model() {
         global $CONF;
         unset($CONF);
         require ("config.tcpro.php");
         $this->db = new Db_model;
         $this->table = $CONF['db_table_allowance'];
         $this->log = $CONF['db_table_log'];
      }

      // ---------------------------------------------------------------------
      /**
       * Creates an allowance record
       */
      function create() {
         $query = "INSERT INTO `".$this->table."` (`username`,`absid`,`lastyear`,`curryear`) VALUES (";
         $query .= "'".$this->username."', ";
         $query .= $this->absid.", ";
         $query .= $this->lastyear.", ";
         $query .= $this->curryear." ";
         $query .= ")";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Updates an allowance record from the local variables
       * 
       */
      function update() {
         $query = "UPDATE `" . $this->table . "` SET ";
         $query .= "`username`='".$this->username."', ";
         $query .= "`absid`=".$this->absid.", ";
         $query .= "`lastyear`=".$this->lastyear.", ";
         $query .= "`curryear`=".$this->curryear." ";
         $query .= "WHERE `id`=".$this->id.";";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Deletes an allowance record
       */
      function delete() {
         $query = "DELETE FROM `".$this->table."` WHERE `id` = '".$this->id."'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Deletes all allowance records for a given absence type
       * 
       * @param string $absid Absence ID to delete
       */
      function deleteAbs($absid='') {
         $query = "DELETE FROM `".$this->table."` WHERE `absid`='".$absid."'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Deletes all allowance records for a given username
       * 
       * @param string $username Username to delete
       */
      function deleteUser($username='') {
         $query = "DELETE FROM `".$this->table."` WHERE `username`='".$username."'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Finds the allowance record for a given username and absence type and
       * fills the local variables with the values found in database
       * 
       * @param string $username Username to find
       * @param string $absid Absence type to find
       * @return boolean True if allowance exists, false if not
       */
      function find($username, $absid) {
         $rc = 0;
         $query = "SELECT * FROM `".$this->table."` WHERE `username`='".$username."' AND `absid`='".$absid."'";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->absid = $row['absid'];
            $this->lastyear = $row['lastyear'];
            $this->curryear = $row['curryear'];
            $rc = 1;
         }
         return $rc;
      }

      // ---------------------------------------------------------------------
      /**
       * Updates the last year amount for a user/absence
       * 
       * @param string $username Username to find
       * @param string $absid Absence ID to find
       * @param integer $lastyear New value for last year
       */
      function updateLastyear($username, $absid, $lastyear) {
         $query = "UPDATE `".$this->table."` SET `lastyear`='".$lastyear."' ".
                  "WHERE `username`='".$username."' AND `absid`='".$absid."'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Updates the current year amount for a user/absence
       * 
       * @param string $username Username to find
       * @param string $absid Absence ID to find
       * @param integer $curryear New value for current year
       */
      function updateCurryear($username, $absid, $curryear) {
         $query = "UPDATE `".$this->table."` SET `curryear`='".$newcurr."' ".
                  "WHERE `username`='".$username."' AND `absid`='".$absid."'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Updates the ast year and current year amount for a user/absence
       * 
       * @param string $username Username to find
       * @param string $absid Absence ID to find
       * @param integer $lastyear New value for last year
       * @param integer $curryear New value for current year
       */
      function updateAllowance($username, $absid, $lastyear, $curryear) {
         $query = "UPDATE `".$this->table."` SET `lastyear`='".$lastyear."', `curryear`='".$curryear."' ".
                  "WHERE `username`='".$username."' AND `absid`='".$absid."'";
         $result = $this->db->db_query($query);
      }
      
      // ---------------------------------------------------------------------
      /**
       * Optimize table
       * 
       * @return boolean Optimize result
       */ 
      function optimize() {
         $result = $this->db->db_query('OPTIMIZE TABLE '.$this->table);
         return $result;
      }
            
   }
}
?>
