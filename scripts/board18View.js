/*
 * Copyright (c) 2014 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 *
 * All BD18 global variables are contained in one
 * 'master variable' called BD18.  This isolates 
 * them from global variables in other packages. 
 */
BD18.bname = "";
BD18.gstat = "Active";

/* Function listReturn is the success callback function for 
 * the ajax allGameList.php call. It appends a list if games
 * to the table in board18View.php.
 */
function listReturn(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = JSON.parse(response);
  if (resp.stat === 'success') {
    $('#gamelist').remove();
    $('#gamehead').remove();
    var gcount = 0;
    var gameHTML ='<table id="gamelist"> <tr>';
    gameHTML += '<th>Game Name</th> <th>Box Name</th>  <th>Version</th>';
    gameHTML += '<th>Status</th> <th>Start Date</th> </tr>';
    $.each(resp.games,function(index,listInfo) {
      if (BD18.bname !== "" && listInfo.bname !== BD18.bname) {return;}
      gcount += 1;
      gameHTML += '<tr class="gamerow"> <td>';
      gameHTML += '<a href="board18Map.php?dogame=';
      gameHTML += listInfo.game_id + ' ">';
      gameHTML += listInfo.gname + '</a></td> <td>';
      gameHTML += listInfo.bname + '</td> <td>';
      gameHTML += listInfo.version + '</td> <td>';
      gameHTML += listInfo.status + '</td> <td>';
      gameHTML += listInfo.start_date + '</td> </tr>';
    }); // end of each
    gameHTML += '</table>';
    if (gcount === 0) {
      var nogames = '<p id="gamehead">';
      nogames += 'There are no matching games in the database.</p>';
      $('#games').append(nogames);
    } else {
      $('#games').append(gameHTML);
    }
  } else if (resp.stat === 'none') {
    $('#gamelist').remove();
    $('#gamehead').remove();
    var nogames = '<p id="gamehead">';
    nogames += 'There are no matching games in the database.</p>';
    $('#games').append(nogames);
  } else if (resp.stat === 'fail') {
    var errmsg1 = 'Program error in allGameList.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from allGameList.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of listReturn

/* Function filters is called by the submit method of the 
 * filters button. It checks the input for missing or invalid 
 * fields and then does an ajax call to allGameList.php.
 */
function filters() {
  $('.error').hide();  
  BD18.bname = $("input#bname").val();  
  var format = /[!@#$%^&*()+\=\[\]{};':"\\|,<>\/?]+/;
  if(format.test(BD18.bname)){
    $("#bname_error").text('Box Name cannot contain special characters.').show();  
    $("#bname") .trigger('focus');  
    return; 
  }
  var ascii = /^[\x00-\x7F]*$/;
  if(!ascii.test(BD18.bname)){
    $("#bname_error").text('Box Name can only contain ascii characters.').show();  
    $("#bname") .trigger('focus');  
    return; 
  }
  if(BD18.bname.length > 25){
    $("#bname_error").text('Box Name must be 25 characters or less.').show();  
    $("#bname") .trigger('focus');  
    return; 
  }
  BD18.gstat = $("input[name='status']:checked").val();

  var aString = 'gstat=' + BD18.gstat;
  $.post('php/allGameList.php', aString, listReturn);
}