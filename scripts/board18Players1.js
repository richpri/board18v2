/*
 * The board18Players1 script contains all of the js functions
 * for the board18Players page except for the Email functions. 
 * 
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 *
 * All BD18 global variables are contained in one 'master variable'
 * called BD18.  This isolates them from all other global variables. 
 */

/* Function listReturn is the success callback function for 
 * the ajax playerShow.php call. It appends a list if players
 * to the table in board18Players.php.
 */
function listReturn(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = jQuery.parseJSON(response);
  if (resp.stat === 'success') {
    var playerHTML= '<table id="playerlist"> <tr>';
    playerHTML+= '<th>Player ID</th> <th>First Name</th>';
    playerHTML+= '<th>Last Name</th> <th>Email Address</th>';
    playerHTML+= '<th>Level</th> <th>Games</th> <th>Active</th> </tr>'; 
    BD18.players = resp.players;
    $.each(resp.players,function(index,listInfo) {
      playerHTML+= '<tr> <td class="playerid">';
      playerHTML+= listInfo.login + '</td> <td>';      
      playerHTML+= listInfo.firstname + '</td> <td>';
      playerHTML+= listInfo.lastname + '</td> <td>';
      playerHTML+= listInfo.email + '</td> <td>';
      playerHTML+= listInfo.level + '</td> <td>';
      playerHTML+= listInfo.gcount + '</td> <td>';
      playerHTML+= listInfo.acount + '</td> </tr>';
    }); // end of each
    playerHTML+= '</table>';
    $("#playerlist").remove();
    $('#players').append(playerHTML);
  } else if (resp.stat === 'fail') {
    var errmsg1 = 'Program error in playerShow.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from playerShow.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of listReturn

/* The doPageList function performs an AJAX call
 * to the playerShow.php function.
 */
function doPageList() {
  var blocksize = BD18.pagesize; 
  var startrow = BD18.pagesize * (BD18.curpage - 1);
  var outstring = "blocksz=" + blocksize;
  outstring += "&startrow=" + startrow;
  $.post("php/playerShow.php", outstring, listReturn);
}

/* Function doPageLinks creates page links for multi page
 * player list output. 
 */
function doPageLinks() {
  var pages = BD18.pagecount;
  var curpage = BD18.curpage;
  if (pages > 1) {
    var plist = "<div id='paginator'>";
    for(var i = 1; i <= pages; i++) { 
      if (i === curpage) {
        plist += "<p><a href='#' class='pagor selected'>Page ";
        plist += i + "</a></p>";
      } else {
        plist += "<p><a href='#' class='pagor'>Page ";
        plist += i + "</a></p>";
      }
    }
    plist += "</div>";
    $("#paginator").remove();
    $("#gamelist").remove();
    $("#pagelinks").append(plist);
  }
}

/* Function playerGames uses the BD18.player.games object to 
 * append a list if games associated with the player 
 * to the leftofpage division in board18Players.php if the
 * player is associated with one or more games.
 */
function playerGames() {
  if (BD18.player.stat === 'success') { // player is in games
    var gamestat;
    var gameHTML= '<table id="gamelist"> <tr>';
    gameHTML+= '<th>Game List</th>';     
    $.each(BD18.player.games,function(index,gameInfo) {
      if (gameInfo.status === 'Active') { 
        gamestat = 'greenit';
      } else {
        gamestat = 'redit';
      }
      gameHTML+= '<tr> <td class="thegame ' + gamestat + '">';
      gameHTML+= gameInfo.gname + '</td> </tr>';
    }); // end of each
    gameHTML+= '</table>';
    $("#paginator").remove();
    $("#gamelist").remove();
    $('#pagelinks').append(gameHTML);
  }
}

/* Function paintPlayer uses the BD18.player object to create a 
 * table of information about a player, to create and display a
 * player dialog and then calls the playerGames function.
 */
function paintPlayer() {
  $("#gamelist").remove();
  $('#theplayer').slideUp(300);
  var getHTML = '<table id="getlist">';
  getHTML+= '<tr><td>Player ID: ' + BD18.player.login + '</td>';
  getHTML+= '<td>Email ID ' + BD18.player.email + '</td></tr><tr>';
  getHTML+= '<tr><td>First Name: ' + BD18.player.firstname + '</td>';
  getHTML+= '<td>Player Level: ' + BD18.player.level + '</td></tr><tr>';
  getHTML+= '<tr><td>Last Name: ' + BD18.player.lastname + '</td>';
  if (BD18.player.update === 'yes') {
    getHTML+= '<td><span style="color: Fuchsia">';
    getHTML+= 'Update was successful.</span></td>';
  }
  getHTML+= '</tr>';
  getHTML+= '</table>';
  $("#getlist").remove();
  $('#playerinfo').append(getHTML);
  $('#login').val(BD18.player.login);
  $('#email').val(BD18.player.email);
  $('#fname').val(BD18.player.firstname);
  $('#lname').val(BD18.player.lastname);
  var levelHTML = '<label for="level">Change Level: </label>';
  levelHTML += '<select name="level" id="level">';
  switch (BD18.player.level) {
    case 'admin':
      levelHTML += '<option value="player">player</option>';
      levelHTML += '<option value="admin" selected>admin</option>';
      levelHTML += '<option value="author">author</option>';
      break;
    case 'author':
      levelHTML += '<option value="player">player</option>';
      levelHTML += '<option value="admin">admin</option>';
      levelHTML += '<option value="author" selected>author</option>';
      break;
    default:
      levelHTML += '<option value="player" selected>player</option>';
      levelHTML += '<option value="admin">admin</option>';
      levelHTML += '<option value="author">author</option>';
      break;
  }
  levelHTML += '</select>';
  $('#levelselect').html(levelHTML);
  $('#theplayer .error').hide();
  $('#theplayer').slideDown(300);
  playerGames();
}

/* Function getReturn is the success callback function for 
 * the ajax playerGet.php call. It initializes the BD18.player
 * object and then it uses the paintPlayer() function to 
 * open the player dialog and to append a list if games to 
 * the leftofpage division in the board18Players page.
 */
function getReturn(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = jQuery.parseJSON(response);
  if (resp.stat === 'success' || resp.stat === 'nogames') {
    BD18.player.stat = resp.stat;
    BD18.player.playerid = resp.playerid;
    BD18.player.login = resp.login;
    BD18.player.firstname = resp.firstname;
    BD18.player.lastname = resp.lastname;
    BD18.player.email = resp.email;
    BD18.player.level = resp.level;
    BD18.player.games = resp.games;
    paintPlayer();
  } else if (resp.stat === 'fail') {
    var errmsg1 = 'Program error in playerGet.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from playerGet.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of getReturn

/* Function playerResult is the success callback function for 
 * the ajax playerUpdate.php call. It processes the response 
 * from playerUpdate.php. If 'success' it calls playerGet.php
 * else if not 'fail' it turns on the approperate error text
 * and returns.
 */
function playerResult(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  if (response === 'success') {
    BD18.player.update = 'yes';
    var outstring = "login=" + BD18.player.newLogin;
    $.post("php/playerGet.php", outstring, getReturn);
  } else if (response === 'login') {  
    var logmessage = 'This player ID is already in use.';
    $("#login_error").text(logmessage).show();
    $("#login").focus();  
  } else if (response === 'bademail') {
    $("#email_error").text('Invalid email format.').show();
    $("#email").focus();
  } else if (response.substring(0, 5) === 'email') {
    var logmessage = 'Player ' + response.substring(5);
    logmessage += ' is already using this email address.';
    $("#email_error").text(logmessage).show();
    $("#email").focus();
  } else if (response === 'fail') {
    var errmsg1 = 'Program error in playerUpdate.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from playerUpdate.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of playerResult

/* The doPlayer function performs an AJAX call
 * to the playerGet.php function.
 */
function doPlayer(login) {
  BD18.player.update = 'no';
  var outstring = "login=" + login;
  $.post("php/playerGet.php", outstring, getReturn);
};

/* 
 * Function updatePlayer is called by the on-click
 * method of the update button in the theplayer form. 
 * It checks the input for missing fields and then
 * does an ajax call to playerUpdate.php. 
 */
function updatePlayer() {
  $('.error').hide();
  var name = $("input#login").val();
  if (name === "") {
    $("#login_error").text('This field is required.').show();
    $("#login").focus();
    return false;
  } else {
    BD18.player.newLogin = name;
  }
  var email = $("input#email").val();
  if (email === "") {
    $("#email_error").text('This field is required.').show();
    $("#email").focus();
    return false;
  }
  var aString = $('.reg').serialize();
  aString += '&level=' + $("#level option:selected").val();
  aString += '&player=' + BD18.player.playerid;
  $.post("php/playerUpdate.php", aString, playerResult);
  return false;
}
