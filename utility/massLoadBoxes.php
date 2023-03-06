#!/usr/bin/env  php
<?php
/* 
 * This is a command line script to upload all game boxes in a 
 * directory to the instance of BOARD18 specified in the 'root' 
 * parameter. Both the root and the directory must be local to 
 * this script. The rptfile will be initialized and will contain 
 * the reports created by loadGameBox.php for each input file.
 * 
 * Copyright (c) 2016 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
if ($argc != 6) {
?>

This is a command line PHP script with five required parameters.

  Usage:
  <?php echo $argv[0]; ?> directory root player password rptfile

'directory' should be the fully qualified name of the game box directory.
'root' should be the fully qualified name of the BOARD18 root directory.
'player' should be the player ID of an admin level player for this BOARD18.
'password' must be the password for this admin level player.
'rptfile' must be the fully qualified name of the report file to be created.

<?php
  exit;
} 

// var_dump($argv); // debugging
$dir = $argv[1];
$root = $argv[2];
$admin = $argv[3];
$passwd = $argv[4];
$rptfile = $argv[5];

if (!is_dir($dir)) {
  $logMessage = $dir . " is not a directory.\n ";
  die( $logMessage);
  exit; // just in case 
}
if (!is_dir($root)) {
  $logMessage = $root . " is not a directory.\n ";
  die( $logMessage);
  exit; // just in case 
}
$topTest = $root . "/board18Main.php";
$imgTest = $root . "/images";
$phpTest = $root . "/php";
if (!file_exists($topTest)||!file_exists($imgTest)||!file_exists($phpTest)) {
  $logMessage = $root . " is not a BOARD18 directory.\n ";
  die( $logMessage);
  exit; // just in case 
}
if (file_exists($rptfile) && is_dir($rptfile)) {
  $logMessage = $rptfile . " is an existing directory.\n ";
  die( $logMessage);
  exit; // just in case   
}
$rptHand = fopen($rptfile, "w");
if (!$rptHand) {
  $logMessage = $rptfile . " could not be opened.\n ";
  die( $logMessage);
  exit; // just in case   
}

// Verify that user is a valid admin for this BOARD18 instance.
require_once($phpTest . '/config.php');
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if ( !$link ) {
	error_log('Failed to connect to server: ' . mysqli_connect_error());
	die( 'Connect error: (' . mysqli_connect_errno() . ') ' 
          . mysqli_connect_error()  . "\n");
	exit; // just in case
}
$qry1 = "SELECT * FROM players WHERE login = '$admin';"; 
$result1 = mysqli_query($link, $qry1);
if (!$result1 || mysqli_num_rows($result1) !== 1) {
  $logMessage = "Player not found.\n ";
  die( $logMessage);
  exit; // just in case
}
$row1 = mysqli_fetch_assoc ($result1);
if (hash('sha256', $passwd) !== $row1["passwd"]) {
  $logMessage = "Invalid password. \n ";
  die( $logMessage);
  exit; // just in case 
}
$playerID = $row1["player_id"];
require_once($phpTest . '/rm_r.php');
require_once($phpTest . '/loadGameBox.php');

/* 
 * function rptLine($line)
 * reptLine writes a line of text to the file pointed to by $rptHand.
 * Input is $line - an unterminated line of text.
 */
function rptLine($line) {
  global $rptHand, $rptfile;
  $line .= "\n";
  $rptStat = fwrite ($rptHand, $line);
  if (!$rptStat || ($rptStat === 0)) {
    fclose ($rptHand);
    $logMessage =  " Write error on" . $rptfile . ".\n";
    die( $logMessage);
    exit; // just in case   
  }
}

// initialize the report file.
rptLine("Mass Game Box Load Report from massLoadBoxes.php on " . date("M j,Y"));
rptLine("");
rptLine("BOARD18 web root is " . $root);
rptLine("Directory containing Game Box files is " . $dir);
rptLine("Admin submitting this job is " . $admin);
rptLine("");

function deleteDir($dirPath) { //Recursive function
  $exfiles = glob($dirPath . '*', GLOB_MARK);
  foreach ($exfiles as $exfile) { 
    if (is_dir($exfile)) {
        deleteDir($exfile); //remove sub directory
    } else {
        unlink($exfile); //remove file
    }
  }
  rmdir($dirPath); //remove directory
}

$nextFile = new DirectoryIterator($dir);
foreach ($nextFile as $fileinfo) {
  if ($fileinfo->isFile()) {
    $fileN1 = $fileinfo->getFilename();
    $fileN2 = $fileinfo->getPath();
    rptLine("");
    rptLine("Starting to process file " . $fileN1);
    $result = nextBox($fileN1,$fileN2,$playerID,$root,$link);
    $tempdir = $fileN2 . '/' . $fileN1 . 'extract/';
    if (is_dir($tempdir)) {
      deleteDir($tempdir); //remove temporary extract directory
    }
    $jsonVar = json_decode($result,true);
//    var_dump($jsonVar);
   if ($jsonVar['stat'] === "success") {
      print $fileN1 ." was processed successfully.\n";  
    } else {
      print $fileN1 ." was not processed successfully.\n";
    }
    foreach ($jsonVar['rpttext'] as $l){
      rptLine($l);
    }
  }
}
Print "End of Process.\n";
rptLine("");
rptLine("");
rptLine("End of Process.");
rptLine("");
fclose ($rptHand);
?>
