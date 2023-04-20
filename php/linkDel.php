<?php
/*
 * This is the server side code for the AJAX linkDel call.
 * 
 * It deletes a game_link record from the game_link table.
 * 
 * Input is: gameid and linkname.
 * 
 * Output is the echo return status: 
 *   "success", "fail" or "missing". 
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
  $logMessage = 'linkDel: MYSQL connect error: ' . mysqli_connect_error();
  error_log($logMessage);
  echo "fail";
  exit;
}
mysqli_set_charset($conn, "utf8mb4");
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

// Start transaction.
$qry1 = "START TRANSACTION";
$result1 = mysqli_query($conn, $qry1);
if (!$result1) {
  $logMessage = 'linkDel: START TRANSACTION error.';
  error_log($logMessage);
  echo "fail";
  exit;
}

//Get game_link record.
$qry2 = "SELECT * FROM game_link 
                  WHERE game_id='$gameid' AND link_name='$linkname'";
$result2 = mysqli_query($conn, $qry2);
if (!$result2) {
  error_log("linkDel: SELECT FROM game_link - Query failed");
  echo "fail";
  exit;
}
if (mysqli_num_rows($result2) === 0) { // missing.
  echo "missing";
  exit;
}

//Create DELETE query 
$qry3 = "DELETE FROM game_link 
         WHERE game_id='$gameid' AND link_name='$linkname'";
$result3 = mysqli_query( $conn, $qry3);
if(!$result3) {   // Was the query successful
  error_log("linkDel: Delete link: Query failed. ");
  error_log("linkDel: $qry3");
  mysqli_query($conn, $qry0); // ROLLBACK
  echo 'fail';
  exit;
}

$qry4 = "COMMIT";
echo "success";
mysqli_query($conn, $qry4); // COMMIT
exit;
?>
