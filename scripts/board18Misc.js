/*
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

/* 
 * All BD18 global variables are contained in one
 * 'master variable' called BD18.  This isolates 
 * them from global variables in other packages. 
 */
BD18.self = 0;

/* 
 * Function emailPlayerResult is the call back function for the
 * ajax calls to emailPlayerAdd.php and emailPlayerRem.php. 
 * 
 * Output from either program is an echo return status:
 *   "success" - Email sent.
 *   "fail"    - Uexpected error - No email sent.
 */
function emailPlayerResult(response) {
  if (response === 'success') {
    if (BD18.pcount === 2) {
      BD18.pcount = 1;
    } else {
      window.location.reload(true);
    }
  }
  else if (response === 'fail') {
    var errmsg = 'Send email to player failed.\n';
    errmsg += 'Please contact the BOARD18 webmaster.';
    alert(errmsg);
  }
  else { // Something is definitly wrong in the code.
    var nerrmsg ='Invalid return code from emailPlayers.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
}

/* 
 * Function changePlayerResult is the call back function for the
 * ajax changePlayers.php call. 
 * 
 * Output from changePlayers.php is an echo return status:
 *   "success" - All changes have been made.
 *   "fail"    - Uexpected error - No changes have been made.
 *   "dupadd"  - Add ID is duplicate - No changes have been made.
 */
function changePlayerResult(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  else if (response === 'success') {
    var cString;
    BD18.pcount = 1;
    switch(BD18.mode) {
      case 1:
        cString = 'game=' + BD18.gname + '&login=' + BD18.prem;
        $.post("php/emailPlayerRem.php", cString, emailPlayerResult);
        break;
      case 2:
        cString = 'game=' + BD18.gname + '&login=' + BD18.padd;
        $.post("php/emailPlayerAdd.php", cString, emailPlayerResult);     
        break;
      case 3:
        cString = 'game=' + BD18.gname + '&login=' + BD18.padd;
        $.post("php/emailPlayerAdd.php", cString, emailPlayerResult);
        cString = 'game=' + BD18.gname + '&login=' + BD18.prem;
        $.post("php/emailPlayerRem.php", cString, emailPlayerResult);
        BD18.pcount = 2;
    }
  }
  else if (response === 'dupadd') {
    $("#pname4_error").text('Player is already in game.').show();
    $("#pname4") .trigger('focus');
  }
  else if (response === 'fail') {
    var errmsg = 'Data Base update failed.\n';
    errmsg += 'Please contact the BOARD18 webmaster.';
    alert(errmsg);
  }
  else { // Something is definitly wrong in the code.
    var nerrmsg ='Invalid return code from changePlayers.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
}

/* 
 * Function changePlayer is called by the on-
 * click method of the players submit button. 
 * It sets BD18.mode based on missing fields
 * and does an ajax call to changePlayers.php. 
 */
function changePlayer(login, game) {
  $('.error').hide();
  BD18.mode = 0;
  var prem = $("input#pname3").val();
  if (prem !== "") {  // Has a "remove player" login been entered?
    if (prem === login) { // Is player trying to remove himself??
      if (BD18.self === 0) { // Is this the first try?
        $("#pname3_error").show(); // Ask if he is sure.
        $("#pname3") .trigger('focus');
        BD18.self = 1;
        return false;
      }
    }
    BD18.mode += 1; // Have changePlayers.php do a remove.
    BD18.prem = prem;
  }
  var padd = $("input#pname4").val();
  if (padd !== "") { // Has an "add player" login been entered?
    BD18.mode += 2; // Have changePlayers.php do an add.
    BD18.padd = padd;
  } else if (BD18.mode === 0) { // If nothing to do.
    $("#pname4_error").show();
    $("#pname4") .trigger('focus');
    return false;
  }
  var cString = 'mode=' + BD18.mode.toString();
  cString += '&game=' + game;
  cString += '&prem=' + prem + '&padd=' + padd;
  $.post("php/changePlayers.php", cString, changePlayerResult);
  return false;
}

/*
 * Function linkReturn is the call back function for the
 * ajax linkGet.php call. It creates and displays the
 * game_link update division for the current game session.
 *
 * The response is the following stringified JSON data structure. 
 *   {
 *     "stat":"success||none||fail",
 *     "links":
 *     [
 *       {
 *         "link_name":"aaaaaa",
 *         "link_url":"bbbbbbb",
 *         "act_date":"mm/dd/yyyy"
 *       },
 *       . . . . more links . . . . . 
 *     ]
 *   }
 */
 function linkReturn(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = JSON.parse(response);
  var linkHTML;
  if (resp.stat === 'success') {
    linkHTML= '<table id="links"> <tr><th>Name</th>';
    linkHTML+= '<th>Link URL</th><th>Act. Date</th></tr>';
    BD18.links = resp.links;
    $.each(resp.links,function(index,listInfo) {
      linkHTML+= '<tr> <td class="deleteflag">';
      linkHTML+= listInfo.link_name + '</td> <td>';      
      linkHTML+= listInfo.link_url + '</td> <td>';
      linkHTML+= listInfo.act_date.substring(0,10) + '</td> </tr>';
    }); // end of each
    linkHTML+= '</table>';
    $("#links").remove();
    $('#linklist').append(linkHTML);  
    $("#lname1").val([]);
    $("#lnkurl1").val([]);
    $('#linkdiv').slideDown(300);
    $('#sidebar').hide();
  } else if (resp.stat === 'none') {
    linkHTML = '<p id="links" class="error">';
    linkHTML+= 'There are no links for this game.</p>';
    $("#links").remove();
    $('#linklist').append(linkHTML);
    $("#lname1").val([]);
    $("#lnkurl1").val([]);
    $('#linkdiv').slideDown(300);
    $('#sidebar').hide();
  } else if (resp.stat === 'fail') {
    var errmsg1 = 'Program error in linkGet.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from linkGet.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of linkReturn

/* The delReturn function is the call back function for the
 * ajax linkDel.php call. It processes the response. 
 * On success, it calls the linkGet function.  
 * The response is the echo return status: 
 *   "success", "fail" or "missing".
 */
function delReturn(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  if (response === 'success') {
    var linkstring = 'gameid=' + BD18.dogame;
    $.post('php/linkGet.php', linkstring,  linkReturn);
  } else if (response === 'missing') {
    var errmsg1 = '"' + BD18.selectDelete;
    errmsg1 += '" was not found by linkDel.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else if (response === 'fail') {
    var errmsg2 = 'Program error in linkDel.php.\n';
    errmsg2 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg2);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from linkDel.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of delReturn

/* The deleteLink function is called by the on-click method  
 * of the "Yes" button on the delpop form. It  performs an 
 * AJAX call to the linkDel.php function.
 */
function deleteLink(gameid,linkname) {
  var outstring = "gameid=" + gameid + "&linkname=" + linkname;
  $.post("php/linkDel.php", outstring, delReturn);
};

/* The deleteCheck function is called by the on-click method  
 * of the Link Name. It queries to make sure that the delete 
 * request is real.
 */
function deleteCheck(linkname) {
  BD18.selectDelete = linkname;
  $("#delname").html(BD18.selectDelete);
  $("#delgrey").show();
  $("#delpop").slideDown(300);
};

/* The addReturn function is the call back function for the
 * ajax linkAdd.php call. It processes the response. 
 * On success, it calls the linkGet function. 
 * The response is the echo return status: 
 *   "success", "fail" or "duplicate".
 */
function addReturn(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  if (response === 'success') {
    var linkstring = 'gameid=' + BD18.dogame;
    $.post('php/linkGet.php', linkstring,  linkReturn);
  } else if (response === 'duplicate') {
    var dupNote = 'A link named ' + BD18.newlink + ' already exists.';
    doLogNote(dupNote);
    $("#lname1_error").show();
    $("#lname1") .trigger('focus');
  } else if (response === 'fail') {
    var errmsg1 = 'Program error in linkDel.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from linkDel.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
} // end of addReturn

/* The addLink function is called by the on-click method of the
 * Add button in the link form. It checks the input parameters 
 * for validity and then performs an AJAX call to the 
 * linkAdd.php function.
 */
function addLink() {
  $('.error').hide();  
  var lname1 = $("input#lname1").val();
  if (lname1 === "") {
    $("#lname1_error").show();
    $("#lname1") .trigger('focus');
    return false;
  }
  BD18.newlink = lname1;
  var lnkurl1 = $("input#lnkurl1").val();
  if (lnkurl1 === "") {
    $("#lnkurl1_error").show();
    $("#lnkurl1") .trigger('focus');
    return false;
  }
  var outstring = "gameid=" + BD18.dogame + "&linkname=";
  outstring += lname1 + "&linkurl=" + lnkurl1;
  $.post("php/linkAdd.php", outstring, addReturn);
}; 