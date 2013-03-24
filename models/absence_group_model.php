<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * absence_group_model.php
 * 
 * Contains the class to interface with the abs group table
 *
 * @package TeamCalPro
 * @version 3.5.003 
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */
/**
 * Make sure the class hasn't been loaded yet
 */
if (!class_exists("Absence_group_model")) {
   /**
    * Requires the database class
    */
   require_once ("includes/db.class.php");
   
   /**
    * Provides objects and methods to interface with the absence group table
    * @package TeamCalPro
    */
   class Absence_group_model {
      var $db = '';
      var $table = '';
      var $log = '';
      var $logtype = '';
      var $id = NULL;
      var $absence = NULL;
      var $group = NULL;

      // ---------------------------------------------------------------------
      /**
       * Constructor
       */
      function Absence_group_model() {
         global $CONF;
         unset($CONF);
         require ("config.tcpro.php");
         $this->db = new myDB;
         $this->table = $CONF['db_table_absence_group'];
         $this->log = $CONF['db_table_log'];
      }

      // ---------------------------------------------------------------------
      /**
       * Creates a record assigning an absence type to a group
       * 
       * @param string $absid Absence ID
       * @param string $group Group short name
       */
      function assign($absid, $group) {
         $query = "INSERT INTO `".$this->table."` (`absid`,`group`) VALUES ('".$absid."','".$group."')";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Deletes a record matching absence and group
       * 
       * @param string $absid Absence ID
       * @param string $group Group short name
       */
      function unassign($absid='', $group='') {
         $query = "DELETE FROM `".$this->table."` WHERE `absid`='".$absid." AND `group`=".$group."'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Deletes all records for an absence type
       * 
       * @param string $absid Absence ID
       */
      function unassignAbs($absid='') {
         $query = "DELETE FROM `".$this->table."` WHERE `absid` = '".$absid."'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Deletes all records for a group
       * 
       * @param string $group Group short name
       */
      function unassignGroup($group = '') {
         $query = "DELETE FROM `".$this->table."` WHERE `group`='".$group."'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Checks whether an absence is assigned to a group
       * 
       * @param string $absid Absence ID
       * @param string $group Group short name
       */
      function isAssigned($absid, $group) {
         $rc = 0;
         $query = "SELECT * FROM `".$this->table."` WHERE `absid`='".$absid."' AND `group`='".$group."'";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $rc = 1;
         }
         return $rc;
      }

      // ---------------------------------------------------------------------
      /**
       * Updates a record with the values in the class variables
       * 
       */
      function update() {
         $query = "UPDATE `".$this->table."` SET `absid`='".$this->absid."', `group`='".$this->group."' WHERE `id`='".$this->id."'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Updates the absence type of an existing record
       * 
       * @param string $absold Absence ID to change
       * @param string $absnew New absence ID
       */
      function updateAbsence($absold, $absnew) {
         $query = "UPDATE `".$this->table."` SET `absence`='".$absnew."' WHERE `absid`='".$absold."'";
         $result = $this->db->db_query($query);
      }

      // ---------------------------------------------------------------------
      /**
       * Updates the group name of an existing record
       * 
       * @param string $groupold Old group name
       * @param string $groupnew New group name
       */
      function updateGroupname($groupold, $groupnew) {
         $query = "UPDATE `".$this->table."` SET `group`='".$groupnew."' WHERE `group`='".$groupold."'";
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