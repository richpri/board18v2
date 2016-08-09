<?php
/*
 * This is the server side code for the AJAX gameBox call.
 * 
 * It returns a game box in JSON format.
 * 
 * Input is the game box id.
 * 
 * Output is JSON game box data.
 * 
 * Copyright (c) 2014 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('auth.php');
require_once('config.php');

$link = @mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'MySQL Error: ' . mysqli_connect_error();
  error_log($logMessage);
  exit;
}
mysqli_set_charset($link, "utf-8");

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($link, $str) {
  $str = @trim($str);
  return mysqli_real_escape_string($link, $str);
}

//Sanitize the POST value
$boxid = clean($link, $_REQUEST['box']);

//Check for valid box ID and get JSON text for box.
$qry1 = "SELECT json_text FROM box WHERE box_id='$boxid'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) == 0) { // Invalid box ID!
  error_log("Check for valid game box: Invalid Box ID: " . $boxid);
  $_SESSION['SESS_HEADER_MESSAGE'] = 
    'The selected box is not in the data base!';
  header("location: ../board18Main.php");
  }
} else {
  error_log("Check for valid box: Query failed");
  exit;
}

$ad = mysqli_fetch_array($result1); // $ad[0] is json_data

echo $ad[0];
?>
