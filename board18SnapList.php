<?php
/*
 * The board18SnapList.php page displays a paginated list of all   
 * the snapshots taken for the current BOARD18 game session.  
 * 
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('php/auth.php');
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

//Sanitize the POST value
$gameid = clean($link, $_REQUEST['gameid']);
$intgame = intval($gameid);

$qry1 = "SELECT * FROM game WHERE game_id = $intgame";
$result1 = mysqli_query( $link, $qry1);
if ($result1 && (mysqli_num_rows($result1) == 1)) { 
  $row1 = mysqli_fetch_assoc($result1);
  $gamestat = $row1['status']; // game status
  $gname = $row1['gname']; // game name
} else {   
  error_log("SELECT * FROM game - Query failed");
  $status = 'fail';
}

//Get count of game_snap records for game.
$qry2 = "SELECT COUNT(*) FROM game_snap WHERE game_id=$intgame";
$result2 = mysqli_query($link, $qry2);
if ($result2) {
  $snaprow = mysqli_fetch_row($result2);
  $snapcount = $snaprow[0];
} else {
  error_log("SELECT COUNT(*) FROM game_snap - Query failed");
  error_log($logMessage);
  $status = 'fail';
  exit;
}
$pagesize = 10;
$pagecount = ceil((float)$snapcount/(float)$pagesize);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Games</title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/board18com-160521.css" />
    <link rel="stylesheet" href="style/board18SnapList.css" />
    <script type="text/javascript" src="scripts/jquery.js">
    </script> 
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18SnapList.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        BD18.gameID = <?php echo $gameid; ?>;
        BD18.snapcount = <?php echo $snapcount; ?>;
        BD18.pagecount = <?php echo $pagecount; ?>;
        BD18.pagesize = <?php echo $pagesize; ?>;
        BD18.curpage = 1;
        doPageList();
        doPageLinks();
        $("#pagelinks").on("click", ".pagor", function() {
          BD18.curpage = $(".pagor").index(this) + 1;
          doPageList();
          doPageLinks();
        }); // end pagelinks.click
      }); // end ready
    </script>
  </head>
  <body onclick="$('.menu').hide();">
    <div id="topofpage">
      <div id="logo">
        <img src="images/logo.png" alt="Logo"/> 
      </div>
      <div id="heading">
        <h1>BOARD18 - <?php echo $gname; ?> - 
          <span style="font-size: smaller">Status: 
            <?php echo $gamestat; ?></span></h1>
      </div>
      <div>
        <span id="newmainmenu" onclick="$('#mainmenu').toggle();
          event.stopPropagation();"> MENU </span>
        <p id="lognote"><?php echo "$welcomename: $headermessage"; ?>
          <span style="font-size: 70%">
            Click <a href="index.html">here</a> 
            if you are not <?php echo "$welcomename"; ?>.
          </span>
        </p>
	<div id="mainmenu" class="menu">
          <ul class="bigMenu">
            <li onclick="window.location = 'board18Map.php?dogame=' + BD18.gameID;">Return to Game</li>
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
        <p>Select the snapshot that you wish to view. 
        </p>   
        <div id='snaps'> 

        </div>
      </div> 
    </div>  
  </body>
</html>

