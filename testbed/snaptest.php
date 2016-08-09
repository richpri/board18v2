#!/usr/bin/php
<?php

if ($argc != 3 || 
        in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

This is a command line PHP script with two positional input parameters.
          Parameter1 is a numeric game id.
          Parameter2 is the count of game_snap records to be added.
          
The script will add the specified number of dummy game_snap records  
to the game with the supplied game id.

  Usage:
  <?php echo $argv[0]; ?> gameid,addcount

<?php
  exit;
} 
echo "Start.\n";

$gameid = $argv[1];
$snapcount = $argv[2] - 1;
require_once('config.php');
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if ( !$link ) {
	echo "Failed to connect to server\n";
	exit; // just in case
}

$qry1 = "SELECT * FROM game WHERE game_id='$gameid';"; 
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  echo "MySQL Error 1\n";
  exit; // just in case
}
echo "Game query OK.\n";
$row1 = mysqli_fetch_row($result1);
$gname = $row1[1];
$boxid = $row1[6];
$jtxt = '{ "gname": "';
$jtxt .= $gname;
$jtxt .= '", "boxID": "';
$jtxt .= $boxid;
$jtxt .= '", "brdTls": [], "brdTks": [], ';
$jtxt .= '"mktTks": [], "trayCounts": []}';
echo "Entering loop.\n";
for ($x = 0; $x <= $snapcount; $x++) {
  $roundname = "Dummy " . $x;
  $qry2 = "INSERT INTO game_snap 
            SET game_id='$gameid',
                player='Rich',  
                last_updater='Rich',
                game_round='$roundname',
                json_text='$jtxt'";  
  $result2 = mysqli_query($link, $qry2);
  if (!$result2) {
  echo "MySQL Error 2\n";
    exit; // just in case
  }
  echo "Record $x created.\n";
}
