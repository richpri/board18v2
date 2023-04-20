<?php
/*
 * gameUpdate.php is the server side code for the 
 * AJAX gameUpdate call. 
 * 
 * It updates the name and/or the status fields 
 * in a row in the game table.
 * 
 * Input consists the following parameters:
 * 
 *   gameid  
 *   gname
 *   status
 * 
 * Output is the echo return status: 
 *   "success", "fail", "gname". 
 * 
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('auth.php');
if ($playerlevel != 'admin') {
  error_log("gameUpdate: Not an admin level player");
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
  $errmsg1 = 'gameUpdate failed to connect to server: ';
  error_log($errmsg1 . mysqli_connect_error());
  echo 'fail';
	exit; 
}

//Sanitize the POST values
$gameid = clean($link, $_REQUEST['gameid']);
$gname = clean($link, $_REQUEST['gname']);
$status = clean($link, $_REQUEST['status']);

//Check for existing game
$qry1 = "SELECT * FROM game WHERE game_id ='$gameid'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) === 0) { // no such game!
    error_log("gameUpdate: Check for existing game: No game found!");
    echo 'fail';
    exit;
  } else {
    $gamerow = mysqli_fetch_assoc($result1);
  }
} else {
  error_log("gameUpdate: Check for existing game: Query failed");
  echo 'fail';
  exit;
}

//Check for name change
if ($gamerow['gname'] !== $gname) { // If game name changed
  //Check for duplicate game name
  $qry2 = "SELECT * FROM game WHERE gname='$gname'";
  $result2 = mysqli_query($link, $qry2);
  if ($result2) {
    if (mysqli_num_rows($result2) > 0) { // duplicate game name
      echo 'gname';
      exit;     
    }
  } else {
    error_log("gameUpdate: Check duplicate gname: Query failed");
    echo 'fail';
    exit;
  }
}

//Create UPDATE query
$qry = "UPDATE game SET gname='$gname', status='$status'
          WHERE game_id=$gameid";
$result = @mysqli_query($link, $qry);
if ($result) {   // Was the query successful
  echo 'success';
} else {
  error_log("Update game: Query failed");
  echo 'fail';
}
?>
