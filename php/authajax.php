<?php
/*
 * authajax.php is indirectly included via auth.php at the start of 
 * all password protected PHP programs that are called via AJAX.
 * 
 * 
 * It will start a php session and then check to see if the player 
 * is logged on and has been active sometime in the last 24 hours.
 * 
 * Copyright (c) 2020 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

  //Start session
  session_start();
  if (isset($_SESSION['LAST_ACTIVITY']) && 
          (time() - $_SESSION['LAST_ACTIVITY'] > 86400)) { // 1 day
    // last request was more than one day ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
  }
  $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
  //Check whether the session variable SESS_PLAYER_ID is present or not
  if(!isset($_SESSION['SESS_PLAYER_ID']) || 
  (trim($_SESSION['SESS_PLAYER_ID']) == '')) { // not present
    $URLraw = $_SERVER['REQUEST_URI'];
    $URLindex = strrpos($URLraw,"/");
    $URLin = substr($URLraw,$URLindex+1);
    setcookie("RedirectCookie", $URLin);
    // The next statement may look odd but for historical reasons this 
    // '<!doctype html>' string is what 20 or more AJAX callback functions 
    // [that I do not wish to rewrite] expect to see for a log on error.
    echo ('<!doctype html>'); 
    exit;
  } else { // present
    $loggedinplayer = $_SESSION['SESS_PLAYER_ID']; // number
    $emailaddress = $_SESSION['SESS_EMAIL_ADDR'];
    $welcomename = $_SESSION['SESS_FIRST_NAME'];
    $headermessage = $_SESSION['SESS_HEADER_MESSAGE'];
    $playerlevel = $_SESSION['SESS_PLAYER_LEVEL'];
    $_SESSION['SESS_HEADER_MESSAGE'] = '';
  }
