<?php
/*
 * The board18Players.php page displays a paginated list of all   
 * players in the "players" table. This page is available only
 * to "admin" players. This page facilitates the administration 
 * of players. Including item such as:
 * - Showing the status and activity of all players.
 * - Deleting a player.
 * - Sending a email to a specific player.
 * - Sending a broadcast email to all players.
 * - Granting "admin" or "author" level to a player.
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

//Check for transfer from board18Games.php and
//sanitize the POST value if it is a transfer.
$xfer = 0;
if(!empty($_REQUEST['login'])) {
  $xfer = clean($link, $_REQUEST['login']);
}
//Get count of player records.
$qry1 = "SELECT COUNT(*) FROM players";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  $row = mysqli_fetch_row($result1);
  $totalcount = $row[0];
} else {
  error_log("SELECT COUNT(*) FROM players - Query failed");
  error_log($logMessage);
  $status = 'fail';
  exit;
}
$pagesize = 20; 
$pagecount = ceil((float)$totalcount/(float)$pagesize);
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
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18Players1.js">
    </script>
    <script type="text/javascript" src="scripts/board18Players2.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        BD18.totalcount = <?php echo $totalcount; ?>;
        BD18.pagecount = <?php echo $pagecount; ?>;
        BD18.pagesize = <?php echo $pagesize; ?>;
        BD18.curpage = 1;
        BD18.player = {};
        BD18.player.update = 'no';
        doPageList();
        doPageLinks();
        var playerselect = '<?php echo $xfer; ?>';
        if (playerselect != 0) { // Do not use "!==", it does not work here.
          doPlayer(playerselect);
          playerselect = 0;
        }     
        $("#pagelinks").on("click", ".pagor", function() {
          BD18.curpage = $(".pagor").index(this) + 1;
          BD18.player.update = 'no';
          doPageList();
          doPageLinks();
        }); // end first pagelinks.click
        $("#pagelinks").on("click", ".thegame", function() {
          var gamename = $(this).html();
          $.each(BD18.player.games,function(index,gameInfo) {
            if (gameInfo.gname === gamename) { 
              var gid = gameInfo.gameid;
              window.location = "board18Games.php?gameid=" + gid;
            }
          }); // end of each
        }); // end second pagelinks.click  
        $("#players").on("click", ".playerid", function() {
          doPlayer($(this).html());
        }); // end players.click
        $('#button1').click(function() {
          BD18.player.update = 'no';
          BD18.first = 0;
          updatePlayer();
          return false;
        }); // end button1 click
        $('#button2').click(function() {
          BD18.player.update = 'no';
          BD18.first = 0;
          paintPlayer();
          return false;
        }); // end button2 click
        $('#button3').click(function() {
          $('#gamelist').remove();
          $('#theplayer').slideUp(300);
          doPageLinks();
          BD18.player.update = 'no';
          BD18.first = 0;
          return false;
        }); // end button3 click
        $('#button4').click(function() {
          $('#theplayer').slideUp(300);
          $('#gamelist').remove();
          $('.error').hide();
          $('#oneto').html(BD18.player.login);
          $('#onemail').slideDown(300);
          BD18.player.update = 'no';
          BD18.first = 0;
          return false;
        }); // end button4 click
        $('#button5').click(function() {
          BD18.player.update = 'no';
          deletePlayer();
          return false;
        }); // end button5 click
        $('#button11').click(function() {
          doEmail();
          return false;
        }); // end button11 click
        $('#button12').click(function() {
          $('.error').hide();
          $("#subject1").val('');
          $("#body1").val('');
          return false;
        }); // end button12 click
        $('#button13').click(function() {
          $('#onemail').slideUp(300);
          $('#theplayer').slideDown(300);
          playerGames();
          return false;
        }); // end button13 click
        $('#button21').click(function() {
          sendBroadcast();  
          return false;
        }); // end button21 click
        $('#button22').click(function() {
          $('.error').hide();
          $("#subject2").val('');
          $("#body2").val('');
          return false;
        }); // end button22 click
        $('#button23').click(function() {
          $('#allmail').slideUp(300);
          return false;
        }); // end button23 click
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
            <li onclick="$('#theplayer').slideUp(300);$('#gamelist').remove();
                         $('.error').hide();$('#allmail').slideDown(300);
			 BD18.player.update = 'no';">Send Broadcast</li>
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
        <p style="margin-left: 10px">Select the player that you wish to manage. 
        </p>   
        <div id="players"> 

        </div>
      </div> 
     
      <div id="theplayer" class="hidediv">
        <div id="playerinfo"></div>
        <form name="theplayer" class="playerform" action="">
          <fieldset>
            <p>
              <label for="login">Change Player ID:</label>
              <input type="text" name="login" id="login" class="reg"
                     value="">
              <label class="error" for="login" id="login_error">
                This field is required.</label>
            </p>
            <p>
              <label for="email">Change Email Address: </label>
              <input type="text" name="email" id="email" class="reg"
                     value="">
              <label class="error" for="email" id="email_error">
                This field is required.</label>
            </p>
            <p>
              <label for="fname">Change First Name: </label>
              <input type="text" name="fname" id="fname" class="reg"
                     value="">
              <label class="error" for="fname" id="fname_error">
                This field is required.</label>
            </p>
            <p>
              <label for="lname">Change Last Name: </label>
              <input type="text" name="lname" id="lname" class="reg"
                     value="">
              <label class="error" for="lname" id="lname_error">
                This field is required.</label>
            </p>
            <p id="levelselect">
            </p>
            <p>
              <input type="button" name="updatebutton" class="pwbutton"  
                     id="button1" value="Update Player" >
              <input type="button" name="deletebutton" class="pwbutton"  
                     id="button5" value="Delete Player" >
              <input type="button" name="resbutton" class="pwbutton"  
                     id="button2" value="Reset Form" >
              <input type="button" name="mailbutton" class="pwbutton"  
                     id="button4" value="Send Email" >
              <input type="button" name="canbutton" class="pwbutton"  
                     id="button3" value="Exit" >
            </p>
          </fieldset>
        </form>
        <div id="deletenote" style="margin-top: 5px;"></div>
      </div>
      <div id="onemail" class="hidediv">
        <form name="onemail" class="playerform" action="">
          <fieldset>
            <p>
              Send an administrative Email to <span id="oneto">dummy</span>.   
            </p>
            <p>
              <label for="subject" style="width: 80px;">Subject:</label>
              <input type="text" name="subject" id="subject1" value="">
              <label class="error" for="subject" id="subject1_error">
                This field is required.</label>
            </p>
            <p>
              <label for="body" style="width: 80px; vertical-align:top;">
                Body: </label>
              <textarea name="body" id="body1" cols=60 rows=10></textarea>
            </p>
            <p>
              <input type="button" name="emailonebutton" class="pwbutton"  
                     id="button11" value="Send Email" >
              <input type="button" name="resbutton" class="pwbutton"  
                     id="button12" value="Reset Form" >
              <input type="button" name="canbutton" class="pwbutton"  
                     id="button13" value="Exit" >
            </p>
          </fieldset>
        </form>
      </div>
      <div id="allmail" class="hidediv">
        <form name="allmail" class="playerform" action="">
          <fieldset>
            <p>
              Send an administrative Email to all players.   
            </p>
            <p>
              <label for="subject" style="width: 80px;">Subject:</label>
              <input type="text" name="subject" id="subject2" value="">
              <label class="error" for="subject" id="subject2_error">
                This field is required.</label>
            </p>
            <p>
              <label for="body" style="width: 80px; vertical-align:top;">
                Body: </label>
              <textarea name="body" id="body2" cols=60 rows=10></textarea>
            </p>
            <p>
              <input type="button" name="emailonebutton" class="pwbutton"  
                     id="button21" value="Send Email" >
              <input type="button" name="resbutton" class="pwbutton"  
                     id="button22" value="Reset Form" >
              <input type="button" name="canbutton" class="pwbutton"  
                     id="button23" value="Exit" >
            </p>
          </fieldset>
        </form>
      </div>
    </div>   
  </body>
</html>
