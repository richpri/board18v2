/*
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

"use strict";

/* All BD18 global variables are contained in one
 * 'master' object called BD18.  This isolates 
 * them from global variables in other packages. 
 */
BD18.loadCount = 0;
BD18.doneWithLoad = false;
BD18.boxID = null;
BD18.boardTiles = [];
BD18.boardTokens = [];
BD18.trays = [];
BD18.curTrayNumb = null;
BD18.curTrayStep = null;
BD18.curIndex = null;
BD18.curRot = 0;
BD18.curFlip = false;
BD18.curHexX = null;
BD18.curHexY = null;
BD18.curMapX = null;
BD18.curMapY = null;
BD18.tileIsSelected = false;
BD18.tokenIsSelected = false;
BD18.hexIsSelected = false;
BD18.hideMapItems = false;
BD18.tknMenu = {};
BD18.tknMenu.timeoutID = 0;
BD18.tknMenu.on = false;
BD18.tknMenu.go = false;
BD18.hexList = {};
BD18.deletedBoardToken = null;
BD18.isSnap = false;

/*  
 * Constructor functions.
 */

/* 
 * GameBoard is a constructor function which creates a gameBoard object.
 * This object fully describes a game board and its use.   
 * The Start and Step attributes are used to locate the hexes on the
 * board for placement of tiles and tokens.
 */
function GameBoard(image,board) {
  this.image=image;
  this.height=parseInt(image.height,10);
  this.width=parseInt(image.width,10);
  this.xStart=parseInt(board.xStart,10);
  this.xStep=parseInt(board.xStep,10);
  this.yStart=parseInt(board.yStart,10);
  this.yStep=parseInt(board.yStep,10);
  var that = this;
  /*
   * The place function places the game board on canvas1.
   */
  this.place=function place() {
    BD18.context1.drawImage(image,0,0);
    BD18.hexIsSelected = false;
    BD18.gameBoard = that;
  };
  /*
   * The clear2 function clears all tokens from canvas2.
   * The default value for keepHexSelect is false;
   */
  this.clear2=function clear2(keepHexSelect) {
    BD18.context2.save();
    BD18.context2.setTransform(1, 0, 0, 1, 0, 0);
    BD18.context2.clearRect(0, 0, this.width, this.height);
    BD18.context2.restore();
    BD18.hexIsSelected = (keepHexSelect) ? true : false;
  };
  /* This function calculates the board coordinates of the containing
   * map hex given an exact position in pixels on the game board.
   */
  this.hexCoord = function hexCoord(xPix, yPix) {
    if (BD18.orientation === "P") {
      var yCent = this.yStart + this.yStep*2/3;
      var yIndex = Math.round(((yPix-yCent)/this.yStep));
      var xCent = this.xStart + this.xStep;
      var xIndex, xCentOdd;
      if (yIndex%2===0) { //if yIndex is even.
        xIndex = Math.round(((xPix-xCent)/this.xStep));
      } else {
        xCentOdd = xCent + this.xStep;
        xIndex = Math.round(((xPix-xCentOdd)/this.xStep))+1;
      }
      var xDiff = xPix-xIndex*this.xStep-xCent;
      if ((xIndex+yIndex)%2===1) { //if not both even or odd.
        if (xDiff>0) {xIndex = xIndex + 1; }
        else { xIndex = xIndex - 1; }
      }
    } else {
      var xCent = this.xStart + this.xStep*2/3;
      var xIndex = Math.round(((xPix-xCent)/this.xStep));
      var yCent = this.yStart + this.yStep;
      var yIndex, yCentOdd;
      if (xIndex%2===0) { //if xIndex is even. 
        yIndex = Math.round(((yPix-yCent)/this.yStep));
      } else {
        yCentOdd = yCent + this.yStep;
        yIndex = Math.round(((yPix-yCentOdd)/this.yStep))+1;
      }
      var yDiff = yPix-yIndex*this.yStep-yCent;
      if ((yIndex+xIndex)%2===1) { //if not both even or odd.
        if (yDiff>0) {yIndex = yIndex + 1; }
        else { yIndex = yIndex - 1; }
      }
    }
    return [xIndex, yIndex];
  };
}
  
/* TileSheet is a constructor function which creates tileSheet objects.
 * These objects fully describe a tile sheet and its contents.   
 * The Start, Size and Step attributes are used to locate tiles on the
 * tile sheet.
 *  */
