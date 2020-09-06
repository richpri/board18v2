/*
 * board18New1.js contains all of the setup code for the board18New
 * page and all of the functions that manipulate the right of page
 * content that displays game boxes and players for selection.
 * 
 * Copyright (c) 2020 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 *
 * All BD18 global variables are contained in one
 * 'master variable' called BD18.  This isolates 
 * them from global variables in other packages. 
 */
BD18.boxList = {};
BD18.playerList = {};
BD18.filter = 0;

/* 
 * Function BoxListResult() is the call back function for 
 * the boxGetAll.php call. It creates the BD18.boxList object.
 * It then calls fillBoxList().
 */
function BoxListResult(result) {
  if (result.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = JSON.parse(result);
  if (resp.stat === 'success') {
    BD18.boxList = resp.boxes;
    fillBoxList();
    $('gamebox').show(); //show gamebox
  } else if (resp.stat === 'noboxes') {
    var errmsg1 = 'There are no boxes in the database.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else if (resp.stat === 'fail') {
    var errmsg2 = 'Program error in box.Filter.php.\n';
    errmsg2 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg2);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from box.Filter.php: ';
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
 * sorted by player login.  
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

/* 
 * Function dofilter() applies the filter entered in filter1
 * to the game box list.
 */
function dofilter() {
  var filter = $("input#filter1").val();
  if (filter === "") {
    BD18.filter = 0;
  } else {
    BD18.filter = filter;
  }
  fillBoxList();
}

/* 
 * Function playerSortReturn() is a callback function for
 * the playerSort.php AJAX call. It creates a list of players
 * sorted in the specified order and displays it 1n one of
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
 * Function playerSortDiscard() is a callback function for
 * the playerSort.php AJAX call. It is used only once during
 * the initial setup of board18New. It creates a list of 
 * players in BD18.playerList but then does nothing with it.
 * It then does an ajax call to boxGetAll.php to load and 
 * display the BD18.BoxList.
 */
function playerSortDiscard(result) {
  if (result.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = JSON.parse(result);
  if (resp.stat === 'success') {
    BD18.playerList = resp.players;
    $.post("php/boxGetAll.php", BoxListResult);
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
 * Function fillBoxList() creates and inserts the boxList table
 * into the gamebox division of the page. It filters this list
 * if BD18.filter is not zero.
 */
function fillBoxList() {
  var matchit;
  var tobematched;
  var boxHTML= '<table border="1" id="boxlist"> <tr><th>Box Name</th>';
  boxHTML+= '<th>Version</th><th>Author</th><th>Creation Date</th></tr>';
  $.each(BD18.boxList,function(index,listInfo) {
    if (BD18.filter !== 0) {
      matchit = BD18.filter.toLowerCase();
      tobematched = listInfo.bname.toLowerCase();
      if (tobematched.toString().indexOf(matchit) === -1) {
        return;
      }
    }
    boxHTML+= '<tr class="boxrow" id=';
    boxHTML+= listInfo.boxid + '> <td>';
    boxHTML+= listInfo.bname + '</td> <td>'; 
    boxHTML+= listInfo.version + '</td> <td>';
    boxHTML+= listInfo.author + '</td> <td>';
    boxHTML+= listInfo.cdate.substring(0,10) + '</td> </tr>';
  }); // end of each
  boxHTML+= '</table>';
  $("#boxlist").remove();
  $('#boxes').append(boxHTML);
  $('#list1').show();
}

/* 
 * Function FillInfoBox() fills the infobox division with
 * the name and version of the selected box.
 */
function FillInfoBox(boxid) {
  $.each(BD18.boxList, function(index,listInfo) {
    if (listInfo.boxid === boxid) {
      $('#infoname').text(listInfo.bname);
      $('#infoversion').text(listInfo.version);
      return false; // breaks
    }
  });
}

/* 
 * Function addPlayer adds a player to the newgame form. 
 */
function addPlayer(player) {
  $('.fn2').each(function(i) {
    if ($(this).val() === "") { 
      $(this).val(player); 
      return false; 
    } 
  });
}

