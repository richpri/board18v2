<?php
/*
 * config.php is included at the start of all pages and php routines
 * that access the board18 database.  Modify DB_HOST and if necessary
 * DB_DATABASE to contain the correct host and database name. You can
 * also change the database user ID and password here if you wish.
 * 
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
  define('DB_HOST', 'b18-db');
  define('DB_DATABASE', 'board18');
  define('DB_USER', 'root');
  define('DB_PASSWORD', 'test');
?>
