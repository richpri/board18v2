<?php
/*
 * This is the server side code for the AJAX tokenRemove call.
 * 
 * It deletes from the auth_tokens table any rows that exist 
 * for the logged in player. 
 * 
 * This function requires no input parameters.
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
  $errmsg1 = 'tokenRemove: failed to connect to server: ';
  error_log($errmsg1 . mysqli_connect_error());
  echo "fail";
  exit;
}
mysqli_set_charset($link, "utf-8");

$id = $_SESSION['SESS_PLAYER_ID'];
$qry1 = "DELETE FROM auth_tokens WHERE player_id=$id";
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  error_log("tokenRemove: DELETE FROM auth_tokens - Query failed");
  echo "fail";
  exit;
}
echo "success";

