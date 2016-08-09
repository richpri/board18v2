<?php
/*
 * This is the server side code for the AJAX linkAdd call.
 * 
 * It adds a game_link record to the game_link table.
 * 
 * Input is: gameid, linkname and linkurl.
 * 
 * Output is the echo return status: 
 *   "success", "fail" or "duplicate". 
 * 
 * Copyright (c) 2016 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * 
 */

require_once('auth.php');
require_once('config.php');

$conn = @mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'MySQL Error 1: ' . mysqli_connect_error();
  error_log($logMessage);
  echo "fail";
  exit;
}
mysqli_set_charset($conn, "utf-8");
$qry0 = "ROLLBACK";

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($conn, $str) {
  $str = @trim($str);
  return mysqli_real_escape_string($conn, $str);
}

//Sanitize the POST value
$gameid = clean($conn, $_REQUEST['gameid']);
$linkname = clean($conn, $_REQUEST['linkname']);
$linkurl = clean($conn, $_REQUEST['linkurl']);

// Start transaction.
$qry1 = "START TRANSACTION";
$result1 = mysqli_query($conn, $qry1);
if (!$result1) {
  $logMessage = 'MySQL Error 2: ' . mysqli_error($conn);
  error_log($logMessage);
  echo "fail";
  exit;
}

//Get game_link record.
$qry2 = "SELECT * FROM game_link 
                  WHERE game_id='$gameid' AND link_name='$linkname'";
$result2 = mysqli_query($conn, $qry2);
if (!$result2) {
  error_log("SELECT FROM game_link - Query failed");
  echo "fail";
  exit;
}
if (mysqli_num_rows($result2) !== 0) { // duplicate.
  echo "duplicate";
  exit;
}

//Create INSERT query
$qry3 = "INSERT INTO game_link SET game_id='$gameid', 
         link_name='$linkname', link_url='$linkurl'";
$result3 = @mysqli_query( $conn, $qry3);
if(!$result3) {   // Was the query successful
  error_log("Insert new link: Query failed");
  mysqli_query($conn, $qry0); // ROLLBACK
  echo 'fail';
  exit;
}

$qry4 = "COMMIT";
echo "success";
mysqli_query($conn, $qry4); // COMMIT
exit;
?>
