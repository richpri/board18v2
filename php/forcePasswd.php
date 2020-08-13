<?php
/*
 * forcePasswd.php is the server side code for the 
 * AJAX forcePasswd call.
 * 
 * It will process a forced password change.
 * 
 * Input consists the following parameters:
 *   player
 *   passwd
 * 
 * Output will be "success", "fail" or an edit failure code.
 * 
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('auth.php');
require_once('config.php');

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($conn, $str) {
  $str = trim($str);
  return mysqli_real_escape_string($conn, $str);
}

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
  error_log('forcePasswd.php: Failed to connect to server: ' . mysqli_connect_error());
  echo 'fail';
  exit; 
}

//Sanitize the POST values
$player = clean($link, $_REQUEST['player']);
$passwd = clean($link, $_REQUEST['passwd']);

//Check for existing login ID
$qry1 = "SELECT * FROM players WHERE player_id='$loggedinplayer'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) === 0) { // no such user!
    error_log("Check for existing user: User not found!");
    echo 'fail';
    exit;
  } else {
    $playerrow = mysqli_fetch_assoc($result1);
    $user = $playerrow['login'];
    if ($player !== $user) {
    echo 'player';
    exit;
    }
  }
} else {
  error_log("forcePasswd.php: Check for existing user: Query failed");
  echo 'fail';
	exit; 
}

//Create UPDATE query
$qry = "UPDATE players SET passwd='$passwd', changeit=0
        WHERE player_id='$loggedinplayer'";
$result = mysqli_query($link, $qry);
if ($result) {   // Was the query successful
  echo 'success';
} else {
  error_log("forcePasswd.php: Update player: Query failed");
  echo 'fail';
	exit; 
}
?>
