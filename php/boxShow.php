<?php
/*
 * This is the server side code for the AJAX boxShow call.
 * 
 * It returns an array of records from the box table.
 * 
 * Input is: blocksz and startrow.
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
 *         "adate":"yyy-mm-dd hh:mm:ss",
 *         "author":"eeeeee",
 *         "status":"fffff",
 *         "gcount":"nnnnnn"
 *       },
 *       . . . . more boxes . . . . . 
 *     ]
 *   }
 *
 * Copyright (c) 2016 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * 
 */

class Gameline
{
  public $boxid;
  public $bname;
  public $version;
  public $cdate;
  public $adate;
  public $author;
  public $status;
  public $gcount;
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

require_once('auth.php');
if ($playerlevel != 'admin') {
  error_log("boxShow: Not an admin level player");
  echo $errResp;
  exit;
} 
require_once('config.php');

$link = @mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'boxShow Error 1: ' . mysqli_connect_error();
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

//Get records from the box table.
$qry1 = "SELECT box_id, bname, version, create_date,
         activity_date, author, status FROM box 
         ORDER BY box_id LIMIT $startrow,$blocksize";
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  error_log("boxShow: SELECT FROM box - Query failed");
  echo $errResp;
  exit;
}
if (mysqli_num_rows($result1) === 0) {
  $noneResp = new Response();
  $noneResp->stat = "noboxes";
  echo json_encode($noneResp);
  exit;
}
$boxlist = array();
$ii = 0;
while ($row1 = mysqli_fetch_assoc($result1)) {
  $boxlist[$ii] = new Gameline();
  $boxlist[$ii]->boxid = $row1['box_id'];
  $boxlist[$ii]->bname = $row1['bname'];
  $boxlist[$ii]->version = $row1['version'];
  $boxlist[$ii]->cdate = $row1['create_date'];
  $boxlist[$ii]->adate = $row1['activity_date'];
  $boxlist[$ii]->author = $row1['author'];
  $boxlist[$ii]->status = $row1['status'];
  $qry3 = "SELECT COUNT(*) FROM game 
           WHERE box_id=$row1[box_id]";
  $result3 = mysqli_query($link, $qry3);
  if ($result3) {
    $countrow = mysqli_fetch_row($result3);
    $boxlist[$ii]->gcount = $countrow[0];
  } else {
    error_log("boxShow: SELECT COUNT(*) FROM box_player - Query failed");
    echo $errResp;
    exit;
  }
  $ii += 1;
}

$succResp = new Response();
$succResp->stat = "success";
$succResp->boxes = $boxlist;
echo json_encode($succResp, JSON_PARTIAL_OUTPUT_ON_ERROR);
exit;
?>

