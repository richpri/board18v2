<?php
/*
 * emailPlayer.php is the server side code for the 
 * AJAX emailPlayer call. It creates a
 * text email for a specific player. If the email
 * creation is successful it then calls sendEmail.php
 * and exits. This leaves it to sendEmail to return the
 * final 'success' status. 
 * 
 * Input consists the following parameters:
 *   login
 *   subject
 *   body
 * 
 * Output, if any, is the echo return status "fail". 
 *
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
require_once('auth.php');
require_once('config.php');
require_once('sendEmail.php');

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
  error_log('Failed to connect to server: ' . mysqli_connect_error());
  echo 'fail';
  exit; 
}

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($conn, $str) {
  $str = @trim($str);
  return mysqli_real_escape_string($conn, $str);
}

//Sanitize the POST value
$login = clean($link, $_REQUEST['login']);
$subject = clean($link, $_REQUEST['subject']);
$midbody = $_REQUEST['body'];

// Look up player via login and send email
// to that player ID [login].
$qry1 = "SELECT * FROM players WHERE login='$login'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) === 0) { // No player!
    echo 'fail';
    error_log("Look up player: Player $login not found!");
    exit;
  } else { // Found login in database!
    $playerrow = mysqli_fetch_assoc($result1);
    $subject = '[BOARD18] ' . $subject;
    $body = 'This is a message from the BOARD18 server at ';
    $body .= $_SERVER['SERVER_NAME'] . ".\n \n";
    $body .= $midbody;
    sendEmail($playerrow['email'], $subject, $body);
    exit;
  }
} else {
  echo 'fail';
  error_log("Look up player: Query failed");
  exit;
}
?>
