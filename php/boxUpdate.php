<?php
/*
 * boxUpdate.php is the server side code for the 
 * AJAX boxUpdate call. 
 * 
 * It updates the name, the version, the author and/or
 * the status fields in a row in the box table.
 * 
 * Input consists the following parameters:
 * 
 *   boxid  
 *   bname
 *   version
 *   author
 *   status
 * 
 * Output is the echo return status: 
 *   "success", "fail", "bname". 
 * 
 * Copyright (c) 2016 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('auth.php');
if ($playerlevel != 'admin') {
  error_log("boxUpdate: Not an admin level player");
  echo "fail";
  exit;
} 
require_once('config.php');

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($conn, $str) {
  $str = @trim($str);
  return mysqli_real_escape_string($conn, $str);
}

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
  $errmsg1 = 'boxUpdate failed to connect to server: ';
  error_log($errmsg1 . mysqli_connect_error());
  echo 'fail';
	exit; 
}

//Sanitize the POST values 
$boxid = clean($link, $_REQUEST['boxid']);
$bname = clean($link, $_REQUEST['bname']);
$version = clean($link, $_REQUEST['version']);
$author = clean($link, $_REQUEST['author']);
$status = clean($link, $_REQUEST['status']);

//Check for existing box
$qry1 = "SELECT * FROM box WHERE box_id ='$boxid'";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) === 0) { // no such box!
    error_log("boxUpdate: Check for existing box: No box found!");
    echo 'fail';
    exit;
  } else {
    $boxrow = mysqli_fetch_assoc($result1);
  }
} else {
  error_log("boxUpdate: Check for existing box: Query failed");
  echo 'fail';
  exit;
}

// If box name or version is different.
if ($boxrow['bname'] !== $bname || $boxrow['version'] !== $version) { 
  //Check for duplicate 
  $qry2 = "SELECT * FROM box WHERE bname='$bname' AND version='$version'";
  $result2 = mysqli_query($link, $qry2);
  if ($result2) {
    if (mysqli_num_rows($result2) > 0) { // duplicate 
      echo 'gname';
      exit;     
    }
  } else {
    error_log("boxUpdate: Check duplicate: Query failed");
    echo 'fail';
    exit;
  }
}

//Create UPDATE query
$qry = "UPDATE box SET bname='$bname', version='$version',
          status='$status', author='$author'
          WHERE box_id=$boxid";
$result = @mysqli_query($link, $qry);
if ($result) {   // Was the query successful
  echo 'success';
} else {
  error_log("boxUpdate: Update box: Query failed");
  echo 'fail';
}
?>
