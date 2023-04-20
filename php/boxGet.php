<?php
/*
 * boxGet.php is the server side code for the AJAX boxGet call.
 * 
 * It returns most fields for a box from the box table.
 * It also returns a list of the games associated with the box.
 * 
 * Input is: boxid.
 * 
 * Output is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||"fail"||"nogames",
 *     "boxid":"nnnn",
 *     "bname":"aaaaaa",
 *     "version":"bbbbbb",
 *     "cdate":"yyy-mm-dd hh:mm:ss",
 *     "adate":"yyy-mm-dd hh:mm:ss",
 *     "author":"llllllll",
 *     "status":"sssss",
 *     "games":
 *     [
 *       {
 *         "gameid":"mmmmm",
 *         "gname":"ggggggg",
 *         "status":"ttttt",
 *       },
 *       . . . . more games . . . . . 
 *     ]
 *   }
 *
 * Copyright (c) 2016 Richard E. Price under the The MIT License.
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
  public $boxid;
  public $bname;
  public $version;
  public $cdate;
  public $adate;
  public $author;
  public $status;
  public $games;
}

// set up fail return object.
$errorResp = new Response();
$errorResp->stat = "fail";
$errResp = json_encode($errorResp);

require_once('auth.php');
if ($playerlevel != 'admin') {
  error_log("boxGet: Not an admin level player");
  echo $errResp;
  exit;
} 
require_once('config.php');

$link = @mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'boxGet: Error 1: ' . mysqli_connect_error();
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
$boxid = intval(clean($link, $_REQUEST['boxid']));

//Get record from the box table.
$qry1 = "SELECT * FROM box WHERE box_id=$boxid";
$result1 = mysqli_query($link, $qry1);
if (!$result1 || mysqli_num_rows($result1) === 0) {
  error_log("boxGet: SELECT FROM box for $boxid - Query failed");
  echo $errResp;
  exit;
} else {
  $boxrow = mysqli_fetch_assoc($result1);
  $boxResp = new Response();
  $boxResp ->boxid  = $boxrow['box_id'];
  $boxResp ->bname = $boxrow['bname'];
  $boxResp ->version = $boxrow['version'];
  $boxResp ->cdate = $boxrow['create_date'];
  $boxResp ->adate = $boxrow['activity_date'];
  $boxResp ->author = $boxrow['author'];
  $boxResp ->status = $boxrow['status'];
}

$qry3 = "SELECT game_id, gname, status FROM game 
           WHERE box_id=$boxrow[box_id]";
$result3 = mysqli_query($link, $qry3);
if ($result3) {
  if (mysqli_num_rows($result3) === 0) { // No games for box!
    $boxResp ->stat = "nogames";
  } else {
    $boxResp ->stat = "success";
    $gamelist =  array();
    $ii = 0;
    while ($gamerow = mysqli_fetch_assoc($result3)) {
      $gamelist[$ii] = new Gameline();
      $gamelist[$ii] ->gameid = $gamerow['game_id'];
      $gamelist[$ii] ->gname = $gamerow['gname'];
      $gamelist[$ii] ->status = $gamerow['status'];
      $ii += 1;
    }
    $boxResp ->games = $gamelist;
  }
  echo json_encode($boxResp);
  exit;
} else {
  error_log("boxGet: SELECT game_id - Query failed");
  echo $errResp;
  exit;
} 
?>
