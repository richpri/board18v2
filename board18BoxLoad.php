<?php
/*
 * The board18BoxLoad.php page displays the Load Game Box form. 
 * This form can be used to upload a zipped game box to BOARD18. 
 * 
 * Copyright (c) 2016 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
/*
require_once('auth.php');
if ($playerlevel != 'admin' && $playerlevel != 'author') {
  error_log("gameGet: Not an admin or author level player");
  echo $errResp;
  exit;
}
 */
require_once('php/auth.php');
require_once('php/rm_r.php');
require_once('php/loadGameBox.php');
 
if ($playerlevel != 'admin' && $playerlevel != 'author') {
  error_log("loadGameBox:loadBox: Not an admin or author level player");
  $_SESSION['SESS_HEADER_MESSAGE'] = 'Not an admin or author level player';
  header("location: board18Admin.php");
	exit;
}

if(isset($_POST['submit'])) { // If reload via zform submit.
  $reportout = loadBox($_FILES,$loggedinplayer);
}

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Games</title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/board18com-160521.css" />
    <link rel="stylesheet" href="style/board18BoxLoad.css" />
    <script type="text/javascript" src="scripts/jquery.js">
    </script> 
    <script type="text/javascript" src="scripts/jquery.ui.position.js">
    </script>
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18BoxLoad.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        if (!window.File) { // Check for File API support.
           // The File API is not supported.
           alert('The file upload API is not supported on this browser.');
        }
        $('#content .error').hide();
        $('#zfile').val('');
        <?php
        if (isset($_POST['submit'])) { // If reload via zform submit.
          echo "zipBoxOk($reportout);";
        }
        ?>
        $('#buttonz2').click(function() {
          $('#content .error').hide();
          $('#zfile').val('');
          return false;
        }); // end buttonz2 click
        $('#buttonz3').click(function() {
          window.location = 'board18Main.php';
          return false;
        }); // end buttonz3 click
        $('#buttonf').click(function() {
          $('#failmsg').hide();
          return false;
        }); // end buttonf click
        $('#buttons').click(function() {
          $('#successmsg').hide();
          return false;
        }); // end buttons click
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
    </div>
    <div id="leftofpage">
    </div>
    <div id="rightofpage"> 
      <div id="content">
        <form action="board18BoxLoad.php" method="post"
              name="loadbox" class="boxform" id="zform" 
              enctype="multipart/form-data">
          <fieldset>
            <p>
              <label for="zfile">Select ZIP file to upload:</label><br>
              <input type="file" id="zfile" name="zfile" required/>
              <label class="error" for="zfile" id="zfile_error"></label>
            </p>
            <p>
              <input type="submit" name="submit" class="zfbutton"  
                     id="buttonz1" value="Load Gamebox" >
              <input type="button" name="resbutton" class="zfbutton"  
                     id="buttonz2" value="Reset Form">
              <input type="button" name="canbutton" class="zfbutton"  
                     id="buttonz3" value="Exit"><br>
            </p>
            <p>
              Loading the Gamebox may take a few minutes. Please be patient.
            </p>
          </fieldset>
        </form>
      </div>
      <div id="successmsg" class="hidemsg">
        <p>The game box creation was successful.
        </p>
        <p>You will be emailed a detailed report.
        </p>
        <input type="button" name="buttons" class="zfbutton"  
               id="buttons" value="Exit">
      </div>
      <div id="failmsg" class="hidemsg">
        <p>The game box creation failed.
        </p>
        <p>You will be emailed a detailed report.
        </p>
        <input type="button" name="buttonf" class="zfbutton"  
               id="buttonf" value="Exit">
      </div>
    </div>  
  </body>
</html>