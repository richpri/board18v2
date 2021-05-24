<?php
/*
 * The board18Misc.php page can be used to perform miscellaneous 
 * service actions on an in progress game session. 
 * 
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * Revised 2020 by Richard E. Price
 */

require_once('php/auth.php');
require_once('php/config.php');

$theLink = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
$open = '';

if (!$theLink) {
  error_log('board18Misc.php: mysqli_connect failed: ' . mysqli_connect_error());
  $open = 'fail';
  exit;
}

//Function to sanitize values received from POST. 
//Prevents SQL injection
function clean($conn, $str) {
  $strt = trim($str);
  return mysqli_real_escape_string($conn, $strt);
}

//Sanitize the dogame value
$dogame = clean($theLink, $_REQUEST['dogame']);

// Find login ID of current player.
$qry1 = "SELECT * FROM players WHERE player_id='$loggedinplayer'";
$result1 = mysqli_query($theLink, $qry1);
// Check whether the query was successful or not
if ($result1) {
  if (mysqli_num_rows($result1) == 1) {
    // Query Successful
    $playerrow = mysqli_fetch_assoc($result1);
    $login = $playerrow['login'];
  } else {
    //Player not found
    header("location: access-denied.html");
  }
} else {
  error_log("board18Misc: player_id query failed");
  $open = 'fail';
}

