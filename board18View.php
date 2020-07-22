<?php
require_once('php/auth.php');
?>
<!doctype html>
<!--
The board18View.php page displays a list of all active games. 
The player can select any of the listed games to view the game 
session for that game.

Copyright (c) 2014 Richard E. Price under the The MIT License.
A copy of this license can be found in the LICENSE.text file.
-->
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Games</title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/jquery.contextMenu.css" />
    <link rel="stylesheet" href="style/board18com.css" />
    <link rel="stylesheet" href="style/board18View.css" /> 
    <script type="text/javascript" src="scripts/jquery.js">
    </script> 
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18View.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        $('.error').hide();
        var dString = 'gstat=Active';
        $.post('php/allGameList.php', dString, listReturn);
        $("#filters").submit(function() {
          filters();
          return false;
        }); // end filters
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
            <li onclick="window.location = 'board18New.php';">Make New Game</li>
            <li onclick="window.location = 'board18Main.php';">Main Page</li>
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
      <div id="filters">
        <form name="filters" action="">
          <fieldset>
            <p>
              <label for="bname">Box Name:</label>
              <input type="text" name="bname" id="bname" 
                     value="">
              <label class="error" for="bname" id="bname_error"></label>
            </p>
            <p> Status:
              <input type="radio" id="active" name="status" 
                     value="Active" checked>
              <label for="active">Active</label>
              <input type="radio" id="completed" name="status" 
                     value="Completed">
              <label for="completed">Completed</label>
              <input type="radio" id="all" name="status" 
                     value="All">
              <label for="all">All</label>
            </p>
            <p> Select filters and press
              <input type="submit" name="fbutton" 
                     id="button1" value="Submit" >
            </p>
          </fieldset>
        </form>
      </div>
      <div id="content">  
        <div>
        <p>Click on the game that you wish to view.  </p> 
        </div>      
        <div id="games">
        </div>
      </div> 
    </div>  
  </body>
</html>

