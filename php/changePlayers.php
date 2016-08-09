<?php

/*
 * changePlayers.php is the server side code for the 
 * AJAX changePlayers call. This program adds and/or 
 * removes players from existing game sessions.
 * 
 * Inputs:
 *    mode - A string with a value of '1', '2' or '3'.
 *           1 - Remove player with login ID in prem.
 *           2 - Add player with login ID in padd.
 *           3 - Take both of the above actions.
 *    game - game_id of current game.
 *    prem - Login ID of the player to be removed from the game.
 *    padd - Login ID of the player to be added to the game.  
 * 
 * Output is echo return status:
 *   "success", "fail" or "dupadd".
 *
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
require_once('auth.php');
require_once('config.php');

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
  error_log('Failed to connect to server: ' . mysqli_connect_error());
  echo 'fail';
  exit;
}
mysqli_set_charset($link, "utf-8");
$qry0 = "ROLLBACK";

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($conn, $str) {
  $str = @trim($str);
  return mysqli_real_escape_string($conn, $str);
}

//Sanitize the POST values.
$mode = clean($link, $_REQUEST['mode']);
$game = intval(clean($link, $_REQUEST['game']));
$prem = clean($link, $_REQUEST['prem']);
$padd = clean($link, $_REQUEST['padd']);

// If add request then get player id of player to be added to game
if ($mode === '2' || $mode === '3') {  
  $qry1 = "SELECT player_id FROM players WHERE login = '$padd'";
  $result1 = mysqli_query($link, $qry1);
  if (!$result1) { 
    $logMessage = 'Error on SELECT player query: ' . mysqli_error($link);
    error_log($logMessage);
    echo 'fail';
    exit;
  }
  if (mysqli_num_rows($result1) !== 1) { 
    $logMessage = 'Add player not in players!: ' . mysqli_error($link);
    error_log($logMessage);
    echo 'fail';
    exit;
  }
  $rowr = mysqli_fetch_array($result1);
  $paddid = $rowr[0]; //  Player ID of player to be added.

  $qry2 = "SELECT * FROM game_player 
           WHERE player_id = $paddid AND game_id = $game";
  $result2 = mysqli_query($link, $qry2);
  if (!$result2) {
    $logMessage = 'Error on SELECT game_player query: ' . 
            mysqli_error($link);
    error_log($logMessage);
    echo 'fail';
    exit;
  }
  if (mysqli_num_rows($result2) === 1) {
    echo 'dupadd'; // player is already in game!
    exit;
  }
  // Start transaction.
  $qry3 = "START TRANSACTION";
  $result3 = mysqli_query($link, $qry3);
  if (!$result3) {
    $logMessage = 'START TRANSACTION Error: ' . mysqli_error($link);
    error_log($logMessage);
    echo "fail";
    exit;
  }
  
  $qry4 = "INSERT INTO game_player
           SET game_id = '$game', player_id =  $paddid";
  if (!mysqli_query($link, $qry4)) {
    $logMessage = 'Error on INSERT query: ' . mysqli_error($link);
    error_log($logMessage);
    mysqli_query($link, $qry0); // ROLLBACK
    echo 'fail';
    exit;
  }
}

// If remove request then get player id of player to be removed
if ($mode === '1' || $mode === '3') {
  $qry5 = "SELECT b.player_id
          FROM players AS a 
            JOIN (game_player AS b)
              ON (a.login = '$prem'
                AND b.game_id = $game
                AND a.player_id = b.player_id)";
  $result5 = mysqli_query($link, $qry5);
  if (!$result5 || mysqli_num_rows($result5) !== 1) {
    $logMessage = 'Error on SELECT JOIN query: ' . mysqli_error($link);
    error_log($logMessage);
    mysqli_query($link, $qry0); // ROLLBACK
    echo 'fail';
    exit;
  } else {
    $rowr = mysqli_fetch_array($result5);
    $playerid = $rowr[0];
  }
  
  $qry6 = "DELETE FROM game_player
           WHERE player_id =  $playerid
             AND game_id = $game";
  if (!mysqli_query($link, $qry6)) {
    $logMessage = 'Error on DELETE query: ' . mysqli_error($link);
    error_log($logMessage);
    mysqli_query($link, $qry0); // ROLLBACK
    echo 'fail';
    exit;
  }
}

$qry7 = "COMMIT";
echo "success";
mysqli_query($link, $qry7); // COMMIT
switch ($mode) {
  case '1':
    $_SESSION['SESS_HEADER_MESSAGE'] = 
      'Player has been successfully removed.';
    break;
  case '2':
    $_SESSION['SESS_HEADER_MESSAGE'] = 
      'Player has been successfully added.';
    break;
  case '3':
    $_SESSION['SESS_HEADER_MESSAGE'] = 
      'Player has been successfully replaced.';
    break;
}
?>