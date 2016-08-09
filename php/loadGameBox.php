<?php
/*
 * loadGameBox.php is a required module for board18BoxLoad.php.
 * 
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * 
 * ------------------------
 * 
 * function loadBox($zfile,$authorID)
 * 
 * loadBox validates an input zip file, unzips it and uses its 
 * contents to create a game box or to modify an existing game box. 
 * It returns the output described below with a status and with 
 * optional report information to be emailed to the author.
 * 
 * Input is the zip file containing the game box and the authorID.
 * 
 * Output is the following stringified JSON data structure [object]. 
 *   {
 *     "stat":"success"||"fail"||"nofile"||"toobig"||"email",
 *     "author":authorLogin
 *     "rpttext":
 *     [
 *       "textline"
 *       "textline"
 *       "textline"
 *        . . . . .
 *     ]
 *   }
 */
 

function loadBox($zfile,$authorID) {
  class Response
  {
    public $stat;
    public $rpttext;
  }

  // set up fail return object.
  $errorResp = new Response();
  $errorResp->stat = "fail";
  $errResp = json_encode($errorResp);

  require_once('config.php');


  $link = @mysqli_connect(DB_HOST, DB_USER, 
          DB_PASSWORD, DB_DATABASE);
  if (mysqli_connect_error()) {
    $logMessage = 'MySQL Error 0: ' . mysqli_connect_error();
    error_log($logMessage);
    return $errResp;
  }
  mysqli_set_charset($link, "utf-8");
  
  // Lookup author login in database.
  $qry0 = "SELECT login FROM players 
           WHERE player_id = '$authorID';"; 
  $result0 = mysqli_query($link, $qry0);
  if (!$result0) {
    $logMessage = 'MySQL Error 1: ' . mysqli_error($link);
    error_log($logMessage);
    return $errResp;
  }
  if (mysqli_num_rows($result0) === 1) { // Author exists.
    $arr0 = mysqli_fetch_array($result0);
    $authorLogin = $arr0[0]; // login
  } else { // Author does not exists - this should never happen.
    $logMessage = 'MySQL Error 1: Author not found! ';
    error_log($logMessage);
    return $errResp;
  }

  // get uploaded file information.
  $zfileName = $_FILES["zfile"]["name"]; // The file name
  $zfileTmpLoc = $_FILES["zfile"]["tmp_name"];
  $zfileType = $_FILES["zfile"]["type"];
  $zfileSize = $_FILES["zfile"]["size"];
  if (!$zfileTmpLoc) {
    $fileResp = new Response();
    if (!$zfileName) {
      $fileResp->stat = "nofile"; // no zip file [shouldn't happen]
    } else {
      $fileResp->stat = "toobig"; // zip file is too big.
    }
    $fr = json_encode($fileResp);
    return "$fr";
  }
  $report = new Response();
  $report->stat = "success";
  $report->author = $authorLogin;
  $report->rpttext = [];
  $report->rpttext[] = "ZIP file was uploaded successfully.";
  $report->rpttext[] = "  file name = " . $zfileName;
  $report->rpttext[] = "  file type = " . $zfileType;
  $report->rpttext[] = "  file size = " . $zfileSize;
  $report->rpttext[] = "  file temp = " . $zfileTmpLoc;
  $report->rpttext[] = "  ";

  // Get file name and test for valid extension [.zip].
  $dirAlength = strpos($zfileName,'.zip');
  if ($dirAlength) {
    $dirAname = 'board18' . substr($zfileName,7,$dirAlength-7);
    $dirBname = substr($dirAname,8);
  } else { // process error and return
    $report->rpttext[] = "Invalid extension on uploaded file.";
    $report->rpttext[] = "Game box not created.";
    $report->stat = "email";
    $fr = json_encode($report);
    return "$fr";
  }

  // Unzip the uploaded Zip file.
  $extractTmpLoc = $zfileTmpLoc . 'extract/';
  $zipfile = new ZipArchive;
  if (($zipfile->open($zfileTmpLoc)) &&
      ($zipfile->extractTo($extractTmpLoc)) &&
      ($zipfile->close())) {
    $report->rpttext[] = "Unzip of uploaded file was successful.";
    $files1 = scandir($extractTmpLoc);
    foreach ($files1 as $line1) {
      if (($line1 !== ".") && ($line1 !== "..")) {
        $report->rpttext[] = "  Gamebox directory " . $line1;
      }
    }
    $report->rpttext[] = "  Directory structure:";
    $struct1 = scandir($extractTmpLoc . $line1);
    foreach ($struct1 as $item1) {
      if (($item1 !== ".") && ($item1 !== "..")) {
        $report->rpttext[] = "    " . $item1;
      }
    }
    $report->rpttext[] = "  ";
  } else {
    $report->rpttext[] = "Unzip of uploaded file failed.";
    $report->rpttext[] = "  temp directory structure:";
    $files2 = scandir('/tmp/');
    foreach ($files2 as $line2) {
      $report->rpttext[] = "    " . $line2;
    }
    $report->rpttext[] = "  ";
    $report->rpttext[] = "Game box not created.";
    $report->stat = "email";
    $fr = json_encode($report);
    return "$fr";
  }

  // Locate control [.json] file and images directory.
  $control = $extractTmpLoc . $dirAname . '/' . $dirBname . '.json';
  $images = $extractTmpLoc . $dirAname . '/' . $dirBname;
  if (strtolower(substr($dirAname,0,7)) !== 'board18') {
    $report->rpttext[] = "Unziped file does not have a valid directory structure.";
    $report->rpttext[] = "  zipped directory name should start 'board18' " ;
    $report->rpttext[] = "Game box not created.";
    $report->stat = "email";
    $fr = json_encode($report);
    return "$fr";
  }
  if (!file_exists ($control)) {
    $report->rpttext[] = "Unziped file does not have a valid directory structure.";
    $report->rpttext[] = "  control file name should be " . $control;
    $report->rpttext[] = "Game box not created.";
    $report->stat = "email";
    $fr = json_encode($report);
    return "$fr";
  }
  if (!is_dir ($images)) {
    $report->rpttext[] = "Unziped file does not have a valid directory structure.";
    $report->rpttext[] = "  images directory name should be " . $images;
    $report->rpttext[] = "Game box not created.";
    $report->stat = "email";
    $fr = json_encode($report);
    return "$fr";
  }
  // Read control file.
  $jsonstring = file_get_contents($control);
  if (!$jsonstring) {
    $report->rpttext[] = "Open failed on file " . $control . ".";
    $report->rpttext[] = "Game box not created.";
    $report->stat = "email";
    $fr = json_encode($report);
    return "$fr";
  }
  $decoded = json_decode($jsonstring,TRUE);
  if ($decoded == NULL) {
    $report->rpttext[] = "Invalid .json string in file " . $control . ".";
    $report->rpttext[] = "Game box not created.";
    $report->stat = "email";
    $fr = json_encode($report);
    return "$fr";
  }
  $bname = $decoded["bname"];
  $ver = $decoded["version"];
  $auth = $decoded["author"];
  $report->rpttext[] = "Control [.json] file was read successfully.";
  $report->rpttext[] = "  box name = " . $bname;
  $report->rpttext[] = "  box version = " . $ver;
  $report->rpttext[] = "  author = " . $auth;
  $report->rpttext[] = "  ";

// check for backup directory 
  $backtmp = $_SERVER['DOCUMENT_ROOT'] . '/backups/';
  $backdir = preg_replace('%//%', '/', $backtmp);
  if (!is_dir ($backdir)) { // need to create backup directory
    $report->rpttext[] = "Backup directory for images does not exist.";
    if (!mkdir ($backdir, $mode = 0755)) { 
      $report->rpttext[] = "The attempt to create a directory named ";
      $report->rpttext[] = "   " . $backdir . "  failed.";
      $report->rpttext[] = "Game box not created.";
      $report->stat = "email";
      $fr = json_encode($report);
      return "$fr";
    }
    $report->rpttext[] = "An image backup directory named ";
    $report->rpttext[] = "   " . $backdir;
    $report->rpttext[] = "was successfully created.";
    $report->rpttext[] = "  ";
  }
  
// Prepare to move image directory.
  $backdate = date("ymdhis");
  $imageback = $backdir . $dirBname . '-' . $backdate;
  $scriptname = $_SERVER['SCRIPT_FILENAME'];
  $scriptprefix = str_replace("board18BoxLoad.php","",$scriptname);
  $imagedest = $scriptprefix . 'images/' . $dirBname;
  if (file_exists ($imagedest)) { // Need to backup old images.
    if (!rename($imagedest,$imageback)) { // Backup failed
      $report->rpttext[] = "Existing image directory backup failed."; 
      $report->rpttext[] = "  directory name = " . $imagedest;
      $report->rpttext[] = "  backup name = " . $imageback;
      $report->rpttext[] = "Game box not created.";
      $report->stat = "email";
      $fr = json_encode($report);
      return "$fr";
    }
  }

// Move image directory.
  exec("mv ".escapeshellarg($images)." ".escapeshellarg($imagedest), $exrtn);
  if (!$exrtn) {
    $report->rpttext[] = "Image directory move succeeded.";  
    $report->rpttext[] = "  ";
  } else {
    $report->rpttext[] = "Image directory move failed.";  
    $report->rpttext[] = "  source name = " . $images;
    $report->rpttext[] = "  target name = " . $imagedest;
    $report->rpttext[] = "Game box not created.";
    $report->stat = "email";
    $fr = json_encode($report);
    return "$fr";
  }

  // Look for box table in database.
  $qry1 = "SELECT box_id FROM box 
           WHERE bname = '$bname' AND version = '$ver';"; 
  $result1 = mysqli_query($link, $qry1);
  if (!$result1) {
    $logMessage = 'MySQL Error 2: ' . mysqli_error($link);
    error_log($logMessage);
    rename($imageback,$imagedest); // Backout image change.
    return $errResp;
  }
  if (mysqli_num_rows($result1) === 1) { // Game box exists - do update.
    $arr1 = mysqli_fetch_array($result1);
    $boxid = $arr1[0]; // box_id
    $report->rpttext[] = "Game box exists - do update.";
    $report->rpttext[] = "  game box id = " . $boxid;
    $report->rpttext[] = "  ";
    $qry2 = "UPDATE box SET json_text = '$jsonstring' 
       WHERE box_id = '$boxid';";
    $result2 = mysqli_query($link, $qry2);
    if (!$result2) {   // If query failed
      $logMessage = 'MySQL Error 3: ' . mysqli_error($link);
      error_log($logMessage);
      rename($imageback,$imagedest); // Backout image directory change.
      return $errResp;
    }  
    $report->rpttext[] = "Game box successfully updated.";
  } else { // Game box does not exists - do insert.
    $report->rpttext[] = "Game box does not exists - do insert.";
    $report->rpttext[] = "  ";
    $qry3 = "INSERT INTO box SET bname = '$bname',version = '$ver',
             author = '$auth',json_text = '$jsonstring';";
    $result3 = mysqli_query($link, $qry3);
    if (!$result3) {   // If query failed
      $logMessage = 'MySQL Error 4: ' . mysqli_error($link);
      error_log($logMessage);
      unlink ($imagedest); // Backout image directory change.
      return $errResp;
    }   
    $qry4 = "UPDATE box SET create_date = activity_date 
             WHERE box_id = LAST_INSERT_ID();";
    $result4 = mysqli_query($link, $qry4);
    if (!$result4) {   // If query failed
      $logMessage = 'MySQL Error 5: ' . mysqli_error($link);
      error_log($logMessage);
      unlink ($imagedest); // Backout image directory change.
      return $errResp;
    }
    $qry5 = "SELECT LAST_INSERT_ID();";
    $result5 = mysqli_query($link, $qry5);
    if (!$result5 || (mysqli_num_rows($result5) !== 1)) {
      $logMessage = 'MySQL Error 6: ' . mysqli_error($link);
      error_log($logMessage);
      unlink ($imagedest); // Backout image directory change.
      return $errResp;
    }
    $arr5 = mysqli_fetch_array($result5);
    $boxid = $arr5[0]; // box_id
    $report->rpttext[] = "Game box successfully created.";
    $report->rpttext[] = "  game box id = " . $boxid;
  }
  $fr = json_encode($report);
  return "$fr";
}
?>
