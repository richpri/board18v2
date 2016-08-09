#!/usr/bin/php
<?php
if ($argc == 1) {
  $cfg = "config.php";
}
elseif ($argc == 2) {
  $cfg = $argv[1];
}
elseif ($argc > 2) {
?>

This is a command line PHP script with one optional parameter.

  Usage:
  <?php echo $argv[0]; ?> configfile

The optional input parameter configfile must be the file 
name of a BOARD18 config.php file in the utility directory.

<?php
  exit;
} 

require_once($cfg);
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if ( !$link ) {
	error_log('Failed to connect to server: ' . mysqli_connect_error());
	die( 'Connect error: (' . mysqli_connect_errno() . ') ' 
          . mysqli_connect_error()  . "\n");
	exit; // just in case
}

$qry1 = "SELECT * FROM game ORDER BY game_id;"; 
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  $logMessage = 'MySQL Error 1: ' . mysqli_error($link) . "\n";
  die( $logMessage);
  exit; // just in case
}

while ($row1 = mysqli_fetch_row($result1)) {
  $gameid = $row1[0];
  $gname = $row1[1];
  $startdate = $row1[2];
  $activitydate = $row1[3]; 
  $updatecounter = $row1[4];
  $lastupdater = $row1[5]; 	
  $boxid = $row1[6];
  printf ("Game: %s (%s)\n", $gameid, $gname);
  printf ("Last Activity Date: %s ", $activitydate);
  printf ("(%s total updates)\n", $updatecounter);
  $qry2 = "SELECT * FROM box WHERE box_id = '$boxid';";
  $result2 = mysqli_query($link, $qry2);
  if (!$result2 || (mysqli_num_rows($result2) !== 1)) {
    $logMessage = 'MySQL Error 2: ' . mysqli_error($link) . "\n";
    die( $logMessage);
    exit; // just in case
  }
  $arr2 = mysqli_fetch_array($result2);
  $boxname = $arr2[1]; 
  $version = $arr2[2]; 
  $author = $arr2[5];  
  printf ("Using Game Box: %s (%s)\n", $boxname, $version);
  
  $qry3 = "SELECT * FROM game_player WHERE game_id = '$gameid';";
  $result3 = mysqli_query($link, $qry3);
  if (!$result3) {
    $logMessage = 'MySQL Error 3: ' . mysqli_error($link) . "\n";
    die( $logMessage);
    exit; // just in case
  }
  printf ("Players:\n");
  while ($arr3 = mysqli_fetch_array($result3)) {
    $playerid = $arr3[1]; 
    $status = $arr3[2];
    $qry4 = "SELECT * FROM players WHERE player_id = '$playerid';";
    $result4 = mysqli_query($link, $qry4);
    if (!$result4) {
      $logMessage = 'MySQL Error 4: ' . mysqli_error($link) . "\n";
      die( $logMessage);
      exit; // just in case
    }
    $arr4 = mysqli_fetch_array($result4);
    $first = $arr4[1];
    $last = $arr4[2]; 
    $email = $arr4[3];	
    $login = $arr4[4];
    $name = $first . " " . $last;
    printf ("  %s (%s) %s %s\n", $login, $name, $email, $status);
  }
  printf ("\n");  
}
?>
