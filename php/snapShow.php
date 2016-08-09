<?php
/*
 * This is the server side code for the AJAX snapShow call.
 * 
 * It returns an array of the game_snap records for this game.
 * 
 * Input is: gameid, blocksz and startrow.
 * 
 * Output is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||none||fail",
 *     "snaps":
 *     [
 *       {
 *         "cp_id":"nnnn",
 *         "round":"gggg",
 *         "snaper":"bbbb",
 *         "updater":"vvvv",
 *         "cp_date":"mm/dd/yyyy"
 *       },
 *       . . . . more snaps . . . . . 
 *     ]
 *   }
 *
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * 
 */

require_once('auth.php');
require_once('config.php');

class Snapline
{
  public $cp_id;
  public $round;
  public $snaper;
  public $updater;
  public $cp_date; 
}
class Response
{
  public $stat;
  public $snaps;
}

// set up fail return object.
$errorResp = new Response();
$errorResp->stat = "fail";
$errResp = json_encode($errorResp);

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
$gameid = clean($link, $_REQUEST['gameid']);
$blocksize = clean($link, $_REQUEST['blocksz']);
$startrow = clean($link, $_REQUEST['startrow']);

//Get all game_snap records for game.
$qry1 = "SELECT cp_id, game_round, player, last_updater, cp_date
         FROM game_snap WHERE game_id='$gameid'
         ORDER BY cp_id DESC LIMIT $startrow,$blocksize";
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  error_log("SELECT FROM game_snap - Query failed");
  echo $errResp;
  exit;
}
if (mysqli_num_rows($result1) === 0) { // no games.
  $noneResp = new Response();
  $noneResp->stat = "none";
  echo json_encode($noneResp);
  exit;
} else {
  $snaplist = array();
  $ii = 0;
  while ($row = mysqli_fetch_array($result1)) {
    $snaplist[$ii] = new Snapline();
    $snaplist[$ii]->cp_id = $row[0];
    $snaplist[$ii]->round = $row[1];
    $snaplist[$ii]->snaper = $row[2];
    $snaplist[$ii]->updater = $row[3];
    $snaplist[$ii]->cp_date = $row[4];
    $ii += 1;
  }
}
$succResp = new Response();
$succResp->stat = "success";
$succResp->snaps = $snaplist;
echo json_encode($succResp);
exit;
?>
