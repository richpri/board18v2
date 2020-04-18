<?php
/*
 * config.php is included at the start of all pages and php routines
 * that access the board18 database.  Modify DB_HOST and if necessary
 * DB_DATABASE to contain the correct host and database name. You can
 * also change the database user ID and password here. Make them match
 * the values that have limited access to the board18 database.
 * 
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
	define('DB_HOST', 'localhost');
  define('DB_DATABASE', 'board18');
  define('DB_USER', 'board18');
  define('DB_PASSWORD', 'board18');
  define('ENABLE_REGISTRATION', true);
?>
