/*
 * The board18Market5.js file contains all the right-click logic.
 * A right click event on the stock chart in the Market page can 
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
 * "1" - Current box selected and token selected
 * "3" - Current unselected box has only one token 
 * "4" - Current unselected box has mutiple tokens 
 */
function getMenuType(event) {
  var type = "0";
  if (BD18.boxIsSelected === true) { 
    if (BD18.tokenIsSelected === true) type = "1";
  } else { 
    var boxX, boxY;
//   [boxX, boxY] = boxPos(event);
    var tArray = boxPos(event);
    boxX = tArray[0];
    boxY = tArray[1];
    BD18.onBoxList = new OnBox(boxX, boxY);
    BD18.onBoxList.stackNorm();
    if (BD18.onBoxList.oneToken) type = "3";
    if (BD18.onBoxList.manyTokens) type = "4";
  }
  return type;
}

/* 
 * The makeMenuItems function will use the getMenuType
 * function to determine which menu items it will
 * include in the currently displayed menu.  Menu type:
 * "0" - Suppress the menue display entirely.
 * "1" - Current box selected and token selected
 * "3" - Current unselected box has only one token 
 * "4" - Current unselected box has mutiple tokens 
 */
function makeMenuItems(e) {
  var menuType = getMenuType(e);
  var menuText;
  switch(menuType)
    {
    case "0":
      menuText = "0";
      break;
    case "1":
      menuText = {
        flip: {name: 'Flip Token'},
        up: {name: 'Move Up'},
        right: {name: 'Move Right'},
        down: {name: 'Move Down'},
        left: {name: 'Move Left'},
        accept: {name: 'Accept Move'},
        reset: {name: 'Cancel Move'}
      };
      break;
    case "3":
      menuText = {
        tflip: {name: 'Flip Token'},
        tmove: {name: 'Adjust Token in Box'},
        tup: {name: 'Move Token Up'},
        tright: {name: 'Move Token Right'},
        tdown: {name: 'Move Token Down'},
        tleft: {name: 'Move Token Left'},
        tdelete: {name: 'Delete Token'}              
      };
      break;
    case "4":
      menuText = {
        stflip: {name: 'Select Token to Flip'},
        stmove: {name: 'Select Token to Adjust in Box'},
        stup: {name: 'Select Token to Move Up'},
        stright: {name: 'Select Token to Move Right'},
        stdown: {name: 'Select Token to Move Down'},
        stleft: {name: 'Select Token to Move Left'},
        sttop: {name: 'Select Token to Put on Top'},
        stbottom: {name: 'Select Token to Put on Bottom'},
        straise: {name: 'Select Token to Raise 1 Step'},
        stlower: {name: 'Select Token to Lower 1 Step'},
        stdelete: {name: 'Select Token to Delete'}
      };
      break;
    default:
      menuText = "0";
      alert("Invalid Menu Type" + menuType + ".");
    } 
  return menuText;
}
