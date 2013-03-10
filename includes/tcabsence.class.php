<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * tcabsence.class.php
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
      var $cfgsym = '';
      var $cfgname = '';
      var $dspsym = '';
      var $dspname = '';
      var $dspcolor = '';
      var $dspbgcolor = '';
      var $allowance = '0';
      var $factor = '1';
      var $options = '0';
      var $iconfile = '';

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
       * Creates an absence type
       */ 
      function create() {
         $query = "INSERT INTO `" . $this->table . "` ";
         $query .= "(`cfgsym`,`cfgname`,`dspsym`,`dspname`,`dspcolor`,`dspbgcolor`,`allowance`,`factor`,`options`,`iconfile`) ";
         $query .= "VALUES ('";
         $query .= $this->cfgsym . "','";
         $query .= $this->cfgname . "','";
         $query .= $this->dspsym . "','";
         $query .= $this->dspname . "','";
         $query .= $this->dspcolor . "','";
         $query .= $this->dspbgcolor . "','";
         $query .= $this->allowance . "','";
         $query .= $this->factor . "','";
         $query .= $this->options . "','";
         $query .= $this->iconfile . "'";
         $query .= ")";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes an absence type by it's symbol
       */ 
      function deleteBySymbol($symbol = '') {
         $query = "DELETE FROM `" . $this->table . "` WHERE `cfgsym` = '" . $symbol . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Finds an absence type by it's symbol
       * 
       * @param string $symbol Specifies the absence type symbol to find
       */ 
      function findBySymbol($symbol = '') {
         $rc = 0;
         $query = "SELECT * FROM `".$this->table."` WHERE `cfgsym` = '".$symbol."';";
         $result = $this->db->db_query($query);

         // exactly one row found (a good thing!)
         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $this->cfgsym = $row['cfgsym'];
            $this->cfgname = $row['cfgname'];
            $this->dspsym = $row['dspsym'];
            $this->dspname = $row['dspname'];
            $this->dspcolor = $row['dspcolor'];
            $this->dspbgcolor = $row['dspbgcolor'];
            $this->allowance = $row['allowance'];
            $this->factor = $row['factor'];
            $this->options = $row['options'];
            $this->iconfile = $row['iconfile'];
            $rc = 1;
         }
         return $rc;
      }

      /**
       * Finds an absence type by it's name
       * 
       * @param string $name Specifies the absence type name to find
       */ 
      function findByName($name = '') {
         $rc = 0;
         $query = "SELECT * FROM `" . $this->table . "` WHERE `cfgname` = '" . $name . "'";
         $result = $this->db->db_query($query);

         // Exactly one row found (a good thing!)
         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $this->cfgsym = $row['cfgsym'];
            $this->cfgname = $row['cfgname'];
            $this->dspsym = $row['dspsym'];
            $this->dspname = $row['dspname'];
            $this->dspcolor = $row['dspcolor'];
            $this->dspbgcolor = $row['dspbgcolor'];
            $this->allowance = $row['allowance'];
            $this->factor = $row['factor'];
            $this->options = $row['options'];
            $this->iconfile = $row['iconfile'];
            $rc = 1;
         }
         return $rc;
      }

      /**
       * Reads all absence codes into an array
       * 
       * @return array $absarray Array with all absence codes
       */
      function getAbsences() {
         $absarray = array();
         $query = "SELECT cfgsym FROM `" . $this->table . "`";
         $result = $this->db->db_query($query);
         while ( $row=$this->db->db_fetch_array($result) ) {
            $absarray[] = stripslashes($row['cfgsym']);
         }
         return $absarray;
      }

      /**
       * Reads all records into an array
       * 
       * @return array $absarray Array with all records
       */
      function getAll($order='cfgsym', $sort='ASC') {
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
       * @param string $symbol Specifies the absence type symbol to find
       * @return string Absence type factor
       */ 
      function getFactor($symbol = '') {
         $rc = 1; // default factor is 1
         $query = "SELECT * FROM `" . $this->table . "` WHERE `cfgsym` = '" . $symbol . "'";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            // exactly one row found ( a good thing!)
            $row = $this->db->db_fetch_array($result);
            $rc = $row['factor'];
         }
         return $rc;
      }

      /**
       * Updates an absence type by it's symbol from the current array data
       * 
       * @param string $name Specifies the absence type symbol to update
       */ 
      function update($symbol) {
         $query = "UPDATE `" . $this->table . "` ";
         $query .= "SET `cfgsym`     = '" . $this->cfgsym . "', ";
         $query .= "`cfgname`    = '" . $this->cfgname . "', ";
         $query .= "`dspsym`     = '" . $this->dspsym . "', ";
         $query .= "`dspname`    = '" . $this->dspname . "', ";
         $query .= "`dspcolor`   = '" . $this->dspcolor . "', ";
         $query .= "`dspbgcolor` = '" . $this->dspbgcolor . "', ";
         $query .= "`allowance`  = '" . $this->allowance . "', ";
         $query .= "`factor`     = '" . $this->factor . "', ";
         $query .= "`options`    = '" . $this->options . "', ";
         $query .= "`iconfile`   = '" . $this->iconfile . "' ";
         $query .= "WHERE `cfgsym`   = '" . $symbol . "';";
         $result = $this->db->db_query($query);
      }

      /**
       * Clears flags in the option bitmask. See config.tcpro.php for predefined bitmasks.
       * 
       * @param integer $bitmask Bitmask with flags to clear
       */ 
      function clearOptions($bitmask) {
         $this->options = $this->options & (~intval($bitmask));
      }

      /**
       * Checks whether a bitmask ist set or not in the option field. See config.tcpro.php for predefined bitmasks.
       * 
       * @param integer $bitmask Bitmask with flags to check
       */ 
      function checkOptions($bitmask) {
         if ($this->options & intval($bitmask))
            return 1;
         else
            return 0;
      }

      /**
       * Sets a bitmask in the option field. See config.tcpro.php for predefined bitmasks.
       * 
       * @param integer $bitmask Bitmask with flags to set
       */ 
      function setOptions($bitmask) {
         $this->options = $this->options | intval($bitmask);
      }
   }
}
?>
