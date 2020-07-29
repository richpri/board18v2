/* 
 * The board18Map3 file contains tile and token manipulation
 * functions. These functions manipulate obects on the map board.
 *
 * All BD18 global variables are contained in one
 * 'master variable' called BD18.  This isolates 
 * them from global variables in other packages
 * 
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

/* The fromUpdateGm function is a callback function for
 * the updateGame.php function. It reports on the status
 * returned by the updateGame.php AJAX call.
 */
function fromUpdateGm(resp) {
  if (resp.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  } 
  var msg;
  if(resp === 'success') {
    msg = "Your move has been successfully updated ";
    msg += "to the server database.";
    doLogNote(msg);
  }
  else if(resp === 'failure') {
    msg = "Your move did not make it to the server database. ";
    msg += "Contact the site administrator about this error.";
    alert(msg);
  }
  else if(resp.substr(0,9) === 'collision') {
    msg = BD18.welcomename + ": ";
    msg += "Your move has been backed out because ";
    msg += resp.substr(10);
    msg += " updated the database after you read it.";
    alert(msg);                         // Fix for BUG 25
    document.location.reload(true);     // Fix for BUG 25
  }
  else if (resp === 'notplaying') {
    msg = BD18.welcomename + ": ";
    msg += "Your move has been rejected because";
    msg += " you are not a player in this game.";
    alert(msg);                    
    document.location.reload(true); 
  }
  else {
    msg = "Invalid return code from updateGame ["+resp+"]. ";
    msg += "Contact the site administrator about this error.";
    alert(msg);
  }
} 

/* The dropTile function places a tile at a specified 
 * location on the map board.  It calls the BoardTile
 * constructor function and then updates some global
 * variables to keep tract of the new tile. Note that
 * this new tile has not yet been permanently added to
 * the list of placed tiles in BD18.gm.brdTls. It is 
 * tracked instead in the BD18.tempTile array.
 */
function dropTile(xI,yI) {
  mainCanvasApp();
  BD18.tempTile = [BD18.curTrayNumb,BD18.curIndex,0,xI,yI];
  var sn = BD18.tempTile[0];
  var ix = BD18.tempTile[1];
  var rot = BD18.tempTile[2];
  var bx = BD18.tempTile[3];
  var by = BD18.tempTile[4];
  var temp=new BoardTile(sn,ix,rot,bx,by);
  temp.place(true);
  BD18.curRot = 0;
  BD18.curHexX = xI;
  BD18.curHexY = yI;
  BD18.hexIsSelected = true;
  var messg = "Select 'Menu-Accept Move' to make ";
  messg += "tile placement permanent.";
  doLogNote(messg);
}

/* The rotateTile function replaces the tile at a specified 
 * location on the map board with a rotated version of that
 * tile. The tile it modifies is the one pointed to by 
 * BD18.tempTile.  
 * 
 * This function calls the BoardTile constructor function
 * after modifying the rotation counter in BD18.curRot. 
 */
function rotateTile(dir) {
  var maxrot = BD18.bx.tray[BD18.curTrayNumb].
               tile[BD18.curIndex].rots;
  if (BD18.hexIsSelected === false) return; 
  if(dir === "cw") {
    BD18.curRot += 1;
    if (BD18.curRot >= maxrot) {
      BD18.curRot = 0;
    }
  } else {
    BD18.curRot -= 1;
    if (BD18.curRot < 0) {
      BD18.curRot = maxrot-1;
    }
  }
  mainCanvasApp();
  BD18.tempTile.splice(2,1,BD18.curRot);
  var sn = BD18.tempTile[0];
  var ix = BD18.tempTile[1];
  var rot = BD18.tempTile[2];
  var bx = BD18.tempTile[3];
  var by = BD18.tempTile[4];
  var temp=new BoardTile(sn,ix,rot,bx,by);
  temp.place(true);
  BD18.hexIsSelected = true;
}

/* The getToken function selects a token at a specified 
 * location on the map board.  It sets the selected token
 * and hex fields in BD18.
 */
