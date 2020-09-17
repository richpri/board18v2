<?php
/*
 * snapShot.php is the server side code for the 
 * AJAX snapShot call.
 * 
 * It loads game status updates into the database.
 * 
 * Input is the gameID and the operating or stock round name.
 * 
 * Output will be "success", "failure", "notplaying"
 * or "collision".
 * 
 * The SESS_UPDATE_COUNTER session variable is used
 * by snapShot.php to support optimistic database 
 * locking. This should stop concurrent updates from
 * messing up the database. [And do this without
 * incurring high overhead.]
 * 
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
require_once('auth.php');
require_once('config.php');
$link = mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'snapShot.php: MySQL connect error: ' . mysqli_connect_error();
  error_log($logMessage);
  echo "failure";
  exit;
}
mysqli_set_charset($link, "utf-8");
$update_counter = $_SESSION['SESS_UPDATE_COUNTER'];
$qry0 = "ROLLBACK";

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($link,$str1) {
  $str = trim($str1);
  return mysqli_real_escape_string($link,$str);
}

//Sanitize the POST values 
$gameid = clean($link,$_REQUEST['gameid']);
$roundname = clean($link,$_REQUEST['roundname']);

// Start transaction.
$qry1 = "START TRANSACTION";
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  $logMessage = 'snapShot.php: START TRANSACTION Error: ' . mysqli_error($link);
  error_log($logMessage);
  echo "failure";
  exit;
}

//Check for valid gameid ID and 
//get counter, updater and json_text.
$qry2 = "SELECT update_counter, last_updater, json_text
  FROM game WHERE game_id='$gameid' FOR UPDATE";
$result2 = mysqli_query($link, $qry2);
if (!$result2 || (mysqli_num_rows($result2) !== 1)) { 
  $logMessage = 'snapShot.php: SELECT FROM game error: ' . mysqli_error($link);
  error_log($logMessage);
  echo "failure";
  mysqli_query($link, $qry0); // ROLLBACK
  exit;
}
$arr2 = mysqli_fetch_array($result2);
$counter = $arr2[0]; // update_counter
$updater = $arr2[1]; // last_updater
$jsontext = $arr2[2]; // json_text

//Check if logged in player is playing this game.
$qry8 = "SELECT * FROM game_player
         WHERE player_id = '$loggedinplayer'
         AND game_id = '$gameid'";
$result8 = mysqli_query($link, $qry8);
if (!$result8) {   // If query failed
  $logMessage = 'snapShot.php: SELECT game_player Error: ' . mysqli_error($link);
  error_log($logMessage);
  echo "failure";
  mysqli_query($link, $qry0); // ROLLBACK
  exit;
}
if ((mysqli_num_rows($result8) !== 1)) { // not playing!
  echo "notplaying";
  mysqli_query($link, $qry0); // ROLLBACK
  exit;
}

//Check for concurrent update [AKA collision].
if ($counter != $update_counter) { // collision 
  echo "collision ";
  echo $updater;
  mysqli_query($link, $qry0); // ROLLBACK
  exit;
}

//Get name of current player.
$qry3 = "SELECT firstname, login FROM players
         WHERE player_id = '$loggedinplayer'";
$result3 = mysqli_query($link, $qry3);
if (!$result3) {   // If query failed
  $logMessage = 'snapShot.php: SELECT FROM players error: ' . mysqli_error($link);
  error_log($logMessage);
  echo "failure";
  mysqli_query($link, $qry0); // ROLLBACK
  exit;
}
$arr3 = mysqli_fetch_array($result3);
if ($arr3[0] == '') {  // If no firstname
  $player = $arr3[1];  // then use just login.
} else {
  $player = $arr3[0] . ' (' . $arr3[1] . ')';
}

//Update game session.
$counter += 1;
$qry4 = "UPDATE game SET update_counter='$counter',
                last_updater='$player'
         WHERE game_id = '$gameid'";
$result4 = mysqli_query($link, $qry4);
if (!$result4) {   // If query failed
  $logMessage = 'snapShot.php: UPDATE game Error: ' . mysqli_error($link);
  error_log($logMessage);
  echo "failure";
  mysqli_query($link, $qry0); // ROLLBACK
  exit;
}

//Create Check Point record.
$qry5 = "INSERT INTO game_snap 
          SET game_id='$gameid',
              player='$player',  
              last_updater='$updater',
              game_round='$roundname',
              json_text='$jsontext'";  
$result5 = mysqli_query($link,$qry5);
if (!$result5) {   // Did the query fail
  $logMessage = 'snapShot.php: INSERT Error: ' . mysqli_error($link);
  error_log($logMessage);
  echo "fail"; 
  mysqli_query($link, $qry0); // ROLLBACK
  exit;
}

$_SESSION['SESS_UPDATE_COUNTER'] = $counter;
$qry6 = "COMMIT";
echo "success";
mysqli_query($link, $qry6); // COMMIT
