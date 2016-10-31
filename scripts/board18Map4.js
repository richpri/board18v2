/* 
 * board18Map4.js contains all the functions that
 * implement the multi token selection logic for
 * cases where more than one token is on the same hex.
 *
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

/* The selectToken function uses the  BD18.hexList.tokens
 * array to display a graphical list of the tokens on a
 * given hex. This list is displayed on canvas3 
 * and the canvas3 click event is turned on. Clicking 
 * on a token should cause canvas3 to be cleared.
 */
function selectToken(event) {
  var numbtok = BD18.hexList.tokens.length;
  $('#canvas3').css('opacity', '1');
  $('#canvas3').prop('height', 40); 
  $('#canvas3').prop('width', numbtok*40);
  var xsize = numbtok*40;
  var xpos = (BD18.xMax>BD18.xPx+xsize) ? BD18.xPx : BD18.xPx-xsize;
  var ypos = (BD18.yMax>BD18.yPx+45) ? BD18.yPx : BD18.yPx-40;
  $('#canvas3').css({"left":xpos,"top":ypos});
  BD18.canvas3 = document.getElementById('canvas3');
  if (!BD18.canvas3 || !BD18.canvas3.getContext) {
    alert ("Canvas3 error in board18Map3.js!");
    return;
  }
  BD18.context3 = BD18.canvas3.getContext('2d');
  if (!BD18.context3) {
    alert ("Context3 error in board18Map3.js!");
    return;
  }
  BD18.context3.fillStyle = "#FFEEDD";
  BD18.context3.fillRect(0, 0, numbtok*40, 40);
  var image,sx,sy,szx,szy,ix,mx;
  for (i=0;i<numbtok;i++) {
    image = BD18.hexList.tokens[i].sheet.image;
    ix = BD18.hexList.tokens[i].index;
    sx = BD18.hexList.tokens[i].sheet.xStart;
    if (BD18.hexList.tokens[i].flip) {
      sx = sx +  BD18.hexList.tokens[i].sheet.xStep;
    }
    sy = BD18.hexList.tokens[i].sheet.yStart + 
       ix*BD18.hexList.tokens[i].sheet.yStep;
    szx = BD18.hexList.tokens[i].sheet.xSize;
    szy = BD18.hexList.tokens[i].sheet.ySize;
    mx = 5 + i*40;
    BD18.context3.drawImage(image,sx,sy,szx,szy,mx,5,30,30);
  }
  $('#canvas3').on({
    "click": doTknMenu,
    "mouseout": delayHideTknMenu,
    "mousein": killHideTknMenu
  });
}

/* 
 * The hideTknMenu function resets the canvas3 token menu.
 */
function hideTknMenu() {
  var numbtok = BD18.hexList.tokens.length;
  BD18.context3.clearRect(0, 0, numbtok*40, 40);
  $('#canvas3').css({
    opacity: '0',
    top: '-200px'
  });
  BD18.tknMenu.timeoutID = 0;
  $('#canvas3').off({
    "click": doTknMenu,
    "mouseout": delayHideTknMenu,
    "mousein": killHideTknMenu
  });
}

/* 
 * The delayHideTknMenu function waits 2 seconds before
 * calling the hideTknMenu function.
 */
function delayHideTknMenu() {
  BD18.tknMenu.timeoutID = window.setTimeout(hideTknMenu, 2000);
}

/* 
 * The killHideTknMenu function stops any delayed
 * calling of the hideTknMenu function.
 */
function killHideTknMenu() {
  if (BD18.tknMenu.timeoutID) {
    window.clearTimeout(BD18.tknMenu.timeoutID);
  }
}

/* 
 * The doTknMenu function processes a click on canvas3
 * This canvas is used to select one of multiple tokens
 * on the same hex. The requested function is performed
 * for the selected token.
 */
function doTknMenu(event) {
  // find token that was clicked
  var xPix, yPix, index, ix, bdtok;
// [xPix, yPix] = offsetIn(event, BD18.canvas3); 
  var tArray = offsetIn(event, BD18.canvas3); 
  xPix = tArray[0];
  yPix = tArray[1];
  index = Math.floor(xPix/40);
  // do requested function to that token.
  switch(BD18.tknMenu.funct) {
    case "delete":
      deleteToken(BD18.hexList.tokens[index].btindex);
      toknCanvasApp();
      trayCanvasApp();
      updateGmBrdTokens();
      updateDatabase();
      break;
    case "move":
    case "flip":
      getToken(index);
      break;
    default:
      alert("Invalid token menu function: " + BD18.tknMenu.funct);
  }
  hideTknMenu();
}

