<?php
/*
 * emailPlayerID.php is the server side code for the 
 * AJAX emailPlayerID call.
 * 
 * It creates a text email to remind a player (that
 * has forgotten) of his player ID. If the email
 * creation is successful it then calls sendEmail.php
 * and exits. This leaves it to sendEmail to return the
 * final 'success" status. 
 * 
 * Input consists the "email" parameter.
 * 
 * Output, if any, is the echo return status "fail" or "bademail". 
 *
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
require_once('config.php');
require_once('sendEmail.php');

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
  error_log('emailPlayerID.php: Connect Failed: ' . mysqli_connect_error());
  echo 'fail';
  exit; 
}

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($conn, $str) {
  $str = trim($str);
  return mysqli_real_escape_string($conn, $str);
}

//Sanitize the POST value
$email = clean($link, $_REQUEST['email']);

//Check for ill formed email address
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  // The email address is ill formed
  echo 'bademail';
  exit;
}

// Look up player via email address and send email
// to that address with associated player ID [login].
$qry1 = "SELECT * FROM players WHERE email='$email'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) === 0) { // No email!
    echo 'noemail';
    exit;
  } else { // Found email address in database!
    $playerrow = mysqli_fetch_assoc($result1);
    $subject = 'BOARD18 Player ID Recovery';
    $body = 'This is a message from the BOARD18 server at ';
    $body .= $_SERVER['SERVER_NAME'] . ".\n \n";
    $body .= 'The player id for user at ' . $email;
    $body .= ' is ' . $playerrow['login'] . '.';
    sendEmail($email, $subject, $body);
    exit;
  }
} else {
  echo 'fail';
  error_log("emailPlayerID.php: Look up email address: Query failed");
  exit;
}
?>