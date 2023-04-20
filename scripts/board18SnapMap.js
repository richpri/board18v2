/*
 * The board18SnapMap.js file contains all the functions that
 * respond to various onclick events on the game board in the 
 * SnapMap page and on the main and tray menues.  But note that 
 * right click events are not used on snapshot displays.
 *
 * Copyright (c) 2015 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

/* 
 * The setUpKeys function performs all of the bind 
 * operations for the keyboard shortcut events.
 *   KEY  Action
 *    H   Hide/Show
 *    M   Goto Stock Chart
 *    G   Return to Game
 *    S   Return to SnapList
 *    O   Goto Main Page
 *    X   Logout
 */
function setUpKeys() {
  $(document).on("keydown",function(e){
    if (BD18.isSnap === false) {
      var keycode = (e.keyCode ? e.keyCode : e.which);
      switch(keycode) {
        case 72: // "H" keycode
          hideShow();
          break;
        case 77: // "M" keycode
          window.location = 'board18SnapMrk.php?show=' + BD18.snapID;
          break;
        case 71: // "G" keycode
          window.location = 'board18Map.php?dogame=' + BD18.gameID;
          break; 
        case 83: // "S" keycode
          window.location = 'board18SnapList.php?gameid=' + BD18.gameID;
          break;
        case 79: // "O" keycode
          window.location = "board18Main.php";
          break;
        case 88: // "X" keydode
          $.post('php/logout.php', logoutOK);
          break;  
        default:
      }
      e.preventDefault();
    }
  });
};

/* The showHide function will use the 
 * BD18.gameBoard to hide/show tiles on the map.
 */
function hideShow(){
  if (BD18.hideMapItems === false) {
    BD18.hideMapItems = true;
    trayCanvasApp();
    BD18.gameBoard.place();
    BD18.gameBoard.clear2();
  } else {
    BD18.hideMapItems = false;
    trayCanvasApp();
    mainCanvasApp();
    toknCanvasApp();
  }
}

/* The makeTrayItems function will use the 
 * BD18.trays array to construct the items
 * to be displayed in the tray menu.
 */
function makeTrayItems() {
  var menuText = '<ul class="leftMenu">';
  var lastItem = BD18.trays.length - 1;
  for (var ix = 0; ix < BD18.trays.length; ix++) {
    menuText += '<li onclick="leftMenuEvent(\'tray' + ix + '\');">';
    menuText += BD18.trays[ix].trayName;
    menuText += '</li>';
    menuText += (ix === lastItem) ? '</ul>' : '';
  }
  //var menuItems = $.parseJSON(menuText);
  return menuText;
}

/* The registerTrayMenu function creates the 
 * tray menu on the board18Map page. It uses
 * the makeTrayItems function.
 */
function registerTrayMenu() {
  var itemlist = makeTrayItems();
  $('#traymenu').html(itemlist);
}


/* This function handles the selection of the leftMenu(Tray)
 * and displays the proper BD18.tray.
 */
function leftMenuEvent(key) {
      /* Remove any uncompleted moves. */
      if (BD18.hexIsSelected === true) {
        mainCanvasApp();
        toknCanvasApp();
        BD18.hexIsSelected = false;
        BD18.tokenIsSelected = false;
        BD18.tileIsSelected = false;
        BD18.curFlip = false;
      }
      $("#botleftofpage").scrollTop(0);
      var ix = parseInt(key.substring(4));
      BD18.trays[ix].place(null);
      $('.menu').hide();
}

/* 
 * This makeMenus function is a stub to make board18SnapMap
 * compatable with board18Map2.php
 */
function makeMenus() { };

/* 
 * This delayCheckForUpdate function is a stub to make 
 * board18SnapMap compatable with board18Map2.php
 */
function delayCheckForUpdate() { };
