<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * tctemplate.class.php
 * 
 * Contains the class dealing with the template table
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
if (!class_exists("tcTemplate")) {
   /**
    * Requires the database class
    */
   require_once ("includes/db.class.php");

   /**
    * Provides objects and methods to manage the template table
    * @package TeamCalPro
    */
   class tcTemplate {
      var $db = '';
      var $table = '';
      var $log = '';
      var $logtype = '';
      var $username = ''; // link to username of user table
      var $year = '';
      var $month = '';
      var $template = '';

      /**
       * Constructor
       */
      function tcTemplate() {
         global $CONF;
         unset($CONF);
         require ("config.tcpro.php");
         $this->db = new myDB;
         $this->table = $CONF['db_table_templates'];
         $this->log = $CONF['db_table_log'];
      }

      /**
       * Creates a template from local variables
       */
      function create() {
         $query = "INSERT INTO `" . $this->table . "` ";
         $query .= "(`username`,`year`,`month`,`template`) ";
         $query .= "VALUES ('";
         $query .= $this->username . "','";
         $query .= $this->year . "','";
         $query .= $this->month . "','";
         $query .= $this->template . "'";
         $query .= ")";
         $result = $this->db->db_query($query);
      }

      /**
       * Creates a template from parameters
       * 
       * @param string $uname Username this template is for
       * @param string $year Year of the template (YYYY)
       * @param string $month Month of the template (MM)
       */
      function deleteTemplate($uname = '', $year = '', $month = '') {
         $query = "DELETE FROM `" . $this->table . "` ";
         $query .= "WHERE `username` = '" . $uname . "'";
         $query .= " AND  `year` = '" . $year . "'";
         $query .= " AND  `month` = '" . $month . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes a template by ID
       * 
       * @param integer $id ID of record to delete
       */
      function deleteById($id = '') {
         $query = "DELETE FROM `" . $this->table . "` WHERE `id` = '" . $id . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Deletes all templates for a username
       * 
       * @param string $uname Username to delete all records of
       */
      function deleteByUser($uname = '') {
         $query = "DELETE FROM `" . $this->table . "` WHERE `username` = '" . $uname . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Finds a template for a given username, year and month
       * 
       * @param string $uname Username to find
       * @param string $year Year to find (YYYY)
       * @param string $month Month to find (MM)
       * @return integer Result of MySQL query
       */
      function findTemplate($uname = '', $year = '', $month = '') {
         $rc = 0;
         $query = "SELECT * FROM `" . $this->table . "` ";
         $query .= "WHERE `username` = '" . $uname . "'";
         $query .= " AND  `year` = '" . $year . "'";
         $query .= " AND  `month` = '" . $month . "'";
         $result = $this->db->db_query($query);

         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $this->username = $row['username'];
            $this->year = $row['year'];
            $this->month = $row['month'];
            $this->template = $row['template'];
            $rc = 1;
         }
         return $rc;
      }

      /**
       * Finds a template for a given ID
       * 
       * @param string $id Record ID to find
       * @return integer Result of MySQL query
       */
      function findTemplateById($id = '') {
         $rc = 0;
         $query = "SELECT * FROM `" . $this->table . "` ";
         $query .= "WHERE `id` = '" . $id . "'";
         $result = $this->db->db_query($query);

         if ($this->db->db_numrows($result) == 1) {
            $row = $this->db->db_fetch_array($result);
            $this->username = $row['username'];
            $this->year = $row['year'];
            $this->month = $row['month'];
            $this->template = $row['template'];
            $rc = 1;
         }
         return $rc;
      }

      /**
       * Updates a template for a given username, year and month
       * 
       * @param string $uname Username for update
       * @param string $year Year for update (YYYY)
       * @param string $month Month for update (MM)
       */
      function update($uname, $year, $month) {
         $query = "UPDATE `" . $this->table . "` ";
         $query .= "SET `username`   = '" . $this->username . "', ";
         $query .= "`year`       = '" . $this->year . "', ";
         $query .= "`month`      = '" . $this->month . "', ";
         $query .= "`template`   = '" . $this->template . "' ";
         $query .= "WHERE `username` = '" . $uname . "'";
         $query .= " AND  `year`     = '" . $year . "'";
         $query .= " AND  `month`    = '" . $month . "'";
         $result = $this->db->db_query($query);
      }

      /**
       * Replaces a symbol in all templates. This needs to be done if an admin
       * changes the symbol of an absence type
       * 
       * @param string $symopld Symbol to be replaced
       * @param string $symnew Symbol to replace with
       */
      function replaceSymbol($symold, $symnew) {
         $query = "SELECT * FROM `" . $this->table . "`";
         $result = $this->db->db_query($query);
         while ($row = $this->db->db_fetch_array($result)) {
            $this->findTemplateById($row['id']);
            // Replace symbol in this record
            $this->template = str_replace($symold, $symnew, $this->template);
            // Now update the record
            $qry = "UPDATE `".$this->table."` SET `template` = '".$this->template."' WHERE `id` = '".$row['id']."';";
            $res = $this->db->db_query($qry);
         }
      }

   } // End Class tcTemplate

} // if ( !class_exists( "tcTemplate" ) ) {
?>
