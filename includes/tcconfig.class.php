<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * tcconfig.class.php
 *
 * Contains the class dealing with the config table
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
if (!class_exists("tcConfig")) {
   /**
    * Requires the database class
    */
   require_once ("includes/db.class.php");

   /**
    * Provides objects and methods to interface with the config table
    * @package TeamCalPro
    */
   class tcConfig {
      var $db = '';
      var $table = '';
      var $log = '';
      var $logtype = '';
      var $id = NULL;
      var $name = '';
      var $value = '';

      /**
       * Constructor
       */
      function tcConfig() {
         global $CONF;
         unset($CONF);
         require ("config.tcpro.php");
         $this->db = new myDB;
         $this->table = $CONF['db_table_config'];
         $this->log = $CONF['db_table_log'];
      }

      /**
       * Read the value of an option
       *
       * @param string $name Name of the option
       * @return string Value of the option or empty if not found
       */
      function readConfig($name) {
         $query = "SELECT value FROM `".$this->table."` WHERE `name` = '".$name."'";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result, MYSQL_ASSOC);
            return trim($row['value']);
         }
         else {
            return "";
         }
      }

      /**
       * Update/create the value of an option
       *
       * @param string $name Name of the option
       * @param string @value Value to save
       * @return integer Query result, or 0 if query not successful
       */
      function saveConfig($name, $value) {
         $query = "SELECT value FROM `" . $this->table . "` WHERE `name` = '" . $name . "'";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $query = "UPDATE `" . $this->table . "` SET `value` = '" . $value . "' WHERE `name` = '" . $name . "'";
            $result = $this->db->db_query($query);
            return $result;
         }
         elseif ($this->db->db_numrows($result) == 0) {
            $query = "INSERT INTO `" . $this->table . "` (`name`,`value`) VALUES ('" . $name . "','" . $value . "')";
            $result = $this->db->db_query($query);
            return $result;
         }
         else {
            return 0;
         }
      }

      function updateRegion($region_old, $region_new='default') {
         $query = "UPDATE `" . $this->table . "` ";
         $query .= "SET `value` = '" . $region_new . "' ";
         $query .= "WHERE `name` = 'defregion' ";
         $query .= "AND `value` = '" . $region_old . "'";
         $result = $this->db->db_query($query);
      }

   } // End Class tcConfig

} // End if (!class_exists("tcConfig"))
?>
