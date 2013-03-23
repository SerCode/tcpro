<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * tcabs.class.php
 * 
 * Contains the class to interface with the absence type table
 *
 * @package TeamCalPro
 * @version 3.5.002 
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */
if (!class_exists("tcAbsence")) {
   /**
    * Requires the database class
    */
   require_once ("includes/db.class.php");
   
   /**
    * Provides objects and methods to interface with the absence type table
    * @package TeamCalPro
    */
   class tcAbsence {
      var $db = NULL;
      var $table = '';
      var $log = '';
      var $logtype = '';
      var $name = '';
      var $symbol = '';
      var $icon = '';
      var $color = '';
      var $bgcolor = '';
      var $factor = 1;
      var $allowance = '0';
      var $show_in_remainder = 1;
      var $show_totals = 1;
      var $approval_required = 0;
      var $counts_as_present = 0;
      var $manager_only = 0;
      var $hide_in_profile = 0;
      var $confidential = 0;
      
      /**
       * Constructor
       */ 
      function tcAbsence() {
         global $CONF;
         unset($CONF);
         require ("config.tcpro.php");
         $this->db = new myDB;
         $this->table = $CONF['db_table_absence'];
         $this->log = $CONF['db_table_log'];
      }

      /**
       * Creates an absence type record
       */ 
      function create() {
         $query = "INSERT INTO `".$this->table."` ";
         $query .= "(
                     `name`,
                     `symbol`,
                     `icon`,
                     `color`,
                     `bgcolor`,
                     `factor`,
                     `allowance`,
                     `show_in_remainder`,
                     `show_totals`,
                     `approval_required`,
                     `counts_as_present`,
                     `manager_only`,
                     `hide_in_profile`,
                     `confidential`
                    ) ";
         
         $query .= "VALUES (
                   '".$this->name."',
                   '".$this->symbol."',
                   '".$this->icon."',
                   '".$this->color."',
                   '".$this->bgcolor."',
                   '".$this->factor."',
                   '".$this->allowance."',
                   '".$this->show_in_remainder."',
                   '".$this->show_totals."',
                   '".$this->approval_required."',
                   '".$this->counts_as_present."',
                   '".$this->manager_only."',
                   '".$this->hide_in_profile."',
                   '".$this->confidential."'
                   )";
         
         $result = $this->db->db_query($query);
         return $this->db->db_query("SELECT LAST_INSERT_ID()");
      }

      /**
       * Deletes an absence type record
       * 
       * @param string $absid Record ID
       */ 
      function delete($absid = '') {
         $result=0;
         if (isset($absid)) {
            $query = "DELETE FROM `".$this->table."` WHERE id='".$absid."';";
            $result = $this->db->db_query($query);
         }
         return $result;
      }

      /**
       * Deletes all absence type records
       */ 
      function deleteAll() {
         $query = "TRUNCATE `".$this->table."`;";
         $result = $this->db->db_query($query);
         return $result;
      }

      /**
       * Gets an absence type record
       * 
       * @param string $absid Record ID
       */ 
      function get($absid = '') {
         $rc = 0;
         $query = "SELECT * FROM `".$this->table."` WHERE id='".$absid."';";
         $result = $this->db->db_query($query);

         // exactly one row found (a good thing!)
         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->symbol = $row['symbol'];
            $this->icon = $row['icon'];
            $this->color = $row['color'];
            $this->bgcolor = $row['bgcolor'];
            $this->factor = $row['factor'];
            $this->allowance = $row['allowance'];
            $this->show_in_remainder = $row['show_in_remainder'];
            $this->show_totals = $row['show_totals'];
            $this->approval_required = $row['approval_required'];
            $this->counts_as_present = $row['counts_as_present'];
            $this->manager_only = $row['manager_only'];
            $this->hide_in_profile = $row['hide_in_profile'];
            $this->confidential = $row['confidential'];
            $rc = 1;
         }
         return $rc;
      }

      /**
       * Reads all records into an array
       * 
       * @return array $absarray Array with all records
       */
      function getAll($order='name', $sort='ASC') {
         $absarray = array();
         $query = "SELECT * FROM `".$this->table."` ORDER BY `".$order."` ".$sort.";";
         $result = $this->db->db_query($query);
         while ( $row=$this->db->db_fetch_array($result) ) {
            $absarray[] = $row;
         }
         return $absarray;
      }

      /**
       * Gets the factor value of an absence type
       * 
       * @param string $absid Record ID
       * @return string Absence type factor
       */ 
      function getFactor($absid = '') {
         $rc = 1; // default factor is 1
         $query = "SELECT factor FROM `".$this->table."` WHERE id='".$absid."';";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $rc = $row['factor'];
         }
         return $rc;
      }

      /**
       * Gets the approval required value of an absence type
       *
       * @param string $absid Record ID
       * @return boolean Approval required
       */
      function getApprovalRequired($absid = '') {
         $rc=0;
         $query = "SELECT approval_required FROM `".$this->table."` WHERE id='".$absid."';";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $rc = $row['approval_required'];
         }
         return $rc;
      }
      
      /**
       * Gets the name of an absence type
       *
       * @param string $absid Record ID
       * @return string Absence type name
       */
      function getName($absid = '') {
         $rc='unknown';
         $query = "SELECT name FROM `".$this->table."` WHERE id='".$absid."';";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $rc = $row['name'];
         }
         return $rc;
      }
      
         /**
       * Gets the symbol of an absence type
       *
       * @param string $absid Record ID
       * @return string Absence type symbol
       */
      function getSymbol($absid = '') {
         $rc='.';
         $query = "SELECT symbol FROM `".$this->table."` WHERE id='".$absid."';";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $rc = $row['symbol'];
         }
         return $rc;
      }
      
      /**
       * Gets the last auto-increment ID
       * 
       * @return string Next auto-incremente ID
       */ 
      function getLastId() {
         $result = mysql_query('SHOW TABLE STATUS LIKE "'.$this->table.'";');
         $row = mysql_fetch_assoc($result);
         return intval($row['Auto_increment'])-1;
      }
            
      /**
       * Gets the next auto-increment ID
       * 
       * @return string Next auto-incremente ID
       */ 
      function getNextId() {
         $result = mysql_query('SHOW TABLE STATUS LIKE "'.$this->table.'"');
         $row = mysql_fetch_assoc($result);
         return $row['auto_increment'];
      }
            
      /**
       * Updates an absence type by it's symbol from the current array data
       * 
       * @param string $absid Record ID
       */ 
      function update($absid='') {
         $result=0;
         if (isset($absid)) {
            $query = "UPDATE `".$this->table."` SET 
                     `name`              = '".$this->name."', 
                     `symbol`            = '".$this->symbol."', 
                     `icon`              = '".$this->icon."', 
                     `color`             = '".$this->color."', 
                     `bgcolor`           = '".$this->bgcolor."', 
                     `factor`            = '".$this->factor."', 
                     `allowance`         = '".$this->allowance."', 
                     `show_in_remainder` = '".$this->show_in_remainder."', 
                     `show_totals`       = '".$this->show_totals."', 
                     `approval_required` = '".$this->approval_required."', 
                     `counts_as_present` = '".$this->counts_as_present."', 
                     `manager_only`      = '".$this->manager_only."', 
                     `hide_in_profile`   = '".$this->hide_in_profile."', 
                     `confidential`      = '".$this->confidential."' 
                     WHERE id='".$absid."';";
            $result = $this->db->db_query($query);
         }
         return $result;
      }

   }
}
?>
