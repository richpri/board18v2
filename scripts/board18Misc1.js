/*
 * board18Misc1.js contains all of the setup code for the board18Misc
 * page and all of the functions that manipulate the right of page
 * content that displays players for selection.
 * 
 * Copyright (c) 2020 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 *
 * All BD18 global variables are contained in one
 * 'master variable' called BD18.  This isolates 
 * them from global variables in other packages. 
 */
BD18.gamePlayers = [];

/* 
 * Function gamePlayersResult is the call back function for the
 * ajax call to gamePlayers.php. It reports any failure and then
 * displays the "List of players in game" table at the top of the
 * "rightofpage" division.
 * 
 * Input is is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||"fail"||"noplayers",
 *     "players":
 *     [
 *       {
 *         "playerid":"nnnn",
 *         "login":"pppppp",
 *         "fname":"fffffff",
 *         "lname":"ggggggg"
 *       },
 *       . . . . more players . . . . . 
 *     ]
 *   }
 * 
 */
function gamePlayersResult(response) {  
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  } 
  var resp = JSON.parse(response);
  if (resp.stat === 'success') {
    BD18.gamePlayers = resp.players;
    var pigHTML= '<table border="1" id="gplayers"> ';
    pigHTML+= '<caption>List of players in<br>' + BD18.gname;
    pigHTML+= '</caption><tr><th>Player ID</th>';
    pigHTML+= '<th>First Name</th><th>Last Name</th></tr>';
    $.each(BD18.gamePlayers,function(index,listInfo) {
      pigHTML+= '<tr class="gprow" id=';
      pigHTML+= listInfo.login + '> <td>';
      pigHTML+= listInfo.login + '</td> <td>';  
      pigHTML+= listInfo.fname + '</td> <td>';
      pigHTML+= listInfo.lname+ '</td> </tr>';
    }); // end of each
    pigHTML+= '</table>';
    $("#gplayers").remove();
    $('#gameplayerlist').append(pigHTML);  
  }
  else if (resp.stat === 'noplayers') {
    var noplHTML='<p id="gplayers" style="color: red">';
    noplHTML+='There are no players in this game!</p>';
    $("#gplayers").remove();
    $('#gameplayerlist').append(noplHTML);
  }
  else if (resp.stat === 'fail') {
    var errmsg = 'Get game players failed.\n';
    errmsg += 'Please contact the BOARD18 webmaster.';
    alert(errmsg);
  }
  else { // Something is definitly wrong in the code.
    var nerrmsg ='Invalid return code from gamePlayers.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
}

/* 
 * Function playerSortReturn() is the callback function for
 * the playerSort.php AJAX call. It creates a list of plaers
 * sorted in the specified order and displays it in one of
 * three hidden divisions. It then shows that division.
 */
function playerSortReturn(result) {
  if (result.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = JSON.parse(result);
  if (resp.stat === 'success') {
    BD18.playerList = resp.players;
    var playerHTML= '<table border="1" class="playerlist"> <tr>';
    playerHTML+= '<th>Player ID</th>';
    playerHTML+= '<th>First Name</th><th>Last Name</th></tr>';
    $.each(BD18.playerList,function(index,listInfo) {
      playerHTML+= '<tr class="playerrow" id=';
      playerHTML+= listInfo.login + '> <td>';
      playerHTML+= listInfo.login + '</td> <td>';  
      playerHTML+= listInfo.firstname + '</td> <td>';
      playerHTML+= listInfo.lastname+ '</td> </tr>';
    }); // end of each
    playerHTML+= '</table>';
    $(".playerlist").remove();
    switch (BD18.sort) {
      case 'ID':
        $('#outplayer2').append(playerHTML);
        $('#list2').show();
        break;
      case 'FN':
        $('#outplayer3').append(playerHTML);
        $('#list3').show();
        break;
      case 'LN':
        $('#outplayer4').append(playerHTML);
        $('#list4').show();
        break
    }
  } else if (resp.stat === 'empty') {
    var errmsg1 = 'There are no player records in the database.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else if (resp.stat === 'fail') {
    var errmsg2 = 'Program error in playerSort.php.\n';
    errmsg2 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg2);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from playerSort.php: ';
    nerrmsg += resp.stat + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
}

/* 
 * Function switchtoid() empties the playerid list.
 * It then hides the currently displayed list and
 * sets BD18.sort to "ID".
 * Finally it does an AJAX call to playerSort.php
 * to load BD18.playerList with a list of players
 * sorted by player ID.  
 */
function switchtoid() {
  $("#PbyIDlist").remove(); 
  $('.outbox').hide(); //hide previous list
  BD18.sort = "ID";
  var outstring = "sortkey=login";
  $.post("php/playerSort.php", outstring, playerSortReturn);
}

/* 
 * Function switchtofn() empties the playerid list.
 * It then hides the currently displayed list and
 * sets BD18.sort to "FN".
 * Finally it does an AJAX call to playerSort.php
 * to load BD18.playerList with a list of players
 * sorted by first name.  
 */
function switchtofn() {
  $("#PbyIDlist").remove(); 
  $('.outbox').hide(); //hide previous list
  BD18.sort = "FN";
  var outstring = "sortkey=firstname";
  $.post("php/playerSort.php", outstring, playerSortReturn);
}

/* 
 * Function switchtoln() empties the playerid list.
 * It then hides the currently displayed list and
 * sets BD18.sort to "LN".
 * Finally it does an AJAX call to playerSort.php
 * to load BD18.playerList with a list of players
 * sorted by last name.  
 */
function switchtoln() {
  $("#PbyIDlist").remove(); 
  $('.outbox').hide(); //hide previous list
  BD18.sort = "LN";
  var outstring = "sortkey=lastname";
  $.post("php/playerSort.php", outstring, playerSortReturn);
}
