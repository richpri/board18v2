<?php
  require_once('php/config.php');
?>

<!doctype html>
<html lang="en">
<!--
The index page is one of the two pages which is not password protected. 
It is the introductory page to BOARD18 and contains a welcome message. 
It is used for logging in to Board18 and for registering new players. 
It also contains player ID and password recovery functions. 

Most changes made to this file should also be made to access-denied.html

Copyright (c) 2014 Richard E. Price under the The MIT License.
A copy of this license can be found in the LICENSE.text file.
-->
  <head>
    <meta charset="utf-8" />
    <title>BOARD18 - Remote Play Tool For 18xx Style Games</title>
    <link rel="shortcut icon" href="images/favicon.ico" >
    <link rel="stylesheet" href="style/board18com.css" />
    <link rel="stylesheet" href="style/board18Index.css" />
    <script type="text/javascript" src="scripts/jquery.js">
    </script> 
    <script type="text/javascript" src="scripts/sha256-min.js">
    </script>
    <script type="text/javascript" src="scripts/board18com.js">
    </script>
    <script type="text/javascript" src="scripts/board18index.js">
    </script>
    <script type="text/javascript" >
      $(function() {
        $('.error').hide(); 
        $('#pointer').hide(); 
        BD18.redirectURL = "board18Main.php";
        BD18.LTPAselector = null;
        var LTPAlocal = BD18.docCookies.getItem('LTPAlocal');
        if(LTPAlocal !== null){
          var LTPAarray = LTPAlocal.split(":",2);
          BD18.LTPAselector = LTPAarray[0];
          var LTPAvalidator = LTPAarray[1];
          // Look up selector in database and check validator using 
          // tokenCheck.php. If validator matches then bypass signon.
          var hash = hex_sha256(LTPAvalidator);
          var dataString = 'selector=' + BD18.LTPAselector + '&validator=' + hash;
          $.post("php/tokenCheck.php", dataString, tokenCheckReturn);
        } // end remember me check
        var RMpersist = BD18.docCookies.getItem('RMpersist');
        if(RMpersist !== null){
          $('#rememberme').prop('checked', true);
        } else {
          $('#rememberme').prop('checked', false);
        } // end Persistent check
        $("#login").submit(function() {  
          login();
          return false;
        }); // end login
        $("#buttonx").click(function(){
          $('#lostid form').slideDown(300);
          return false;
        }); // end buttonx click
        $("#regbutton").click(function(){
          $('#register .error').hide();
          $('#register :password').val('');
          $('#register :text').val('');
          $('#register form').slideDown(300);
          $("#newuser").focus();
          return false;
        }); // end regbutton click
        $("#helpbutton").click(function(){
          window.open(BD18.help, "HelpGuide");
          return false;
        }); // end helpbutton click
        $("#buttony").click(function(){
          $('#lostpw form').slideDown(300);
          return false;
        }); // end buttony click
        $("#register").submit(function() {  
          register();
          return false;
        }); // end register submit
        $("#button3").click(function(){  //cancel register
          $('#register form').slideUp(300);
          return false;
        }); // end button3 click
        $("#lostid").submit(function() {  
          lostid();
          return false;
        }); // end lostid submit
        $("#button5").click(function(){
          $('#lostid form').slideUp(300);
          $('#login form').slideDown(300);
          return false;
        }); // end button5 click
        $("#lostpw").submit(function() {  
          lostpw();
          return false;
        }); // end lostpw submit
        $("#button7").click(function(){
          $('#lostpw form').slideUp(300);
          $('#login form').slideDown(300);
          return false;
        }); // end button5 click      
        $("#content").click(function(){
          $('#lostpw form').hide();
          $('#lostid form').hide();
          $('#register form').hide();
        }); // end content click
        $("#leftofpage").click(function(){
          $('#lostpw form').hide();
          $('#lostid form').hide();
          $('#register form').hide();
        }); // end leftofpage click
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
        <p id="lognote"></p>
      </div>
    </div>
    
    <div id="leftofpage">

    </div>
    
    <div id="rightofpage"> 
      <div id="login">
        <form name="login" action="">
          <fieldset>
            <p style="font-size: 130%">Login to BOARD18</p>
            <p>
              <label for="username">Player ID:</label>
              <input type="text" name="username" id="username">
              <label class="error" for="username" id="name_error">
                This field is required.</label>
            </p>
            <p>
              <label for="password">Password: </label>
              <input type="password" name="password" id="password">
              <label class="error" for="password" id="password_error">
                This field is required.</label>
            </p>
            <p>
              <input type="submit" name="pwbutton" class="pwbutton" 
                     id="button1" value="Login" >
              <label class="error" for="button1" id="signon_error">
                Username or password is invalid.</label>
              <input type="checkBox" name="rememberme"
                id="rememberme" value="yes" >
              <label for="rememberme">Remember me.</label>
            </p>
            <p>
              <?php if (ENABLE_REGISTRATION) { ?>
                <input type="button" name="regbutton" class="pwbutton" 
                     id="regbutton" value="Register" >
              <?php } ?>
              <input type="button" name="helpbutton" class="pwbutton" 
                     id="helpbutton" value="Help" >
            </p>
            <p>
              <input type="button" name="lostbuttonx" class="pwbutton" 
                     id="buttonx" value="Forgot your Player ID?" >
              <input type="button" name="lostbuttony" class="pwbutton" 
                     id="buttony" value="Forgot your Password?" >
            </p>
          </fieldset>
        </form>
      </div>
      <?php 
        include_once("./view-player-register.php");
        viewPlayerRegister("", "", "Register your Player ID", true);
      ?>
      <div id="lostid">
        <form name="lostid" class="hideform" action="">
          <fieldset>
            <p style="font-size: 130%">
              Enter the email address that we have on file for you
              and your Player ID will be emailed to you.
            </p>
            <p>
              <label for="email1">Email Address: </label>
              <input type="text" name="email1" id="email1">
              <label class="error" for="email1" id="email1_error">
                This field is required.</label>
            </p>
            <p>
              <input type="submit" name="regbutton1" class="pwbutton"  
                     id="button4" value="Submit" >
              <input type="button" name="canbutton1" class="pwbutton"  
                     id="button5" value="Cancel" >              
            </p>
          </fieldset>
        </form>
      </div>
      <div id="lostpw">
        <form name="lostpw" class="hideform" action="">
          <fieldset>
            <p style="font-size: 110%">
              Enter your Player ID and Email Address. <br>
              A temporary Password will be emailed to you.
            </p>
            <p>
              <label for="username1">Player ID:</label>
              <input type="text" name="username1" id="username1">
              <label class="error" for="username1" id="name1_error">
                This field is required.</label>
            </p>
            <p>
              <label for="email2">Email Address: </label>
              <input type="text" name="email2" id="email2">
              <label class="error" for="email2" id="email2_error">
                This field is required.</label>
            </p>
            <p>
              <input type="submit" name="regbutton2" class="pwbutton"  
                     id="button6" value="Submit" >
              <input type="button" name="canbutton2" class="pwbutton"  
                     id="button7" value="Cancel" >              
            </p>
          </fieldset>
        </form>
      </div>   
      <div id="content" style="padding-right: 10px">    
        <p style="font-size: 130%"><b>Welcome to Board18.</b>
        </p>
        <p>BOARD18 is intended to be used with a spreadsheet to play an 
          18xx style game remotely.  The Google+ spreadsheet is a good 
          choice for this. As each player takes a turn, he will send an 
          Email to the other players. The Email will contain a list of 
          recent moves ending with his current move. If you don't use 
          Google+, the Email will also have to contain an 
          updated copy of the spread sheet being use to track the game 
          finances. 
        </p>
        <p>
          The BOARD18 game session associated with the game will be used 
          to display the game board and the stock market for the game. 
          Any active player can update the game.
        </p>
        <p><span style="font-size: 130%">NOTES:</span></p>
        <ol> 
          <li>BOARD18 is designed to take advantage of the HTML5 standard.
          Older releases of some browsers do not fully support this 
          standard.</li>
          <li>Some features of BOARD18 require that cookies be enabled on 
          the browser.</li>
        </ol>
      </div>
    </div>  

  </body>
</html>
