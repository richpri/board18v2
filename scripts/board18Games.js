/*
 * The board18Games script contains all of the js functions
 * for the board18Games page.
 * 
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 *
 * All BD18 global variables are contained in one 'master variable'
 * called BD18.  This isolates them from all other global variables. 
 */

/* Function listReturn is the success callback function for 
 * the ajax gameShow.php call. It appends a list if games
 * to the table in board18Games.php.
 */
function listReturn(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = jQuery.parseJSON(response);
  if (resp.stat === 'success') {
    var gameHTML= '<table id="gamelist"> <tr><th>Game ID</th>';
    gameHTML+= '<th>Game Name</th><th>Start Date</th><th>Status</th></tr>';
    gameHTML+= '<tr class="lightrow"><th></th>';
    gameHTML+= '<th>Game Box</th><th>Activity Date</th><th>Players</th></tr>';
    BD18.games = resp.games;
    $.each(resp.games,function(index,listInfo) {
      gameHTML+= '<tr> <td class="gameid">';
      gameHTML+= listInfo.gameid + '</td> <td>';
      gameHTML+= listInfo.gname + '</td> <td>';      
      gameHTML+= listInfo.sdate + '</td> <td>';
      gameHTML+= listInfo.status + '</td></tr>';
      gameHTML+= '<tr class="lightrow"><td></td> <td>';
      gameHTML+= listInfo.bname + '</td> <td>';
      gameHTML+= listInfo.adate + '</td> <td>';
      gameHTML+= listInfo.pcount + '</td> </tr>';
    }); // end of each
    gameHTML+= '</table>';
    $("#gamelist").remove();
    $('#games').append(gameHTML);
  } else if (resp.stat === 'nogames') {
    gameHTML = '<p id="gamelist" class="error">';
    gameHTML+= 'There are no games in the database.</p>';
    $("#gamelist").remove();
    $('#games').append(gameHTML);
  } else if (resp.stat === 'fail') {
    var errmsg1 = 'Program error in gameShow.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from gameShow.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of listReturn

/* The doPageList function performs an AJAX call
 * to the gameShow.php function.
 */
function doPageList() {
  var blocksize = BD18.pagesize; 
  var startrow = BD18.pagesize * (BD18.curpage - 1);
  var outstring = "blocksz=" + blocksize;
  outstring += "&startrow=" + startrow;
  $.post("php/gameShow.php", outstring, listReturn);
}

/* Function doPageLinks creates page links for multi page
 * game list output. 
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
    $("#playerlist").remove();
    $("#pagelinks").append(plist);
  }
}

/* Function gamePlayers uses the BD18.game.players object to 
 * append a list of players associated with the game to the
 * space under the game dialog in the board18Games page if 
 * the game is associated with one or more players.
 */
function gamePlayers() {
  var playerHTML;
  if (BD18.game.stat === 'success') { // game does have players
    playerHTML= '<p id="playerhead">Player List</p>';
    playerHTML+= '<table id="playerlist"> <tr>';
    playerHTML+= '<th>Login</th> <th>First Name</th>';
    playerHTML+= '<th>Last Name</th> </tr>'; 
    $.each(BD18.game.players,function(index,listInfo) {
      playerHTML+= '<tr> <td class="playerid">';
      playerHTML+= listInfo.login + '</td> <td>';      
      playerHTML+= listInfo.fname + '</td> <td>';
      playerHTML+= listInfo.lname + '</td> </tr>';
    }); // end of each
    playerHTML+= '</table>';
  } else {
    playerHTML= '<p id="playerhead">There are no players in this game.</p>';
  }
  $("#paginator").remove();
  $("#playerlist").remove();
  $("#playerhead").remove();
  $('#thegame').append(playerHTML);
}

/* Function paintGame uses the BD18.game object to create a 
 * table of information about a game, to create and display a
 * game dialog and then calls the gamePlayers function.
 */
function paintGame() {
  $("#playerlist").remove();
  $('#thegame').slideUp(300);
  var getHTML = '<table id="getlist">';
  getHTML+= '<caption id="gamehead">Current field values for Game ID ';
  getHTML+= BD18.game.gameid + '</caption>';
  getHTML+= '<tr><td>Game Name: ' + BD18.game.gname + '</td></tr>';
  getHTML+= '<tr><td>Box Name: ' + BD18.game.bname + '</td>';
  getHTML+= '<td>Activity Date: ' + BD18.game.adate + '</td></tr>';
  getHTML+= '<tr><td>Box Version: ' + BD18.game.version + '</td>';
  getHTML+= '<td>Start Date: ' + BD18.game.sdate + '</td></tr>';
  getHTML+= '<tr><td>Last Updater: ' + BD18.game.lastupdater + '</td>';
  getHTML+= '<td>Game Status: ' + BD18.game.status + '</td></tr>';
  if (BD18.game.update === 'yes') {
    getHTML+= '<tr><td><span style="color: Fuchsia">';
    getHTML+= 'Update was successful.</span></td></tr>';
  }
  getHTML+= '</table><br>';
  $("#getlist").remove();
  $('#gameinfo').append(getHTML);
  $('#gname').val(BD18.game.gname);
  var statusHTML = '<label for="status">Change Status: </label>';
  statusHTML += '<select name="status" id="status">';
  if (BD18.game.status === 'Active') {
    statusHTML += '<option value="Active" selected>Active</option>';  
    statusHTML += '<option value="Completed">Completed</option>';
  } else {
    statusHTML += '<option value="Active">Active</option>';  
    statusHTML += '<option value="Completed" selected>Completed</option>';
  }
  statusHTML += '</select>';
  $('#statusselect').html(statusHTML);
  $('#thegame .error').hide();
  gamePlayers();
  $('#thegame').slideDown(300);
}

/* Function getReturn is the success callback function for 
 * the ajax gameGet.php call. It initializes the BD18.game
 * object and then it uses the paintGame() function to 
 * open the game dialog and to append a list if players to 
 * the space under the game dialog in the board18Games page.
 */
function getReturn(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = jQuery.parseJSON(response);
  if (resp.stat === 'success' || resp.stat === 'noplayers') {
    BD18.game.stat = resp.stat;
    BD18.game.gameid = resp.gameid;
    BD18.game.gname = resp.gname;
    BD18.game.boxid = resp.boxid;
    BD18.game.bname = resp.bname;
    BD18.game.version = resp.version;
    BD18.game.sdate = resp.sdate;
    BD18.game.adate = resp.adate;
    BD18.game.lastupdater = resp.lastupdater;
    BD18.game.status = resp.status;
    BD18.game.players = resp.players;
    paintGame();
  } else if (resp.stat === 'fail') {
    var errmsg1 = 'Program error in gameGet.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from gameGet.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of getReturn

/* Function gameResult is the success callback function for 
 * the ajax gameUpdate.php call. It processes the response 
 * from gameUpdate.php. If 'success' it calls gameGet.php
 * else if not 'fail' it turns on the approperate error text
 * and returns.
 */
function gameResult(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  if (response === 'success') {
    BD18.game.update = 'yes'; 
    var outstring = "gameid=" + BD18.game.gameid;
    $.post("php/gameGet.php", outstring, getReturn);
  } else if (response === 'gname') {  
    var logmessage = 'This game name is already in use.';
    $("#gname_error").text(logmessage).show();
    $("#gname") .trigger('focus');  
  } else if (response === 'fail') {
    var errmsg = 'Program error in gameUpdate.php.\n';
    errmsg += 'Please contact the BOARD18 webmaster.';
    alert(errmsg);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from gameUpdate.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of gameResult

/* The doGame function performs an AJAX call
 * to the gameGet.php function.
 */
function doGame(gameid) {
  BD18.game.update = 'no';
  var outstring = "gameid=" + gameid;
  $.post("php/gameGet.php", outstring, getReturn);
};

/* 
 * Function updateGame is called by the on-click
 * method of the update button in the thegame form. 
 * It checks the input for missing fields and then
 * does an ajax call to gameUpdate.php. 
 */
function updateGame() {
  $('.error').hide();
  var gname = $("input#gname").val();
  if (gname === "") {
    $("#gname_error").text('This field is required.').show();
    $("#gname") .trigger('focus');
    return false;
  } else {
    BD18.game.newGname = gname;
  }
  var aString = $('.reg').serialize();
  aString += '&status=' + $("#status option:selected").val();
  aString += '&gameid=' + BD18.game.gameid;
  $.post("php/gameUpdate.php", aString, gameResult);
  return false;
}