// Find name of current game.
$qry2 = "SELECT * FROM game WHERE game_id='$dogame'";
$result2 = mysqli_query($theLink, $qry2);
// Check whether the query was successful or not
if ($result2 && mysqli_num_rows($result2) == 1) {
  // Query Successful
  $gamerow = mysqli_fetch_assoc($result2);
  $gname = $gamerow['gname'];
  $gstat = $gamerow['status'];
  $_SESSION['SESS_UPDATE_COUNTER'] = $gamerow['update_counter'];
} else {
  error_log("board18Misc: game name query failed");
  $open = 'fail';
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Games</title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/board18com.css" />
    <link rel="stylesheet" href="style/board18Misc.css" />
    <script type="text/javascript" src="scripts/jquery.js">
    </script> 
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18Misc1.js">
    </script>
    <script type="text/javascript" src="scripts/board18Misc2.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        $('.error').hide();
        if ('<?php echo "$open"; ?>' === 'fail') {
          var errmsg = 'Data Base access failed.\n';
          errmsg += 'Please contact the BOARD18 webmaster.';
          alert(errmsg);
        }

//      buttons on list2 form
        $('#byfn02').on("click",function() {
          switchtofn();
          return false;
        }); // end byfn02 click
        $('#byln02').on("click",function() {
          switchtoln();
          return false;
        }); // end byln02 click
//      buttons on list3 form
        $('#byid03').on("click",function() {
          switchtoid();
          return false;
        }); // end byid03 click
        $('#byln03').on("click",function() {
          switchtoln();
          return false;
        }); // end byln03 click
//      buttons on list4 form
        $('#byid04').on("click",function() {
          switchtoid();
          return false;
        }); // end byid04 click
        $('#byfn04').on("click",function() {
          switchtofn();
          return false;
        }); // end byfn04 click
//      Select "player to remove from game"
        $('#players').on( 'click', '.gprow', function(e) {
          $('#pname3').val($(this).attr('id'));
        }); // end gprow
//      Select "player to add into game"
        $('.outplayer').on( 'click', '.playerrow', function(e) {
          $('#pname4').val($(this).attr('id'));
        }); // end playerrow
        
//      Buttons on game session edit form
        $('#playerform').on( "submit",function(event) {
          changePlayer(BD18.login, BD18.dogame);  
          event.preventDefault();
        }); // end playerform
        $('#button4').on("click",function() {
          var swapstring = '&gameid=' + BD18.dogame;
          $.post('php/statSwap.php', swapstring,  statswapOK);
          return false;
        }); // end button4 click
        $('#button5').on("click",function() {
          var linkstring = 'gameid=' + BD18.dogame;
          $.post('php/linkGet.php', linkstring,  linkReturn);
          return false;
        }); // end button5 click
        $('#button6').on("click",function() {
          window.location = "board18Main.php";
          return false;
        }); // end button6 click
        $('#button11').on("click",function() {
          addLink();
          return false;
        }); // end button11 click
        $('#button15').on("click",function() {
          switch (BD18.sort) {
            case 'ID':
              var pString = "sortkey=login";
              $.post("php/playerSort.php", pString, playerSortReturn);
              break;
            case 'FN':
              var fString = "sortkey=firstname";
              $.post("php/playerSort.php", fString, playerSortReturn);
              break;
            case 'LN':
              var lString = "sortkey=lastname";
              $.post("php/playerSort.php", lString, playerSortReturn);
              break
          }
          $('#linkdiv').slideUp(300);
          return false;
        }); // end button15 click
        $("#linklist").on("click", ".deleteflag", function() {
          deleteCheck($(this).html());
        }); // end deleteflag click
        $('#button1').on("click",function() {
          deleteLink(BD18.dogame, BD18.selectDelete);
          $('#delpop').slideUp(300);
          $('#delgrey').hide();
          return false;
        }); // end button1 click
        $('#button2').on("click",function() {
          $('#delpop').slideUp(300);
          $('#delgrey').hide();
          return false;
        }); // end button2 click
        BD18.login = '<?php echo $login; ?>';
        BD18.gname = '<?php echo $gname; ?>'; 
        BD18.dogame = '<?php echo $dogame; ?>'; 
        var gString = "gameid=" + BD18.dogame;
        $.post("php/gamePlayers.php", gString, gamePlayersResult);
        BD18.sort = "ID";
        var pString = "sortkey=login";
        $.post("php/playerSort.php", pString, playerSortReturn); 
      }); // end ready
    </script>
  </head>
  <body>

    <div id="topofpage">
      <div id="logo">
        <img src="images/logo.png" alt="Logo"/> 
      </div>
      <div id="heading">
        <h1>BOARD18 - Remote Play Tool For 18xx Style Games</h1>
      </div>
      <div>
        <p id="lognote"><?php echo "$welcomename: $headermessage"; ?>
          <span style="font-size: 70%">
            Click <a href="index.html">here</a> 
            if you are not <?php echo "$welcomename"; ?>.
          </span>
        </p>
      </div>
    </div>

    <div id="leftofpage">
      <div id='sidebar'>
        <h3>Edit this Game Session</h3>
        <p>Please use this page to edit this game session. 
          <br><br>A list of the players in this game session
          appears to the top right. <br><br>A form for editing
          the game session appears below this list. It can be used
          to add or remove players from the game session. It can
          also change the status of the game session and add
          links to the help menu for the game session. <br><br>
          At the bottom right is a list of all BOARD18 players. 
          You can use the<br>
          <span style="font-weight: bold; color: Fuchsia">
          List Display Selection Controls</span> <br>
          to sort this list by first or last name. <br><br>
          <span style="color: Fuchsia">Clicking on a  
          row of either list will enter it in the form.</span>
        </p>
      </div>
    </div>

    <div id="rightofpage"> 
      <div id="content">    
        <div id="players">
          <div id='gameplayerlist'></div>
          <p id="status1">
                  The current status of this game is <?php echo "$gstat"; ?>.
          </p>         
          <form name="player" id="playerform" action="">
            <fieldset>
              <p>
                <label for="pname3">
                  Enter login of player to remove from game.
                </label>
                <input type="text" name="pname3" id="pname3">
                <label class="error" for="pname3" id="pname3_error">
                  Press submit again to remove yourself.</label>
              </p>
              <p>
                <label for="pname4">
                  Enter login of player to add to game.
                </label>
                <input type="text" name="pname4" id="pname4">
                <label class="error" for="pname4" id="pname4_error">
                  No player action selected.</label>
              </p>
              <p>
                <input type="submit" name="playerbutton" class="pwbutton"  
                       id="button3" value="Submit" >
                <input type="button" name="toggle" class="pwbutton"  
                       id="button4" value="Toggle Status" >
              </p>
              <p>
                <input type="button" name="links" class="pwbutton"  
                       id="button5" value="Maintain Links" >
                <input type="button" name="canbutton" class="pwbutton"  
                       id="button6" value="Exit" >   
              </p>
            </fieldset>
          </form>
        </div> 

        <div id="newgame">
        <div id="list2" class="outbox">
          <form name="plidbox" action="">
            <p>
              <span class="ctlhead">List Display Selection Controls</span>
              <br><span class="ctllabel">Player List by ID</span>
            </p>
            <p>
              <input type="button" name="byfn02" class="pwbutton"  
                     id="byfn02" value="Player List By First Name" > 
              <input type="button" name="byln02" class="pwbutton"  
                     id="byln02" value="Player List By Last Name" > 
            </p>
          </form>
          <div id="outplayer2" class="outplayer"></div>
        </div>  
        <div id="list3" class="outbox">
          <form name="plfnbox" action="">
            <p>
              <span class="ctlhead">List Display Selection Controls</span>
              <br><span class="ctllabel">Player List by First Name</span>
            </p>
            <p>
              <input type="button" name="byid03" class="pwbutton"  
                     id="byid03" value="Player List By ID" > 
              <input type="button" name="byln03" class="pwbutton"  
                     id="byln03" value="Player List By Last Name" > 
            </p>
          </form>
          <div id="outplayer3" class="outplayer"></div>
        </div>  
        <div id="list4" class="outbox">
          <form name="pllnbox" action="">
            <p>
              <span class="ctlhead">List Display Selection Controls</span>
              <br><span class="ctllabel">Player List by Last Name</span>
            </p>
            <p>
              <input type="button" name="byid04" class="pwbutton"  
                     id="byid04" value="Player List By ID" > 
              <input type="button" name="byfn04" class="pwbutton"  
                     id="byfn04" value="Player List By First Name" > 
            </p>
          </form>
          <div id="outplayer4" class="outplayer"></div>
        </div>
        </div>
        
      </div> 
      <div id='linkdiv'> 
        <p id="linkheader">
           Click on Link Name to delete link.
        </p>
        <div id='linklist'> 
        </div> 
        <form name="link" id="linkform" action="">
          <fieldset>
            <p>
              <label for="lname1">
                Name of new link:
              </label>
              <input type="text" name="lname1" id="lname1">
              <label class="error" for="lname1" id="lname1_error">
                This field is required.</label>
            </p>
            <p>
              <label for="lnkurl1">
                URL of new link:
              </label>
              <input type="text" name="lnkurl1" id="lnkurl1">
              <label class="error" for="lnkurl1" id="lnkurl1_error">
                This field is required.</label>
            </p>
            <p>
              <input type="submit" name="linkbutton" class="pwbutton"  
                     id="button11" value="Add" >
              <input type="button" name="canbutton" class="pwbutton"  
                     id="button15" value="Exit" >   
            </p>
          </fieldset>
        </form>     
      </div>
    </div> 
    <div id=delgrey></div>
    <div id=delpop> 
      <form name="del" id="delform" action="">
        <fieldset>
          <p> Do you really want to delete the 
              <span id="delname"></span> link?</p>
          <p>
            <input type="button" name="yesbutton" class="pwbutton"  
                   id="button1" value="Yes" >
            <input type="button" name="nobutton" class="pwbutton"  
                   id="button2" value="No" >   
          </p>
        </fieldset>
      </form>     
    </div>
  </body>
</html>

