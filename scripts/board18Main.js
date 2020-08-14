/*
 * Copyright (c) 2014 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 *
 * All BD18 global variables are contained in one
 * 'master variable' called BD18.  This isolates 
 * them from global variables in other packages. 
 */

/* Function listReturn is the success callback function for 
 * the ajax myGameList.php call. It appends a list of games
 * to the table in board18Main.php.
 */
function listReturn(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = JSON.parse(response);
  if (resp.stat === 'success') {
    var gameHTML ='';
    $.each(resp.games,function(index,listInfo) {
      gameHTML += '<tr class="gamerow"> <td>';
      gameHTML += '<a href="board18Map.php?dogame=';
      gameHTML += listInfo.game_id + ' ">';
      gameHTML += listInfo.gname + '</a></td> <td>';
      gameHTML += listInfo.bname + '</td> <td>';
      gameHTML += listInfo.version + '</td> <td>';
      gameHTML += listInfo.start_date + '</td> <td>';
      gameHTML += '<a href="board18Misc.php?dogame=';
      gameHTML += listInfo.game_id + '"><button type="button">';
      gameHTML += 'Edit</button></a> </td> </tr>';
    }); // end of each
    $('#gamelist').append(gameHTML);
  } else if (resp.stat === 'none') {
    var nogames = '<p id="gamehead">';
    nogames += 'You are not currently playing any games</p>';
    $('#games').append(nogames);
  } else if (resp.stat === 'fail') {
    var errmsg1 = 'Program error in myGameList.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from myGameList.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of listReturn


