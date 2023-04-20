<?php
/*
 * This is the server side code for the AJAX gameShow call.
 * 
 * It returns an array of records from the game table.
 * 
 * Input is: blocksz and startrow.
 * 
 * Output is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||"nogames"||"fail",
 *     "games":
 *     [
 *       {
 *         "gameid":"nnnn",
 *         "gname":"gggggg",
 *         "bname":"aaaaaa", 
 *         "sdate":"yyy-mm-dd hh:mm:ss",
 *         "adate":"yyy-mm-dd hh:mm:ss",
 *         "boxid":"eeeeee",
 *         "status":"fffff",
 *         "pcount":"nnnnnn"
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
  public $bname;
  public $sdate;
  public $adate;
  public $status;
  public $pcount;
}
class Response
{
  public $stat;
  public $games;
}

// set up fail return object.
$errorResp = new Response();
$errorResp->stat = "fail";
$errResp = json_encode($errorResp);

require_once('auth.php');
if ($playerlevel != 'admin') {
  error_log("gameShow: Not an admin level player");
  echo $errResp;
  exit;
} 
require_once('config.php');

$link = mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'gameShow: MySQL Connect Error: ' . mysqli_connect_error();
  error_log($logMessage);
  echo $errResp;
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
$blocksize = clean($link, $_REQUEST['blocksz']);
$startrow = clean($link, $_REQUEST['startrow']);

//Get records from the game table.
$qry1 = "SELECT game_id, gname, start_date,
         activity_date, box_id, status FROM game 
         ORDER BY game_id LIMIT $startrow,$blocksize";
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  error_log("gameShow: SELECT FROM game - Query failed");
  echo $errResp;
  exit;
}
if (mysqli_num_rows($result1) === 0) {
  $noneResp = new Response();
  $noneResp->stat = "nogames";
  echo json_encode($noneResp);
  exit;
}
$gamelist = array();
$ii = 0;
while ($row1 = mysqli_fetch_assoc($result1)) {
  $gamelist[$ii] = new Gameline();
  $gamelist[$ii]->gameid = $row1['game_id'];
  $gamelist[$ii]->gname = $row1['gname'];
  $qry2 = "SELECT bname FROM box 
           WHERE box_id=$row1[box_id]";
  $result2 = mysqli_query($link, $qry2);
  if ($result2) {
    $boxrow = mysqli_fetch_row($result2);
    $gamelist[$ii]->bname = $boxrow[0];
  } else {
    error_log("gameShow: SELECT bname FROM box - Query failed");
    echo $errResp;
    exit;
  }
  $gamelist[$ii]->sdate = $row1['start_date'];
  $gamelist[$ii]->adate = $row1['activity_date'];
  $gamelist[$ii]->status = $row1['status'];
  $qry3 = "SELECT COUNT(*) FROM game_player 
           WHERE game_id=$row1[game_id]";
  $result3 = mysqli_query($link, $qry3);
  if ($result3) {
    $countrow = mysqli_fetch_row($result3);
    $gamelist[$ii]->pcount = $countrow[0];
  } else {
    error_log("gameShow: SELECT COUNT(*) FROM game_player - Query failed");
    echo $errResp;
    exit;
  }
  $ii += 1;
}

$succResp = new Response();
$succResp->stat = "success";
$succResp->games = $gamelist;
echo json_encode($succResp, JSON_PARTIAL_OUTPUT_ON_ERROR);
exit;
