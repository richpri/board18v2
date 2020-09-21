/* 
 * board18Map7.js contains the function that implements the
 * keyboard shortcut events and the functions that implement the
 * calls to the checkForUpdate.php and snapShot.php routines.
 * 
 * It now also contains the code for the new snapshot function.
 *
 * All BD18 global variables are contained in one
 * 'master variable' called BD18.  This isolates 
 * them from global variables in other packages
 * 
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

/* 
 * The setUpKeys function performs all of the bind 
 * operations for the keyboard shortcut events.
 *   KEY  Action
 *  Enter Accept Move
 *  C/ESC Cancel Move
 *    Z   Undo Move
 *    Y   Redo Move
 *    H   Hide/Show
 *    S   Take Snapshot
 *    M   Goto Stock Chart
 *    O   Goto Main Page
 *    X   Logout
 *    F   Flip Token
 *    R   Rotate Tile Clockwise
 *    E   Rotate Tile Counterclockwise
 */
function setUpKeys() {
  $(document).on("keydown",function(e){
    if (BD18.isSnap === false) {
  	  var keycode = (e.keyCode ? e.keyCode : e.which);
      switch(keycode) {
        case 13: // "Enter" keycode
          acceptMove();
          break;
        case 27:// ESC keycode
        case 67: // "C" keycode
          cancelMove();
          break;  
	case 90: // "Z" keycode
	  historyMove(-1);
          break;
 	case 89: // "Y" keycode
	  historyMove(1);
          break;
	case 72: // "H" keycode
	  hideShow();
	  break;
	case 83: // "S" keycode
	  $('#snapname .error').hide();
	  $('#snapname :text').val('');
	  $('#snapname form').slideDown(300);
	  BD18.isSnap = true;
	  $('#rname') .trigger('focus');
	  break;
        case 77: // "M" keycode
          window.location = "board18Market.php?dogame=" + BD18.gameID;
          break;
        case 79: // "O" keycode
          window.location = "board18Main.php";
          break;
	case 88: // "X" keydode
	  $.post('php/logout.php', logoutOK);
          break; 
        case 70: // "F" keycode
          if (BD18.hexIsSelected === true && 
              BD18.tokenIsSelected === true){
            flipToken();
          };
          break; 
        case 82: // "R" keycode
          if (BD18.hexIsSelected === true && 
              BD18.tileIsSelected === true){
            rotateTile("cw");
          };
          break; 
        case 69: // "E" keycode
          if (BD18.hexIsSelected === true && 
              BD18.tileIsSelected === true){
            rotateTile("ccw");
          };
          break; 
        default:
      }
      e.preventDefault();
    }
  });
};

/* 
 * The checkForUpdateCallback function is the callback function
 * for the checkForUpdate function. It acts on the status
 * returned by the checkForUpdate.php AJAX call.
 */
function checkForUpdateCallback(resp) {
  if (resp.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  } 
  var msg;
  if(resp === 'failure') {
    msg = "The page refresh function failed. This should not happen.";
    msg += "Contact the site administrator about this error.";
    alert(msg);
  }
  else if(resp === 'noupdate') {
    resetCheckForUpdate();
  }
  else if(resp === 'updatefound') {
    document.location.reload(true);
  }
  else {
    msg = "Invalid return code from checkForUpdate.php.\n";
    msg += resp + "\nContact the site administrator about this error.";
    alert(msg);
  }
}


/* 
 * The checkForUpdate function does an AJAX post call 
 * for checkForUpdate.php passing the gameid.
 */
function checkForUpdate() {
  var outstring = "gameid=" + BD18.gameID;
  $.post("php/checkForUpdate.php", outstring, checkForUpdateCallback);
}

/* 
 * The delayCheckForUpdate function waits 2 minutes before
 * calling the checkForUpdate function.
 */
function delayCheckForUpdate() {
  BD18.checkForUpdateTimeout = 
    window.setTimeout(checkForUpdate, 120000);
}

/* 
 * The resetCheckForUpdate function stops any current
 * setTimeout for the checkForUpdate function and then
 * calls the delayCheckForUpdate function.
 */
function resetCheckForUpdate() {
  if (BD18.checkForUpdateTimeout) {
    window.clearTimeout(BD18.checkForUpdateTimeout);
  }
  delayCheckForUpdate();
}

/* 
 * Function snapshotOK is the call back function for the
 * ajax snapShot.php call. 
 * 
 * Output from snapShot.php is an echo return status:
 *   "success" - Snapshot has been successfull.
 *   "fail"    - Uexpected error - No snapshot has been taken.
 */
function snapshotOK(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  else if (response === 'success') {
    msg = "Your snap shot request has been successful. ";
    doLogNote(msg);
  }
  else if (response === 'fail') {
    var errmsg = 'Snapshot failed.\n';
    errmsg += 'Please contact the BOARD18 webmaster.';
    alert(errmsg);
  }
   else if(resp.substr(0,9) === 'collision') {
    msg = BD18.welcomename + ": ";
    msg += "Your snap shot request has been backed out because ";
    msg += resp.substr(10);
    msg += " updated the database after you read it.";
    alert(msg); 
    document.location.reload(true);   
  }
  else if (resp === 'notplaying') {
    msg = BD18.welcomename + ": ";
    msg += "Your snap shot request has been rejected because";
    msg += " you are not a player in this game.";
    alert(msg);                    
    document.location.reload(true); 
  }
  else { // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from snapShot.php.\n';
    nerrmsg += resp + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
}

/* 
 * Function snapshot is called by the submit
 * method of the snapname submit button. It 
 * checks the input for missing fields and
 * does an ajax call to snapShot.php. 
 */
function snapshot() {
  $('.error').hide();  
  BD18.roundname = $("input#rname").val();  
  if (BD18.roundname === "") {  
    $("#rname_error").show();  
    $("#rname") .trigger('focus');  
    return;  
  }
  var postString = 'gameid=' + BD18.gameID;
  postString += '&roundname=' + BD18.roundname;
  $.post("php/snapShot.php", postString,  function(response) {
    snapshotOK(response);
  });
  $('#snapname form').hide();
  BD18.isSnap = false;
}
