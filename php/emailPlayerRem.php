<?php
/*
 * emailPlayerRem.php is the server side code for the 
 * AJAX emailPlayerRem call. 
 * It creates a text email to notify a player
 * that has been removed from a game. If the email
 * creation is successful it then calls sendEmail.php
 * and exits. This leaves it to sendEmail to return the
 * final 'success" status. 
 * 
 * Input consists the following parameters:
 *   login
 *   game
 * 
 * Output, if any, is the echo return status "fail". 
 *
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
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
$game = clean($link, $_REQUEST['game']);

// Look up player via login and send email
// to that player ID [login].
$qry1 = "SELECT * FROM players WHERE login='$login'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) === 0) { // No player!
    echo 'fail';
    error_log("Look up player: Player login not found!");
    exit;
  } else { // Found login in database!
    $playerrow = mysqli_fetch_assoc($result1);
    $subject = 'BOARD18 Game Removal Report';
    $body = 'This is a message from the BOARD18 server at ';
    $body .= $_SERVER['SERVER_NAME'] . ".\n \n";
    $body .= "You have been removed from the BOARD18 game titled \n\t";
    $body .= $game . ".\n \nHave a nice day!";
    sendEmail($playerrow['email'], $subject, $body);
    exit;
  }
} else {
  echo 'fail';
  error_log("Look up player: Query failed");
  exit;
}
?>