function TileSheet(image,sheet) {
  this.sheetType=sheet.type;
  this.trayName=sheet.tName;
  this.image=image;
  this.xStart=parseInt(sheet.xStart,10);
  this.xSize=parseInt(sheet.xSize,10);
  this.xStep=parseInt(sheet.xStep,10);
  this.yStart=parseInt(sheet.yStart,10);
  this.ySize=parseInt(sheet.ySize,10);
  this.yStep=parseInt(sheet.yStep,10);
  this.tilesOnSheet=sheet.tile.length;
  this.tileDups=[];
  this.tileRots=[];
  for(var i=0;i<this.tilesOnSheet;i++) {
    this.tileDups[i]=parseInt(sheet.tile[i].dups,10);
    this.tileRots[i]=parseInt(sheet.tile[i].rots,10);
  }
  this.place=function place(high) {
    var img = this.image;
    var sx;
    var sy = this.yStart;
    var szx = Math.min(this.xSize,105);
    var szy = this.ySize;
    var a = 10;  // This is the tray's Top and Left Margin.
    var b = szy+5; // This is the tray's Y Step Value.
    BD18.curTrayNumb = this.trayNumb;
    BD18.curTrayStep = b;
    BD18.tileIsSelected = false;
    BD18.tokenIsSelected = false;
    BD18.canvas0.height = a+(this.tilesOnSheet*b); 
    for (var i=0;i<this.tilesOnSheet;i++)
    {
      sx = this.xStart+i*this.xStep;
      if (high === i) {
        BD18.context0.fillStyle = "red";
        BD18.context0.fillRect(a,b*i,szx,szy);
        BD18.context0.fillStyle = "black";
	      BD18.tileIsSelected = true;
      }
      BD18.context0.drawImage(img,sx,sy,szx,szy,a,b*i,szx,szy);
      BD18.context0.font = "18pt Arial";
      BD18.context0.textBaseline = "top";
      BD18.context0.textAlign = "left";
      BD18.context0.fillText(BD18.gm.trayCounts[this.trayNumb][i],a,b*i);
      if (BD18.gm.trayCounts[this.trayNumb][i] === 0) {  
        BD18.context0.fillStyle = "rgba(255,255,255,0.7)";
        BD18.context0.fillRect(a,b*i,szx,szy);
        BD18.context0.fillStyle = "black";
      }      
    }
  };
}

/* TokenSheet is a constructor function which creates tokenSheet
 * objects. These objects fully describe a token sheet and its    
 * contents. The Start, Size and Step attributes are used to 
 * locate tokens on the token sheet.
 */
function TokenSheet(image,sheet) {
  this.sheetType=sheet.type;
  this.trayName=sheet.tName;
  this.image=image;
  this.xStart=parseInt(sheet.xStart,10);
  this.xSize=parseInt(sheet.xSize,10);
  this.xStep=parseInt(sheet.xStep,10);
  this.yStart=parseInt(sheet.yStart,10);
  this.ySize=parseInt(sheet.ySize,10);
  this.yStep=parseInt(sheet.yStep,10);
  this.tokensOnSheet=sheet.token.length;
  this.tokenDups=[];
  this.tokenFlip=[];
  for(var i=0;i<this.tokensOnSheet;i++) {
    this.tokenDups[i]=parseInt(sheet.token[i].dups,10);
    this.tokenFlip[i]=sheet.token[i].flip;
  }
  this.place=function place(high) {
    var img = this.image;
    var sx = this.xStart;
    var sy;
    var szx = Math.min(this.xSize,105);
    var szy = this.ySize;
    var a = 10; // This is the tray's Top and Left Margin.
    var b = szy+5; // This is the tray's Y Step Value.
    var c = 20 // This is the token padding value.
    BD18.curTrayNumb = this.trayNumb;
    BD18.curTrayStep = b;
    BD18.tileIsSelected = false;
    BD18.tokenIsSelected = false;
    BD18.canvas0.height = a+(this.tokensOnSheet*b); 
    for (var i=0;i<this.tokensOnSheet;i++)
    {
      sy = this.yStart+i*this.yStep;
      if (high === i) {
        BD18.context0.fillStyle = "red";
        BD18.context0.fillRect(a,b*i,szx,szy);
        BD18.context0.fillStyle = "black";
	      BD18.tokenIsSelected = true;
      }
      BD18.context0.drawImage(img,sx,sy,szx,szy,a+c,b*i,szx,szy);
      BD18.context0.font = "18pt Arial";
      BD18.context0.textBaseline = "top";
      BD18.context0.textAlign = "left";
      BD18.context0.fillText(BD18.gm.trayCounts[this.trayNumb][i],a,b*i);
      if (BD18.gm.trayCounts[this.trayNumb][i] === 0) {  
        BD18.context0.fillStyle = "rgba(255,255,255,0.7)";
        BD18.context0.fillRect(a,b*i,szx+c,szy);
        BD18.context0.fillStyle = "black";
      }      
    }
  };
}

/* BoardTile is a constructor function which creates boardTile objects.
 * These objects are used to list the tiles that have been placed on a
 * board. The sheet index and rotation attributes describe the tile.   
 * The bx and by parameters are used to locate the tile on the game 
 * board.
 * */
