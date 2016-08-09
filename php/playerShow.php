<?php
/*
 * This is the server side code for the AJAX playerShow call.
 * 
 * It returns an array of records from the players table.
 * 
 * Input is: blocksz and startrow.
 * 
 * Output is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||"fail",
 *     "players":
 *     [
 *       {
 *         "login":"aaaaaa",
 *         "firstname":"bbbbbb",
 *         "lastname":"cccccc",
 *         "email":"eeeeee",
 *         "level":"llllll",
 *         "gcount":"nnnnnn",
 *         "acount":"nnnnnn"
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
  public $login;
  public $firstname;
  public $lastname;
  public $email;
  public $level;
  public $gcount;
  public $acount;
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

require_once('auth.php');
if ($playerlevel != 'admin') {
  error_log("playerShow: Not an admin level player");
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
$blocksize = clean($link, $_REQUEST['blocksz']);
$startrow = clean($link, $_REQUEST['startrow']);

//Get records from the players table.
$qry1 = "SELECT player_id, login, firstname, lastname, 
         email, level FROM players ORDER BY login
         LIMIT $startrow,$blocksize";
$result1 = mysqli_query($link, $qry1);
if (!$result1 || mysqli_num_rows($result1) === 0) {
  error_log("SELECT FROM players - Query failed");
  echo $errResp;
  exit;
}
$playerlist = array();
$ii = 0;
while ($row1 = mysqli_fetch_array($result1)) {
  $playerlist[$ii] = new Playerline();
  $playerlist[$ii]->login = $row1[1];
  $playerlist[$ii]->firstname = $row1[2];
  $playerlist[$ii]->lastname = $row1[3];
  $playerlist[$ii]->email = $row1[4];
  $playerlist[$ii]->level = $row1[5];
  $qry2 = "SELECT COUNT(*) FROM game_player 
           WHERE player_id=$row1[0]";
  $result2 = mysqli_query($link, $qry2);
  if ($result2) {
    $countrow = mysqli_fetch_row($result2);
    $playerlist[$ii]->gcount = $countrow[0];
  } else {
    error_log("SELECT COUNT(*) FROM game_player - Query failed");
    echo $errResp;
    exit;
  }
  if ($countrow[0] === 0) {
    $playerlist[$ii]->acount = 0;
  } else {
    $qry3 = "SELECT b.status, COUNT(*) 
             FROM game_player AS a 
               JOIN game AS b 
                 ON (a.player_id = $row1[0] 
                   AND a.game_id = b.game_id)
             GROUP BY b.status";
    $result3 = mysqli_query($link, $qry3);
    if ($result3) {
      $playerlist[$ii]->acount = 0;
      while ($row3 = mysqli_fetch_array($result3)) {
        if ($row3[0] === "Active") {
          $playerlist[$ii]->acount = $row3[1];
        }
      }
    } else {
      error_log("SELECT RIGHT JOIN - Query failed");
      echo $errResp;
      exit;
    } 
  }
  $ii += 1;
}

$succResp = new Response();
$succResp->stat = "success";
$succResp->players = $playerlist;
echo json_encode($succResp);
exit;
?>
