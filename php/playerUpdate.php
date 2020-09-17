<?php
/*
 * playerUpdate.php is the server side code for the 
 * AJAX playerUpdate call. 
 * 
 * It updates a row in the players table.
 * Unlike updateUser, playerUpdate keys on player_id.
 * 
 * Input consists the following parameters:
 * 
 *   player  
 *   login
 *   email
 *   fname
 *   lname
 *   level
 * 
 * Output is the echo return status: 
 *   "success", "fail", "login", "bademail" or "email xxxx". 
 * 
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('auth.php');
if ($playerlevel != 'admin') {
  error_log("playerUpdate: Not an admin level player");
  echo "fail";
  exit;
}
require_once('config.php');

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($conn, $str1) {
  $str = trim($str1);
  return mysqli_real_escape_string($conn, $str);
}

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
  error_log('playerUpdate: mysql connect error: ' . mysqli_connect_error());
  echo 'fail';
	exit; 
}

//Sanitize the POST values
$player = clean($link, $_REQUEST['player']);
$login = clean($link, $_REQUEST['login']);
$fname = clean($link, $_REQUEST['fname']);
$lname = clean($link, $_REQUEST['lname']);
$email = clean($link, $_REQUEST['email']);
$level = clean($link, $_REQUEST['level']);

//Check for existing player ID
$qry1 = "SELECT * FROM players WHERE player_id ='$player'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) === 0) { // no such player!
    error_log("playerUpdate: Check for existing player: No player found!");
    echo 'fail';
    exit;
  } else {
    $playerrow = mysqli_fetch_assoc($result1);
  }
} else {
  error_log("playerUpdate: Check for existing player: Query failed");
  echo 'fail';
  exit;
}

if ($playerrow['email'] !== $email) { // If email changed
  //Check for ill formed email address
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // The email address is ill formed
    echo 'bademail';
    exit;
  }
  //Check for duplicate email address
  $qry2 = "SELECT * FROM players WHERE email='$email'";
  $result2 = mysqli_query($link, $qry2);
  if ($result2) {
    if (mysqli_num_rows($result2) > 0) { 
      while ($playerrow2 = mysqli_fetch_assoc($result2)) {
        if ($playerrow2['player_id'] !== $player) { // duplicate email
          $duperr = 'email' . $playerrow2['login'];
          echo $duperr;
          exit;
        }
      }
    }
  } else {
    error_log("playerUpdate: Check duplicate email: Query failed");
    echo 'fail';
    exit;
  }
}

if ($playerrow['login'] !== $login) { // If login changed
  //Check for duplicate player login
  $qry3 = "SELECT login FROM players WHERE login='$login'";
  $result3 = mysqli_query($link, $qry3);
  if ($result3) {
    if (mysqli_num_rows($result3) > 0) { // duplicate login
      $duperr = 'login';
      echo $duperr;
      exit;
    }
  } else {
    error_log("playerUpdate: Check duplicate login: Query failed");
    echo 'fail';
    exit;
  }
} 

//Create UPDATE query
$qry = "UPDATE players SET firstname='$fname', lastname='$lname',
          email='$email', login='$login', level='$level'
          WHERE player_id=$player";
$result = mysqli_query($link, $qry);
if ($result) {   // Was the query successful
  echo 'success';
} else {
  error_log("playerUpdate: Update player: Query failed");
  echo 'fail';
}