function BoardTile(snumb,index,rotation,bx,by) {
  this.snumb=snumb;
  this.sheet=BD18.trays[snumb];
  this.index=index;
  this.rotation=rotation;
  this.bx=bx;
  this.by=by;
  /*
   * The place function places the tile on the board.
   * The optional tmp parameter should be true if this tile
   * has not been added to the BD18.boardTiles array.
   */  
  this.place=function place(tmp) {
    var temp = typeof tmp !== 'undefined' ? tmp : false;
    var image = this.sheet.image;
    var sx = this.sheet.xStart+index*this.sheet.xStep;
    var sy = this.sheet.yStart+rotation*this.sheet.yStep;
    var dx = BD18.gameBoard.xStart+BD18.gameBoard.xStep*bx;
    var dy = BD18.gameBoard.yStart+BD18.gameBoard.yStep*by;
    var dxf = dx.toFixed();
    var dyf = dy.toFixed();
    var szx = this.sheet.xSize;
    var szy = this.sheet.ySize;
    if (temp) {BD18.context1.globalAlpha = 0.5;}
    BD18.context1.drawImage(image,sx,sy,szx,szy,dxf,dyf,szx,szy);
    BD18.context1.globalAlpha = 1;
  };
  /*
   * The togm function exports the boardTile as a JSON object.
   */
  this.togm=function togm() {
    var brdTile = {};
    brdTile.sheetNumber = this.snumb;
    brdTile.tileNumber = this.index;
    brdTile.xCoord = this.bx;
    brdTile.yCoord = this.by;
    brdTile.rotate = this.rotation;
    return brdTile;
  };
}

/* BoardToken is a constructor function which creates boardToken
 * objects. These objects are used to list the tokens that have 
 * been placed on the map board. The snumb, sheet, index and flip 
 * parameters describe the token. The bx and by parameters are 
 * used to specify the exact position of the token on the game
 * board. And the hx and hy calculated parameters identify the 
 * game board hex that contains the token.
 * */
function BoardToken(snumb,index,flip,bx,by) {
  this.snumb=snumb;
  this.sheet=BD18.trays[snumb];
  this.index=index;
  this.flip=flip;
  this.bx=bx;
  this.by=by;
// [this.hx, this.hy] = BD18.gameBoard.hexCoord(bx, by);
  var tArray = BD18.gameBoard.hexCoord(bx, by);
  this.hx = tArray[0];
  this.hy = tArray[1];
  /*
   * The place function places the token on the board.
   * The optional alpha parameter should be 1 [if this is
   * a permanent token] or 0.5 [if this is not a permanent 
   * token].  Default is 1.
   */
  this.place=function place(alpha) {
    var ga = typeof alpha !== 'undefined' ? alpha : 1;
    var image = this.sheet.image;
    var sx = this.sheet.xStart + (this.flip?this.sheet.xStep:0);
    var sy = this.sheet.yStart + index*this.sheet.yStep;
    var szx = this.sheet.xSize;
    var szy = this.sheet.ySize;
    var midx = this.bx - (szx/2).toFixed(); // Adjust to center of token.
    var midy = this.by - (szy/2).toFixed(); // Adjust to center of token.
    BD18.context2.globalAlpha = ga;
    BD18.context2.drawImage(image,sx,sy,szx,szy,midx,midy,szx,szy);
    BD18.context2.globalAlpha = 1;
  };
  /*
   * The togm function exports the boardToken as a JSON object.
   */
  this.togm=function togm() {
    var brdToken = {};
    brdToken.sheetNumber = this.snumb;
    brdToken.tokenNumber = this.index;
    brdToken.xCoord = this.bx;
    brdToken.yCoord = this.by;
    brdToken.flip = this.flip;
    return brdToken;
  };
}
  
/* 
 * OnHex is a constructor function which creates an object 
 * listing all items [tile and tokens] on the hex with the  
 * supplied coordinates. 
 */
function OnHex(hexX, hexY) {
  this.hexX = parseInt(hexX);
  this.hexY = parseInt(hexY);
  this.tile = {};
  this.tokens = [];
  this.isTile = false;
  var item, i;
  if (BD18.boardTiles.length !== 0) {
    for (i=0;i<BD18.boardTiles.length;i++) {
      if (!(i in BD18.boardTiles)) continue ;
      item = BD18.boardTiles[i];
      if (item.bx === hexX && item.by === hexY) {
        this.tile = item;
        this.tile.btindex = i;
        this.isTile = true;
        break;
      }
    }  
  }
  if (BD18.boardTokens.length !== 0) {
    for (i=0;i<BD18.boardTokens.length;i++) {
      if (!(i in BD18.boardTokens)) continue ;
      item = BD18.boardTokens[i];
      if (item.hx === hexX && item.hy === hexY) {
        this.tokens.push(item);
        this.tokens[this.tokens.length-1].btindex = i;
      }
    }
  }
  this.noToken = (this.tokens.length === 0);
  this.oneToken = (this.tokens.length === 1);
  this.manyTokens = (this.tokens.length > 1);
  this.manyItems = (this.manyTokens || 
    (this.isTile && this.oneToken));
}
