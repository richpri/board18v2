<?php
/*
 * This is the server side code for the AJAX linkGet call.
 * 
 * It returns an array of the game_link records for this game.
 * 
 * Input is: gameid.
 * 
 * Output is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||none||fail",
 *     "links":
 *     [
 *       {
 *         "link_name":"aaaaaa",
 *         "link_url":"bbbbbbb",
 *         "act_date":"mm/dd/yyyy"
 *       },
 *       . . . . more links . . . . . 
 *     ]
 *   }
 *
 * Copyright (c) 2016 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * 
 */

require_once('auth.php');
require_once('config.php');

class Linkline
{
  public $link_name;
  public $link_url;
  public $act_date; 
}
class Response
{
  public $stat;
  public $links;
}

// set up fail return object.
$errorResp = new Response();
$errorResp->stat = "fail";
$errResp = json_encode($errorResp);

$conn = mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'linkGet: MySQL Connect Error: ' . mysqli_connect_error();
  error_log($logMessage);
  echo $errResp;
  exit;
}
mysqli_set_charset($conn, "utf8mb4");

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($conn, $str1) {
  $str = trim($str1);
  return mysqli_real_escape_string($conn, $str);
}

//Sanitize the POST value
$gameid = clean($conn, $_REQUEST['gameid']);

//Get all game_link records for game.
$qry1 = "SELECT link_name, link_url, activity_date
         FROM game_link WHERE game_id='$gameid'";
$result1 = mysqli_query($conn, $qry1);
if (!$result1) {
  error_log("linkGet: SELECT FROM game_link - Query failed");
  echo $errResp;
  exit;
}
if (mysqli_num_rows($result1) === 0) { // no links.
  $noneResp = new Response();
  $noneResp->stat = "none";
  echo json_encode($noneResp);
  exit;
} else {
  $linklist = array();
  $ii = 0;
  while ($row = mysqli_fetch_array($result1)) {
    $linklist[$ii] = new Linkline();
    $linklist[$ii]->link_name = $row[0];
    $linklist[$ii]->link_url = $row[1];
    $linklist[$ii]->act_date = $row[2];
    $ii += 1;
  }
}
$succResp = new Response();
$succResp->stat = "success";
$succResp->links = $linklist;
echo json_encode($succResp, JSON_PARTIAL_OUTPUT_ON_ERROR);
exit;
