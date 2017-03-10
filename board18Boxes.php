<?php
/*
 * The board18Boxes.php page displays a paginated list of all   
 * boxes in the "box" table. This page is available only
 * to "admin" players. This page facilitates the administration 
 * of boxes. It includes the following facilities:
 * - Listing all boxes showing their status and usage.
 * - Listing the games associated with a specific box.
 * - Deactivating and/or reactivating boxes.
 * - Deleting deactive boxes with no associated games.
 * 
 * Copyright (c) 2016 Richard E. Price under the The MIT License.
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
if(!empty($_REQUEST['boxid'])) {
  $xfer = clean($link, $_REQUEST['boxid']);
}
//Get count of box records.
$qry1 = "SELECT COUNT(*) FROM box";
$result1 = mysqli_query($link, $qry1);
if ($result1) {
  $row = mysqli_fetch_row($result1);
  $totalcount = $row[0];
} else {
  error_log("SELECT COUNT(*) FROM box - Query failed");
  error_log($logMessage);
  $status = 'fail';
  exit;
}
$pagesize = 10; 
$pagecount = ceil((float)$totalcount/(float)$pagesize);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Boxes</title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/board18com-160521.css" />
    <link rel="stylesheet" href="style/board18Boxes.css" />
    <script type="text/javascript" src="scripts/jquery.js">
    </script> 
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18Boxes.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        BD18.totalcount = <?php echo $totalcount; ?>;
        BD18.pagecount = <?php echo $pagecount; ?>;
        BD18.pagesize = <?php echo $pagesize; ?>;
        BD18.curpage = 1;
        BD18.box = {};
        BD18.box.update = 'no';
        doPageList();
        doPageLinks();
        var boxselect = <?php echo $xfer; ?>;
        if (boxselect != 0) { // Do not use "!==", it does not work here.
          setTimeout(function(){ // This is to avoid a race condition.
            doBox(boxselect);
            boxselect = 0;
          }, 300);
        } 
        $("#pagelinks").on("click", ".pagor", function() {
          BD18.curpage = $(".pagor").index(this) + 1;
          BD18.box.update = 'no';
          doPageList();
          doPageLinks();
        }); // end pagelinks.click
        $("#boxes").on("click", ".boxid", function() {
          BD18.box.update = 'no';
          doBox($(this).html());
        }); // end boxes.click
        $("#thebox").on("click", ".gameid", function() {
          var gameURL = "board18Games.php?gameid=" + $(this).html();
          window.location = gameURL;
        }); // end gameid.click
        $('#button1').click(function() {
          BD18.box.update = 'no';
          updateBox();
          return false;
        }); // end button1 click
        $('#button2').click(function() {
          BD18.box.update = 'no';
          paintBox();
          return false;
        }); // end button2 click
        $('#button3').click(function() {
          $('#playerlist').remove();
          $('#thebox').slideUp(300);
          BD18.box.update = 'no';
          doPageList();
          doPageLinks();
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
        <p style="margin-left: 10px">Select the box that you wish to manage. 
        </p>   
        <div id="boxes"> 

        </div>
      </div> 
     
      <div id="thebox" class="hidediv">
        <div id="boxinfo"></div>
        <form name="thebox" class="boxform" action="">
          <fieldset>
            <p>
              <label for="bname">Change Box Name:</label>
              <input type="text" name="bname" id="bname"
                     value="">
              <label class="error" for="bname" id="bname_error">
                This field is required.</label>
            </p>
           <p>
              <label for="version">Change Version:</label>
              <input type="text" name="version" id="version"
                     value="">
              <label class="error" for="version" id="version_error">
                This field is required.</label>
            </p>
           <p>
              <label for="author">Change Author:</label>
              <input type="text" name="author" id="author"
                     value="">
              <label class="error" for="author" id="author_error">
                This field is required.</label>
            </p>
            <p id="statusselect">
            </p>
            <p>
              <input type="button" name="updatebutton" class="pwbutton"  
                     id="button1" value="Update Box" >
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
