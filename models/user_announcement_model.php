<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * user_announcement_model.php
 * 
 * Contains the class to interface with the user-announcement table
 *
 * @package TeamCalPro
 * @version 3.6.010 
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */

/**
 * Make sure the class hasn't been loaded yet
 */
if (!class_exists("User_announcement_model")) {
   /**
    * Requires the database class
    */
   require_once ("models/db_model.php");

   /**
    * Provides objects and methods to interface with the announcement and user-announcement table
    * @package TeamCalPro
    */
   class User_announcement_model {
      var $db = '';
      var $table = '';
      var $log = '';
      var $logtype = '';
      var $id = NULL;
      var $timestamp = '';
      var $text = '';
      var $popup = '0';
      var $silent = '0';

      // ---------------------------------------------------------------------
      /**
       * Constructor
       */
      function User_announcement_model() {
         global $CONF;
         unset($CONF);
         require ("config.tcpro.php");
         $this->db = new Db_model;
         $this->table = $CONF['db_table_user_announcement'];
         $this->log = $CONF['db_table_log'];
      }

      // ---------------------------------------------------------------------
      /**
       * Clear all records in the user-announcement table
       */
      function deleteAll() {
         $query = "TRUNCATE TABLE `".$this->table."`";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Clear all records in the user-announcement table for a given user
       * 
       * @param string $username Username of the records to delete
       */
      function deleteAllForUser($username) {
         $query = "DELETE FROM `".$this->table."` WHERE `username`='".$username."';";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Reads all records for a given user into an array
       * 
       * @param string $username Username
       * @return array $uaarray Array with all records
       */
      function getAllForUser($username) {
         $uaarray = array();
         $query = "SELECT * FROM `".$this->table."` WHERE username='".$username."' ORDER BY ats DESC;";
         $result = $this->db->db_query($query);
         while ( $row=$this->db->db_fetch_array($result) ) {
            $uaarray[] = $row;
         }
         return $uaarray;
      }

      // ---------------------------------------------------------------------
      /**
       * Reads all records for a given timestamp into an array
       * 
       * @param string $ts Timestamp
       * @return array $uaarray Array with all records
       */
      function getAllForTimestamp($ts) {
         $uaarray = array();
         $query = "SELECT * FROM `".$this->table."` WHERE ats='".$ts."' ORDER BY ats DESC;";
         $result = $this->db->db_query($query);
         while ( $row=$this->db->db_fetch_array($result) ) {
            $uaarray[] = $row;
         }
         return $uaarray;
      }

      // ---------------------------------------------------------------------
      /**
       * Assign an announcement to a user (by timestamp)
       * 
       * @param string $ts Timestamp to search for
       * @param string $user Username to assign to
       */
      function assign($ts, $user) {
         $query = "INSERT into `".$this->table."` (`username`,`ats`) ";
         $query .= "VALUES ('".$user."','".$ts."')";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Delete an announcement by timestamp and username
       * 
       * @param string $ts Timestamp to search for
       * @param string $user Username to search for
       */
      function unassign($ts, $user) {
         $query = "DELETE FROM ".$this->table." WHERE username='".$user."' AND ats='".$ts."'";
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
