<?php
/*
 * gamePlayers.php is the server side code for the AJAX gamePlayers call.
 * 
 * It returns a list of the players who are playing the game.
 * 
 * Input is: gameid.
 * 
 * Output is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||"fail"||"noplayers",
 *     "players":
 *     [
 *       {
 *         "playerid":"nnnn",
 *         "login":"pppppp",
 *         "fname":"fffffff",
 *         "lname":"ggggggg"
 *       },
 *       . . . . more players . . . . . 
 *     ]
 *   }
 *
 * Copyright (c) 2020 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * 
 */

class Playerline
{
  public $playerid;
  public $login;
  public $fname;
  public $lname;
}
class Response
{
  public $stat;
  public $players;
}

// set up fail return object.
$errorResp = new Response();
$errorResp->stat = "fail";
$errResp = json_encode($errorResp);

$emptyResp = new Response();
$emptyResp->stat = "noplayers";
$nopResp = json_encode($emptyResp);

require_once('auth.php');
require_once('config.php');

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'gamePlayers: MySQL connect error: ' . mysqli_connect_error();
  error_log($logMessage);
  echo $errResp;
  exit;
}
mysqli_set_charset($link, "utf-8");

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($link, $str1) {
  $str = trim($str1);
  return mysqli_real_escape_string($link, $str);
}

//Sanitize the POST value
$gameid = intval(clean($link, $_REQUEST['gameid']));

$qry1 = "SELECT a.player_id, b.login, b.firstname, b.lastname
          FROM game_player AS a 
            JOIN (players AS b)
              ON (a.player_id = b.player_id)
            WHERE a.game_id = $gameid
          ORDER BY a.player_id";
$result1 = mysqli_query($link,$qry1);

if ($result1) {
  if (mysqli_num_rows($result1) === 0) { // no players.
    echo $nopResp;
    exit;
  } else {
    $gameResp = new Response();
    $gameResp ->stat = "success";
    $playerlist =  array();
    $ii = 0;
    while ($playerrow = mysqli_fetch_assoc($result1)) {
      $playerlist[$ii] = new Playerline();
      $playerlist[$ii] ->login = $playerrow['login'];
      $playerlist[$ii] ->fname = $playerrow['firstname'];
      $playerlist[$ii] ->lname = $playerrow['lastname'];
      $ii += 1;
    }
    $gameResp ->players = $playerlist;
    echo json_encode($gameResp);
    exit;
  }
} else {
  $logMessage = 'gamePlayers: Error on SELECT query.';
  echo $errResp;
  exit;
}
