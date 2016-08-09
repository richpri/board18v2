<?php
/*
 * This is a command line program for dumping
 * formatted game table records.
 */

require_once('../php/config.php');
$link = @mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'MySQL Error 1: ' . mysqli_connect_error();
  error_log($logMessage);
  exit($logMessage);
}
mysqli_set_charset($link, "utf-8");

// Open an output dump file. Overwrite previous contents.
$filename = 'dumpfile.txt';
$mode = 'w';
$dump = fopen ($filename,$mode);
if ($dump == FALSE) {
  $logMessage = 'File Open Error ';
  error_log($logMessage);
  exit($logMessage);
}

//Read each game row and get gameid ID and JSON text for the game.
$qry1 = "SELECT game_id, json_text FROM game";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  if (mysqli_num_rows($result1) != 0) { 
    $format1 = "\nGame_id is %s: \n";
    while ($row = mysqli_fetch_array($result1)) {
      $dumpheader = sprintf($format1, $row[0]);
      $headerOK = fwrite ($dump, $dumpheader);
      if ($headerOK == FALSE) {
        $logMessage = 'File Write Error ';
        error_log($logMessage);
        exit($logMessage);
      }
      $dumprecord = print_r(json_decode($row[1], true), true);
      $fileOK = fwrite ($dump, $dumprecord);
      if ($fileOK == FALSE) {
        $logMessage = 'File Write Error ';
        error_log($logMessage);
        exit($logMessage);
      }
    }
    fclose ($dump);
  } else {
// Game table is empty.
  $dumprecord = 'The Game table is empty!\n';
  $fileOK = fwrite ($dump, $dumprecord);
  if ($fileOK == FALSE) {
    $logMessage = 'File Write Error ';
    error_log($logMessage);
    exit($logMessage);
  }
  fclose ($dump);
  exit;
  }
} else {
  $logMessage = 'MySQL Error 2: ' . mysqli_error($link);
  error_log($logMessage);
  exit($logMessage);
}

?>