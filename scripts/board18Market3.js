/* 
 * The board18Market3 file contains token manipulation functions. 
 * These functions manipulate tokens on the stock market chart.
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
  if (resp === 'success') {
    msg = "Your move has been successfully updated ";
    msg += "to the server database.";
    doLogNote(msg);
  }
  else if (resp === 'failure') {
    msg = "Your move did not make it to the server database. ";
    msg += "Contact the site administrator about this error.";
    alert(msg);
  }
  else if (resp.substr(0, 9) === 'collision') {
    msg = BD18.welcomename + ": ";
    msg += "Your move has been backed out because ";
    msg += resp.substr(10);
    msg += " updated the database after you read it.";
    alert(msg);                     // Fix for BUG 25
    document.location.reload(true); // Fix for BUG 25
  }
  else if (resp === 'notplaying') {
    msg = BD18.welcomename + ": ";
    msg += "Your move has been rejected because";
    msg += " you are not a player in this game.";
    alert(msg);                    
    document.location.reload(true); 
  }
  else {
    msg = "Invalid return code from updateGame [" + resp + "]. ";
    msg += "Contact the site administrator about this error.";
    alert(msg);
  }
}

/* The dropToken function places a token at a specified 
 * location on the stock market.  It calls the MarketToken
 * constructor function and then updates some global
 * variables to keep track of the new token. Note that
 * this new token has not yet been permanently added to
 * the list of placed tokens in BD18.gm.mktTks. It is 
 * tracked instead in the BD18.tempToken array.
 */
function dropToken(x, y, xI, yI) {
  BD18.tempToken = [BD18.curTrayNumb, BD18.curIndex, 
                    BD18.curFlip, xI, yI, null];
  var sn = BD18.tempToken[0];
  var ix = BD18.tempToken[1];
  var flip = BD18.tempToken[2];
  var bx = BD18.tempToken[3];
  var by = BD18.tempToken[4];
  var stack = BD18.tempToken[5];
  var temp = new MarketToken(sn, ix, flip, stack, bx, by);
  toknCanvasApp(true);
  temp.place(0.5); // Semi-transparent
  BD18.curStack = stack;
  BD18.curBoxX = x;
  BD18.curBoxY = y;
  BD18.curMktX = xI;
  BD18.curMktY = yI;
  BD18.boxIsSelected = true;
  var messg = "Select 'Menu-Accept Move' to make ";
  messg += "token placement permanent.";
  doLogNote(messg);
}

/* The repositionToken function moves the current token  
 * to a specified new location on the selected price box.  
 * It calls the MarketToken constructor function. 
 * Note that this new token has not yet been
 * permanently added to the list of placed tokens in
 * BD18.gm.mktTks. It is tracked instead in the 
 * BD18.tempToken array.
 */
function repositionToken(xI, yI) {
  var xs = BD18.stockMarket.xStart;
  var ys = BD18.stockMarket.yStart;
  var xm = BD18.stockMarket.width;
  var ym = BD18.stockMarket.height;
  // If new position is within stock market grid.
  if ((xI > xs)&&(yI > ys)&&(xI < xm)&&(yI < ym)) {
    BD18.tempToken[3] = xI;
    BD18.tempToken[4] = yI;
    BD18.curMktX = xI;
    BD18.curMktY = yI;
  } else { // restore curMkt values.
    BD18.curMktX = BD18.tempToken[3];
    BD18.curMktY = BD18.tempToken[4];
  }
  var sn = BD18.tempToken[0];
  var ix = BD18.tempToken[1];
  var flip = BD18.tempToken[2];
  var bx = BD18.tempToken[3];
  var by = BD18.tempToken[4];
  var stack = BD18.tempToken[5];  
  toknCanvasApp(true);
  var temp = new MarketToken(sn, ix, flip, stack, bx, by);
  BD18.curBoxX = temp.hx;
  BD18.curBoxY = temp.hy;
  temp.place(0.5); // Semi-transparent
  var messg = "Select 'Menu-Accept Move' or press Enter ";
  messg += "to make token placement permanent.";
  doLogNote(messg);
  updateMenu('active');
}

