/* 
 * This file contains scripts that are common to 
 * all board18 web pages.
 *
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

/* All board18 global variables are contained in one
 * 'master variable' called BD18.  This isolates 
 * them from global variables in other packages. 
 */
var BD18 = {};
BD18.noteTimeout = null; // Used by doLogNote().
BD18.welcomename = null; // Used by doLogNote().
BD18.help = "http://wiki.board18.org/w/Player%27s_Guide_V2.4";
BD18.version = "2.4.6";

/* Function setPage() adjusts the height and width
 * of rightofpage and the height of lefttofpage.
 */
function setPage()
{
  var winH = $(window).height();
  var winW = $(window).width();
  var winName = location.pathname.
          substring(location.pathname.lastIndexOf("/") + 1);
  $('#rightofpage').css('height', winH-90);
  $('#rightofpage').css('width', winW-135);
  if(winName === "board18Map.php" || 
     winName === "board18Market.php" || 
     winName === "board18SnapMap.php" || 
     winName === "board18SnapMrk.php") {
    $('#botleftofpage').css('height', winH-140);
  }
  else $('#leftofpage').css('height', winH-90);
}

/* Function resize() waits for 200 ms before
 * executing setPage. Multiple window resize  
 * events that occur within this time peroid 
 * will only trigger the setPage function once.
 */  
$(window).resize(function() 
{
  if(this.resizeTO) clearTimeout(this.resizeTO);
  this.resizeTO = setTimeout(function() 
  {
    $(this).trigger('resizeEnd');
  }, 200);
});
$(window).bind('resizeEnd', function() {
  setPage();
});

/* Initial page resizing. */
$(function(){
  setPage();  
});

/* 
 * Utility Functions 
 */


/* The findPos, getScrolled and getOffset functions 
 * calculate the real position of an element on the
 * page adjusted for scrolling. I got them from this 
 * web site: http://help.dottoro.com/ljnvukbb.php
 */
function getOffset (object, offset) {
  if (!object) {return;}
  offset.x += object.offsetLeft;
  offset.y += object.offsetTop;
  getOffset (object.offsetParent, offset);
}

function getScrolled (object, scrolled) {
  if (!object) {return;}
  scrolled.x += object.scrollLeft;
  scrolled.y += object.scrollTop;
  if (object.tagName.toLowerCase () !== "html") {
    getScrolled (object.parentNode, scrolled);
  }
}

function findPos(obj) {
  var offset = {x : 0, y : 0};
  getOffset (obj, offset);
  var scrolled = {x : 0, y : 0};
  getScrolled (obj.parentNode, scrolled);
  var posX = offset.x - scrolled.x;
  var posY = offset.y - scrolled.y;
  return [posX, posY];
}

/* The offsetIn function finds the offset of the
 * cursor [at a click event] from the top/left
 * of the specified containing object. It uses 
 * findPos() to calculate the object's top/left.
 */
function offsetIn(event, obj) {
  var a, b;
// [a, b] = findPos(obj);
  var tArray = findPos(obj);
  a = tArray[0];
  b = tArray[1];
  var x = event.pageX - a;
  var y = event.pageY - b;
  return [x, y];
}

/*
 * Function docPos(event) returns the position 
 * in the document of a mouse event. 
 */
function docPos(event) {
	var posx = 0;
	var posy = 0;
	if (!event) return;
	if (event.pageX || event.pageY) 	{
		posx = event.pageX;
		posy = event.pageY;
	}
	else if (event.clientX || event.clientY) 	{
		posx = event.clientX + document.body.scrollLeft
			+ document.documentElement.scrollLeft;
		posy = event.clientY + document.body.scrollTop
			+ document.documentElement.scrollTop;
	}
  return [posx, posy];
}

/* Function doLogNote displays a lognote for 30 seconds.
 * A new lognote will replace any previous log note 
 * that has not yet timed out. BD18.noteTimeout is a
 * global variable with an initial value of null.
 */
function doLogNote(note) {
  if(BD18.noteTimeout !== null) {
    clearTimeout(BD18.noteTimeout);
  }
  if(BD18.welcomename !== null) {
    var msg = BD18.welcomename + ": " + note;
  } else {
    var msg = note;
  }
  $('#lognote').text(msg);
  BD18.noteTimeout = setTimeout(function() {
    $('#lognote').text("");
    BD18.noteTimeout = null;
  },"20000");
}

/* The following functions are callback function for various 
 * ajax calls to server side PHP code. 
 */

/* Function logoutOK is the callback function for the ajax
 * lgout call. 
 */
function logoutOK(resp) {
  if(resp === 'success') {
    window.location = "index.html";
  }
  else {
    alert("Logout failed! This should never happen.");
  } 
}

/* Function statswapOK is the callback function for the ajax
 * statSwap call. 
 */
function statswapOK(resp) {
  if (resp.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  } 
  var msg;
  if(resp === 'success') {
    document.location.reload(true);
  }
  else if(resp === 'failure') {
    msg = "The toggle of the game status failed. ";
    msg += "Contact the site administrator about this error.";
    alert(msg);
  }
  else if(resp.substr(0,9) === 'collision') {
    msg = BD18.welcomename + ": ";
    msg += "Your move has been backed out because ";
    msg += resp.substr(10);
    msg += " updated the database after you read it.";
    alert(msg); 
    document.location.reload(true);
  }
  else if (resp === 'notplaying') {
    msg = BD18.welcomename + ": ";
    msg += "Your request has been rejected because";
    msg += " you are not a player in this game.";
    alert(msg);                    
    document.location.reload(true); 
  }
  else {
    msg = "Invalid return code from statSwap ["+resp+"]. ";
    msg += "Contact the site administrator about this error.";
    alert(msg);
  }
}

/* Function aboutBoard18 is called from the MAIN menu on
 * most BOARD18 pages. It displays a popup about message. 
 */
function aboutBoard18() {
  var aboutmsg = 'BOARD18 version ' + BD18.version + '\n';
  aboutmsg += '\nAN OPEN SOURCE APPLICATION';
  aboutmsg += '\nCopyright (c) 2013 Richard E. Price';
  aboutmsg += '\nDistributed under the MIT License';
  alert(aboutmsg);
}
