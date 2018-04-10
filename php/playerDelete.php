<?php
/*
 * playerDelete.php is the server side code for the 
 * AJAX playerDelete call. 
 * 
 * It deletes a row in the players table.
 * It also deletes all associated rows in the game_player table.
 * 
 * Input consists the following parameter:
 * 
 *   player  
 * 
 * Output is the echo return status: 
 *   "success", "admin" or "fail". 
 * 
 * Copyright (c) 2018 Richard E. Price under the The MIT License.
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

//Sanitize the POST value
$player = clean($link, $_REQUEST['player']);

//Check for existing player ID
$qry1 = "SELECT * FROM players WHERE player_id ='$player'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) === 0) { // no such player!
    error_log("Check for existing player: No player found!");
    echo 'fail';
    exit;
  }
  $playerrow = mysqli_fetch_assoc($result1);
  $level = $playerrow['level'];
  if ($level === 'admin') { // Cannot delete an admin level player.
    echo 'admin';
    exit;
  }
} else {
  error_log("Check for existing player: Query failed");
  echo 'fail';
  exit;
}

//Create Delete querys
$qry2 = "DELETE FROM game_player WHERE player_id=$player";
$qry3 = "DELETE FROM players WHERE player_id=$player";

$result2 = mysqli_query($link, $qry2);
if ($result2) {   // Was query 2 successful
  $result3 = mysqli_query($link, $qry3);
  if ($result3) {   // Was query 3 successful
    echo 'success';
  } else {
    error_log("Delete players: Query failed");
  }
} else {
  error_log("Delete game_player: Query failed");
  echo 'fail';
}