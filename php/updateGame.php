<?php
/*
 * updateGame.php is the server side code for the 
 * AJAX updateGame call.
 * 
 * It loads game status updates into the database.
 * 
 * Input is the JSON game session data and the gameID.
 * 
 * Output will be "success", "failure", "notplaying"
 * or "collision".
 * 
 * The SESS_UPDATE_COUNTER session variable is used
 * by updateGame.php to support optimistic database 
 * locking. This should stop concurrent updates from
 * messing up the database. [And do this without
 * incurring high overhead.]
 * 
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
require_once('auth.php');
require_once('config.php');
$link = mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $errmsg1 = 'updateGame.php: failed to connect to server: ';
  error_log($errmsg1 . mysqli_connect_error());
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

//Sanitize the POST values (but not json string)
$gameSession = $_REQUEST['json'];
$gameid = clean($link,$_REQUEST['gameid']);

// Start transaction.
$qry1 = "START TRANSACTION";
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  $logMessage = 'updateGame.php: START TRANSACTION Error';
  error_log($logMessage);
  echo "failure";
  exit;
}

//Check for valid gameid ID and get counter and updater.
$qry2 = "SELECT update_counter, last_updater
  FROM game WHERE game_id='$gameid' FOR UPDATE";
$result2 = mysqli_query($link, $qry2);
if (!$result2 || (mysqli_num_rows($result2) !== 1)) { 
  $logMessage = 'updateGame.php: SELECT FROM game Error.';
  error_log($logMessage);
  echo "failure";
  mysqli_query($link, $qry0); // ROLLBACK
  exit;
}
$arr2 = mysqli_fetch_array($result2);
$counter = $arr2[0]; // update_counter
$updater = $arr2[1]; // last_updater

//Check if logged in player is playing this game.
$qry8 = "SELECT * FROM game_player
         WHERE player_id = '$loggedinplayer'
         AND game_id = '$gameid'";
$result8 = mysqli_query($link, $qry8);
if (!$result8) {   // If query failed
  $logMessage = 'updateGame.php: SELECT FROM game_player Error.';
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
  $logMessage = 'updateGame.php: SELECT FROM players Error.';
  error_log($logMessage);
  echo "failure";
  mysqli_query($link, $qry0); // ROLLBACK
  exit;
}
$arr3 = mysqli_fetch_array($result3);
if ($arr3[0] == '') {  // If no firstname
  $player = $arr3[1];  // then use login.
} else {
  $player = $arr3[0] . ' (' . $arr3[1] . ')';
}

//Update game session.
$counter += 1;
$qry4 = "UPDATE game SET update_counter='$counter',
                last_updater='$player',
                json_text='$gameSession'
         WHERE game_id = '$gameid'";
$result4 = mysqli_query($link, $qry4);
if (!$result4) {   // If query failed
  $logMessage = 'updateGame.php: UPDATE game Error.';
  error_log($logMessage);
  echo "failure";
  mysqli_query($link, $qry0); // ROLLBACK
  exit;
}

$_SESSION['SESS_UPDATE_COUNTER'] = $counter;
$qry5 = "COMMIT";
echo "success";
mysqli_query($link, $qry5); // COMMIT