function getToken(index) {
  var ix = BD18.hexList.tokens[index].btindex;
  var bdtok = BD18.boardTokens[ix];
  if (BD18.trays[bdtok.snumb].tokenFlip[bdtok.index] === false 
          && BD18.tknMenu.funct === "flip") {
    return;
  }
  BD18.tempToken = [bdtok.snumb,bdtok.index,
  bdtok.flip,bdtok.bx,bdtok.by];
  BD18.hexIsSelected = true;
  BD18.tokenIsSelected = true;
  BD18.curTrayNumb = bdtok.snumb;
  BD18.curIndex = bdtok.index;
  BD18.curRot = 0;
  BD18.curFlip = bdtok.flip;
  BD18.curHexX = bdtok.hx;
  BD18.curHexY = bdtok.hy;
  BD18.curMapX = bdtok.bx;
  BD18.curMapY = bdtok.by;
  deleteToken(ix);
  updateMenu('active');
  switch(BD18.tknMenu.funct) {
    case "move":
      repositionToken(BD18.curMapX,BD18.curMapY);
      break;
    case "flip":
      flipToken();
      break;
    default:
      alert("Invalid token menu function: " + BD18.tknMenu.funct);
  }
}

/* The dropToken function places a token at a specified 
 * location on the map board.  It calls the BoardToken
 * constructor function and then updates some global
 * variables to keep track of the new token. Note that
 * this new token has not yet been permanently added to
 * the list of placed tokens in BD18.gm.brdTks. It is 
 * tracked instead in the BD18.tempToken array.
 */
function dropToken(x,y,xI,yI) {
  BD18.tempToken = [BD18.curTrayNumb,BD18.curIndex,BD18.curFlip,xI,yI];
  var sn = BD18.tempToken[0];
  var ix = BD18.tempToken[1];
  var flip = BD18.tempToken[2];
  var bx = BD18.tempToken[3];
  var by = BD18.tempToken[4];
  var temp=new BoardToken(sn,ix,flip,bx,by);
  toknCanvasApp(true);
  temp.place(0.5); // Semi-transparent
  BD18.curRot = 0;
  BD18.curHexX = x;
  BD18.curHexY = y;
  BD18.curMapX = xI;
  BD18.curMapY = yI;
  BD18.hexIsSelected = true;
  var messg = "Select 'Menu-Accept Move' to make ";
  messg += "token placement permanent.";
  doLogNote(messg);
}

/* The repositionToken function moves the current token  
 * to a specified new location on the selected tile.  
 * It calls the BoardToken constructor function. 
 * Note that this new token has not yet been
 * permanently added to the list of placed tokens in
 * BD18.gm.brdTks. It is tracked instead in the 
 * BD18.tempToken array.
 */
function repositionToken(xI,yI) {
  BD18.tempToken[3] = xI;
  BD18.tempToken[4] = yI;
  BD18.curMapX = xI;
  BD18.curMapY = yI;
  var sn = BD18.tempToken[0];
  var ix = BD18.tempToken[1];
  var flip = BD18.tempToken[2];
  var bx = BD18.tempToken[3];
  var by = BD18.tempToken[4];
  toknCanvasApp(true);
  var temp = new BoardToken(sn,ix,flip,bx,by);
  temp.place(0.5); // Semi-transparent
  var messg = "Select 'Menu-Accept Move' to make ";
  messg += "token placement permanent.";
  doLogNote(messg);
}

/* The flipToken function flips the current token.
 * It does nothing and returns if flip is disallowed. 
 * Else it calls the BoardToken constructor function. 
 * Note that this flipped token has not yet been
 * permanently added to the list of placed tokens
 * in BD18.gm.brdTks. It is tracked instead in the 
 * BD18.tempToken array.
 */
