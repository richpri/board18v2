<?php
/*
 * This is the server side code for the AJAX allGameList call.
 * 
 * It produces the data needed to create a list of all games.
 * 
 * There are no input parameters.
 *
 * Output is the following stringified JSON data structure. 
 *   {
 *     "stat":"success",
 *     "games":
 *     [
 *       {
 *         "game_id":"nnnn",
 *         "gname":"gggg",
 *         "bname":"bbbb",
 *         "version":"vvvv",
 *         "start_date":"mm/dd/yyyy"
 *       },
 *       . . . . more games . . . . . 
 *     ]
 *   }
 *
 * Copyright (c) 2014 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
require_once('auth.php');
require_once('config.php');

class Gameline
{
  public $game_id;
  public $gname;
  public $bname;
  public $version;
  public $start_date; 
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

$link = @mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'allGameList: Failed to connect to server: ';
  $logMessage .= mysqli_connect_error();
  error_log($logMessage);
  echo $errResp;
  exit;
}

$you = intval($_SESSION['SESS_PLAYER_ID']);
$qry = "SELECT a.game_id, a.gname, b.bname, 
               b.version, DATE(a.start_date) 
          FROM game AS a JOIN box AS b
               ON a.box_id = b.box_id
          ORDER BY a.start_date DESC";
$result = mysqli_query($link,$qry);
if ($result) {
  if (mysqli_num_rows($result) === 0) { // no games.
    $noneResp = new Response();
    $noneResp->stat = "none";
    echo json_encode($noneResp);
    exit;
  } else {
    $gamelist = array();
    $ii = 0;
    while ($row = mysqli_fetch_array($result)) {
      $gamelist[$ii] = new Gameline();
      $gamelist[$ii]->game_id = $row[0];
      $gamelist[$ii]->gname = $row[1];
      $gamelist[$ii]->bname = $row[2];
      $gamelist[$ii]->version = $row[3];
      $gamelist[$ii]->start_date = $row[4];
      $ii += 1;
    }
    $succResp = new Response();
    $succResp->stat = "success";
    $succResp->games = $gamelist;
    
    echo json_encode($succResp, JSON_PARTIAL_OUTPUT_ON_ERROR);
    exit;
  }
} else {
  $logMessage = 'allGameList: Error on SELECT query: ' . mysqli_error($link);
  error_log($logMessage);
  echo $errResp;
}
