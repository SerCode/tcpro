<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * tcallowance.class.php
 * 
 * Contains the class to interface with the allowance table
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
if (!class_exists("tcAllowance")) {
   /**
    * Requires the database class
    */
   require_once ("includes/db.class.php");
   
   /**
    * Provides objects and methods to interface with the allownace table
    * @package TeamCalPro
    */
   class tcAllowance {
      var $db = NULL;
      var $table = '';
      var $log = '';
      var $logtype = '';
      var $id = NULL;
      var $username = '';
      var $abssym = '';
      var $lastyear = '0';
      var $curryear = '0';

      /**
       * Constructor
       */
      function tcAllowance() {
         global $CONF;
         unset($CONF);
         require ("config.tcpro.php");
         $this->db = new myDB;
         $this->table = $CONF['db_table_allowance'];
         $this->log = $CONF['db_table_log'];
      }

      /**
       * Creates an allowance record
       * 
       * @param string $createuser User name for this allowance record
       * @param string $createsym Absence type for this allowance record
       * @param integer $createlastyear Number of taken absences of this type in the last year
       * @param integer $createcurryear Allowance for this absence for the current year
       */
      function createAllowance($createuser, $createsym, $createlastyear, $createcurryear) {
         $query = "INSERT INTO `" . $this->table . "` ";
         $query .= "(`username`,`abssym`,`lastyear`,`curryear`) ";
         $query .= "VALUES ('";
         $query .= $createuser . "','";
         $query .= $createsym . "','";
         $query .= $createlastyear . "','";
         $query .= $createcurryear . "'";
         $query .= ")";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes a record by ID taken from local variable
       * 
       */
      function deleteById() {
         $query = "DELETE FROM `" . $this->table . "` WHERE `id` = '" . $this->id . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes all allowance records for a given absence type
       * 
       * @param string $symbol Absence symbol to delete
       */
      function deleteBySymbol($symbol = '') {
         $query = "DELETE FROM `" . $this->table . "` WHERE `abssym` = '" . $symbol . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes all allowance records for a given username
       * 
       * @param string $symbol Absence symbol to delete
       */
      function deleteByUser($uname = '') {
         $query = "DELETE FROM `" . $this->table . "` WHERE `username` = '" . $uname . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Finds the allowance record for a given username and absence type and
       * fills the local variables with the values found in database
       * 
       * @param string $finduser Username to find
       * @param string $findsym Absence type to find
       * @return boolean True if allowance exists, false if not
       */
      function findAllowance($finduser, $findsym) {
         $rc = 0;
         $query = "SELECT * FROM `" . $this->table . "` WHERE `username` = '" . $finduser . "' AND `abssym` = '" . $findsym . "'";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $this->username = $row['username'];
            $this->abssym = $row['abssym'];
            $this->lastyear = $row['lastyear'];
            $this->curryear = $row['curryear'];
            $rc = 1;
         }
         return $rc;
      }

      /**
       * Updates an allowance record from the local variables
       * 
       */
      function update() {
         $query = "UPDATE `" . $this->table . "` ";
         $query .= "SET `username`   = '" . $this->username . "', ";
         $query .= "`abssym`     = '" . $this->abssym . "', ";
         $query .= "`lastyear`   = '" . $this->lastyear . "', ";
         $query .= "`curryear`   = '" . $this->curryear . "' ";
         $query .= "WHERE `id`       = '" . $this->id . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Updates the last year amount for a user/absence
       * 
       * @param string $upduser Username to find
       * @param string $updsym Absence type to find
       * @param integer $newlast New value for last year
       */
      function updateLastyear($upduser, $updsym, $newlast) {
         $query = "UPDATE `" . $this->table . "` ";
         $query .= "SET `lastyear`   = '" . $newlast . "' ";
         $query .= "WHERE `username` = '" . $upduser . "' AND `abssym` = '" . $updsym . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Updates the current year amount for a user/absence
       * 
       * @param string $upduser Username to find
       * @param string $updsym Absence type to find
       * @param integer $newcurr New value for current year
       */
      function updateCurryear($upduser, $updsym, $newcurr) {
         $query = "UPDATE `" . $this->table . "` ";
         $query .= "SET `curryear`   = '" . $newcurr . "' ";
         $query .= "WHERE `username` = '" . $upduser . "' AND `abssym` = '" . $updsym . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Updates the ast year and current year amount for a user/absence
       * 
       * @param string $upduser Username to find
       * @param string $updsym Absence type to find
       * @param integer $newlast New value for last year
       * @param integer $newcurr New value for current year
       */
      function updateAllowance($upduser, $updsym, $newlast, $newcurr) {
         $query = "UPDATE `" . $this->table . "` ";
         $query .= "SET `lastyear`   = '" . $newlast . "', ";
         $query .= "`curryear`   = '" . $newcurr . "' ";
         $query .= "WHERE `username` = '" . $upduser . "' AND `abssym` = '" . $updsym . "'";
         $result = $this->db->db_query($query);
      }
   } // End Class tcAllowance
} // if ( !class_exists( "tcAllowance" ) ) {
?>
