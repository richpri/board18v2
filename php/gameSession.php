<?php
 /*
 * This is the server side code for the AJAX gameSession call.
 * 
 * It returns game activity information in JSON format.
 * 
 * Input is the game_id.
 * 
 * Output is JSON game session data.
 * 
 * The SESS_UPDATE_COUNTER is initialized here and used by
 * updateGame.php to support optimistic  database locking.
 * This should stop concurrent updates from messing up the 
 * database.
 */

require_once('auth.php');
require_once('config.php');
$link = mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'gameSession: MySQL Connect Error: ' . mysqli_connect_error();
  error_log($logMessage);
  exit;
}
mysqli_set_charset($link, "utf8mb4");

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($link, $str1) {
  $str = trim($str1);
  return mysqli_real_escape_string($link, $str);
}

//Sanitize the POST value
$gameid = clean($link, $_REQUEST['session']);

//Check for valid gameid ID and get JSON text for game.
$qry1 = "SELECT json_text, update_counter
  FROM game WHERE game_id='$gameid'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) == 0) { // Invalid Game ID!
  error_log("gameSession: Check for valid game: Invalid Game ID: " . $gameid);
  $_SESSION['SESS_HEADER_MESSAGE'] = 
    'The selected game is not in the data base!';
  header("location: board18Main.php");
  }
} else {
  error_log("gameSession: Check for valid game: Query failed");
  exit;
}

$ad = mysqli_fetch_array($result1); 
$_SESSION['SESS_UPDATE_COUNTER'] = $ad[1]; // update_counter
echo $ad[0]; // $ad[0] is json_data