/* The flipToken function flips the current token.
 * If flip is disallowed, it does nothing and returns. 
 * Else it calls the MarketToken constructor function. 
 * Note that this flipped token has not yet been
 * permanently added to the list of placed tokens
 * in BD18.gm.mktTks. It is tracked instead in the 
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
  var stack = BD18.tempToken[5]; 
  toknCanvasApp(true);
  var temp = new MarketToken(sn, ix, flip, stack, bx, by);
  BD18.curBoxX = temp.hx;
  BD18.curBoxY = temp.hy;
  temp.place(0.5); // Semi-transparent
  var messg = "Select 'Menu-Accept Move' to make ";
  messg += "token placement permanent.";
  doLogNote(messg);
  updateMenu('active');
}

/* 
 * The deleteToken function deletes a market token object 
 * from the BD18.marketTokens array. The ix parameter is
 * the index of the tile to be deleted. This function
 * returns false if no token is deleted and true otherwise.
 */
function deleteToken(ix) {
  if (BD18.marketTokens.length === 0)
    return false;
  var tix = BD18.marketTokens[ix];
  if (!tix)
    return false;
  BD18.deletedMarketToken = tix;
  delete BD18.marketTokens[ix];
  return true;
}

/* 
 * The tokenSort function sorts an array of 
 * MarketToken objects so that tokens in the
 * same price box are placed in reverse order
 * of their stack values.
 */
function tokenSort(tokA, tokB) {
  if (tokA.hx < tokB.hx) return -1;
  if (tokA.hx > tokB.hx) return 1;
  if (tokA.hy < tokB.hy) return -1;
  if (tokA.hy > tokB.hy) return 1;
  if (tokA.stack < tokB.stack) return 1;
  if (tokA.stack > tokB.stack) return -1;
  return 0;
}

/* 
 * This function first sorts the BD18.marketTokens 
 * array. It then uses the contents of this array 
 * and the MarketToken.togm method to update the
 * BD18.gm.mktTks array.
 */
function updateMarketTokens() {
  BD18.marketTokens.sort(tokenSort);
  BD18.gm.mktTks = [];
  for (var i = 0; i < BD18.marketTokens.length; i++) {
    if (BD18.marketTokens[i]) {
      BD18.gm.mktTks.push(BD18.marketTokens[i].togm());
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

/* This function calls the addToken function if 
 * the market price box is selected and the
 * token is selected. Otherwise it does nothing.
 */
function acceptMove() {
  if (BD18.boxIsSelected === true &&
          BD18.tokenIsSelected === true) {
    BD18.deletedMarketToken = null;
    addToken();
  }
}

/* This function calls the CanvasApp() functions
 * to reset trays and market when canceling a move
 */
function cancelMove() {
  if (BD18.deletedMarketToken) {
    BD18.curTrayNumb = BD18.deletedMarketToken.snumb;
    BD18.curIndex = BD18.deletedMarketToken.index;
    BD18.curFlip = BD18.deletedMarketToken.flip;
    BD18.curStack = BD18.deletedMarketToken.stack;
    BD18.curMktX = BD18.deletedMarketToken.bx;
    BD18.curMktY = BD18.deletedMarketToken.by;
    BD18.curBoxX = BD18.deletedMarketToken.hx;
    BD18.curBoxY = BD18.deletedMarketToken.hy;
    addToken();
      }
  trayCanvasApp();
  mainCanvasApp();
  toknCanvasApp();
  BD18.boxIsSelected = false;
  BD18.tokenIsSelected = false;
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

/* This function adds the current board token object 
 * to the BD18.marketTokens array.  It then calls
 * the finishMove function.
 */
function addToken() {
  var t = BD18.curTrayNumb;
  var n = BD18.curIndex;
  var f = BD18.curFlip;
  var s = BD18.curStack;
  var x = BD18.curMktX;
  var y = BD18.curMktY;
  var token = new MarketToken(t, n, f, s, x, y);
  if (BD18.curStack === null) {
    var curBox = new OnBox(token.hx, token.hy);
    if (curBox.noToken) {
      token.stack = 0;
    } else {
      var max = 0;
      for (var i = 0; i < curBox.tokens.length; i++) {
        max = Math.max(max, curBox.tokens[i].stack); 
      }
      token.stack = max + 1;
    }
    BD18.curStack = token.stack;
  }
  BD18.marketTokens.push(token);
  finishMove();
}

/* This function updates the Market Tokens
 * array and the board18 database. It also
 * redisplays the stock market.
 */
function finishMove() {
  BD18.curIndex = null;
  updateMarketTokens();
  toknCanvasApp();
  trayCanvasApp();
  BD18.boxIsSelected = false;
  BD18.tokenIsSelected = false;
  BD18.curFlip = false;
  updateMenu('no');
  updateDatabase();
}

