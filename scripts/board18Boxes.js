/*
 * The board18Boxes script contains all of the js functions
 * for the board18Boxes page.
 * 
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 *
 * All BD18 global variables are contained in one 'master variable'
 * called BD18.  This isolates them from all other global variables. 
 */

/* Function listReturn is the success callback function for 
 * the ajax boxShow.php call. It appends a list if boxes
 * to the table in board18Boxes.php.
 */
function listReturn(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = jQuery.parseJSON(response);
  if (resp.stat === 'success') {
    var boxHTML= '<table id="boxlist"> <tr><th>Box ID</th>';
    boxHTML+= '<th>Box Name</th><th>Creation Date</th><th>Status</th></tr>';
    boxHTML+= '<tr class="lightrow"><th></th>';
    boxHTML+= '<th>Version</th><th>Activity Date</th><th>Games</th></tr>';
    BD18.boxes = resp.boxes;
    $.each(resp.boxes,function(index,listInfo) {
      boxHTML+= '<tr> <td class="boxid">';
      boxHTML+= listInfo.boxid + '</td> <td>';
      boxHTML+= listInfo.bname + '</td> <td>';      
      boxHTML+= listInfo.cdate + '</td> <td>';
      boxHTML+= listInfo.status + '</td></tr>';
      boxHTML+= '<tr class="lightrow"><td></td> <td>';
      boxHTML+= listInfo.version + '</td> <td>';
      boxHTML+= listInfo.adate + '</td> <td>';
      boxHTML+= listInfo.gcount + '</td> </tr>';
    }); // end of each
    boxHTML+= '</table>';
    $("#boxlist").remove();
    $('#boxes').append(boxHTML);
  } else if (resp.stat === 'noboxes') {
    boxHTML = '<p id="boxlist" class="error">';
    boxHTML+= 'There are no boxes in the database.</p>';
    $("#boxlist").remove();
    $('#boxes').append(boxHTML);
  } else if (resp.stat === 'fail') {
    var errmsg1 = 'Program error in boxShow.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from boxShow.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of listReturn

/* The doPageList function performs an AJAX call
 * to the boxShow.php function.
 */
function doPageList() {
  var blocksize = BD18.pagesize; 
  var startrow = BD18.pagesize * (BD18.curpage - 1);
  var outstring = "blocksz=" + blocksize;
  outstring += "&startrow=" + startrow;
  $.post("php/boxShow.php", outstring, listReturn);
}

/* Function doPageLinks creates page links for multi page
 * box list output. 
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
    $("#boxlist").remove();
    $("#pagelinks").append(plist);
  }
}

/* Function boxGames uses the BD18.box.games object to 
 * append a list of games associated with the box to the
 * space under the box dialog in the board18Boxes page if 
 * the box is associated with one or more games.
 */
function boxGames() {
  var gameHTML;
  if (BD18.box.stat === 'success') { // box does have games
    gameHTML= '<p id="gamehead">Game List</p>';
    gameHTML+= '<table id="gamelist"> <tr>';
    gameHTML+= '<th>Game ID</th> <th>Game Name</th>';
    gameHTML+= '<th>Status</th> </tr>'; 
    $.each(BD18.box.games,function(index,listInfo) {
      gameHTML+= '<tr> <td class="gameid">';
      gameHTML+= listInfo.gameid + '</td> <td>';      
      gameHTML+= listInfo.gname + '</td> <td>';
      gameHTML+= listInfo.status + '</td> </tr>';
    }); // end of each
    gameHTML+= '</table>';
  } else {
    gameHTML= '<p id="gamehead">There are no games in this box.</p>';
  }
  $("#paginator").remove();
  $("#gamelist").remove();
  $("#gamehead").remove();
  $('#thebox').append(gameHTML);
}

/* Function paintBox uses the BD18.box object to create a 
 * table of information about a box, to create and display a
 * box dialog and then calls the boxGames function.
 */
function paintBox() {
  $("#boxlist").remove(); 
  $('#thebox').slideUp(300);
  var getHTML = '<table id="getlist">';
  getHTML+= '<caption id="boxhead">Current field values for Box ID ';
  getHTML+= BD18.box.boxid + '</caption>';
  getHTML+= '<tr><td>Box Name: ' + BD18.box.bname + '</td>';
  getHTML+= '<td>Creation Date: ' + BD18.box.cdate + '</td></tr>';
  getHTML+= '<tr><td>Version: ' + BD18.box.version + '</td>';
  getHTML+= '<td>Activity Date: ' + BD18.box.adate + '</td></tr>';
  getHTML+= '<tr><td>Author: ' + BD18.box.author + '</td>';
  getHTML+= '<td>Box Status: ' + BD18.box.status + '</td></tr>';
  if (BD18.box.update === 'yes') {
    getHTML+= '<tr><td><span style="color: Fuchsia">';
    getHTML+= 'Update was successful.</span></td></tr>';
  }
  getHTML+= '</table><br>';
  $("#getlist").remove();
  $('#boxinfo').append(getHTML);
  $('#bname').val(BD18.box.bname);
  var statusHTML = '<label for="status">Change Status: </label>';
  statusHTML += '<select name="status" id="status">';
  if (BD18.box.status === 'Active') {
    statusHTML += '<option value="Active" selected>Active</option>';  
    statusHTML += '<option value="Disabled">Disabled</option>';
  } else {
    statusHTML += '<option value="Active">Active</option>';  
    statusHTML += '<option value="Disabled" selected>Disabled</option>';
  }
  statusHTML += '</select>';
  $('#statusselect').html(statusHTML);
  $('#thebox .error').hide();
  boxGames();
  $('#thebox').slideDown(300);
}

/* Function getReturn is the success callback function for 
 * the ajax boxGet.php call. It initializes the BD18.box
 * object and then it uses the paintBox() function to 
 * open the box dialog and to append a list if games to 
 * the space under the box dialog in the board18Boxes page.
 */
function getReturn(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = jQuery.parseJSON(response);
  if (resp.stat === 'success' || resp.stat === 'nogames') {
    BD18.box.stat = resp.stat;
    BD18.box.boxid = resp.boxid;
    BD18.box.bname = resp.bname;
    BD18.box.version = resp.version;
    BD18.box.cdate = resp.cdate;
    BD18.box.adate = resp.adate;
    BD18.box.author = resp.author;
    BD18.box.status = resp.status;
    BD18.box.games = resp.games;
    paintBox();
  } else if (resp.stat === 'fail') {
    var errmsg1 = 'Program error in boxGet.php.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from boxGet.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of getReturn

/* Function boxResult is the success callback function for 
 * the ajax boxUpdate.php call. It processes the response 
 * from boxUpdate.php. If 'success' it calls boxGet.php
 * else if not 'fail' it turns on the approperate error text
 * and returns.
 */
function boxResult(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  if (response === 'success') {
    BD18.box.update = 'yes'; 
    var outstring = "boxid=" + BD18.box.boxid;
    $.post("php/boxGet.php", outstring, getReturn);
  } else if (response === 'bname') {  
    var logmessage = 'This box name is already in use.';
    $("#bname_error").text(logmessage).show();
    $("#bname").focus();  
  } else if (response === 'fail') {
    var errmsg = 'Program error in boxUpdate.php.\n';
    errmsg += 'Please contact the BOARD18 webmaster.';
    alert(errmsg);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from boxUpdate.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of boxResult

/* The doBox function performs an AJAX call
 * to the boxGet.php function.
 */
function doBox(boxid) {
  BD18.box.update = 'no';
  var outstring = "boxid=" + boxid;
  $.post("php/boxGet.php", outstring, getReturn);
};

/* 
 * Function updateBox is called by the on-click
 * method of the update button in the thebox form. 
 * It checks the input for missing fields and then
 * does an ajax call to boxUpdate.php. 
 */
function updateBox() {
  $('.error').hide();
  var bname = $("input#bname").val();
  var version = $("input#version").val();
  if (bname === "") {
    if (version === "") {
      $("#version_error").text('This field is required.').show();
    }
    $("#bname_error").text('This field is required.').show();
    $("#bname").focus();
    return false;
  } else if (version === "") {
    $("#version_error").text('This field is required.').show();
    $("#version").focus();
    return false;
  } else {
    BD18.box.newBox = bname;
    BD18.box.newVersion = version;
  }
  var aString = $('.reg').serialize();
  aString += '&status=' + $("#status option:selected").val();
  aString += '&boxid=' + BD18.box.boxid;
  $.post("php/boxUpdate.php", aString, boxResult);
  return false;
}

