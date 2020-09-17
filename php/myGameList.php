<?php
/*
 * This is the server side code for the AJAX myGameList call.
 * 
 * It produces the data needed to create a list of all active
 * games that the signed in player is playing.
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
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
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

$link = mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'myGameList.php: MySQL connect error: ' . mysqli_connect_error();
  error_log($logMessage);
  echo $errResp;
  exit;
}

$you = intval($_SESSION['SESS_PLAYER_ID']);
$qry = "SELECT b.game_id, b.gname, c.bname, 
               c.version, DATE(b.start_date) 
          FROM game_player AS a 
            JOIN (game AS b, box AS c)
              ON (a.player_id = $you
                AND a.game_id = b.game_id
                AND b.status = 'Active'
                AND b.box_id = c.box_id)
          ORDER BY b.start_date DESC";
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
  $logMessage = 'myGameList.php: Error on SELECT query.';
  error_log($logMessage);
  echo $errResp;
}