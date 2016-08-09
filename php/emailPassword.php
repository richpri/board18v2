<?php
/*
 * emailPassword.php is the server side code for the 
 * AJAX emailPassword call.
 * 
 * It creates a text email to inform a player of his
 * new temporary password. If the email
 * creation is successful it then calls sendEmail.php
 * and exits. This leaves it to sendEmail to return the
 * final 'success" status. 
 * 
 * Input consists the following parameters:
 *   name
 *   email
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

//Sanitize the POST values
$name = clean($link, $_REQUEST['name']);
$email = clean($link, $_REQUEST['email']);

//Check for ill formed email address
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  // The email address is ill formed
  echo 'bademail';
  exit;
}
// Generate a table of temporary passwords.
$pw1 = array('HM1j*BrahI', 'wXz*yCtRnI', '!uhY8FnMMX', 
       'HA*S7lEeG4', 'rWt8jUENfq', 'bxv*UzGUv2', 'EU4Y9u*FBB',
       'z7WjRB2*TU', 'zuX9wU5YWR', 'q3nM6AjnAk');

// Look up player via login name and verify email address.
// If email address is correct save temporary password
// and then send an email to that address with the
// login name and the new temporary password.
$qry1 = "SELECT * FROM players WHERE login='$name'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) === 0) { // No player!
    echo 'noplayer';
    exit;
  } else { // Found login name in database!
    $playerrow = mysqli_fetch_assoc($result1);
    $dbemail = $playerrow['email']; 
    if ($email !== $dbemail) { // Email mismatch
      echo 'noemail';
      exit;
    } else { // Everything is OK!
      $rnd10 = rand(0, 9);
      $hash = hash('sha256', $pw1[$rnd10]);
      //Create UPDATE query
      $qry2 = "UPDATE players SET passwd='$hash', changeit=1
               WHERE login='$name'";
      $result2 = mysqli_query($link, $qry2);
      if ($result2) {   // If the query was successful.
        $subject = 'BOARD18 Password Recovery';
        $body = 'This is a message from the BOARD18 server at ';
        $body .= $_SERVER['SERVER_NAME'] . ".\n \n";
        $body .= 'The new temporary password for user ' . $name; 
        $body .= ' at ' . $email . ' is  ' . $pw1[$rnd10]; 
        $body .= ".\n \nYou must change this password when you next log in.";
        sendEmail($email, $subject, $body);
        exit; 
      } else { 
        error_log("Update password: Query failed");
        echo 'fail';
	      exit; 
      }
    }
  }
} else {
  echo 'fail';
  error_log("Look up login name: Query failed");
  exit;
}
?>