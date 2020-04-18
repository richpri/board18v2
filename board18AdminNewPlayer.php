<?php
/* 
 * Copyright (c) 2020 Victor Parmar under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('php/auth.php');
if ($playerlevel != 'admin') {
  $_SESSION['SESS_HEADER_MESSAGE'] = 'You are not an admin level player.';
  session_write_close();
  header("location: board18Main.php");
	exit;
}
require_once('php/config.php');

/*
$status = 'ok';
$link = @mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'MySQL Error 1: ' . mysqli_connect_error();
  error_log($logMessage);
  $status = 'fail';
  exit;
}
mysqli_set_charset($link, "utf-8");

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($link, $str) {
  $str = @trim($str);
  return mysqli_real_escape_string($link, $str);
}

//Check for transfer from board18Games.php and
//sanitize the POST value if it is a transfer.
$xfer = 0;
if(!empty($_REQUEST['login'])) {
  $xfer = clean($link, $_REQUEST['login']);
}
*/
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Games</title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/board18com.css" />
    <link rel="stylesheet" href="style/board18Players.css" />
    <script type="text/javascript" src="scripts/jquery.js">
    </script>
    <script type="text/javascript" src="scripts/sha256-min.js">
    </script>
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18Players1.js">
    </script>
    <script type="text/javascript" src="scripts/board18Players2.js">
    </script>
    <script type="text/javascript" src="scripts/register-player.js">
    </script>
    <script type="text/javascript" >
      $(function() {
      });
    </script>
  </head>
  <body onclick="$('.menu').hide();">
    <div id="topofpage">
      <div id="logo">
        <img src="images/logo.png" alt="Logo"/> 
      </div>
      <div id="heading">
        <h1>BOARD18 - Remote Play Tool For 18xx Style Games</h1>
      </div>
      <div>
        <span id="newmainmenu" onclick="$('#traymenu').hide();
          $('#mainmenu').toggle();event.stopPropagation();"> MENU </span>
        <p id="lognote"><?php echo "$welcomename: $headermessage"; ?>
          <span style="font-size: 70%">
            Click <a href="index.php">here</a> 
            if you are not <?php echo "$welcomename"; ?>.
          </span>
        </p>
	<div id="mainmenu" class="menu">
          <ul class="bigMenu">
            <li onclick="window.location = 'board18Players.php';">Return to Players</li>
            <li onclick="window.location = 'board18Admin.php';">Return to Admin</li>
            <li onclick="window.location = 'board18Main.php';">Main Page</li>
            <li onclick="$.post('php/logout.php', logoutOK);">Log Out</li>
            <li onclick="window.open(BD18.help, 'HelpGuide');">Help</li>
            <li onclick="$('.menu').hide();aboutBoard18();">About BOARD18</li>
          </ul>
        </div> 
      </div>
    </div>
    <div id="leftofpage">
      <div id="adminlevel">
      </div>
    </div>
      </div>
    </div>
    <div id="rightofpage"> 
      <div id="content">  
        <?php 
          include_once("./view-player-register.php");
          viewPlayerRegister("", "background-color: #ddffdd;", "Register a Player ID.", false);
        ?>
      </div> 
     
      
    </div>   
  </body>
</html>
