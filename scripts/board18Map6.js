/*
 * The board18Map6.js file contains all the functions that
 * respond to various onclick events on the game board in the 
 * Map page and on the main and tray menues.  But right click 
 * events that cause a context menu to be displayed are handled
 * by functions in the board18Map5.js file.
 *
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */


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

/* The updateMenu function will update the menu 
 * move specific actions
 */
function updateMenu(menuType){
  $('.move').hide();
  $('.'+menuType+'.move').show();
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
  if( BD18.trayCount > 1 ) {
    var itemlist = makeTrayItems();
    $('#traymenu').html(itemlist);
  } else {
    $('#botleftofpage').css('top',90);
    setPage();
  }
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
        updateMenu('no');
      }
      $("#botleftofpage").scrollTop(0);
      var ix = parseInt(key.substring(4));
      BD18.trays[ix].place(null);
      $('.menu').hide();
}

/* This function calculates the board coordinates of a map
 * tile given the raw coordinates of a mouse click event.
 */
function tilePos(event) {
  var xPix, yPix, xIndex, yIndex;
// [xPix, yPix] = offsetIn(event, BD18.canvas1);
  var tArray = offsetIn(event, BD18.canvas1);
  xPix = tArray[0];
  yPix = tArray[1];
// [xIndex, yIndex] = BD18.gameBoard.hexCoord(xPix, yPix);
  tArray = BD18.gameBoard.hexCoord(xPix, yPix);
  xIndex = tArray[0];
  yIndex = tArray[1];
  return [xIndex, yIndex];
}

/* This function responds to onclick events in the trays canvas.
 * It selects a item from those currently displayed and highlites
 * the selected item.
 */
function traySelect(event) {
  if (BD18.hexIsSelected === true) return;
  var tray = BD18.trays[BD18.curTrayNumb];
  var a, b, c, x, y;
  if(tray.sheetType==="tile") {
    a = 10;  // This is the tray Top Margin.
    b = BD18.curTrayStep; // This is the tray Y Step Value.
    c = tray.tilesOnSheet;
  } else if(tray.sheetType==="btok") {
    a = 0;  // This is the tray Top Margin.
    b = BD18.curTrayStep;  // This is the tray Y Step Value.
    c = tray.tokensOnSheet;
  } else {
    return; // Invalid sheet type!!
  }
// [x, y] = offsetIn(event, BD18.canvas0);
  var tArray = offsetIn(event, BD18.canvas0);
  x = tArray[0];
  y = tArray[1];
  var ind = (y-a)/b;
  var inde = (ind>=c)?c-1:ind; 
  var index = Math.floor((inde<0)?0:inde);
  if (BD18.gm.trayCounts[BD18.curTrayNumb][index] === 0) return;
  BD18.curIndex = index;
  BD18.curRot = 0; // Eliminate any previous rotation.
  BD18.curFlip = false; // Eliminate any previous flip.
  BD18.deletedBoardToken = 0; // Eliminate any backout of token delete.
  tray.place(index); // Set highlight.
  updateMenu('active');
}

/* This function responds to left mousedown events in the  
 * map canvas. It checks various conditions and executes 
 * the appropriate function based on them. If it can find 
 * no relevant condition then it calls the ContextMenu function.
 */
function hexSelect(event) {
      var x, y, xPix, yPix;
//     [x, y] = tilePos(event);
      var tArray = tilePos(event);
      x = tArray[0];
      y = tArray[1];
//     [xPix, yPix] = offsetIn(event, BD18.canvas1);
      tArray = offsetIn(event, BD18.canvas1);
      xPix = tArray[0];
      yPix = tArray[1];
  if (BD18.hexIsSelected === true) {
    if (x !== BD18.curHexX) { ContextMenu(event);return; }
    if (y !== BD18.curHexY) { ContextMenu(event);return; }
    if (BD18.tileIsSelected === true) {
      rotateTile("cw");       
    }
    if (BD18.tokenIsSelected === true) {
      repositionToken(xPix,yPix);
    }
  } else { // BD18.hexIsSelected === false
    if (BD18.tileIsSelected === true) {
      dropTile(x,y); 
    }else if (BD18.tokenIsSelected === true) {
      dropToken(x,y,xPix,yPix); 
    }else{
      ContextMenu(event);
    }
  }
}

/* This function responds to mousedown events on the map canvas.
 * It uses event.which to determine which mouse button was pressed.
 * If the left or center button was pressed then it calls the
 * hexSelect functon. Otherwise it assumes that the right button
 * was pressed and calls the ContextMenu function.
 */
function mapMouseEvent(event) {
  event.stopPropagation();
  event.preventDefault();
  event.cancelBubble = true;
  $('.menu').hide();
  if(event.which === 0 || event.which === 1) { // Left or Center
    hexSelect(event);
  } else {
    ContextMenu(event);
  }
}

/* 
 * This function uses makeMenuItems to create a ContextMenu.
 * The menu will be positioned so as not to overlap the
 * bounderies of the document. The displayed menu will not scroll.
 */
function ContextMenu(event) {
  var items = makeMenuItems(event);
  if( parseInt(items) === 0 ) return;
  var menuList = '';
  var itemCount = 0;
  for(var key in items) {
    itemCount += 1;
    menuList += "<li class='contextMenu' data-action='";
    menuList +=  key + "'>"+items[key].name + "</li>";
  }
  $('#onMapMenu ul').html(menuList);
  $('#onMapMenu li').click(function(e){
                           doit(this.getAttribute("data-action"),e);
                          });
// [BD18.xPx, BD18.yPx] = offsetIn(event, BD18.canvas1);
  var tArray = docPos(event)
  BD18.xPx = tArray[0];
  BD18.yPx = tArray[1];
  BD18.xMax = $(document).width();
  BD18.yMax = $(document).height();
  var xpos = (BD18.xMax>BD18.xPx+180) ? BD18.xPx : BD18.xPx-180;
  var ysize = 25*itemCount;
  var ypos = (BD18.yMax>BD18.yPx+ysize) ? BD18.yPx : BD18.yPx-ysize;
  $('#onMapMenu').css({"left":xpos,"top":ypos});
  $('#onMapMenu').show();
}

/* This function is called via onclick events coded into the
 * main menu on the board18Map page. the passed parameter 
 * indicates the menue choice to be acted upon.
 */
function doit(mm,e) { // mm is the onclick action to be taken.
  switch(mm)
  {
    case "rcw":
      rotateTile("cw");
      break;
    case "rccw":
      rotateTile("ccw");
      break;
    case "flip":
      flipToken();
      break;
    case "accept":
      acceptMove();
      break;
    case "reset":
      cancelMove();
      break;
    case "stokenf":
      BD18.tknMenu.funct = 'flip';
      selectToken(e);
      break;
    case "stokenm":
      BD18.tknMenu.funct = 'move';
      selectToken(e);
      break;
    case "stokend":
      BD18.tknMenu.funct = 'delete';
      selectToken(e);
      break;
    case "ftoken":
      BD18.tknMenu.funct = 'flip';
      getToken(0);
      break;
    case "mtoken":
      BD18.tknMenu.funct = 'move';
      getToken(0);
      break;
    case "dtoken":
      deleteToken(BD18.hexList.tokens[0].btindex);
      toknCanvasApp();
      trayCanvasApp();
      updateGmBrdTokens();
      updateDatabase();
      break;
    case "dtile":
		  deleteTile(BD18.hexList.tile.btindex);
		  mainCanvasApp();
		  trayCanvasApp();
		  updateGmBrdTiles();
		  updateDatabase();
			break;
    default:
      alert("Unknown menu item selected: " + mm);
  }   
}
