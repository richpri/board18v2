<?php
/*
 * This is the server side code for the AJAX playerSort call.
 * 
 * It returns a sorted array of records from the players table.
 * 
 * Input is: sortkey.
 * 
 * Output is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||fail||empty",
 *     "players":
 *     [
 *       {
 *         "login":"aaaaaa",
 *         "firstname":"bbbbbb",
 *         "lastname":"cccccc",
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
  public $login;
  public $firstname;
  public $lastname;
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

// set up empty return object.
$emptResp = new Response();
$emptResp->stat = "empty";
$emptyResp = json_encode($emptResp);

require_once('auth.php');
require_once('config.php');

$link = mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'playerSort.php: MySQL connect error: ' . mysqli_connect_error();
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

//Sanitize the POST value.
$sortkey = clean($link, $_REQUEST['sortkey']);
$fullkey = "login";
//Check for invalid sort key.
if ($sortkey !== "login" && $sortkey !==  "firstname" && $sortkey !==  "lastname") {
  error_log("playerSort.php: invalid sort key.");
  echo $errResp;
  exit;
};
// Generate full sort key.
if ($sortkey ===  "firstname") {
  $fullkey = "firstname, login";
} else if ($sortkey ===  "lastname") {
  $fullkey = "lastname, login";
}

//Get records from the players table.
$qry1 = "SELECT login, firstname, lastname 
         FROM players ORDER BY $fullkey";
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  error_log("playerSort.php: SELECT FROM players - Query failed");
  echo $errResp;
  exit;
}
if (mysqli_num_rows($result1) === 0) {
  echo $emptyResp;
  exit;
}
 
$playerlist = array();
$ii = 0;
while ($row1 = mysqli_fetch_array($result1)) {
  $playerlist[$ii] = new Playerline();
  $playerlist[$ii]->login = $row1[0];
  $playerlist[$ii]->firstname = $row1[1];
  $playerlist[$ii]->lastname = $row1[2];
  $ii += 1;
}

$succResp = new Response();
$succResp->stat = "success";
$succResp->players = $playerlist;
echo json_encode($succResp, JSON_PARTIAL_OUTPUT_ON_ERROR);
exit;

