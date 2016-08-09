<?php
 /*
 * This is the server side code for the AJAX gameSnap call.
 * 
 * It returns a snapshot of  game activity information in 
 * JSON format.
 * 
 * Input is the snapshot id.
 * 
 * Output is JSON game session data.
 */

require_once('auth.php');
require_once('config.php');
$link = @mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'MySQL Error: ' . mysqli_connect_error();
  error_log($logMessage);
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
$cpid = clean($link, $_REQUEST['snapshot']);

//Check for valid snapshot ID and get JSON text for snapshot.
$qry1 = "SELECT json_text FROM game_snap WHERE cp_id='$cpid'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) == 0) { // Invalid snapshot ID!
  error_log("Check for valid snapshot ID failed: " . $cpid);
  $_SESSION['SESS_HEADER_MESSAGE'] = 
    'The selected snapshot is not in the data base!';
  header("location: board18Main.php");
  }
} else {
  error_log("Check for valid snapshot ID: Query failed");
  exit;
}

$ss = mysqli_fetch_array($result1); 
echo $ss[0]; // $ss[0] is json_data
?>

