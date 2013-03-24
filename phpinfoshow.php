<?php
/**
 * phpinfoshow.php
 *
 * Launches phpinfo(). Called from within an iFrame in phpinfo.php.
 *
 * @package TeamCalPro
 * @version 3.5.002
 * @author George Lewe
 * @copyright Copyright (c) 2004-2013 by George Lewe
 * @link http://www.lewe.com
 * @license http://www.lewe.com/tcpro/doc/license.txt Extended GNU Public License
 */

/**
 * Set parent flag to control access to child scripts
 */
define( '_VALID_TCPRO', 1 );

/**
 * Includes
 */
require_once ("config.tcpro.php");
require_once ("helpers/global_helper.php");

/**
 * Check authorization
 */
/**
 * Check if allowed
 */
if (!isAllowed("viewEnvironment")) showError("notallowed");

phpinfo();
?>
