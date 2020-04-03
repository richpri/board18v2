<?php
/*
 * The board18Games.php page displays a paginated list of all   
 * games in the "game" table. This page is available only
 * to "admin" players. This page facilitates the administration 
 * of games. It includes the following facilities:
 * - Listing all games showing their status and usage.
 * - Listing the players associated with a specific game.
 * - Archiving and/or deleting games.
 * 
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
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

// Check for transfer from board18Players.php or board18Boxs.php
// and sanitize the POST value if it is a transfer.
$xfer = 0;
if(!empty($_REQUEST['gameid'])) {
  $xfer = clean($link, $_REQUEST['gameid']);
}

//Get count of game records.
$qry1 = "SELECT COUNT(*) FROM game";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  $row = mysqli_fetch_row($result1);
  $totalcount = $row[0];
} else {
  error_log("SELECT COUNT(*) FROM game - Query failed");
  error_log($logMessage);
  $status = 'fail';
  exit;
}
$pagesize = 12; 
$pagecount = ceil((float)$totalcount/(float)$pagesize);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Games</title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/board18com.css" />
    <link rel="stylesheet" href="style/board18Games.css" />
    <script type="text/javascript" src="scripts/jquery.js">
    </script> 
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18Games.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        BD18.totalcount = <?php echo $totalcount; ?>;
        BD18.pagecount = <?php echo $pagecount; ?>;
        BD18.pagesize = <?php echo $pagesize; ?>;
        BD18.curpage = 1;
        BD18.game = {};
        BD18.game.update = 'no';
        var gameselect = "<?php echo $xfer; ?>";
        if (gameselect != 0) { // Do not use "!==", it does not work here.
          setTimeout(function(){ // This is to avoid a race condition.
            doGame(gameselect);
            gameselect = 0;
          }, 300);
        } 
        else {
          doPageList();
          doPageLinks(); 
        }
        $("#pagelinks").on("click", ".pagor", function() {
          BD18.curpage = $(".pagor").index(this) + 1;
          BD18.game.update = 'no';
          doPageList();
          doPageLinks();
        }); // end pagelinks.click  
        $("#games").on("click", ".gameid", function() {
          BD18.game.update = 'no';
          doGame($(this).html());
        }); // end games.click
        $("#thegame").on("click", ".playerid", function() {
          var playerURL = "board18Players.php?login=" + $(this).html();
          window.location = playerURL;
        }); // end playerlist.click
        $('#button1').click(function() {
          BD18.game.update = 'no';
          updateGame();
          return false;
        }); // end button1 click
        $('#button2').click(function() {
          BD18.game.update = 'no';
          paintGame();
          return false;
        }); // end button2 click
        $('#button3').click(function() {
          $('#playerlist').remove();
          $('#thegame').slideUp(300);
          BD18.game.update = 'no';
          doPageList();
          doPageLinks();
          return false;
        }); // end button3 click
        $('#button4').click(function() {
          var boxURL = "board18Boxes.php?boxid=" + BD18.game.boxid;
          window.location = boxURL;
          return false;
        }); // end button3 click
      }); // end ready
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
            Click <a href="index.html">here</a> 
            if you are not <?php echo "$welcomename"; ?>.
          </span>
        </p>
	<div id="mainmenu" class="menu">
          <ul class="bigMenu">
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
      <div id="pagelinks">
      </div>
    </div>
    <div id="rightofpage"> 
      <div id="content">  
        <p style="margin-left: 10px">Select the game that you wish to manage. 
        </p>   
        <div id="games"> 

        </div>
      </div> 
     
      <div id="thegame" class="hidediv">
        <div id="gameinfo"></div>
        <form name="thegame" class="gameform" action="">
          <fieldset>
            <p>
              <label for="gname">Change Game Name:</label>
              <input type="text" name="gname" id="gname" class="reg"
                     value="">
              <label class="error" for="gname" id="gname_error">
                This field is required.</label>
            </p>
            <p id="statusselect">
            </p>
            <p>
              <input type="button" name="updatebutton" class="pwbutton"  
                     id="button1" value="Update Game" >
              <input type="button" name="boxbutton" class="pwbutton"  
                     id="button4" value="Go To Box" >
              <input type="button" name="resbutton" class="pwbutton"  
                     id="button2" value="Reset Form" >
              <input type="button" name="canbutton" class="pwbutton"  
                     id="button3" value="Exit" >
            </p>
          </fieldset>
        </form>
      </div>
    </div>   
  </body>
</html>

