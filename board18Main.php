<?php
require_once('php/auth.php');
?>
<!doctype html>
<!--
The board18Main.php page displays a list of the logged in player's active
games. The player can select any of the listed games to start a game 
session in that game or to link to the board18Misc page where miscellaneous
service actions can be performed for that game. He can also select
a link to the board18New page if he wishes to start a new game.

Copyright (c) 2013 Richard E. Price under the The MIT License.
A copy of this license can be found in the LICENSE.text file.
-->
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Games</title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/board18com.css" />
    <link rel="stylesheet" href="style/board18Main.css" />
    <script type="text/javascript" src="scripts/jquery.js">
    </script> 
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18Main.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        $.post('php/myGameList.php', listReturn);
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
            Click <a href="index.php">here</a> 
            if you are not <?php echo "$welcomename"; ?>.
          </span>
        </p>
	<div id="mainmenu" class="menu">
          <ul class="bigMenu">
            <li onclick="window.location = 'board18New.php';">Make New Game</li>
            <li onclick="window.location = 'board18View.php';">View All Games</li>
            <li onclick="window.location = 'board18Admin.php';">Administration</li>
            <li onclick="$.post('php/logout.php', logoutOK);">Log Out</li>
            <li onclick="window.open(BD18.help, 'HelpGuide');">Help</li>
            <li onclick="$('.menu').hide();aboutBoard18();">About BOARD18</li>
          </ul>
        </div> 
      </div>
    </div>
    <div id="leftofpage">
      
    </div>
    <div id="rightofpage"> 
      <div id="content">    
        <div id="games">
          <table id='gamelist'> 
            <tr>
              <th>Game Name</th> <th>Box Name</th> 
              <th>Version</th> <th>Start Date</th> 
              <th>Edit</th>
            </tr>
          </table>
        </div>
        <div>
        <p>At this point you can select an existing game to play,<br>
          you can click the Edit button for a given game to update it
          <br>or you can use the menu to start up a new game, perform
          <br>general administrative functions and do other stuff. 
        </p>   
        </div>
      </div> 
    </div>  
  </body>
</html>
