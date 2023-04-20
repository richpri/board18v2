#!/usr/bin/php
<?php

if ($argc != 2 || 
        in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

This is a command line PHP script with one input parameter.
It will update the json string in an existing game box.

  Usage:
  <?php echo $argv[0]; ?> jsonfile

The positional input parameter jsonfile must be the
the qualified file name of a JSON game box file. 
This game box must already exist in the database!

<?php
  exit;
} 
$jsonstring = file_get_contents($argv[1]);
if (!$jsonstring) {
  die('Open failed on file ' . $argv[1] . ' DB update was not done.');
  exit; // just in case
}
$decoded = json_decode($jsonstring,TRUE);
$bname = $decoded["bname"];
$version = $decoded["version"];
require_once('config.php');
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if ( !$link ) {
	error_log('Failed to connect to server: ' . mysqli_connect_error());
	die( 'Connect error: (' . mysqli_connect_errno() . ') ' 
          . mysqli_connect_error() );
	exit; // just in case
}

$qry1 = "SELECT box_id FROM box 
         WHERE bname = '$bname' AND version = '$version';"; 
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  $logMessage = 'MySQL Error 1: ' . mysqli_error($link);
  die( $logMessage);
  exit; // just in case
}
if (mysqli_num_rows($result1) !== 1) {
  die('Game box ' . $bname . ' does not exist in data base.');
  exit; // just in case
}
$arr1 = mysqli_fetch_array($result1);
$boxid = $arr1[0]; // box_id

$qry2 = "UPDATE box SET json_text = '$jsonstring' 
   WHERE box_id = '$boxid';";
$result2 = mysqli_query($link, $qry2);
if (!$result2) {   // If query failed
  $logMessage = 'MySQL Error 2: ' . mysqli_error($link);
 die( $logMessage);
 exit; // just in case
}
  
$qry3 = "SELECT bname, version, author, activity_date 
   FROM box WHERE box_id = '$boxid';";
$result3 = mysqli_query($link, $qry3);
if (!$result3 || (mysqli_num_rows($result3) !== 1)) {
  $logMessage = 'MySQL Error 3: ' . mysqli_error($link);
 die( $logMessage);
 exit; // just in case
}
$arr3 = mysqli_fetch_array($result3);
$boxname = $arr3[0]; // bname
$version = $arr3[1]; // version
$author = $arr3[2];  // author
$date = $arr3[3];    // date
?>

Successfully updated the json string in 
game box table row <?php echo $boxid; ?>.
The box name is <?php echo $boxname; ?>.
The version is <?php echo $version; ?>.
The author is <?php echo $author; ?>.
The date is <?php echo $date; ?>.
