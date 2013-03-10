<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * tcabsencegroup.class.php
 * 
 * Contains the class to interface with the absence group record
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
if (!class_exists("tcAbsenceGroup")) {
   /**
    * Requires the database class
    */
   require_once ("includes/db.class.php");
   
   /**
    * Provides objects and methods to interface with the absence group records
    * @package TeamCalPro
    */
   class tcAbsenceGroup {
      var $db = '';
      var $table = '';
      var $log = '';
      var $logtype = '';
      var $id = NULL;
      var $absence = NULL;
      var $group = NULL;

      /**
       * Constructor
       */
      function tcAbsenceGroup() {
         global $CONF;
         unset($CONF);
         require ("config.tcpro.php");
         $this->db = new myDB;
         $this->table = $CONF['db_table_absence_group'];
         $this->log = $CONF['db_table_log'];
      }

      /**
       * Creates a record assigning an absence type to a group
       * 
       * @param string $absence Absence symbol
       * @param string $group Group short name
       */
      function assign($absence, $group) {
         $query = "INSERT INTO `" . $this->table . "` ";
         $query .= "(`absence`,`group`) ";
         $query .= "VALUES ('";
         $query .= $absence . "','";
         $query .= $group . "'";
         $query .= ")";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes a record matching absence and group
       * 
       * @param string $absence Absence symbol
       * @param string $group Group short name
       */
      function unassign($absence='', $group='') {
         $query = "DELETE FROM `" . $this->table . "` WHERE `absence` = '" . $absence . " AND `group` = " .$group. "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes all records for an absence type
       * 
       * @param string $absence Absence symbol
       */
      function unassignAbsence($absence = '') {
         $query = "DELETE FROM `" . $this->table . "` WHERE `absence` = '" . $absence . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes all records for a group
       * 
       * @param string $group Group short name
       */
      function unassignGroup($group = '') {
         $query = "DELETE FROM `" . $this->table . "` WHERE `group` = '" . $group . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Checks whether an absence is assigned to a group
       * 
       * @param string $absence Absence symbol
       * @param string $group Group short name
       */
      function isAssigned($absence, $group) {
         $rc = 0;
         // see if absence is member of group
         $query = "SELECT * FROM `" . $this->table . "` WHERE `absence` = '" . $absence . "' AND `group` = '" . $group . "'";
         $result = $this->db->db_query($query);
         // exactly one row found (a good thing!)
         if ($this->db->db_numrows($result) == 1) {
            $rc = 1;
         }
         return $rc;
      }

      /**
       * Updates a record with the values in the class variables
       * 
       */
      function update() {
         $query = "UPDATE `" . $this->table . "` ";
         $query .= "SET `absence`   = '" . $this->absence . "', ";
         $query .= "`group`  = '" . $this->group . "' ";
         $query .= "WHERE `id`       = '" . $this->id . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Updates the absence type of an existing record
       * 
       * @param string $absold Absence type to change
       * @param string $absnew New absence type
       */
      function updateAbsence($absold, $absnew) {
         $query = "UPDATE `" . $this->table . "` ";
         $query .= "SET `absence`   = '" . $absnew . "' ";
         $query .= "WHERE `absence` = '" . $absold . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Updates the group name of an existing record
       * 
       * @param string $groupold Group name type to change
       * @param string $absnew New group name
       */
      function updateGroupname($groupold, $groupnew) {
         $query = "UPDATE `" . $this->table . "` ";
         $query .= "SET `group`   = '" . $groupnew . "' ";
         $query .= "WHERE `group` = '" . $groupold . "'";
         $result = $this->db->db_query($query);
      }
   } // End Class tcAbsenceGroup
} // if (!class_exists("tcAbsenceGroup"))
?>
