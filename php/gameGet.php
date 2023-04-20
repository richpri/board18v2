<?php
/*
 * gameGet.php is the server side code for the AJAX gameGet call.
 * 
 * It returns most fields for a game from the game table.
 * It also returns a list of the players who are playing the game.
 * 
 * Input is: gameid.
 * 
 * Output is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||"fail"||"noplayers",
 *     "gameid":"nnnn",
 *     "gname":"aaaaaa",
 *     "boxid":"nnnn",
 *     "bname":"bbbbbb",
 *     "version":"vvvvvvv",
 *     "sdate":"yyy-mm-dd hh:mm:ss",
 *     "adate":"yyy-mm-dd hh:mm:ss",
 *     "lastupdater":"llllllll",
 *     "status":"sssss",
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
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
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
  public $gameid;
  public $gname;
  public $boxid;
  public $bname;
  public $version;
  public $sdate;
  public $adate;
  public $lastupdater;
  public $status;
  public $players;
}

// set up fail return object.
$errorResp = new Response();
$errorResp->stat = "fail";
$errResp = json_encode($errorResp);

require_once('auth.php');
if ($playerlevel != 'admin') {
  error_log("gameGet: Not an admin level player");
  echo $errResp;
  exit;
} 
require_once('config.php');

$link = @mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'gameGet: MySQL Error 1: ' . mysqli_connect_error();
  error_log($logMessage);
  echo $errResp;
  exit;
}
mysqli_set_charset($link, "utf8mb4");

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($link, $str) {
  $str = @trim($str);
  return mysqli_real_escape_string($link, $str);
}

//Sanitize the POST value
$gameid = intval(clean($link, $_REQUEST['gameid']));

//Get record from the game table.
$qry1 = "SELECT * FROM game WHERE game_id='$gameid'";
$result1 = mysqli_query($link, $qry1);
if (!$result1 || mysqli_num_rows($result1) === 0) {
  error_log("gameGet: SELECT FROM game for $gameid - Query failed");
  echo $errResp;
  exit;
} else {
  $gamerow = mysqli_fetch_assoc($result1);
  $gameResp = new Response();
  $gameResp ->gameid  = $gamerow['game_id'];
  $gameResp ->gname = $gamerow['gname'];
  $gameResp ->boxid = $gamerow['box_id'];
  $gameResp ->sdate = $gamerow['start_date'];
  $gameResp ->adate = $gamerow['activity_date'];
  $gameResp ->lastupdater = $gamerow['last_updater'];
  $gameResp ->status = $gamerow['status'];
}

//Get box name from box table.
$boxid = $gamerow['box_id'];
$qry2 = "SELECT bname, version FROM box WHERE box_id='$boxid'";
$result2 = mysqli_query($link, $qry2);
if (!$result2 || mysqli_num_rows($result2) === 0) {
  error_log("gameGet: SELECT FROM box - Query failed");
  echo $errResp;
  exit;
} else {
  $boxrow = mysqli_fetch_assoc($result2);
  $gameResp ->bname = $boxrow['bname'];
  $gameResp ->version = $boxrow['version'];
}

//Get array of players in game.
$agameid = $gamerow['game_id'];
$qry3 = "SELECT b.player_id, b.login, b.firstname, b.lastname
         FROM game_player AS a 
           JOIN players AS b 
             ON (a.game_id = $agameid
               AND a.player_id = b.player_id)";
$result3 = mysqli_query($link, $qry3);
if ($result3) {
  if (mysqli_num_rows($result3) === 0) { // No players in game!
    $gameResp ->stat = "noplayers";
  } else {
    $gameResp ->stat = "success";
    $playerlist =  array();
    $ii = 0;
    while ($playerrow = mysqli_fetch_assoc($result3)) {
      $playerlist[$ii] = new Playerline();
      $playerlist[$ii] ->playerid = $playerrow['player_id'];
      $playerlist[$ii] ->login = $playerrow['login'];
      $playerlist[$ii] ->fname = $playerrow['firstname'];
      $playerlist[$ii] ->lname = $playerrow['lastname'];
      $ii += 1;
    }
    $gameResp ->players = $playerlist;
  }
  echo json_encode($gameResp);
  exit;
} else {
  error_log("gameGet: SELECT JOIN - Query failed");
  echo $errResp;
  exit;
} 
?>
