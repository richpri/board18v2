/*
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 *
 * All BD18 global variables are contained in one
 * 'master variable' called BD18.  This isolates 
 * them from global variables in other packages. 
 */
BD18.goToMain  = {};
BD18.goToMain.timeoutID = 0;

/* 
 * I found this generalized numeric test function
 * at http://stackoverflow.com/questions/18082/
 *    validate-numbers-in-javascript-isnumeric 
 */
function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

/* 
 * The goToMain function transfers control to the
 * board18Main.php page.
 */
function goToMain() {
  document.location.assign('board18Main.php');
}

/* 
 * The delayGoToMain function waits 3 seconds before
 * calling the goToMain function.
 */
function delayGoToMain() {
  BD18.goToMain.timeoutID = window.setTimeout(goToMain, 3000);
}

/* 
 * The killGoToMain function stops any delayed
 * calling of the goToMain function.
 */
function killGoToMain() {
  if (BD18.goToMain.timeoutID) {
    window.clearTimeout(BD18.goToMain.timeoutID);
  }
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

/* 
 * Function makeNewGame creates a JSON object for the ajax
 * newgame call. 
 */
function makeNewGame(name, boxid, players, player) {
  var pp = [];
  var i, j;
  for(i=0; i<players; i++) { 
    pp[i] = player[i]; 
    for(j=0; j<i; j++) { // test for duplicate player.
      if (pp[j] === pp[i]) return;
    }
  }
  return JSON.stringify({
    gname : name,
    boxid : boxid,
    players : pp
  });
}

/* 
 * Function emailPlayerResult is the call back function for the
 * ajax calls to emailPlayerAdd.php. It only needs to check for
 * errors and it only needs to report the first error. Before  
 * reporting this error it will call the killGoToMain() function.
 * 
 * Output from emailPlayerAdd.php is an echo return status:
 *   "success" - Email sent.
 *   "fail"    - Uexpected error - No email sent.
 */
function emailPlayerResult(result) {
  if (response === 'fail') {
    if (BD18.mailError === false) {
      var errmsg = 'Send email to player failed.\n';
      errmsg += 'Please contact the BOARD18 webmaster.';
      killGoToMain();
      alert(errmsg);
      BD18.mailError = true;
    }
  }
  else if (response !== 'success') { 
    // Something is definitly wrong in the code.
    var nerrmsg ='Invalid return code from emailPlayers.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    killGoToMain();
    alert(nerrmsg);
  }
}

/* 
 * Function newgameOK is the callback function for the ajax
 * newgame call. After sending emails to all players it calls
 * the delayGoToMain() function.
 */
function newgameOK(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  } else if (response === "nobox") {
    $("#bi_error").text('Invalid Game Box ID.').show();  
    $("#boxid").focus();
  } else if (response === "dupname") {
    $("#sn_error").text('Duplicate Game Name is not allowed.').show();  
    $("#sessionname").focus();
  } else if (response.indexOf("noplayer") !== -1) {  
    // Response contains "noplayer".
    var plerr = 'Player #' + response.substr(9) + ' does not exist';
    $("#pc_error").text(plerr).show();  
    $("#player1").focus();
  } else if (response === "success") {
    $('#newgame .error').hide();
    $('#newgame :text').val('');
    // Send an email notification to each player in the game.
    var cString;
    BD18.mailError = false;
    for (var i = 0; i < BD18.playerCount; i++) {
        cString = 'game=' + BD18.name + '&login=' + BD18.player[i];
        $.post("php/emailPlayerAdd.php", cString, emailPlayerResult);
    }
    delayGoToMain();
  } else if (response === "fail") {
    var ferrmsg ='New game was not created due to an error.\n';
    ferrmsg += 'Please contact the BOARD18 webmaster.';
    alert(ferrmsg);
  } else { // Something is definitly wrong in the code.
    var nerrmsg ='Invalid return code from createGame.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
}

/* 
 * Function newgame is called by the on-click
 * method of the newgame submit button. It 
 * checks the input for missing fields and
 * does an ajax call to createGame.php. 
 */
function newgame() {
  $('.error').hide();  
  BD18.name = $("input#sessionname").val();  
  if (BD18.name === "") {  
    $("#sn_error").text('This field is required.').show();  
    $("#sessionname").focus();  
    return;  
  }
  var format = /[!@#$%^&*()+\=\[\]{};':"\\|,<>\/?]+/;
  if(format.test(BD18.name)){
    $("#sn_error").text('Game Name cannot contain special characters.').show();  
    $("#sessionname").focus();  
    return; 
  }
  BD18.boxid = $("input#boxid").val();
  if (BD18.boxid === "") {  
    $("#bi_error").text('This field is required.').show();  
    $("#boxid").focus();
    return; 
  }
  if (!$.isNumeric(BD18.boxid) || (parseInt(BD18.boxid) <= 0)) {  
    $("#bi_error").text('Invalid box id.').show();  
    $("#boxid").focus();  
    return;  
  }
  BD18.playerCount = $("input#pcount").val();
  if (BD18.playerCount === "") {  
    $("#pc_error").text('# of Players is required.').show();  
    $("#pcount").focus();  
    return;  
  }
  if (!isNumber(BD18.playerCount) || (BD18.playerCount < 1) || 
    (BD18.playerCount > 6)) {  
    $("#pc_error").text('# of players must be between 1 and 6.').show();  
    $("#pcount").focus();  
    return;  
  }
  var pp = 0;
  BD18.player = [];
  BD18.errtxt = "";
  $('.fn2').each(function(i) {
    BD18.player[i] = $(this).val();
    if (BD18.player[i] === "" && i < BD18.playerCount) { 
      pp = i + 1;
      BD18.errtxt = 'Player' + pp + ' is missing.';
      $("#pc_error").text(BD18.errtxt).show();  
      $(this).focus();  
      return false;  
    } 
    if (BD18.player[i] !== "" && i >= BD18.playerCount) {  
      BD18.errtxt = 'There are more than ' + BD18.playerCount 
        + ' players.';
      $("#pc_error").text(BD18.errtxt).show();  
      $("#pcount").focus();  
      return false;  
    }
  });
  if (BD18.errtxt === "") {
    var dataString = makeNewGame(BD18.name, BD18.boxid, 
    BD18.playerCount, BD18.player); 
    if (dataString === undefined) {
      $("#pc_error").text('Do not duplicate player names.').show();  
      $("#pcount").focus();  
      return; 
    }
    var postString = 'newgame=' + dataString;
    $.post("php/createGame.php", postString,  function(response) {
      newgameOK(response);
    });
  }
}
