<?php
/*
 * The board18New.php page contains a set of forms that can be  
 * used to create a new BOARD18 game session.  
 * 
 * Copyright (c) 2020 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('php/auth.php');
require_once('php/config.php');

$theLink = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
$open = '';
if (!$theLink) {
  error_log('board18New.php: mysqli_connect failed: ' . mysqli_connect_error());
  $open = 'fail';
  exit;
}

$qry = "SELECT login FROM players WHERE player_id = $loggedinplayer";
$result = mysqli_query($theLink, $qry);
if ($result) {
  if (mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_array($result);
    $login = $row[0];
  } else {
    error_log('board18New.php: SELECT login: no player found.');
    $open = 'fail';
  }
} else {
  error_log('board18New.php: SELECT login: select call failed.');
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
    <script type="text/javascript" src="scripts/board18New1.js">
    </script>
    <script type="text/javascript" src="scripts/board18New2.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        $('.error').hide();
        if ('<?php echo "$open"; ?>' === 'fail') {
          var errmsg = 'Data Base access failed.\n';
          errmsg += 'Please contact the BOARD18 webmaster.';
          alert(errmsg);
        }
//      buttons on list1 form
        $('#byid01').on("click",function() {
          switchtoid();
          return false;
        }); // end byid01 click
        $('#byfn01').on("click",function() {
          switchtofn();
          return false;
        }); // end byfn01 click
        $('#byln01').on("click",function() {
          switchtoln();
          return false;
        }); // end byln01 click
        $('#flt01').on("click",function() {
          dofilter();
          return false;
        }); // end flt01 click
//      buttons on list2 form
        $('#box02').on("click",function() {
          $('.outbox').hide(); // hide previous list
          fillBoxList();
          return false;
        }); // end byid02 click
        $('#byfn02').on("click",function() {
          switchtofn();
          return false;
        }); // end byfn02 click
        $('#byln02').on("click",function() {
          switchtoln();
          return false;
        }); // end byln02 click
//      buttons on list3 form
        $('#box03').on("click",function() {
          $('.outbox').hide(); // hide previous list
          fillBoxList();
          return false;
        }); // end byid03 click
        $('#byid03').on("click",function() {
          switchtoid();
          return false;
        }); // end byfn03 click
        $('#byln03').on("click",function() {
          switchtoln();
          return false;
        }); // end byln03 click
//      buttons on list4 form
        $('#box04').on("click",function() {
          $('.outbox').hide(); // hide previous list
          fillBoxList();
          return false;
        }); // end byid03 click
        $('#byfn04').on("click",function() {
          switchtofn();
          return false;
        }); // end byfn03 click
        $('#byln04').on("click",function() {
          switchtoln();
          return false;
        }); // end byln04 click
//      Select box ID into form
        $('#content').on( 'click', '.boxrow', function(e) {
          $('#boxid').val($(this).attr('id'));
          FillInfoBox($(this).attr('id'));
        }); // end boxrow
        $('#boxid').on( "mouseenter",function() {
          $('#infobox').show();
        }).on( "mouseleave",function() {
          $('#infobox').hide();
        }); // end infobox
//      Select players into form
        $('#content').on( 'click', '.playerrow', function(e) {
          addPlayer($(this).attr('id'));
        }); // end playerrow
//      Submit or exit
        $("#newgame").on( "submit",function() {
          doNewGame();
          return false;
        }); // end newgame submit
        $('#button2').on("click",function() {
          window.location = "board18Main.php";
          return false;
        }); // end button2 click
//      Make this player be player 1 in the new game.
        addPlayer('<?php echo "$login"; ?>'); 
//      Make initial BD18.playerList
        var outstring = "sortkey=login";
        $.post("php/playerSort.php", outstring, playerSortDiscard);

        
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
        <h3>Start a New Game Session</h3>
        <p>Please use this form to start a new game session. 
          <br><br>For your convenience, a table of available
          game boxes appears to the right. <br><br>And use the<br>
          <span style="font-weight: bold; color: Fuchsia">
          List Display Selection Controls</span> <br>
          to Filter this list or to select a sorted list of registered 
          players to appear here instead. <br><br>
          <span style="color: Fuchsia">Clicking on the  
          row of a box or player will enter it in the form.</span>
        </p>
      </div>
    </div>
    <div id="rightofpage"> 
      <div id="content"> 
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
                <input type="text" name="boxid" readonly
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
          <div id="infobox"> 
            <p> 
              Box Name<br>
              <span id="infoname">  </span><br><br>
              Version<br>
              <span id="infoversion">  </span>
            </p>
          </div>
        </div>
        <div id="list1" class="outbox">
          <form name="gamebox" action="">
            <p>
              <span class="ctlhead">List Display Selection Controls</span>
              <br><span class="ctllabel">Game Box List</span>
            </p>
            <p>
              <input type="button" name="byid01" class="pwbutton"  
                     id="byid01" value="Player List By ID" > 
              <input type="button" name="byfn01" class="pwbutton"  
                     id="byfn01" value="Player List By First Name" > <br>
              <input type="button" name="byln01" class="pwbutton"  
                     id="byln01" value="Player List By Last Name" > 
              <input type="button" name="flt01" class="pwbutton"  
                     id="flt01" value="Do Game Box Filter" >  <br>
              <label id="fgpl" for="filter1">Filter Game Box Name By</label> 
              <input type="text" name="filter1" id="filter1">
            </p>
          </form>
          <div id="boxes"></div>
        </div>  
        <div id="list2" class="outbox">
          <form name="plidbox" action="">
            <p>
              <span class="ctlhead">List Display Selection Controls</span>
              <br><span class="ctllabel">Player List by ID</span>
            </p>
            <p>
              <input type="button" name="box02" class="pwbutton"  
                     id="box02" value="Game Box List" > 
              <input type="button" name="byfn02" class="pwbutton"  
                     id="byfn02" value="Player List By First Name" > <br>
              <input type="button" name="byln02" class="pwbutton"  
                     id="byln02" value="Player List By Last Name" > 
            </p>
          </form>
          <div id="outplayer2"></div>
        </div>  
        <div id="list3" class="outbox">
          <form name="plfnbox" action="">
            <p>
              <span class="ctlhead">List Display Selection Controls</span>
              <br><span class="ctllabel">Player List by First Name</span>
            </p>
            <p>
              <input type="button" name="box03" class="pwbutton"  
                     id="box03" value="Game Box List" > 
              <input type="button" name="byid03" class="pwbutton"  
                     id="byid03" value="Player List By ID" > <br>
              <input type="button" name="byln03" class="pwbutton"  
                     id="byln03" value="Player List By Last Name" > 
            </p>
          </form>
          <div id="outplayer3"></div>
        </div>  
        <div id="list4" class="outbox">
          <form name="pllnbox" action="">
            <p>
              <span class="ctlhead">List Display Selection Controls</span>
              <br><span class="ctllabel">Player List by Last Name</span>
            </p>
            <p>
              <input type="button" name="box04" class="pwbutton"  
                     id="box04" value="Game Box List" > 
              <input type="button" name="byid04" class="pwbutton"  
                     id="byid04" value="Player List By ID" > <br>
              <input type="button" name="byfn04" class="pwbutton"  
                     id="byfn04" value="Player List By First Name" > 
            </p>
          </form>
          <div id="outplayer4"></div>
        </div>
      </div>    
    </div>  
  </body>
</html>

