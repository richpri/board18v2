<?php
/*
 * auth.php is included at the start of all password protected pages 
 * and all password protected PHP programs called via AJAX.
 * 
 * If the PHP program doing the include was called via AJAX then
 * authajax.php will be used instead of auth.php. Otherwise auth.php
 * will start a php session and then checks to see that the player 
 * is logged on and has been active sometime in the last 24 hours.
 * 
 * Copyright (c) 2016 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * 
 * Modified by Rich Price in 2020 to conditionally use authajax.php
 */

  if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
    require_once('authajax.php');
  } else {
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
      header("location: access-denied.html");
      exit;
    } else { // present
      $loggedinplayer = $_SESSION['SESS_PLAYER_ID']; // number
      $emailaddress = $_SESSION['SESS_EMAIL_ADDR'];
      $welcomename = $_SESSION['SESS_FIRST_NAME'];
      $headermessage = $_SESSION['SESS_HEADER_MESSAGE'];
      $playerlevel = $_SESSION['SESS_PLAYER_LEVEL'];
      $_SESSION['SESS_HEADER_MESSAGE'] = '';
    }
  }