function flipToken() {
  if (BD18.trays[BD18.curTrayNumb].tokenFlip[BD18.curIndex] === false) {
    return;
  }
  BD18.tempToken[2] = !BD18.curFlip;
  BD18.curFlip = BD18.tempToken[2];
  var sn = BD18.tempToken[0];
  var ix = BD18.tempToken[1];
  var flip = BD18.tempToken[2];
  var bx = BD18.tempToken[3];
  var by = BD18.tempToken[4];
  toknCanvasApp(true);
  var temp = new BoardToken(sn,ix,flip,bx,by);
  temp.place(0.5); // Semi-transparent
  var messg = "Select 'Menu-Accept Move' to make ";
  messg += "token placement permanent.";
  doLogNote(messg);
}

/* This function reduces the available count for  
 * a specific tile or token [item]. These counts 
 * are stored in the BD18.gm.trayCounts array 
 * which is indexed by the sheet number and the 
 * item index. These are the input parameters.
 * This function returns false if no item is
 * available and true otherwise.
 */
function reduceCount(sheet,ind) {
  if (BD18.gm.trayCounts[sheet][ind] === 'U') // Count is unlimited.
    return true;
  if (BD18.gm.trayCounts[sheet][ind] === 0) 
    return false;
  BD18.gm.trayCounts[sheet][ind] -= 1;
  return true;
}

/* This function increases the available count for 
 * a specific tile or token [item]. These counts 
 * are stored in the BD18.gm.trayCounts array 
 * which is indexed by the sheet number and the 
 * item index. These are the input parameters.
 */
function increaseCount(sheet,ind) {
  if (BD18.gm.trayCounts[sheet][ind] === 'U') // Count is unlimited.
    return true;   
  BD18.gm.trayCounts[sheet][ind] += 1;  
}

/* 
 * The deleteTile function deletes a board tile object 
 * from the BD18.boardTiles array. The ix parameter is
 * the index of the tile to be deleted.  This function 
 * also increases the count of available tiles for the
 * tile that is being deleted.  This function returns
 * false if no tile is deleted and true otherwise.
 */
function deleteTile(ix) {
  if (BD18.boardTiles.length === 0) return false;
  var tix = BD18.boardTiles[ix];
  if (!tix) return false;
  increaseCount(tix.sheet.trayNumb,tix.index);
  delete BD18.boardTiles[ix];
  return true;
}

/* 
 * The deleteToken function deletes a board token object 
 * from the BD18.boardTokens array. The ix parameter is
 * the index of the token to be deleted.  This function 
 * also increases the count of available tokens for the
 * token that is being deleted.  This function returns
 * false if no token is deleted and true otherwise.
 */
function deleteToken(ix) {
  if (BD18.boardTokens.length === 0) return false;
  var tix = BD18.boardTokens[ix];
  if (!tix) return false;
  increaseCount(tix.sheet.trayNumb,tix.index);
  BD18.deletedBoardToken = tix;
  delete BD18.boardTokens[ix];
  return true;
}

/* This function uses the contents of the 
 * the BD18.boardTiles array and the
 * BoardTile.togm method to update the
 * BD18.gm.brdTls array.
 */
function updateGmBrdTiles() {
  BD18.gm.brdTls = [];
  for (var i=0;i<BD18.boardTiles.length;i++) {
    if (BD18.boardTiles[i]) {
      BD18.gm.brdTls.push(BD18.boardTiles[i].togm());
    }
  }
}

/* This function uses the contents of the 
 * the BD18.boardTokens array and the
 * BoardToken.togm method to update the
 * BD18.gm.brdTks array.
 */
function updateGmBrdTokens() {
  BD18.gm.brdTks = [];
  for (var i=0;i<BD18.boardTokens.length;i++) {
    if (BD18.boardTokens[i]) {
      BD18.gm.brdTks.push(BD18.boardTokens[i].togm());
    }
  }
}

/* This function sends the stringified BD18.gm object
 * to the updateGame.php function via an AJAX call.
 * But, it first calls the resetCheckForUpdate function.
 */
function updateDatabase() {
  resetCheckForUpdate();
  var jstring = JSON.stringify(BD18.gm);
  if(BD18.historyPosition !== BD18.history.length - 1)
    BD18.history.length = BD18.historyPosition + 1;
  BD18.history.push(jstring);
  BD18.historyPosition = BD18.history.length - 1;
  var outstring = "json=" + jstring + "&gameid=" + BD18.gameID;
  $.post("php/updateGame.php", outstring, fromUpdateGm);
}
  

