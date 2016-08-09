/*
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

/* 
 * All BD18 global variables are contained in one
 * 'master' object called BD18.  This isolates 
 * them from global variables in other packages. 
 */
BD18.loadCount = 0;
BD18.doneWithLoad = false;
BD18.boxID = null;
BD18.marketTokens = [];
BD18.trays = [];
BD18.curTrayNumb = null;
BD18.curTrayStep = null;
BD18.curIndex = null;
BD18.curFlip = false;
BD18.curStack = null;
BD18.curBoxX = null;     
BD18.curBoxY = null;     
BD18.curMktX = null;     
BD18.curMktY = null;     
BD18.tokenIsSelected = false;
BD18.boxIsSelected = false;
BD18.tknMenu = {};
BD18.tknMenu.timeoutID = 0;
BD18.onBoxList = {};
BD18.deletedMarketToken = null;
BD18.isSnap = false;

/*  
 * Constructor functions.
 */

/* 
 * StockMarket is a constructor function which creates a stockMarket
 * object. This object fully describes the stock market and its use.   
 * The Start and Step attributes are used to locate the squares on the
 * market for placement of tokens.
 */
function StockMarket(image,market) {
  this.image=image;
  this.height=parseInt(image.height,10);
  this.width=parseInt(image.width,10);
  this.xStart=parseInt(market.xStart,10);
  this.xStep=parseInt(market.xStep,10);
  this.yStart=parseInt(market.yStart,10);
  this.yStep=parseInt(market.yStep,10);
  var that = this;
  /*
   * The place function places the stock market on canvas1.
   */
  this.place=function place() {
    BD18.context1.drawImage(image,0,0);
    BD18.boxIsSelected = false;
    BD18.stockMarket = that;
  };
  /*
   * The clear2 function clears all tokens from canvas2.
   * The default value for keepHexSelect is false;
   */
  this.clear2=function clear2(keepBoxSelect) {
    BD18.context2.clearRect(0, 0, this.width, this.height);
    BD18.boxIsSelected = (keepBoxSelect) ? true : false;
  };
  /* This function calculates the chart coordinates of the containing
   * price box given an exact position in pixels on the stock market.
   */
  this.chartCoord = function chartCoord(xPix, yPix) {
    var yCent = this.yStart + this.yStep/2;
    var yIndex = Math.round(((yPix-yCent)/this.yStep));
    var xCent = this.xStart + this.xStep/2;
    var xIndex = Math.round(((xPix-xCent)/this.xStep));
    return [xIndex, yIndex];
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
    BD18.tokenIsSelected = false;
    BD18.canvas0.height = a+(this.tokensOnSheet*b); 
    for (var i=0;i<this.tokensOnSheet;i++)
    {
      sy = this.yStart+i*this.yStep;
      if (high === i) {
        BD18.context0.fillStyle = "red";
        BD18.context0.fillRect(a,b*i,szx+c,szy);
        BD18.context0.fillStyle = "black";
	      BD18.tokenIsSelected = true;
      }
      BD18.context0.drawImage(img,sx,sy,szx,szy,a+c,b*i,szx,szy);
    }
  };
}

/* 
 * MarketToken is a constructor function which creates marketToken
 * objects. These objects are used to list the tokens that have 
 * been placed on the stock market. The snumb, sheet, index and flip 
 * parameters describe the token. The stack parameter [initially set
 * to zero] controls terminal stacking. The bx and by parameters are 
 * used to specify the exact position of the token on the stock
 * market. And the hx and hy calculated parameters identify the 
 * market price box that contains the token.
 */
function MarketToken(snumb,index,flip,stack,bx,by) {
  this.snumb=snumb;
  this.sheet=BD18.trays[snumb];
  this.index=index;
  this.flip=flip;
  this.stack=stack;
  this.bx=bx;
  this.by=by;
// [this.hx, this.hy] = BD18.stockMarket.chartCoord(bx, by);
  var tArray = BD18.stockMarket.chartCoord(bx, by);
  this.hx = tArray[0];
  this.hy = tArray[1];
  /*
   * The place function places the token on the stock market.
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
   * The togm function exports the marketToken as a JSON object.
   */
  this.togm=function togm() {
    var mktToken = {};
    mktToken.sheetNumber = this.snumb;
    mktToken.tokenNumber = this.index;
    mktToken.xCoord = this.bx;
    mktToken.yCoord = this.by;
    mktToken.flip = this.flip;
    mktToken.stack = this.stack;
    return mktToken;
  };
}
  
/* 
 * OnBox is a constructor function which creates an object 
 * listing all tokens on the stock market price box with  
 * the supplied coordinates. 
 */
function OnBox(boxX, boxY) {
  this.boxX = parseInt(boxX);
  this.boxY = parseInt(boxY);
  this.tokens = [];
  var item, i;
  if (BD18.marketTokens.length !== 0) {
    for (i=0;i<BD18.marketTokens.length;i++) {
      if (!(i in BD18.marketTokens)) continue ;
      item = BD18.marketTokens[i];
      if (item.hx === boxX && item.hy === boxY) {
        this.tokens.push(item);
        this.tokens[this.tokens.length-1].mtindex = i;
      }
    }
  }
  this.noToken = (this.tokens.length === 0);
  this.oneToken = (this.tokens.length === 1);
  this.manyTokens = (this.tokens.length > 1);
  /*
   * The stackSort function orders the tokens by stack
   * value high to low. It returns false if no tokens.
   */
  this.stackSort=function stackSort() {
    if (this.noToken) return false;
    if (this.oneToken) return true;
    this.tokens.sort(function(tokA, tokB) {
      if (tokA.stack < tokB.stack) return 1;
      if (tokA.stack > tokB.stack) return -1;
      return 0;
    });
    return true;
  };
  /*
   * The stackNorm function calls stackSort.
   * It then normalizes the stack values for
   * the tokens in the tokens array such that
   * the last token has a stack value of 1 and
   * each preceding token has a value 1 higher. 
   * It returns false if no tokens.
   */
  this.stackNorm=function stackNorm() {
    if (!this.stackSort()) return false;
    var max = this.tokens.length;
    for (var i=0;i<max;i++) {
      this.tokens[i].stack = max - i;
    }
    return true;
  };
  /*
   * The stackSpread function adds one to the
   * stack value of each token that has a current
   * stack value that is greater than or equal to ss. 
   */
  this.stackSpread=function stackSpread(ss) {
    var i, curS;
    for (i=0;i<this.tokens.length;i++) {
      curS = this.tokens[i].stack;
      this.tokens[i].stack= (curS>=ss)?++curS:curS;
    }
    return true;
  };
}
