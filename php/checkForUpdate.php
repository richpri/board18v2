<?php
/*
 * checkForUpdate.php is the server side code for the 
 * AJAX checkForUpdate call.
 * 
 * It checks the database to see if another player
 * has made an update.
 * 
 * Input is the gameID.
 * 
 * Output will be "noupdate", "updatefound" or "failure".
 * 
 * The SESS_UPDATE_COUNTER session variable which is used
 * by updateGame.php to support optimistic database 
 * locking is also used by checkForUpdate.php to detect
 * updates made by another player.
 * 
 * Copyright (c) 2014 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
require_once('auth.php');
require_once('config.php');
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'checkForUpdate: Failed to connect to server';
  error_log($logMessage);
  echo "failure";
  exit;
}
mysqli_set_charset($link, "utf-8");
$update_counter = $_SESSION['SESS_UPDATE_COUNTER'];

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($link,$str) {
  $str = @trim($str);
  return mysqli_real_escape_string($link,$str);
}

//Sanitize the POST values (but not json string)
$gameid = clean($link,$_REQUEST['gameid']);

//Check for valid gameid ID and get counter and updater.
$qry2 = "SELECT update_counter, last_updater
  FROM game WHERE game_id='$gameid'";
$result2 = mysqli_query($link, $qry2);
if (!$result2 || (mysqli_num_rows($result2) !== 1)) { 
  $logMessage = 'checkForUpdate: SELECT failed ';
  error_log($logMessage);
  echo "failure";
  exit;
}
$arr2 = mysqli_fetch_array($result2);
$counter = $arr2[0]; // update_counter
$updater = $arr2[1]; // last_updater

//Check for update made by another player.
if ($counter != $update_counter && $updater != $loggedinplayer) { 
  echo "updatefound";
  exit;
}

echo "noupdate";
exit;
?>