/* This function calls the addTile function if the hex
 * is selected and the tile is selected. It calls the
 * addToken function if the hex is selected and the
 * token is selected. Otherwise it does nothing.
 */
function acceptMove() {
  if (BD18.hexIsSelected === false) return;
  if (BD18.tileIsSelected === true) {
    addTile();
    return;
  }
  if (BD18.tokenIsSelected === true) {
    BD18.deletedBoardToken = null;
    addToken();
    return;
  }
  return;
}

/* This function calls the CanvasApp() functions
 * to reset trays and board when canceling a move
 */
function cancelMove() {
  if (BD18.deletedBoardToken) {
    BD18.curTrayNumb = BD18.deletedBoardToken.snumb;
    BD18.curIndex = BD18.deletedBoardToken.index;
    BD18.curFlip = BD18.deletedBoardToken.flip;
    BD18.curMapX = BD18.deletedBoardToken.bx;
    BD18.curMapY = BD18.deletedBoardToken.by;
    addToken();
  }
  trayCanvasApp();
  mainCanvasApp();
  toknCanvasApp();
  updateMenu('no');
}

/* This function moves in the BD18.history to provide
 * undo/redo functionality
 */
function historyMove(move) {
  resetCheckForUpdate();
  if( move > 0 && ((BD18.historyPosition + 1) < BD18.history.length) ) {
    loadSession(JSON.parse( BD18.history[++BD18.historyPosition] ));
  } else if( move < 0 && BD18.historyPosition > 0 )  {
    loadSession(JSON.parse( BD18.history[--BD18.historyPosition] ));
  } else {
    return;
  }
  var outstring = "json=" + BD18.history[BD18.historyPosition] + "&gameid=" + BD18.gameID;
  $.post("php/updateGame.php", outstring, fromUpdateGm);
}
/* This function adds the current board tile object 
 * to the BD18.boardTiles array.  If a tile is 
 * already in the hex in question then that existing 
 * tile is deleted via the deleteTile function.
 * If the tile count for the tile is already 0 then
 * an error has occured. This should never happen.
 */
function addTile() {
  var s=BD18.curTrayNumb;
  var n=BD18.curIndex;
  var r=BD18.curRot;
  var x=BD18.curHexX;
  var y=BD18.curHexY;
  var tile = new BoardTile(s,n,r,x,y);
  var stat = reduceCount(tile.sheet.trayNumb,tile.index);
  if (stat) {
    var hexhas = new OnHex(x,y);
    if (hexhas.isTile) {
      deleteTile(hexhas.tile.btindex);
    }
    BD18.boardTiles.push(tile);
    BD18.curIndex = null;
    mainCanvasApp();
    trayCanvasApp();
    BD18.hexIsSelected = false;
    BD18.tokenIsSelected = false;
    BD18.tileIsSelected = false;
    updateMenu('no');
    updateGmBrdTiles();
    updateDatabase();
  } else {
    alert("ERROR: Tile not available.");
  }
}

/* This function adds the current board token object 
 * to the BD18.boardTokens array.  
 * If the token count for the token is already 0 then
 * an error has occured. This should never happen.
 */
function addToken() {
  var s=BD18.curTrayNumb;
  var n=BD18.curIndex;
  var f=BD18.curFlip;
  var x=BD18.curMapX;
  var y=BD18.curMapY;
  var token = new BoardToken(s,n,f,x,y);
  var stat = reduceCount(token.sheet.trayNumb,token.index);
  if (stat) {
    BD18.boardTokens.push(token);
    BD18.curIndex = null;
    toknCanvasApp();
    trayCanvasApp();
    BD18.hexIsSelected = false;
    BD18.tokenIsSelected = false;
    BD18.tileIsSelected = false;
    BD18.curFlip = false;
    updateMenu('no');
    updateGmBrdTokens();
    updateDatabase();
  } else {
    alert("ERROR: Token not available.");
  }
}
