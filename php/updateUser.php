<?php
/*
 * updateUser.php is the server side code for the 
 * AJAX updateUser call. 
 * 
 * It updates a row in the players table.
 * 
 * Input consists the following parameters:
 *   pname
 *   email
 *   fname
 *   lname
 *   passwrd
 * But note that the passwrd parameter is optional.
 * 
 * Output is the echo return status: 
 *   "success", "fail", "bademail" or "email xxxx". 
 * 
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('auth.php');
require_once('config.php');

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($conn, $str) {
  $str = @trim($str);
  return mysqli_real_escape_string($conn, $str);
}

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
  error_log('Failed to connect to server: ' . mysqli_connect_error());
  echo 'fail';
	exit; 
}

//Sanitize the POST values
$fname = clean($link, $_REQUEST['fname']);
$lname = clean($link, $_REQUEST['lname']);
$login = clean($link, $_REQUEST['pname']);
$passwd = "";
$email = clean($link, $_REQUEST['email']);
if (array_key_exists('passwrd',$_REQUEST)) {
  $passwd = clean($link, $_REQUEST['passwrd']);
};

//Check for existing login ID
$qry1 = "SELECT * FROM players WHERE login='$login'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) === 0) { // no such user!
    error_log("Check for existing user: No user found!");
    echo 'fail';
    exit;
  } else {
    $playerrow = mysqli_fetch_assoc($result1);
    $playerid = $playerrow['player_id'];
    // allow for unchanged password
    if ($passwd === "") $passwd = $playerrow['passwd']; 
  }
} else {
  error_log("Check for existing user: Query failed");
  echo 'fail';
  exit;
}

//Check for ill formed email address
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  // The email address is ill formed
  echo 'bademail';
  exit;
}

//Check for duplicate email address
$qry2 = "SELECT login FROM players WHERE email='$email'";
$result2 = mysqli_query($link, $qry2);
if ($result2) {
  if (mysqli_num_rows($result2) > 0) { 
    while ($playerrow2 = mysqli_fetch_assoc($result2)) {
      if ($playerrow2['login'] !== $login) { // duplicate email
        $duperr = 'email' . $playerrow2['login'];
        echo $duperr;
        exit;
      }
    };
  }
} else {
  error_log("Check duplicate email: Query failed");
  echo 'fail';
  exit;
}

//Create UPDATE query
$qry = "UPDATE players SET firstname='$fname', lastname='$lname',
          email='$email', login='$login', passwd='$passwd'
          WHERE player_id=$playerid";
$result = @mysqli_query($link, $qry);
if ($result) {   // Was the query successful
  echo 'success';
} else {
  error_log("Update player: Query failed");
  echo 'fail';
}
?>
