<?php
/*
 * This is the server side code for the AJAX playerGet call.
 * 
 * It returns most fields for a player in the players table.
 * It also returns a list of the games the player is playing.
 * 
 * Input is: login.
 * 
 * Output is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||"fail"||"nogames",
 *     "playerid":"nnnnnn",
 *     "login":"aaaaaa",
 *     "firstname":"bbbbbb",
 *     "lastname":"cccccc",
 *     "email":"eeeeee",
 *     "level":"llllll",
 *     "games":
 *     [
 *       {
 *         "gname":"gggggg",
 *         "status":"ssssss"
 *       },
 *       . . . . more games . . . . . 
 *     ]
 *   }
 *
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * 
 */

class Gameline
{
  public $gameid;
  public $gname;
  public $status;
}
class Response
{
  public $stat;
  public $playerid;
  public $login;
  public $firstname;
  public $lastname;
  public $email;
  public $level;
  public $games;
}

// set up fail return object.
$errorResp = new Response();
$errorResp->stat = "fail";
$errResp = json_encode($errorResp);

require_once('auth.php');
if ($playerlevel != 'admin') {
  error_log("playerGet: Not an admin level player");
  echo $errResp;
  exit;
}
require_once('config.php');

$link = @mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'MySQL Error 1: ' . mysqli_connect_error();
  error_log($logMessage);
  echo $errResp;
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
$player = clean($link, $_REQUEST['login']);

//Get record from the players table.
$qry1 = "SELECT * FROM players WHERE login='$player'";
$result1 = mysqli_query($link, $qry1);
if (!$result1 || mysqli_num_rows($result1) === 0) {
  error_log("SELECT FROM players - Query failed");
  echo $errResp;
  exit;
} else {
  $playerrow = mysqli_fetch_assoc($result1);
  $playerResp = new Response();
  $playerResp ->playerid  = $playerrow['player_id'];
  $playerResp ->login = $playerrow['login'];
  $playerResp ->firstname = $playerrow['firstname'];
  $playerResp ->lastname = $playerrow['lastname'];
  $playerResp ->email = $playerrow['email'];
  $playerResp ->level = $playerrow['level'];
}

$player1 = $playerrow['player_id'];
$qry2 = "SELECT b.game_id, b.gname, b.status 
         FROM game_player AS a 
           JOIN game AS b 
             ON (a.player_id =  $player1
               AND a.game_id = b.game_id)";
$result2 = mysqli_query($link, $qry2);
if ($result2) {
  if (mysqli_num_rows($result2) === 0) { // Player in no games!
    $playerResp ->stat = "nogames";
  } else {
    $playerResp ->stat = "success";
    $gamelist =  array();
    $ii = 0;
    while ($gamerow = mysqli_fetch_assoc($result2)) {
      $gamelist[$ii] = new Gameline();
      $gamelist[$ii] ->gameid = $gamerow['game_id'];
      $gamelist[$ii] ->gname = $gamerow['gname'];
      $gamelist[$ii] ->status = $gamerow['status'];
      $ii += 1;
    }
    $playerResp ->games = $gamelist;
  }
  echo json_encode($playerResp);
  exit;
} else {
  error_log("SELECT JOIN - Query failed");
  echo $errResp;
  exit;
} 
?>
