#!/usr/bin/php
<?php

if ($argc != 2 || 
        in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>
This script is deprecated. Use Load Game Box function in BOARD18 instead.

This is a command line PHP script with one option.

  Usage:
  <?php echo $argv[0]; ?> jsonfile

The positional input parameter jsonfile must be the
qualified file name of a JSON game box file.

<?php
  exit;
} 
$jsonstring = file_get_contents($argv[1]);
if (!$jsonstring) {
  die('Open failed on file ' . $argv[1] . 'DB update was not done.');
  exit; // just in case
}
$decoded = json_decode($jsonstring,TRUE);
$bname = $decoded["bname"];
$ver = $decoded["version"];
$auth = $decoded["author"];
require_once('config.php');
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if ( !$link ) {
	error_log('Failed to connect to server: ' . mysqli_connect_error());
	die( 'Connect error: (' . mysqli_connect_errno() . ') ' 
          . mysqli_connect_error() );
	exit; // just in case
}
$qry1 = "INSERT INTO box SET bname = '$bname',version = '$ver',
   author = '$auth',json_text = '$jsonstring';";
$result1 = mysqli_query($link, $qry1);
if (!$result1) {   // If query failed
  $logMessage = 'MySQL Error 1: ' . mysqli_error($link);
 die( $logMessage);
 exit; // just in case
}   
$qry2 = "UPDATE box SET create_date = activity_date 
   WHERE box_id = LAST_INSERT_ID();";
$result2 = mysqli_query($link, $qry2);
if (!$result2) {   // If query failed
  $logMessage = 'MySQL Error 2: ' . mysqli_error($link);
 die( $logMessage);
 exit; // just in case
}   
$qry3 = "SELECT box_id, bname, version, author, activity_date 
   FROM box WHERE box_id = LAST_INSERT_ID();";
$result3 = mysqli_query($link, $qry3);
if (!$result3 || (mysqli_num_rows($result3) !== 1)) {
  $logMessage = 'MySQL Error 3: ' . mysqli_error($link);
 die( $logMessage);
 exit; // just in case
}
$arr3 = mysqli_fetch_array($result3);
$boxid = $arr3[0]; // box_id
$boxname = $arr3[1]; // bname
$version = $arr3[2]; // version
$author = $arr3[3];  // author
$date = $arr3[4];    // date
?>

Successfully created game box table row <?php echo $boxid; ?>.
The box name is <?php echo $boxname; ?>.
The version is <?php echo $version; ?>.
The author is <?php echo $author; ?>.
The date is <?php echo $date; ?>.

