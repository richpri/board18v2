<?php
/*
 * The board18SnapMap page is used to display snapshots of the
 * the game board for an existing game. This page cannot be used  
 * to update the game in any way.
 * 
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('php/auth.php');
require_once('php/config.php'); 

//Function to sanitize values received from POST. 
//Prevents SQL injection
function clean( $conn, $str ) {
  $str = @trim($str);
  return mysqli_real_escape_string( $conn, $str );
}

//Initialize $status flag.
$status = 'ok';

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if ( !$link ) {
	error_log('Failed to connect to server: ' . mysqli_connect_error());
	$status = 'fail';
}  
  
//Sanitize the snap shot ID
$cpid = clean( $link, $_REQUEST['show']);
$intsnap = intval($cpid);

$qry1 = "SELECT * FROM game_snap WHERE cp_id = $intsnap";
$result1 = mysqli_query( $link, $qry1 );
if ($result1 && (mysqli_num_rows($result1) == 1)) { 
  $row1 = mysqli_fetch_assoc($result1);
  $gameid = $row1['game_id']; // game id
  $gname = '';//$row1['gname']; // game name ## ajprog -- Field not in table
  $ground = $row1['game_round']; // game round
} else {   
  error_log("game_snap query failed");
  $status = 'fail';
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Games
    </title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/board18com.css" />
    <link rel="stylesheet" href="style/board18Map.css" />
    <script type="text/javascript" src="scripts/jquery.js">
    </script>
    <script type="text/javascript" src="scripts/jqueryMigrate.js">
    </script> 
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/jquery.ui.position.js">
    </script>
    <script type="text/javascript" src="scripts/board18Map1.js">
    </script> 
    <script type="text/javascript" src="scripts/board18Map2.js">
    </script>
    <script type="text/javascript" src="scripts/board18SnapMap.js">
    </script>
    <script type="text/javascript">
      $(function() {
        if ('<?php echo "$status"; ?>' === 'fail') {
          var errmsg = 'Data Base access failed.\n';
          errmsg += 'Please contact the BOARD18 webmaster.';
          alert(errmsg);
        }
        BD18.welcomename = "<?php echo "$welcomename"; ?>";
        BD18.headermessage = "<?php echo "$headermessage"; ?>";
        BD18.gameID = "<?php echo $gameid; ?>";
        BD18.snapID = "<?php echo $cpid; ?>";
        var startMessage = BD18.welcomename + ": ";
        startMessage += BD18.headermessage;
        $('#lognote').text(startMessage);
        setUpKeys();
        var snapToShow = 'snapshot='+BD18.snapID;
        $.getJSON("php/gameSnap.php", snapToShow, loadSession)
                .error(function() {
          var msg = "Error loading game file. \n";
          alert(msg);
        });
      });
    </script>    
  </head>

  <body onclick="$('.menu').hide();$('.menu ul ul').hide();">
    <div id="topofpage">
      <div id="logo">
        <img src="images/logo.png" alt="Logo"/> 
      </div>
      <div id="heading">
        <h1>BOARD18 - <?php echo $gname; ?> - 
          <span style="font-size: smaller">Snapshot: 
            <?php echo $ground; ?></span></h1>
      </div>
      <div>
        <span id="newmainmenu" onclick="$('#traymenu').hide();
          $('#mainmenu').toggle();event.stopPropagation();"> MENU </span>
        <p id="lognote"></p>
  <div id="mainmenu" class="menu">
    <ul class="bigMenu" style="width: 300px;">
      <li onclick="hideShow();">Hide/Show(H)
      </li>
      <li onclick="window.location = 'board18SnapMrk.php?show=' + BD18.snapID;">
        Go To Snapshot Stock Market(M)
      </li>
      <li onclick="window.location = 'board18SnapList.php?gameid=' + BD18.gameID;">
        Return to Snap List(S)
      </li>
      <li onclick="window.location = 'board18Map.php?dogame=' + BD18.gameID;">
        Return to Game(G)
      </li>
      <li onclick="window.location = 'board18Main.php';">Go To Main Page(O)
      </li>
      <li onclick="$.post('php/logout.php', logoutOK);">Log Out(X)
      </li>
      <li onclick="window.open(BD18.help, 'HelpGuide');">Help
      </li>
      <li onclick="$('.menu').hide();aboutBoard18();">About BOARD18</li>
    </ul>
  </div>
      </div>
    </div>

    <div id="topleftofpage">
      <span id="traybutton" onclick="$('#mainmenu').hide();
        $('#traymenu').toggle();event.stopPropagation();"> Trays </span>
    </div> 
    <div id="traymenu" class="menu"></div> 
    <div id="botleftofpage">
      <div id="sidebar">
        <div id="tiles" onclick="traySelect(event);">
          <canvas id="canvas0" width="120">
            Your browser does not support the HTML 5 Canvas. 
          </canvas>
        </div> 

      </div>
    </div>

    <div id="rightofpage">
      <div id="content">
        <canvas id="canvas1">
          Your browser does not support the HTML 5 Canvas. 
        </canvas>
        <canvas id="canvas2">
        </canvas>
      </div>  
    </div>

  </body>
</html>

