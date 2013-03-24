<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * permission_model.php
 *
 * Contains the class dealing with permission schemes
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
if (!class_exists("Permission_model")) {
   /**
    * Requires the database class
    */
   require_once ("models/db_model.php");

   /**
    * Provides objects and methods to interface with the config table
    * @package TeamCalPro
    */
   class Permission_model {
      var $db = '';
      var $table = '';
      var $log = '';

      // ---------------------------------------------------------------------
      /**
       * Constructor
       */
      function Permission_model() {
         global $CONF;
         unset($CONF);
         require ("config.tcpro.php");
         $this->db = new Db_model;
         $this->table = $CONF['db_table_permissions'];
         $this->log = $CONF['db_table_log'];
      }

      // ---------------------------------------------------------------------
      /**
       * Read all unique scheme names
       *
       * @return array $schemes Array containing the scheme names
       */
      function getSchemes() {
         $schemes = array();
         $query = "SELECT DISTINCT scheme FROM ".$this->table.";";
         $result = $this->db->db_query($query);
         while ( $row = $this->db->db_fetch_array($result,MYSQL_ASSOC) ) $schemes[]=$row['scheme'];
         return $schemes;
      }

      // ---------------------------------------------------------------------
      /**
       * Read all unique scheme names
       *
       * @return array $schemes Array containing the scheme names
       */
      function schemeExists($scheme) {
         $query = "SELECT * FROM ".$this->table." WHERE scheme='".$scheme."';";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) >= 1) {
            return TRUE;
         }
         else {
            return FALSE;
         }
      }

      // ---------------------------------------------------------------------
      /**
       * Read the value of a permission
       *
       * @param string $scheme Name of the permission scheme
       * @param string $permission Name of the permission
       * @param string $role Role of the permission
       * @return boolean True or False
       */
      function isAllowed($scheme, $permission, $role) {
         $query = "SELECT ".$role." FROM ".$this->table." WHERE scheme='".$scheme."' AND permission = '".$permission."';";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result, MYSQL_NUM);
            return $row[0];
         } else {
            return FALSE;
         }
      }

      // ---------------------------------------------------------------------
      /**
       * Update/create a permission
       *
       * @param string $scheme Name of the permission scheme
       * @param string $permission Name of the permission
       * @param string $role Role of the permission
       * @param boolean @allowed True or False
       * @return integer Query result, or 0 if query not successful
       */
      function setPermission($scheme, $permission, $role, $allowed) {
         $query = "SELECT * FROM `".$this->table."` WHERE scheme='".$scheme."' AND permission = '".$permission."';";
         $result = $this->db->db_query($query);
         if ($this->db->db_numrows($result) == 1) {
            $query = "UPDATE ".$this->table." SET ".$role."=".$allowed." WHERE scheme='".$scheme."' AND permission = '".$permission."';";
            $result = $this->db->db_query($query);
            return $result;
         }
         elseif ($this->db->db_numrows($result) == 0) {
            $query = "INSERT INTO ".$this->table." (scheme, permission, admin, director, manager, user, public) VALUES ('".$scheme."', '".$permission."', 0, 0, 0, 0, 0)";
            $result = $this->db->db_query($query);
            $query = "UPDATE ".$this->table." SET ".$role."=".$allowed." WHERE scheme='".$scheme."' AND permission = '".$permission."';";
            $result = $this->db->db_query($query);
            return $result;
         }
         else {
            return 0;
         }
      }

      // ---------------------------------------------------------------------
      /**
       * Delete a permission scheme
       *
       * @param string $scheme Name of the permission scheme
       * @return integer Query result, or 0 if query not successful
       */
      function deleteScheme($scheme) {
         $query = "DELETE FROM ".$this->table." WHERE scheme = '".$scheme."';";
         $result = $this->db->db_query($query);
         return $result;
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