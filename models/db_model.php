<?php
if (!defined('_VALID_TCPRO')) exit ('No direct access allowed!');
/**
 * db_model.php
 * 
 * Interface to the TeamCal Pro database
 *
 * @package TeamCalPro
 * @version 3.6.000 
 * @author George Lewe <george@lewe.com>
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://tcpro.lewe.com/doc/license.txt Based on GNU Public License v3
 */

/**
 * Make sure the class hasn't been loaded yet
 */
if (!class_exists("Db_model")) {
   /**
    * Interface to the TeamCal Pro database
    * @package TeamCalPro
    */
   class Db_model {
      var $db_type = '';
      var $db_server = '';
      var $db_name = '';
      var $db_user = '';
      var $db_pass = '';
      var $db_persistent = '';
      var $db_errortxt = '';
      static $db_handle = '';
      static $query_cache = array();
      
      // ---------------------------------------------------------------------
      /**
       * Constructor reading server and database information from the
       * configuration file. 
       */
      function Db_model() {
         global $CONF;
         $this->db_type = $CONF['db_type'];
         $this->db_server = $CONF['db_server'];
         $this->db_name = $CONF['db_name'];
         $this->db_user = $CONF['db_user'];
         $this->db_pass = $CONF['db_pass'];
         $this->db_persistent = $CONF['db_persistent'];
         $this->db_connect();
      }

      // ---------------------------------------------------------------------
      /**
       * Connects to the database server and to the database
       */
      function db_connect() {
         if (!Db_model::$db_handle) {
            switch ($this->db_type) {
               case 1 : // MySQL
               if ($this->db_persistent) {
                  Db_model::$db_handle = @ mysql_pconnect($this->db_server, $this->db_user, $this->db_pass);
               } else {
                  Db_model::$db_handle = @ mysql_connect($this->db_server, $this->db_user, $this->db_pass);
               }
               if (!Db_model::$db_handle) {
                  $errtxt = "Connecting to mySQL server " . $this->db_server . " failed.";
                  $this->db_error($errtxt, "db_connect()", true);
                  return;
               }
               if (!@ mysql_select_db($this->db_name, Db_model::$db_handle)) {
                  $errtxt = "
                  Error: Connection to MySQL database " . $this->db_server . "/" . $this->db_name . " failed.<BR>
                         Code:    " . @ mysql_errno(Db_model::$db_handle) . ",<BR>
                         Message: " . @ mysql_error(Db_model::$db_handle);
                  $this->db_error($errtxt, "db_connect()", true);
               }
               break;
            }
         }
      }

      // ---------------------------------------------------------------------
      /**
       * Executes a query on the database
       * 
       * @param string $query String containing the query to be executed. Will be initialized to empty if not passed
       * @return integer Result of the query
       */
      function db_query($query = '') {
         switch ($this->db_type) {
            case 1 : // MySQL
            $upp_query = strtoupper($query);
            if (strpos($upp_query, 'UPDATE') || strpos($upp_query, 'INSERT') || strpos($upp_query, 'DELETE') || strpos($upp_query, 'TRUNCATE')) {
               // We are changing the database so throw away the cache
               Db_model::$query_cache = array();               
            }
            if (!array_key_exists($query, Db_model::$query_cache)) {
               $result = mysql_query($query, Db_model::$db_handle);
               if (!$result) {
                  echo "Error: A problem was encountered while executing this query:<br><br>\n\n$query<br><br>\n\n";
                  die("Error: Fatal database error!<br>\n");
               }
               Db_model::$query_cache[$query] = $result;
            } 
            else {
               $result = Db_model::$query_cache[$query]; 
               if (!$result) {
                  echo "Error: A problem was encountered while executing this query:<br><br>\n\n$query<br><br>\n\n";
                  die("Error: Fatal database error!<br>\n");
               }
               else { 
                  if (is_resource($result)) {
                     if (mysql_num_rows($result) > 0) {
                        mysql_data_seek($result, 0);
                     }
                  }
               }
            }
            break;
         }
         return $result;
      }

      // ---------------------------------------------------------------------
      /**
       * Returns the number of records based on the result of a query
       * 
       * @param integer $result Result of the query 
       * @return integer Number of records matching the query
       */
      function db_numrows($result) {
         switch ($this->db_type) {
            case 1 : // MySQL
            return mysql_num_rows($result);
         }
      }

      // ---------------------------------------------------------------------
      /**
       * Returns an array containing the matching records of a query
       * 
       * @param integer $result Result of the query 
       * @param integer $type  MYSQL_ASSOC, MYSQL_NUM or MYSQL_BOTH, defining the type of index for the returned array 
       * @return array Array of records matching the query
       */
      function db_fetch_array(& $result, $type = MYSQL_BOTH) {
         switch ($this->db_type) {
            case 1 : // MySQL
            return mysql_fetch_array($result, $type);
         }
      }

      // ---------------------------------------------------------------------
      /**
       * Sending an error message to the browser
       * 
       * @param string $errtxt Error text to display 
       * @param string $func Name of the method in which this error ocurred
       * @param boolean $die Switch whether to die with this error or to procede after displayed
       */
      function db_error($errtxt, $func, $die) {
         $this->db_errortxt = "
                     <table class=\"err\">\n
                        <tr>\n
                           <td class=\"err-header\">TeamCal Pro Controlled Error Exit</td>\n
                        </tr>\n
                        <tr>\n
                           <td class=\"err-body\">\n
                              <span class=\"modcap\">Module:</span> <span class=\"module\">db_model.php</span><br>\n
                              <span class=\"classcap\">Class:</span> <span class=\"class\">Db_model</span><br>\n
                              <span class=\"funcap\">Function:</span> <span class=\"function\"> " . $func . "</span><br><br>\n
                              <span class=\"errortext\"> " . $errtxt . "</span><br><br>\n";
         if ($die) $this->db_errortxt .= "<span class=\"erraction\">Execution halted!</span><br>\n";
         $this->db_errortxt .= "
                           </td>\n
                        </tr>\n
                     </table>\n
                     <br>";
         if ($die)
            die($this->db_errortxt);
         else
            echo $this->db_errortxt;
      }
   }
}
?>
