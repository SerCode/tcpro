<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * user_option_model.php
 *
 * Contains the class dealing with the user-option table
 *
 * @package TeamCalPro
* @version 3.6.004
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */
/**
 * Make sure the class hasn't been loaded yet
 */
if (!class_exists("User_option_model")) {
   /**
    * Requires the database class
    */
   require_once ("models/db_model.php");

   /**
    * Provides objects and methods to manage the user-option table
    * @package TeamCalPro
    */
   class User_option_model {
      var $db = '';
      var $table = '';
      var $log = '';
      var $logtype = '';

      var $id = NULL;
      var $username = NULL;
      var $option = NULL;
      var $value = NULL;

      // ---------------------------------------------------------------------
      /**
       * Constructor
       */
      function User_option_model() {
         global $CONF;
         unset($CONF);
         require ("config.tcpro.php");
         $this->db = new Db_model;
         $this->table = $CONF['db_table_user_options'];
         $this->log = $CONF['db_table_log'];
      }

      // ---------------------------------------------------------------------
      /**
       * Creates a new user-option record
       *
       * @param string $createuser Username
       * @param string $createoption Option name
       * @param string $createvalue Option value
       */
      function create($createuser, $createoption, $createvalue) {
         $query = "INSERT INTO `" . $this->table . "` ";
         $query .= "(`username`,`option`,`value`) ";
         $query .= "VALUES ('";
         $query .= $createuser . "','";
         $query .= $createoption . "','";
         $query .= $createvalue . "'";
         $query .= ")";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Deletes a user-option record by ID from local class variable
       */
      function deleteById() {
         $query = "DELETE FROM `" . $this->table . "` WHERE `id` = '" . $this->id . "'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Deletes all records for a given user
       *
       * @param string $deluser Username to delete
       */
      function deleteByUser($deluser = '') {
         $query = "DELETE FROM `" . $this->table . "` WHERE `username` = '" . $deluser . "'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Delete an option records for a given user
       *
       * @param string $deluser Username to find
       * @param string $deloption Option to delete
       */
      function deleteUserOption($deluser,$deloption) {
         $query = "DELETE FROM `" . $this->table . "` WHERE `username` = '" . $deluser . "' AND `option` = '".$deloption."';";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Finds the value of an option for a given user
       *
       * @param string $finduser Username to find
       * @param string $findoption Option to find
       * @return string Value of the option (or NULL of not found)
       */
      function find($finduser, $findoption) {
         $rc = NULL;
         // see if an entry for this user and absence type exists
         $query = "SELECT value FROM " . $this->table .
         " WHERE `username`=\"" . $finduser . "\"" .
         " AND `option`=\"" . $findoption . "\"";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1)
         {
            // exactly one row found ( a good thing!)
            $row = $this->db->db_fetch_array($result, MYSQL_ASSOC);
            $rc = trim($row['value']);
         }
         return $rc;
      }

      // ---------------------------------------------------------------------
      /**
       * Finds the boolean value of an option for a given user
       *
       * @param string $finduser Username to find
       * @param string $findoption Option to find
       * @return string True or false
       */
      function true($finduser, $findoption) {
         $rc = NULL;
         // see if an entry for this user and absence type exists
         $query = "SELECT * FROM `" . $this->table . "` WHERE `username` = '" . $finduser . "' AND `option` = '" . $findoption . "'";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            // exactly one row found ( a good thing!)
            $row = $this->db->db_fetch_array($result);
            if (trim($row['value'])=="yes" OR trim($row['value'])!="no")
               $rc = 1;
         }
         return $rc;
      }

      // ---------------------------------------------------------------------
      /**
       * Updates a user option
       *
       * @param string $upduser Username to find
       * @param string $updoption Option to find
       * @param string $updvalue New value
       */
      function update($upduser, $updoption, $updvalue) {
         $query = "SELECT value FROM `".$this->table."` WHERE `username` = '".$upduser."' AND `option`='".$updoption."';";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $query = "UPDATE `".$this->table."` SET `value` = '".$updvalue."' WHERE `username` = '".$upduser."' AND `option`='".$updoption."';";
            $result = $this->db->db_query($query);
            return $result;
         }
         elseif ($this->db->db_numrows($result) == 0) {
            $query = "INSERT INTO `".$this->table."` (`username`,`option`,`value`) VALUES ('".$upduser."','".$updoption."','".$updvalue."')";
            $result = $this->db->db_query($query);
            return $result;
         }
      }

      // ---------------------------------------------------------------------
      /**
       * Saves a user option, creates it if not exists
       *
       * @param string $upduser Username to find
       * @param string $updoption Option to find
       * @param string $updvalue New value
       */
      function save($upduser, $updoption, $updvalue) {
         $query = "SELECT value FROM `".$this->table."` WHERE `username` = '".$upduser."' AND `option`='".$updoption."';";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $query = "UPDATE `".$this->table."` SET `value` = '".$updvalue."' WHERE `username` = '".$upduser."' AND `option`='".$updoption."';";
            $result = $this->db->db_query($query);
            return $result;
         }
         elseif ($this->db->db_numrows($result) == 0) {
            $query = "INSERT INTO `".$this->table."` (`username`,`option`,`value`) VALUES ('".$upduser."','".$updoption."','".$updvalue."')";
            $result = $this->db->db_query($query);
            return $result;
         }
      }

      // ---------------------------------------------------------------------
      /**
       * Updates a user option
       *
       * @param string $upduser Username to find
       * @param string $updoption Option to find
       * @param string $updvalue New value
       */
      function updateRegion($region_old, $region_new='default') {
         $query = "UPDATE `".$this->table."` SET `value`='".$region_new."' WHERE `option`='defregion' AND `value`='".$region_old."'";
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
