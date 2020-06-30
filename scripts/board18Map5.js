/*
 * The board18Map5.js file contains all the right-click logic.
 * A right click event on the game board in the Map page can 
 * cause a context menu to be displayed. Whether this menu is 
 * displayed at all and the selection of items in this menu 
 * are both dependant on the context of the event. 
 *
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

/*
 * The getMenuType() function returns a character
 * from the list below to specify what type of
 * menu to display.
 *
 * "0" - Suppress the menue display entirely.
 * "1" - Current hex selected and token selected
 * "2" - Current hex selected and tile selected
 * "3" - Current unselected hex has only one token [no tile]
 * "4" - Current unselected hex has mutiple tokens [no tile]
 * "5" - Current unselected hex has only a tile [no token] 
 * "6" - Current unselected hex has a tile and one token
 * "7" - Current unselected hex has a tile and mutiple tokens
 */
function getMenuType(event) {
  var type = "0";
  if (BD18.hexIsSelected === true) { 
    if (BD18.tokenIsSelected === true) type = "1";
    if (BD18.tileIsSelected === true) type = "2";
  } else { 
    var hexX, hexY;
//   [hexX, hexY] = tilePos(event);
    var tArray = tilePos(event); 
    hexX = tArray[0];
    hexY = tArray[1];
    BD18.hexList = new OnHex(hexX, hexY);
    if (!BD18.hexList.isTile) {
      if (BD18.hexList.oneToken) type = "3";
      if (BD18.hexList.manyTokens) type = "4";
    } else {
      if (BD18.hexList.noToken) type = "5";
      if (BD18.hexList.oneToken) type = "6";
      if (BD18.hexList.manyTokens) type = "7";
    }
  }
  return type;
}

/* 
 * The makeMenuItems function will use the getMenuType
 * function to determine which menu items it will
 * include in the currently displayed menu.  Menu type:
 * "0" - Suppress the menue display entirely.
 * "1" - Current hex selected and token selected
 * "2" - Current hex selected and tile selected
 * "3" - Current unselected hex has only one token [no tile]
 * "4" - Current unselected hex has mutiple tokens [no tile]
 * "5" - Current unselected hex has only a tile [no token] 
 * "6" - Current unselected hex has a tile and one token
 * "7" - Current unselected hex has a tile and mutiple tokens
 */
function makeMenuItems(e) {
  var menuType = getMenuType(e);
  var menuText;
  switch(menuType)
    {
    case "0":   // Suppress the menue display entirely.
      menuText = "0";
      break;
    case "1":   // Current hex selected and token selected
      menuText = {
        flip: {name: 'Flip Token'},
        accept: {name: 'Accept Move'},
        reset: {name: 'Cancel Move'}
      };
      break;
    case "2":   // Current hex selected and tile selected
      menuText = {
        rcw: {name: 'Rotate CW'},
        rccw: {name: 'Rotate CCW'},
        accept: {name: 'Accept Move'},
        reset: {name: 'Cancel Move'}
      };
      break;
    case "3":   // Current unselected hex has only one token [no tile]
      menuText = {
        ftoken: {name: 'Flip Token'},
        mtoken: {name: 'Adjust Token'},
        dtoken: {name: 'Delete Token'}
      };
      break;
    case "4":   // Current unselected hex has mutiple tokens [no tile]
      menuText = {
        stokenf: {name: 'Select Token to Flip'},
        stokenm: {name: 'Select Token to Adjust'},
        stokend: {name: 'Select Token to Delete'}
      };
      break;
    case "5":   // Current unselected hex has only a tile [no token]
      menuText = {
        dtile: {name: 'Delete Tile'}
      };
      break;
    case "6":   // Current unselected hex has a tile and one token
      menuText = {
        ftoken: {name: 'Flip Token'},
        mtoken: {name: 'Adjust Token'},
        dtoken: {name: 'Delete Token'},
        dtile: {name: 'Delete Tile'}
      };
      break;
    case "7":   // Current unselected hex has a tile and mutiple tokens
      menuText = {
        stokenf: {name: 'Select Token to Flip'},
        stokenm: {name: 'Select Token to Adjust'},
        stokend: {name: 'Select Token to Delete'},   
        dtile: {name: 'Delete Tile'}
      };
      break;
    default:
      menuText = "0";
      alert("Invalid Menu Type" + menuType + ".");
    } 
  return menuText;
}
