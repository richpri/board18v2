<?php
/*
 * This is the server side code for the AJAX boxGetAll call.
 * 
 * It returns an array of all active records in the box table.
 * 
 * No input parameters are used.
 * 
 * Output is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||"noboxes"||"fail",
 *     "boxes":
 *     [
 *       {
 *         "boxid":"nnnn",
 *         "bname":"aaaaaa", 
 *         "version":"vvvvvv",
 *         "cdate":"yyy-mm-dd hh:mm:ss",
 *         "author":"eeeeee",
 *         "status":"fffff"
 *       },
 *       . . . . more boxes . . . . . 
 *     ]
 *   }
 *
 * Copyright (c) 2020 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * 
 */

class Gameline
{
  public $boxid;
  public $bname;
  public $version;
  public $cdate;
  public $author;
}
class Response
{
  public $stat;
  public $boxes;
}

// set up fail return object.
$errorResp = new Response();
$errorResp->stat = "fail";
$errResp = json_encode($errorResp);

// set up noboxes return object.
$noResp = new Response();
$noResp->stat = "noboxes";
$noBoxesResp = json_encode($noResp);

require_once('auth.php');
require_once('config.php');

$link = mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'boxGetAll.php connect error: ' . mysqli_connect_error();
  error_log($logMessage);
  echo $errResp;
  exit;
}
mysqli_set_charset($link, "utf8mb4");

//Get records from the box table.
$qry1 = "SELECT box_id, bname, version, create_date,
         author, status FROM box ORDER BY bname";
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  error_log("boxGetAll: SELECT FROM box - Query failed");
  echo $errResp;
  exit;
}
if (mysqli_num_rows($result1) === 0) {
  echo $noBoxesResp;
  exit;
}

$boxlist = array();
$ii = 0;
while ($row1 = mysqli_fetch_assoc($result1)) {
  if ($row1['status'] === "Disabled") {continue;}
  $boxlist[$ii] = new Gameline();
  $boxlist[$ii]->boxid = $row1['box_id'];
  $boxlist[$ii]->bname = $row1['bname'];
  $boxlist[$ii]->version = $row1['version'];
  $boxlist[$ii]->cdate = $row1['create_date'];
  $boxlist[$ii]->author = $row1['author'];
  $ii += 1;
}

$succResp = new Response();
$succResp->stat = "success";
$succResp->boxes = $boxlist;
echo json_encode($succResp, JSON_PARTIAL_OUTPUT_ON_ERROR);
exit;
