<?php
/*
 * The board18New.php page contains a form that can be used to create 
 * a new BOARD18 game session. It displays a list of the available game
 * boxes and a list of the available players as aids to filling out the form. 
 * 
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('php/auth.php');
require_once('php/makeTables.php');

$qry = "SELECT login FROM players WHERE player_id = $loggedinplayer";
$result = mysqli_query($theLink, $qry);
if ($result) {
  if (mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_array($result);
    $login = $row[0];
  } else {
    error_log('SELECT login: no player found.');
    $open = 'fail';
  }
} else {
  error_log('SELECT login: select call failed.');
  $open = 'fail';
  exit;
}

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Games</title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/board18com.css" />
    <link rel="stylesheet" href="style/board18New.css" />
    <script type="text/javascript" src="scripts/jquery.js">
    </script>
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18New.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        $('.error').hide();
        if ('<?php echo "$open"; ?>' === 'fail') {
          var errmsg = 'Data Base access failed.\n';
          errmsg += 'Please contact the BOARD18 webmaster.';
          alert(errmsg);
        }
        $('.plid').mousedown(function() {
          addPlayer($(this).attr('id'));
        });
        $('.gbrow').mousedown(function() {
          $('#boxid').val($(this).children('.gbid').text());
        });
        $("#newgame").submit(function() {
          newgame();
          return false;
        }); // end newgame
        $('#button2').click(function() {
          window.location = "board18Main.php";
          return false;
        }); // end button2 click
//      Make this player be player 1 in the new game.
        addPlayer('<?php echo "$login"; ?>'); 
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
            <li onclick="window.location = 'board18Main.php';">Main Page</li>
            <li onclick="$.post('php/logout.php', logoutOK);">Log Out</li>
            <li onclick="window.open(BD18.help, 'HelpGuide');">Help</li>
            <li onclick="$('.menu').hide();aboutBoard18();">About BOARD18</li>
          </ul>
        </div> 
      </div>
    </div>

    <div id="leftofpage">
      <div id='sidebar'>
        <?php showPlayers($theLink); ?>
      </div>
    </div>
    <div id="rightofpage"> 
      <div id="content">   
        <div>
          <h3>Start a New Game Session</h3>
          <p>Please use this form to start a new game session. 
            <br>For your convenience, a list of registered players 
            appears to the left<br>and a table of available game 
            boxes appears below.
          </p>
        </div>
        <div id="newgame">
          <form name="newgame" action="">
            <fieldset>
              <p>
                <label for="sessionname">Game Name:</label>
                <input type="text" name="sessionname" id="sessionname">
                <label class="error" for="sessionname" id="sn_error">
                  This field is required.</label>
              </p>
              <p>
                <label for="boxid" class="label1">Game Box ID:</label>
                <input type="text" name="boxid" 
                       id="boxid" class="fn1">
                <label class="error" for="boxid" id="bi_error">
                  This field is required.</label>
                <label for="pcount" class="label1"># of Players:</label>
                <input type="text" name="pcount" 
                       id="pcount" class="fn1">
                <label class="error" for="pcount" id="pc_error">
                  This field is required.</label>
              </p>
              <p>
                <label for="player1" class="label2">Player 1:</label>
                <input type="text" name="player1" 
                       id="player1" class="fn2">
                <label class="error" for="player1" id="p1_error">
                  This field is required.</label>
                <label for="player2" class="label2">Player 2:</label>
                <input type="text" name="player2" 
                       id="player2" class="fn2">
                <label class="error" for="player2" id="p2_error">
                  This field is required.</label>
              </p>
              <p>
                <label for="player3" class="label2">Player 3:</label>
                <input type="text" name="player3" 
                       id="player3" class="fn2">
                <label class="error" for="player3" id="p3_error">
                  This field is required.</label>
                <label for="player4" class="label2">Player 4:</label>
                <input type="text" name="player4" 
                       id="player4" class="fn2">
                <label class="error" for="player4" id="p4_error">
                  This field is required.</label>
              </p>
              <p>
                <label for="player5" class="label2">Player 5:</label>
                <input type="text" name="player5" 
                       id="player5" class="fn2">
                <label class="error" for="player5" id="p5_error">
                  This field is required.</label>
                <label for="player6" class="label2">Player 6:</label>
                <input type="text" name="player6" 
                       id="player6" class="fn2">
                <label class="error" for="player6" id="p6_error">
                  This field is required.</label>
              </p>
              <p>
                <input type="submit" name="pwbutton" class="pwbutton" 
                       id="button1" value="Submit" >
                <label class="error" for="button1" id="signon_error">
                  Duplicate Game Name.</label>
                <input type="button" name="canbutton" class="pwbutton"  
                       id="button2" value="Exit" >
              </p>
            </fieldset>
          </form>
        </div>        
        <div id="boxes">
          <h3>Available Game Boxes</h3>
<?php showBoxes($theLink); ?>
        </div>
      </div>    
    </div>  
  </body>
</html>
