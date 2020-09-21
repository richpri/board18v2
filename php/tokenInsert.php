<?php
/*
 * This is the server side code for the AJAX tokenInsert call.
 * 
 * It inserts a new row in the auth_tokens table. 
 * 
 * Input is: selector and validator.
 * 
 * Output is the echo return status: 
 *   "success" or "fail"
 *
 * Copyright (c) 2017 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * 
 */

require_once('auth.php');
require_once('config.php');

$link = mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $errmsg1 = 'tokenInsert: failed to connect to server: ';
  error_log($errmsg1 . mysqli_connect_error());
  echo "fail";
  exit;
}
mysqli_set_charset($link, "utf-8");

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($link, $str) {
  $str1 = trim($str);
  return mysqli_real_escape_string($link, $str1);
}

//Sanitize the POST value
$selector = clean($link, $_REQUEST['selector']);
$validator = $_REQUEST['validator']; // don't need to sanitize this.

// Insert record into the auth_tokens table.
$playerid = $_SESSION['SESS_PLAYER_ID'];
$expires = mktime(0,0,0,date("m"),date("d")+14,date("Y")); // 14 days from now
$qry1 = "INSERT INTO auth_tokens (selector, hashedValidator, player_id, expires) ";
$qry1 .= "VALUES ('$selector', '$validator', '$playerid', '$expires')";
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  error_log("tokenInsert: INSERT INTO auth_tokens - Query failed");
  echo "fail";
  exit;
} else {
  echo "success";
}