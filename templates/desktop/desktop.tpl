<?xml-stylesheet href="#internalStyle" type="text/css"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="et">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>{VAR:name}</title>
<style type="text/css" id="internalStyle">
/*<![CDATA[*/

IMG {border-width:0px}

.boxA {
  border: 1px solid transparent;
  position: relative;
  left: 0px;
  top: 0px;
}

.boxB {
  background-color: #fffff0;
  padding: 2px;
}

.wincap{}
.baricon
{
visibility:hidden;
position:absolute;
}

.windowframe {
margin:0px;padding:0em;z-index: 100;
}
.window {
  background-color: #c0c0c0;
  border-color: #f0f0f0 #606060 #404040 #d0d0d0;
  border-style: solid;
  border-width: 4px;
  margin: 5px;
  padding: 1px;
  position: absolute;
  text-align: left;
  visibility: hidden;
}

.titleBar {
  background-color: #008080;
  cursor: default;
  color: #ffffff;
  font-family: "MS Sans Serif", "Arial", "Helvetica";
  font-size: 10pt;
  font-weight: bold;
  margin: 0px;
  padding: 0px 2px 0px 2px;
  text-align: right;
  white-space: nowrap;
  vertical-align: middle;
}

.titleBarText {
  margin: 0px;
  float: left;
  overflow: hidden;
  text-align: left;
  vertical-align: middle;
}

.titleBarButtons {
  width: 1px;
  height: 20px;
}

.clientArea {
  background-color: #ffffff;
  border-color: #404040 #e0e0e0 #f0f0f0 #505050;
  border-style: solid;
  border-width: 0px;
  color: #000000;
  font-family: "Arial", "Helvetica", sans-serif;
  font-size: 10pt;
  margin: 0px 0px 0px 0px;
  overflow: hidden;
  padding: 1px;
  z-index: 105;
}

body {
  font-family: "Arial", "Helvetica", sans-serif;
  font-size: 10pt;
  margin: 0px 0px 0px 0px;

	color: #{VAR:backgroundtextcolor};
	background-color: #{VAR:backgroundcolor};
<!-- SUB: backgroundimage -->
	background-image: url({VAR:bgimage});
<!-- END SUB: backgroundimage -->
	background-attachment:fixed;
	{VAR:bgstyle}
	z-index:1;
}

div.menuBar,
div.menuBar a.menuButton,
div.menu,
div.menu a.menuItem {
  font-family: "MS Sans Serif", Arial, sans-serif;
  font-size: 10pt;
  font-style: normal;
  font-weight: normal;
  color: #000000;
}

div.menuBar {
  background-color: #d0d0d0;
  border: 2px solid;
  border-color: #f0f0f0 #909090 #909090 #f0f0f0;
  padding: 0px 0px 4px 0px;
  text-align: left;

}

div.menuBar a.menuButton {
  background-color: transparent;
  border: 1px solid #d0d0d0;
  color: #000000;
  cursor: default;
  left: 0px;
  margin: 1px;
  margin-right: 0px;
  padding: 4px 0px 2px 3px;
  position: relative;
  text-decoration: none;
  font-weight: bold;
  top: 0px;
  z-index: 1000;

}

div.menuBar a.menuButton:hover {
  background-color: transparent;
  border-color: #f0f0f0 #909090 #909090 #f0f0f0;
  color: #000000;
}

div.menuBar a.menuButtonActive,
div.menuBar a.menuButtonActive:hover {
  background-color: #a0a0a0;
  border-color: #909090 #f0f0f0 #f0f0f0 #909090;
  color: #ffffff;
  left: 0px;
  top: 1px;

}

div.menu {
  background-color: #d0d0d0;
  border: 2px solid;
  border-color: #f0f0f0 #909090 #909090 #f0f0f0;
  left: 0px;
  padding: 0px 1px 1px 0px;
  position: absolute;
  top: 0px;
  visibility: hidden;
  z-index: 1001;
}

div.menu a.menuItem {
  color: #000000;
  cursor: default;
  display: block;
  padding: 3px 1em;
  text-decoration: none;
  white-space: nowrap;
}

div.menu a.menuItem:hover, div.menu a.menuItemHighlight {
  background-color: #000080;
  border-color: #ffffff;
  border:solid 1px;
  color: #ffffff;
}

div.menu a.menuItem span.menuItemText {}

div.menu a.menuItem span.menuItemArrow {
  margin-right: -.55em;
height:16px;
}

div.menu div.menuItemSep {
  border-top: 1px solid #909090;
  border-bottom: 1px solid #f0f0f0;
  margin: 4px 2px;
}


/*]]>*/
</style>
<script type="text/javascript">
/*<![CDATA[*/

//------------clock------------------------------------

var currenttime;
var WINDOW = new Array;

function clock() {
var date = new Date();
//var year = date.getYear() +1900;
var year = date.getFullYear();
var month = date.getMonth();
var day = date.getDate();
var hour = date.getHours();
var minute = date.getMinutes();
var second = date.getSeconds();
var months = new Array("JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC")
var monthname = months[month];

if({VAR:usdate})
{
	if (hour > 12)
	{
		hour = hour - 12;
		usd = 'PM';
	}
	else
	{
		usd = 'AM';
	}
}
else
{
	usd = '';
}

if (minute < 10) {
minute = "0" + minute;
}

if (second < 10) {
second = "0" + second;
}

if (hour < 10) {
hour = "  " + hour;
}


currenttime = day + '. ' + monthname + " " + year + "  " + hour + ":" + minute + ":" + second;

//document.forms['page'].elements['clock'].value = hour + ":" + minute + " " + usd ;

el = document.getElementById("clock").innerHTML = hour + ":" + minute + " " + usd ;

/*
for (var i=1;i < 10; i++)
{
	if (WINDOW[i])
	{
		if (WINDOW[i].closed)
		{
			document.getElementById('w[' + i + ']').innerHTML = '';
			WINDOW[i] = 0;
		}
		else
		{
		}
	}
}
*/
setTimeout("clock()", 10000);
}


//------------windows------------------------------------

//*****************************************************************************
// Do not remove this notice.
//
// Copyright 2001 by Mike Hall.
// See http://www.brainjar.com for terms of use.
//*****************************************************************************

var barheight = 22;

// Determine browser and version.

function Browser() {

  var ua, s, i;

  this.isIE    = false;  // Internet Explorer
  this.isNS    = false;  // Netscape
  this.version = null;

  ua = navigator.userAgent;

  s = "MSIE";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isIE = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  s = "Netscape6/";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  // Treat any other "Gecko" browser as NS 6.1.

  s = "Gecko";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = 6.1;
    return;
  }
}

var browser = new Browser();

//=============================================================================
// Window Object
//=============================================================================

function Window(el) {

  var i, mapList, mapName;

  // Get window components.

  this.frame           = el;
  this.titleBar        = winFindByClassName(el, "titleBar");
  this.titleBarText    = winFindByClassName(el, "titleBarText");
  this.titleBarButtons = winFindByClassName(el, "titleBarButtons");
  this.clientArea      = winFindByClassName(el, "clientArea");

  // Find matching button image map.

//  mapName = this.titleBarButtons.useMap.substr(1);
//  mapList = document.getElementsByTagName("MAP");
//  for (i = 0; i < mapList.length; i++)
//    if (mapList[i].name == mapName)
//      this.titleBarMap = mapList[i];

  // Save colors.

  this.activeFrameBackgroundColor  = this.frame.style.backgroundColor;
  this.activeFrameBorderColor      = this.frame.style.borderColor;
  this.activeTitleBarColor         = this.titleBar.style.backgroundColor;
  this.activeTitleTextColor        = this.titleBar.style.color;
  this.activeClientAreaBorderColor = this.clientArea.style.borderColor;
  if (browser.isIE)
    this.activeClientAreaScrollbarColor = this.clientArea.style.scrollbarBaseColor;

  // Save images.

  this.activeButtonsImage   = this.titleBarButtons.src;
  this.inactiveButtonsImage = this.titleBarButtons.longDesc;

  // Set flags.

  this.isOpen      = false;
  this.isMinimized = false;

  // Set methods.

  this.open       = winOpen;
  this.close      = winClose;
  this.minimize   = winMinimize;
  this.restore    = winRestore;
  this.makeActive = winMakeActive;

  // Set up event handling.

  this.frame.parentWindow = this;
  this.frame.onmousemove  = winResizeCursorSet;
  this.frame.onmouseout   = winResizeCursorRestore;
  this.frame.onmousedown  = winResizeDragStart;

  this.titleBar.parentWindow = this;
  this.titleBar.onmousedown  = winMoveDragStart;

  this.clientArea.parentWindow = this;
  this.clientArea.onclick      = winClientAreaClick;

//  for (i = 0; i < this.titleBarMap.childNodes.length; i++)
//  if (this.titleBarMap.childNodes[i].tagName == "AREA")
//      this.titleBarMap.childNodes[i].parentWindow = this;

  // Calculate the minimum width and height values for resizing
  // and fix any initial display problems.

  var initLt, initWd, w, dw;

  // Save the inital frame width and position, then reposition
  // the window.

  initLt = this.frame.style.left;
  initWd = parseInt(this.frame.style.width);
  this.frame.style.left = -this.titleBarText.offsetWidth + "px";

  // For IE, start calculating the value to use when setting
  // the client area width based on the frame width.

  if (browser.isIE) {
    this.titleBarText.style.display = "none";
    w = this.clientArea.offsetWidth;
    this.widthDiff = this.frame.offsetWidth - w;
    this.clientArea.style.width = w + "px";
    dw = this.clientArea.offsetWidth - w;
    w -= dw;     
    this.widthDiff += dw;
    this.titleBarText.style.display = "";
  }

  // Find the difference between the frame's style and offset
  // widths. For IE, adjust the client area/frame width
  // difference accordingly.

  w = this.frame.offsetWidth;
  this.frame.style.width = w + "px";
  dw = this.frame.offsetWidth - w;
  w -= dw;     
  this.frame.style.width = w + "px";
  if (browser.isIE)
    this.widthDiff -= dw;

  // Find the minimum width for resize.

  this.isOpen = true;  // Flag as open so minimize call will work.
  this.minimize();
  this.minimumWidth = this.frame.offsetWidth - dw;

  // Find the frame width at which or below the title bar text will
  // need to be clipped.

  this.titleBarText.style.width = "";
  this.clipTextMinimumWidth = this.frame.offsetWidth - dw;

  // Set the minimum height.

  this.minimumHeight = 1;

  // Restore window. For IE, set client area width.

  this.restore();
  this.isOpen = false;  // Reset flag.
  initWd = Math.max(initWd, this.minimumWidth);
  this.frame.style.width = initWd + "px";
  if (browser.isIE)
    this.clientArea.style.width = (initWd - this.widthDiff) + "px";

  // Clip the title bar text if needed.

  if (this.clipTextMinimumWidth >= this.minimumWidth)
    this.titleBarText.style.width = (winCtrl.minimizedTextWidth + initWd - this.minimumWidth) + "px";

  // Restore the window to its original position.

  this.frame.style.left = initLt;
}

//=============================================================================
// Window Methods
//=============================================================================

function winOpen() {

  if (this.isOpen)
    return;

  // Restore the window and make it visible.

  this.makeActive();
  this.isOpen = true;
  if (this.isMinimized)
    this.restore();
  this.frame.style.visibility = "visible";
}

function winClose() {

  // Hide the window.

  this.frame.style.visibility = "hidden";
  this.isOpen = false;
}

function winMinimize() {

  if (!this.isOpen || this.isMinimized)
    return;

  this.makeActive();

  // Save current frame and title bar text widths.

  this.restoreFrameWidth = this.frame.style.width;
  this.restoreTextWidth = this.titleBarText.style.width;

  // Disable client area display.

  this.clientArea.style.display = "none";

  // Minimize frame and title bar text widths.

  if (this.minimumWidth)
    this.frame.style.width = this.minimumWidth + "px";
  else
    this.frame.style.width = "";
  this.titleBarText.style.width = winCtrl.minimizedTextWidth + "px";

  this.isMinimized = true;
}

function winRestore() {

  if (!this.isOpen || !this.isMinimized)
    return;

  this.makeActive();

  // Enable client area display.

  this.clientArea.style.display = "";

  // Restore frame and title bar text widths.

  this.frame.style.width = this.restoreFrameWidth;
  this.titleBarText.style.width = this.restoreTextWidth;

  this.isMinimized = false;
}

function winMakeActive() {

  if (winCtrl.active == this)
    return;

  // Inactivate the currently active window.

  if (winCtrl.active) {
    winCtrl.active.frame.style.backgroundColor    = winCtrl.inactiveFrameBackgroundColor;
    winCtrl.active.frame.style.borderColor        = winCtrl.inactiveFrameBorderColor;
    winCtrl.active.titleBar.style.backgroundColor = winCtrl.inactiveTitleBarColor;
    winCtrl.active.titleBar.style.color           = winCtrl.inactiveTitleTextColor;
    winCtrl.active.clientArea.style.borderColor   = winCtrl.inactiveClientAreaBorderColor;
    if (browser.isIE)
      winCtrl.active.clientArea.style.scrollbarBaseColor = winCtrl.inactiveClientAreaScrollbarColor;
    if (browser.isNS && browser.version < 6.1)
      winCtrl.active.clientArea.style.overflow = "hidden";
    if (winCtrl.active.inactiveButtonsImage)
      winCtrl.active.titleBarButtons.src = winCtrl.active.inactiveButtonsImage;
  }

  // Activate this window.

  this.frame.style.backgroundColor    = this.activeFrameBackgroundColor;
  this.frame.style.borderColor        = this.activeFrameBorderColor;
  this.titleBar.style.backgroundColor = this.activeTitleBarColor;
  this.titleBar.style.color           = this.activeTitleTextColor;
  this.clientArea.style.borderColor   = this.activeClientAreaBorderColor;
  if (browser.isIE)
    this.clientArea.style.scrollbarBaseColor = this.activeClientAreaScrollbarColor;
  if (browser.isNS && browser.version < 6.1)
    this.clientArea.style.overflow = "auto";
//  if (this.inactiveButtonsImage)
//    this.titleBarButtons.src = this.activeButtonsImage;
  this.frame.style.zIndex = ++winCtrl.maxzIndex + 100; //!!!!
  winCtrl.active = this;
}

//=============================================================================
// Event handlers.
//=============================================================================

function winClientAreaClick(event) {

  // Make this window the active one.

  this.parentWindow.makeActive();
}

//-----------------------------------------------------------------------------
// Window dragging.
//-----------------------------------------------------------------------------

function winMoveDragStart(event) {

  var target;
  var x, y;
  if (browser.isIE)
  {
    target = window.event.srcElement.tagName;
    }
  if (browser.isNS)
  {
    target = event.target.tagName;
    }


  if (target == "AREA")
    return;

  this.parentWindow.makeActive();

  // Get cursor offset from window frame.

  if (browser.isIE) {
    x = window.event.x;
    y = window.event.y;
  }
  if (browser.isNS) {
    x = event.pageX;
    y = event.pageY;
  }
  winCtrl.xOffset = winCtrl.active.frame.offsetLeft - x;
  winCtrl.yOffset = winCtrl.active.frame.offsetTop  - y;

  // Set document to capture mousemove and mouseup events.

  if (browser.isIE) {
    document.onmousemove = winMoveDragGo;
    document.onmouseup   = winMoveDragStop;
  }
  if (browser.isNS) {
    document.addEventListener("mousemove", winMoveDragGo,   true);
    document.addEventListener("mouseup",   winMoveDragStop, true);
    event.preventDefault();
  }

  winCtrl.inMoveDrag = true;
}

function winMoveDragGo(event) {

  var x, y;

  if (!winCtrl.inMoveDrag)
    return;

  // Get cursor position.

  if (browser.isIE) {
    x = window.event.x;
    y = window.event.y;
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (browser.isNS) {
    x = event.pageX;
    y = event.pageY;
    event.preventDefault();
  }

  // Move window frame based on offset from cursor.

  winCtrl.active.frame.style.left = (x + winCtrl.xOffset) + "px";
  winCtrl.active.frame.style.top  = (y + winCtrl.yOffset) + "px";

}

function winMoveDragStop(event) {

  winCtrl.inMoveDrag = false;

  // Remove mousemove and mouseup event captures on document.

  if (browser.isIE) {
    document.onmousemove = null;
    document.onmouseup   = null;
  }
  if (browser.isNS) {
    document.removeEventListener("mousemove", winMoveDragGo,   true);
    document.removeEventListener("mouseup",   winMoveDragStop, true);
  }

}

//-----------------------------------------------------------------------------
// Window resizing.
//-----------------------------------------------------------------------------

function winResizeCursorSet(event) {

  var target;
  var xOff, yOff;

  if (this.parentWindow.isMinimized || winCtrl.inResizeDrag)
    return;

  // If not on window frame, restore cursor and exit.

  if (browser.isIE)
    target = window.event.srcElement;
  if (browser.isNS)
    target = event.target;
  if (target != this.parentWindow.frame)
    return;

  // Find resize direction.

  if (browser.isIE) {
    xOff = window.event.offsetX;
    yOff = window.event.offsetY;
  }
  if (browser.isNS) {
    xOff = event.layerX;
    yOff = event.layerY;
  }
  winCtrl.resizeDirection = ""
  if (yOff <= winCtrl.resizeCornerSize)
    winCtrl.resizeDirection += "n";
  else if (yOff >= this.parentWindow.frame.offsetHeight - winCtrl.resizeCornerSize)
    winCtrl.resizeDirection += "s";
  if (xOff <= winCtrl.resizeCornerSize)
    winCtrl.resizeDirection += "w";
  else if (xOff >= this.parentWindow.frame.offsetWidth - winCtrl.resizeCornerSize)
    winCtrl.resizeDirection += "e";

  // If not on window edge, restore cursor and exit.

  if (winCtrl.resizeDirection == "") {
    this.onmouseout(event);
    return;
  }

  // Change cursor.

  if (browser.isIE)
    document.body.style.cursor = winCtrl.resizeDirection + "-resize";
  if (browser.isNS)
    this.parentWindow.frame.style.cursor = winCtrl.resizeDirection + "-resize";
}

function winResizeCursorRestore(event) {

  if (winCtrl.inResizeDrag)
    return;

  // Restore cursor.

  if (browser.isIE)
    document.body.style.cursor = "";
  if (browser.isNS)
    this.parentWindow.frame.style.cursor = "";
}

function winResizeDragStart(event) {

  var target;

  // Make sure the event is on the window frame.

  if (browser.isIE)
    target = window.event.srcElement;
  if (browser.isNS)
    target = event.target;
  if (target != this.parentWindow.frame)
    return;

  this.parentWindow.makeActive();

  if (this.parentWindow.isMinimized)
    return;

  // Save cursor position.

  if (browser.isIE) {
    winCtrl.xPosition = window.event.x;
    winCtrl.yPosition = window.event.y;
  }
  if (browser.isNS) {
    winCtrl.xPosition = event.pageX;
    winCtrl.yPosition = event.pageY;
  }

  // Save window frame position and current window size.

  winCtrl.oldLeft   = parseInt(this.parentWindow.frame.style.left,  10);
  winCtrl.oldTop    = parseInt(this.parentWindow.frame.style.top,   10);
  winCtrl.oldWidth  = parseInt(this.parentWindow.frame.style.width, 10);
  winCtrl.oldHeight = parseInt(this.parentWindow.clientArea.style.height, 10);

  // Set document to capture mousemove and mouseup events.

  if (browser.isIE) {
    document.onmousemove = winResizeDragGo;
    document.onmouseup   = winResizeDragStop;
  }
  if (browser.isNS) {
    document.addEventListener("mousemove", winResizeDragGo,   true);
    document.addEventListener("mouseup"  , winResizeDragStop, true);
    event.preventDefault();
  }

  winCtrl.inResizeDrag = true;

}

function winResizeDragGo(event) {

 var north, south, east, west;
 var dx, dy;
 var w, h;

  if (!winCtrl.inResizeDrag)
    return;

  // Set direction flags based on original resize direction.

  north = false;
  south = false;
  east  = false;
  west  = false;
  if (winCtrl.resizeDirection.charAt(0) == "n")
    north = true;
  if (winCtrl.resizeDirection.charAt(0) == "s")
    south = true;
  if (winCtrl.resizeDirection.charAt(0) == "e" || winCtrl.resizeDirection.charAt(1) == "e")
    east = true;
  if (winCtrl.resizeDirection.charAt(0) == "w" || winCtrl.resizeDirection.charAt(1) == "w")
    west = true;

  // Find change in cursor position.

  if (browser.isIE) {
    dx = window.event.x - winCtrl.xPosition;
    dy = window.event.y - winCtrl.yPosition;
  }
  if (browser.isNS) {
    dx = event.pageX - winCtrl.xPosition;
    dy = event.pageY - winCtrl.yPosition;
  }

  // If resizing north or west, reverse corresponding amount.

  if (west)
    dx = -dx;
  if (north)
    dy = -dy;

  // Check new size.

  w = winCtrl.oldWidth  + dx;
  h = winCtrl.oldHeight + dy;
  if (w <= winCtrl.active.minimumWidth) {
    w = winCtrl.active.minimumWidth;
    dx = w - winCtrl.oldWidth;
  }
  if (h <= winCtrl.active.minimumHeight) {
    h = winCtrl.active.minimumHeight;
    dy = h - winCtrl.oldHeight;
  }

  // Resize the window. For IE, keep client area and frame widths in synch.

  if (east || west) {
    winCtrl.active.frame.style.width = w + "px";
    if (browser.isIE)
      winCtrl.active.clientArea.style.width = (w - winCtrl.active.widthDiff) + "px";
  }
  if (north || south)
    winCtrl.active.clientArea.style.height = h + "px";

  // Clip the title bar text, if necessary.

  if (east || west) {
    if (w < winCtrl.active.clipTextMinimumWidth)
      winCtrl.active.titleBarText.style.width = (winCtrl.minimizedTextWidth + w - winCtrl.active.minimumWidth) + "px";
    else
      winCtrl.active.titleBarText.style.width = "";
  }

  // For a north or west resize, move the window.

  if (west)
    winCtrl.active.frame.style.left = (winCtrl.oldLeft - dx) + "px";
  if (north)
    winCtrl.active.frame.style.top  = (winCtrl.oldTop  - dy) + "px";

  if (browser.isIE) {
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (browser.isNS)
    event.preventDefault();

}

function winResizeDragStop(event) {

  winCtrl.inResizeDrag = false;

  // Remove mousemove and mouseup event captures on document.

  if (browser.isIE) {
    document.onmousemove = null;
    document.onmouseup   = null;
  }
  if (browser.isNS) {
    document.removeEventListener("mousemove", winResizeDragGo,   true);
    document.removeEventListener("mouseup"  , winResizeDragStop, true);
  }

}

//=============================================================================
// Utility functions.
//=============================================================================

function winFindByClassName(el, className) {

  var i, tmp;

  if (el.className == className)
    return el;

  // Search for a descendant element assigned the given class.

  for (i = 0; i < el.childNodes.length; i++) {
    tmp = winFindByClassName(el.childNodes[i], className);
    if (tmp != null)
      return tmp;
  }

  return null;
}

//=============================================================================
// Initialization code.
//=============================================================================

var winList = new Array();
var winCtrl = new Object();

function winInit() {

  var elList;

  // Initialize window control object.

  winCtrl.maxzIndex                        =   0;
  winCtrl.resizeCornerSize                 =  26;
  winCtrl.minimizedTextWidth               = 100;
  winCtrl.inactiveFrameBackgroundColor     = "#c0c0c0";
  winCtrl.inactiveFrameBorderColor         = "#f0f0f0 #505050 #404040 #e0e0e0";
  winCtrl.inactiveTitleBarColor            = "#808080";
  winCtrl.inactiveTitleTextColor           = "#c0c0c0";
  winCtrl.inactiveClientAreaBorderColor    = "#404040 #e0e0e0 #f0f0f0 #505050";
  winCtrl.inactiveClientAreaScrollbarColor = "";
  winCtrl.inMoveDrag                       = false;
  winCtrl.inResizeDrag                     = false;

  // Initialize windows and build list.

  elList = document.getElementsByTagName("DIV");
  for (var i = 0; i < elList.length; i++)
    if (elList[i].className == "window")
      winList[elList[i].id] = new Window(elList[i]);
}

window.onload = winInit;  // run initialization code after page loads.

//*****************************************************************************
// Do not remove this notice.
//
// Copyright 2000 by Mike Hall.
// See http://www.brainjar.com for terms of use.
//*****************************************************************************

//------------menu bar------------------------------------

//----------------------------------------------------------------------------
// Code for handling the menu bar and active button.
//----------------------------------------------------------------------------

var activeButton = null;

// Capture mouse clicks on the page so any active button can be
// deactivated.

if (browser.isIE)
  document.onmousedown = pageMousedown;
else
  document.addEventListener("mousedown", pageMousedown, true);

function pageMousedown(event) {

  var el;

  // If there is no active button, exit.

  if (activeButton == null)
    return;

  // Find the element that was clicked on.

  if (browser.isIE)
    el = window.event.srcElement;
  else
    el = (event.target.tagName ? event.target : event.target.parentNode);

  // If the active button was clicked on, exit.

  if (el == activeButton)
    return;

  // If the element is not part of a menu, reset and clear the active
  // button.

  if (getContainerWith(el, "DIV", "menu") == null) {
    resetButton(activeButton);
    activeButton = null;
  }
}

function buttonClick(event, menuId,fix) {

  var button;

  // Get the target button element.

  if (browser.isIE)
    button = window.event.srcElement;
  else
    button = event.currentTarget;

  // Blur focus from the link to remove that annoying outline.

  button.blur();

  // Associate the named menu to this button if not already done.
  // Additionally, initialize menu display.

  if (button.menu == null) {
    button.menu = document.getElementById(menuId);
    if (button.menu.isInitialized == null)
      menuInit(button.menu);
  }

  // Reset the currently active button, if any.

  if (activeButton != null)
    resetButton(activeButton);

  // Activate this button, unless it was the currently active one.

  if (button != activeButton) {
    depressButton(button,menuId,fix);
    activeButton = button;
  }
  else
    activeButton = null;

  return false;
}

function buttonMouseover(event, menuId,fix) {

  var button;

  // Find the target button element.

  if (browser.isIE)
    button = window.event.srcElement;
  else
    button = event.currentTarget;

  // If any other button menu is active, make this one active instead.

  if (activeButton != null && activeButton != button)
    buttonClick(event, menuId,fix);
}

function depressButton(button,celem,fix) {

  var x, y;

  // Update the button's style class to make it look like it's
  // depressed.

  button.className += " menuButtonActive";

  // Position the associated drop down menu under the button and
  // show it.

  x = getPageOffsetLeft(button);
  y = getPageOffsetTop(button) + button.offsetHeight - fix;//<<<<

  // For IE, adjust position.

  if (browser.isIE) {
    x += button.offsetParent.clientLeft;
    y += button.offsetParent.clientTop;
  }

  button.menu.style.left = x + "px";
  button.menu.style.top  = y + "px";
  button.menu.style.visibility = "visible";
}

function resetButton(button) {

  // Restore the button's style class.

  removeClassName(button, "menuButtonActive");

  // Hide the button's menu, first closing any sub menus.

  if (button.menu != null) {
    closeSubMenu(button.menu);
    button.menu.style.visibility = "hidden";
  }
}

//----------------------------------------------------------------------------
// Code to handle the menus and sub menus.
//----------------------------------------------------------------------------

function menuMouseover(event) {

  var menu;

  // Find the target menu element.

  if (browser.isIE)
    menu = getContainerWith(window.event.srcElement, "DIV", "menu");
  else
    menu = event.currentTarget;

  // Close any active sub menu.

  if (menu.activeItem != null)
    closeSubMenu(menu);
}

function menuItemMouseover(event, menuId) {

  var item, menu, x, y;

  // Find the target item element and its parent menu element.

  if (browser.isIE)
    item = getContainerWith(window.event.srcElement, "A", "menuItem");
  else
    item = event.currentTarget;
  menu = getContainerWith(item, "DIV", "menu");

  // Close any active sub menu and mark this one as active.

  if (menu.activeItem != null)
    closeSubMenu(menu);
  menu.activeItem = item;

  // Highlight the item element.

  item.className += " menuItemHighlight";

  // Initialize the sub menu, if not already done.

  if (item.subMenu == null) {
    item.subMenu = document.getElementById(menuId);
    if (item.subMenu.isInitialized == null)
      menuInit(item.subMenu);
  }

  // Get position for submenu based on the menu item.

  x = getPageOffsetLeft(item) + item.offsetWidth;
  y = getPageOffsetTop(item);

  // Adjust position to fit in view.

  var maxX, maxY;

  if (browser.isNS) {
    maxX = window.scrollX + window.innerWidth;
    maxY = window.scrollY + window.innerHeight;
  }
  if (browser.isIE) {
    maxX = (document.documentElement.scrollLeft   != 0 ? document.documentElement.scrollLeft    : document.body.scrollLeft)
         + (document.documentElement.clientWidth  != 0 ? document.documentElement.clientWidth   : document.body.clientWidth);
    maxY = (document.documentElement.scrollTop    != 0 ? document.documentElement.scrollTop    : document.body.scrollTop)
         + (document.documentElement.clientHeight != 0 ? document.documentElement.clientHeight : document.body.clientHeight);
  }
  maxX -= item.subMenu.offsetWidth;
  maxY -= item.subMenu.offsetHeight;

  if (x > maxX)
    x = Math.max(0, x - item.offsetWidth - item.subMenu.offsetWidth
      + (menu.offsetWidth - item.offsetWidth));
  y = Math.max(0, Math.min(y, maxY));

  // Position and show it.

  item.subMenu.style.left = x + "px";
  item.subMenu.style.top  = y + "px";
  item.subMenu.style.visibility = "visible";

  // Stop the event from bubbling.

  if (browser.isIE)
    window.event.cancelBubble = true;
  else
    event.stopPropagation();
}

function closeSubMenu(menu) {

  if (menu == null || menu.activeItem == null)
    return;

  // Recursively close any sub menus.

  if (menu.activeItem.subMenu != null) {
    closeSubMenu(menu.activeItem.subMenu);
    menu.activeItem.subMenu.style.visibility = "hidden";
    menu.activeItem.subMenu = null;
  }
  removeClassName(menu.activeItem, "menuItemHighlight");
  menu.activeItem = null;
}

//----------------------------------------------------------------------------
// Code to initialize menus.
//----------------------------------------------------------------------------

function menuInit(menu) {

  var itemList, spanList;
  var textEl, arrowEl;
  var itemWidth;
  var w, dw;
  var i, j;

  // For IE, replace arrow characters.

  if (browser.isIE) {
    menu.style.lineHeight = "2.5ex";
    spanList = menu.getElementsByTagName("SPAN");
    for (i = 0; i < spanList.length; i++)
      if (hasClassName(spanList[i], "menuItemArrow")) {
        spanList[i].style.fontFamily = "Webdings";
        spanList[i].firstChild.nodeValue = "4";
      }
  }

  // Find the width of a menu item.

  itemList = menu.getElementsByTagName("A");
  if (itemList.length > 0)
    itemWidth = itemList[0].offsetWidth;
  else
    return;

  // For items with arrows, add padding to item text to make the
  // arrows flush right.

  for (i = 0; i < itemList.length; i++) {
    spanList = itemList[i].getElementsByTagName("SPAN");
    textEl  = null;
    arrowEl = null;
    for (j = 0; j < spanList.length; j++) {
      if (hasClassName(spanList[j], "menuItemText"))
        textEl = spanList[j];
      if (hasClassName(spanList[j], "menuItemArrow"))
        arrowEl = spanList[j];
    }
    if (textEl != null && arrowEl != null)
      textEl.style.paddingRight = (itemWidth
        - (textEl.offsetWidth + arrowEl.offsetWidth)) + "px";
  }

  // Fix IE hover problem by setting an explicit width on first item of
  // the menu.

  if (browser.isIE) {
    w = itemList[0].offsetWidth;
    itemList[0].style.width = w + "px";
    dw = itemList[0].offsetWidth - w;
    w -= dw;
    itemList[0].style.width = w + "px";
  }

  // Mark menu as initialized.

  menu.isInitialized = true;
}

//----------------------------------------------------------------------------
// General utility functions.
//----------------------------------------------------------------------------

function getContainerWith(node, tagName, className) {

  // Starting with the given node, find the nearest containing element
  // with the specified tag name and style class.

  while (node != null) {
    if (node.tagName != null && node.tagName == tagName &&
        hasClassName(node, className))
      return node;
    node = node.parentNode;
  }

  return node;
}

function hasClassName(el, name) {

  var i, list;

  // Return true if the given element currently has the given class
  // name.

  list = el.className.split(" ");
  for (i = 0; i < list.length; i++)
    if (list[i] == name)
      return true;

  return false;
}

function removeClassName(el, name) {

  var i, curList, newList;

  if (el.className == null)
    return;

  // Remove the given class name from the element's className property.

  newList = new Array();
  curList = el.className.split(" ");
  for (i = 0; i < curList.length; i++)
    if (curList[i] != name)
      newList.push(curList[i]);
  el.className = newList.join(" ");
}

function getPageOffsetLeft(el) {

  var x;

  // Return the x coordinate of an element relative to the page.

  x = el.offsetLeft;
  if (el.offsetParent != null)
    x += getPageOffsetLeft(el.offsetParent);

  return x;
}

function getPageOffsetTop(el) {

  var y;

  // Return the x coordinate of an element relative to the page.

  y = el.offsetTop;
  if (el.offsetParent != null)
    y += getPageOffsetTop(el.offsetParent);
  return y;
}

//----dragging--


// Global object to hold drag information.

var dragObj = new Object();
dragObj.zIndex = 10;

function dragStart(event, id) {

  var el;
  var x, y;

  // If an element id was given, find it. Otherwise use the element being
  // clicked on.

  if (id)
    dragObj.elNode = document.getElementById(id);
  else {
    if (browser.isIE)
      dragObj.elNode = window.event.srcElement;
    if (browser.isNS)
      dragObj.elNode = event.target;

    // If this is a text node, use its parent element.

    if (dragObj.elNode.nodeType == 3)
      dragObj.elNode = dragObj.elNode.parentNode;
  }

  // Get cursor position with respect to the page.

  if (browser.isIE) {
    x = window.event.clientX + document.documentElement.scrollLeft
      + document.body.scrollLeft;
    y = window.event.clientY + document.documentElement.scrollTop
      + document.body.scrollTop;
  }
  if (browser.isNS) {
    x = event.clientX + window.scrollX;
    y = event.clientY + window.scrollY;
  }

  // Save starting positions of cursor and element.

  dragObj.cursorStartX = x;
  dragObj.cursorStartY = y;
  dragObj.elStartLeft  = parseInt(dragObj.elNode.style.left, 10);
  dragObj.elStartTop   = parseInt(dragObj.elNode.style.top,  10);

  if (isNaN(dragObj.elStartLeft)) dragObj.elStartLeft = 0;
  if (isNaN(dragObj.elStartTop))  dragObj.elStartTop  = 0;

  // Update element's z-index.

  dragObj.elNode.style.zIndex = ++dragObj.zIndex;

  // Capture mousemove and mouseup events on the page.

  if (browser.isIE) {
    document.attachEvent("onmousemove", dragGo);
    document.attachEvent("onmouseup",   dragStop);
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (browser.isNS) {
    document.addEventListener("mousemove", dragGo,   true);
    document.addEventListener("mouseup",   dragStop, true);
    event.preventDefault();
  }

POS['element'] = id;
}

var POS = new Array();

function dragGo(event) {

  var x, y;

  // Get cursor position with respect to the page.

  if (browser.isIE) {
    x = window.event.clientX + document.documentElement.scrollLeft
      + document.body.scrollLeft;
    y = window.event.clientY + document.documentElement.scrollTop
      + document.body.scrollTop;
  }
  if (browser.isNS) {
    x = event.clientX + window.scrollX;
    y = event.clientY + window.scrollY;
  }

  // Move drag element by the same amount the cursor has moved.

  dragObj.elNode.style.left = (dragObj.elStartLeft + x - dragObj.cursorStartX) + "px";
  dragObj.elNode.style.top  = (dragObj.elStartTop  + y - dragObj.cursorStartY) + "px";

  if (browser.isIE) {
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (browser.isNS)
    event.preventDefault();


}


function dragStop(event) {

  // Clear the drag element global.

  POS['left'] = dragObj.elNode.style.left;
  POS['top'] = dragObj.elNode.style.top;


  dragObj.elNode = null;

  // Stop capturing mousemove and mouseup events.

  if (browser.isIE) {
    document.detachEvent("onmousemove", dragGo);
    document.detachEvent("onmouseup",   dragStop);
  }
  if (browser.isNS) {
    document.removeEventListener("mousemove", dragGo,   true);
    document.removeEventListener("mouseup",   dragStop, true);
  }

  //document.getElementById('pipe').src = '{VAR:pipe_url}&left=' + POS['left'] + '&top=' + POS['top'] + '&element=' + POS['element'];

}

//--end dragging---




function pop(url,w,h,capt,icon)//oid
{
	var win = 1;
	while (WINDOW[win])
	{
		win += 1;
	}
	if (win > 9)
	{
		alert('liiga palju aknaid lahti!!');
		return false;
	}

	prop = 'width=' + w + ',height=' + h + ',scrollbars=yes,toolbar=no,menubar=no,resizable=yes';
	label = 'aken' + win ;
	el = 'w[' + win + ']';
ifrn = 'frei' + win;
ifr = "'" + ifrn + "'";

	icon = (icon ? icon : 100);

baritem = 'BARITEM' + el;

divv = '';
divv += '<div id="' + baritem + '" class="menu" onmouseover="menuMouseover(event)">';
divv += '<a class="menuItem" href="" onclick="WINDOW[' + win + '] = false;';
divv += " document.getElementById('" + el + "').innerHTML = '';";
divv += " document.getElementById('WINSPACE" + el + "').innerHTML = '';";
divv += " hide('" + baritem + "');";
divv += 'return false;" title="Sulge aken" >Sulge</a>';
divv += '<a class="menuItem" href="" onclick="WINDOW[' + win + '].window.focus();';
divv += " hide('" + baritem + "');";
divv += 'return false;" title="" >Tõsta esile</a>';
divv += '</div>';

document.getElementById('BARITEMSP' + el).innerHTML = divv;

windstatus = '<span name="ic' + ifrn + '" id="ic' + ifrn + '" class="baricon">' + icon + '</span>';
windstatus += '<a class="menuButton" class="barbutton" title="' + capt + '" style="border:solid 1px gray;margin:1px;" ';
//windstatus += 'onclick="WINDOW[' + win + '].window.focus(); ';
windstatus += 'onclick=" ';
windstatus += 'document.getElementById(' + ifr + ').style.visibility = ' + "'" + 'visible' + "'" + ';';
//windstatus +='winList[' + ifr + '].restore();';
windstatus += 'return false;" ';
windstatus += 'oncontextmenu="buttonClick(event, ' + "'" + baritem + "'" + ',40 + barheight); return false;" >';
windstatus += '<img style="position:relative;top:4px;" src="{VAR:icons_path}/class_' + icon + '.gif" height="16" />&nbsp;' + capt + '</a>';

document.getElementById(el).innerHTML = windstatus;

//WINDOW[win] = window.open(url,label,prop);
WINDOW[win] = 1;


fen ='';

//height:' + h + 'px;

fen +='<div name="' + ifrn + '" id="' + ifrn + '" class="window" style="left:' + (23 * win + 50) + 'px;top:' + (30 * win + 40) + 'px;width:' + w + 'px;z-index: 100;">';
fen +='<div class="titleBar" style="z-index: 100;">';
fen +='<span class="titleBarText" id="tb' + ifrn + '">';
fen +='<img style="position:relative;top:1px;" src="{VAR:icons_path}/class_' + icon + '.gif" height="14" />&nbsp;';
fen +='<span class="wincap" id="wc' + ifrn + '">' + capt + '</span></span>';
fen +='<img class="titleBarButtons" src="" /><img height="17" ';
fen +='src="http://axel.struktuur.ee/tmp/restore.gif" alt="" ';
fen +='onclick="hide(' + ifr + ');';
//fen +='winList[' + ifr + '].minimize();';
fen +='return false;" />';
//fen +='<img  height="17" src="http://axel.struktuur.ee/tmp/max.gif" alt="" ';
//fen +="onclick=" + '"' + "document.getElementById('ClA" + w + "').style.width='800px';";
//fen +='document.getElementById(' + ifr + ').style.height=' + "'" + '500px' + "'" + '; ';
//fen +='return false;" />';
fen +='<img  height="17" src="http://axel.struktuur.ee/tmp/close.gif" alt="Close" ';
fen +='onclick="';
fen += ' WINDOW[' + win + '] = false;';
fen += " document.getElementById('" + el + "').innerHTML = '';";
fen += " document.getElementById('WINSPACE" + el + "').innerHTML = '';";
fen += " document.getElementById('" + baritem + "').style.visibility = 'hidden';";
fen += " hide('" + baritem + "');";

fen +='return false;" /></div><div id="ClA' + w + '" class="clientArea" style="height:' + h + 'px;z-index: 100;"><iframe name="i' + ifrn + '" id="i' + ifrn + '" ';
fen +='width="100%" height="100%" style="" class="windowframe" src=""></iframe></div></div>';
document.getElementById('WINSPACE' + el).innerHTML = fen;

winList[ifrn] = new Window(document.getElementById(ifrn));
winList[ifrn].open();
document.getElementById('i' + ifrn).src = url;

return ifrn;
}



var programs = new Array();
<!-- SUB: RUNPROGRAMS -->
programs['{VAR:prg_file}'] = {VAR:class_id};
<!-- END SUB: RUNPROGRAMS -->


function hide(el)
{
document.getElementById(el).style.visibility = 'hidden';
}

function drun(txt)
{

	if (txt.indexOf('http://') >= 0)
	{
		pop(txt,{VAR:xy}, txt, '100');
	}
	else
	if (txt.indexOf('www.') >= 0)
	{
		pop('http://' + txt,{VAR:xy}, txt, '100');
	}
	else
	if (programs[txt])
	{
		url = '{VAR:new_link}'.replace('xxx', txt);
		pop(url,{VAR:xy}, txt, programs[txt]);
	}
	else
	{
		alert('programmi ei leitud');
	}

	return false;
}

//     var kasIE=false;


// this keeps mouse x,y updated in memory

var mouseX=0, mouseY=0;

     if(browser.isIE){
       document.onmousemove=hiireliikumineIE;
     } else {
       document.captureEvents(Event.MOUSEMOVE);
       document.onmousemove=hiireliikumine;
     }
     function hiireliikumineIE(){
      document.test.posxy.value = event.x+", "+event.y;
        mouseX=event.x;
        mouseY=event.y;
     }

     function hiireliikumine(syndmus){
      document.test.posxy.value = syndmus.pageX+", "+syndmus.pageY;
        mouseX=syndmus.pageX;
        mouseY=syndmus.pageY;
     }

     /*function kontrolliHiireAsukohta(){
       if(mouseX>140) return true;
       return false;
     }*/



function savedesktop(a)
{
  var allelements = '';
  var List = document.getElementsByTagName("DIV");
  for (var i = 0; i < List.length; i++)
  {
    if (List[i].className == "window")
    {
    id = List[i].id;
    allelements += '&W[' + id + '][z]=' + List[i].style.zIndex + '&W[' + id + '][left]=' + List[i].style.left + '&W[' + id + '][top]=' + List[i].style.top + "";
    }
  }

  List = document.getElementsByTagName("TABLE");
  for (var i = 0; i < List.length; i++)
  {
    if (List[i].className == "boxA")
    {
    id = List[i].id;
    allelements += '&I[' + id + '][z]=' + List[i].style.zIndex + '&I[' + id + '][left]=' + List[i].style.left + '&I[' + id + '][top]=' + List[i].style.top + "";
    }
  }

  List = document.getElementsByTagName("IFRAME");
  for (var i = 0; i < List.length; i++)
  {
    if (List[i].className == "windowframe")
    {
    id = List[i].id;
//alert(top.frames[List[i].id].document.title);

    allelements += '&WS[' + id + '][src]=' + encodeURIComponent(List[i].src) + "";
    }
  }

  List = document.getElementsByTagName("SPAN");
  for (var i = 0; i < List.length; i++)
  {
    if (List[i].className == "wincap")
    {
    id = List[i].id;
    allelements += '&WC[' + id + '][capt]=' + List[i].innerHTML + "";
    }
    else
    if (List[i].className == "baricon")
    {
    id = List[i].id;
    allelements += '&WI[' + id + '][icon]=' + List[i].innerHTML + "";
    }
  }
  //alert(allelements);

  top.frames['pipe'].document.getElementById('activity').innerHTML = 'Salvestamine!!';
  document.getElementById('pipe').src = '{VAR:pipe_url}' + allelements;

}

function reordericons(a)
{
  top.frames['pipe'].document.getElementById('activity').innerHTML = 'reordering..';
  document.getElementById('pipe').src = '{VAR:pipe_url}&reorder=1';
}


function findPosX(obj)
{
	var curleft = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft;
}

function findPosY(obj)
{
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
		curtop += obj.y;
	return curtop;
}

/*

function delete(val)
{
	strFeatures = "HEIGHT=190,WIDTH=320,top=300,left=400,scrollbars=no";
	window.open("", "värvid", strFeatures);
}

function todo(do, oid)
{
	if (do == 'delete')
	{
		document.getElementById(oid).style.visibility = 'hidden';
	}
}
*/


function rbackmenu(event)
{
buttonClick(event, 'bodycontext',0);
document.getElementById('bodycontext').style.position = 'absolute';
document.getElementById('bodycontext').style.left = mouseX + 'px';
document.getElementById('bodycontext').style.top = mouseY + 'px';
document.getElementById('bodycontext').style.visibility = 'visible';
}



/*//]]>*/
</script></head>

<body>
<div id="bodycontext" class="menu" onmouseover="menuMouseover(event)">
<a class="menuItem" href="" title="whee" onclick="pop('{VAR:add_folder}',{VAR:xy}, 'Lisa Kaust', '1');hide('bodycontext');return false;">
<img src="{VAR:icons_path}/class_1.gif" height="16" alt="" />
Lisa Kaust</a>
<a class="menuItem" href="" onclick="pop('{VAR:desktop_change}',{VAR:xy}, 'Desktopi seaded', '1');hide('bodycontext');return false;"
title="Desktopi seaded"><img src="{VAR:icons_path}/small_settings.gif" height="16" alt="" />
Seaded</a>
<a class="menuItem" href="" onclick="javascript:savedesktop(0);hide('bodycontext');return false;"
title="Salvesta desktop"><img src="{VAR:icons_path}/save.gif" height="16" alt="" />
Salvesta</a>
<a class="menuItem" onclick="reordericons(0);hide('bodycontext');return false;" href="{VAR:refresh_url}"
title="Sorteeri ikoonid"><img src="{VAR:icons_path}/prog_42.gif" height="16"/>
Sorteeri ikoonid</a>
<a class="menuItem" onclick="document.unload();hide('bodycontext');return false;" href="{VAR:refresh_url}"
title="Refresh"><img src="{VAR:images_path}/blue/awicons/refresh.gif" height="16" />
Värskenda</a>
<a class="menuItem" href="" href="{VAR:baseurl}/orb.{VAR:ext}?class=users&action=logout"
title="Logout" >Logi välja</a>
<a class="menuItem" href="" onclick="
var x=window.confirm('Oled sa ikka kindel, et tahad AW desktopi sulgeda?', 'jep', 'näi')
if (x){window.close();} else {hide('bodycontext');}return false;"
title="Sulge desktop"><img src="{VAR:icons_path}/class_1.gif" height="16" alt="" />
Sulge Desktop</a>
</div>

<!-- tausta menüü -->
<a id="backg" style="cursor:normal;position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:1;" style="text-decoration:none"
oncontextmenu="rbackmenu(event);return false;" onclick="hide('bodycontext');return false;"
 href=""> </a>


<iframe name="pipe" id="pipe" scrolling="no" width="100" height="50" src="{VAR:pipe_url}" style="border:0px;position:absolute;right:0px;z-index:0"></iframe>

<!--
<a href="" onclick="document.getElementById('sample1').style.visibility = 'visible';return false;">show</a>

<a href="" onclick="if (winList['sample1']) winList['sample1'].open();
document.getElementById('iframe1').src = 'http://www.neti.ee/';
return false;">Window 1</a>

<a href="" onclick="if (winList['sample2']) winList['sample2'].open();
document.getElementById('iframe2').src = 'http://www.neti.ee/';
return false;">Window 2</a>
-->

<span id="WINSPACEw[1]" name="WINSPACEw[1]"></span>
<span id="WINSPACEw[2]" name="WINSPACEw[2]"></span>
<span id="WINSPACEw[3]" name="WINSPACEw[3]"></span>
<span id="WINSPACEw[4]" name="WINSPACEw[4]"></span>
<span id="WINSPACEw[5]" name="WINSPACEw[5]"></span>
<span id="WINSPACEw[6]" name="WINSPACEw[6]"></span>
<span id="WINSPACEw[7]" name="WINSPACEw[7]"></span>
<span id="WINSPACEw[8]" name="WINSPACEw[8]"></span>
<span id="WINSPACEw[9]" name="WINSPACEw[9]"></span>



<div class="menuBar" style="width:100%;height:25;position:absolute;bottom:0px;left:0px;z-index:0"><img oncontextmenu="return false;"
src="{VAR:transgif}" width="100%" height="20" /></div>

<div class="menuBar" style="position:absolute;bottom:0px;left:0px;white-space:nowrap"><a class="menuButton"
href="" title="Start" onclick="return buttonClick(event, 'filemenu{VAR:datadir}',{VAR:filemenufix} + barheight);"
onmouseover="buttonMouseover(event, 'filemenu{VAR:datadir}',{VAR:filemenufix} + barheight);">AW</a>
{VAR:launchbar}

<a href="" onclick="return buttonClick(event, 'launche',20 + barheight);"
class="menuButton" title="" style="border:solid gray 1px"
oncontextmenu="return buttonClick(event, 'launche',20 + barheight);"
onmouseover="buttonMouseover(event, 'launche',20 + barheight);">{VAR:showlaunche}</a>

<span id="w[1]" name="w[1]"></span>
<span id="w[2]" name="w[2]"></span>
<span id="w[3]" name="w[3]"></span>
<span id="w[4]" name="w[4]"></span>
<span id="w[5]" name="w[5]"></span>
<span id="w[6]" name="w[6]"></span>
<span id="w[7]" name="w[7]"></span>
<span id="w[8]" name="w[8]"></span>
<span id="w[9]" name="w[9]"></span>
</div>


<!--<img style="position:relative;top:4px;bottom:4px;z-index:0" src="{VAR:aw_icon}" height="16" border="0"/>-->
<!--
<div class="menuBar" style="position:absolute;bottom:0px;left:100px;height:20px;white-space:nowrap">
<span id="w[1]" name="w[1]"></span>
<span id="w[2]" name="w[2]"></span>
<span id="w[3]" name="w[3]"></span>
<span id="w[4]" name="w[4]"></span>
<span id="w[5]" name="w[5]"></span>
<span id="w[6]" name="w[6]"></span>
<span id="w[7]" name="w[7]"></span>
<span id="w[8]" name="w[8]"></span>
<span id="w[9]" name="w[9]"></span>
</div>-->

<span id="BARITEMSPw[1]" name="BARITEMSPw[1]"></span>
<span id="BARITEMSPw[2]" name="BARITEMSPw[2]"></span>
<span id="BARITEMSPw[3]" name="BARITEMSPw[3]"></span>
<span id="BARITEMSPw[4]" name="BARITEMSPw[4]"></span>
<span id="BARITEMSPw[5]" name="BARITEMSPw[5]"></span>
<span id="BARITEMSPw[6]" name="BARITEMSPw[6]"></span>
<span id="BARITEMSPw[7]" name="BARITEMSPw[7]"></span>
<span id="BARITEMSPw[8]" name="BARITEMSPw[8]"></span>
<span id="BARITEMSPw[9]" name="BARITEMSPw[9]"></span>

<!-- keelevaliku nupp -->

<div class="menuBar" style="z-index:1100;position:absolute;bottom:0px;right:0px;white-space:nowrap">
<img src="{VAR:transgif}"  width="1" height="16" />
<a class="menuButton" style="background-color:#0000b0;color:#eeeeee;border-left:0px"
href="" onclick="return buttonClick(event, 'filemenu_lang',{VAR:langmenufix} + barheight);"
oncontextmenu="return false;"
onmouseover="buttonMouseover(event, 'filemenu_lang',{VAR:langmenufix} + barheight);" title="{VAR:active_lang}">{VAR:active_acceptlang}</a>


{VAR:calendar}

<!-- SUB: CLOCK -->
<a class="menuButton"
oncontextmenu="return false;"
onclick="
kal = document.getElementById('minikal');
if (kal.style.visibility == 'hidden')
kal.style.visibility = 'visible';
else
hide('minikal');
return false;"
title="{VAR:date}"><span name="clock" style="font-weight: normal;" id="clock"></span></a>
<!-- END SUB: CLOCK -->

<!-- SUB: NOCALENDER -->
<a class="menuButton" href="" title="vali kalendri objekt"
onclick="javascript:pop('{VAR:desktop_change}',{VAR:xy},'Muuda desktopi','100');return false;">Vali kalendri objekt</a>
<!-- END SUB: NOCALENDER -->

</div>

<div id="minikal" style="position:absolute;bottom:27px;right:0px;visibility:hidden;z-index:3002;">
<table border="1" style="background-color:#eeeeee">
<tr><td>
{VAR:minikal}
</td><tr>
</table>
</div>

<!--onmouseover="document.getElementById('dra{VAR:oid}').style.backgroundColor='red'"

onmouseout="document.getElementById('dra{VAR:oid}').style.backgroundColor='transparent'"

-->

<!-- tausta ikoonid -->
<!-- SUB: DESKTOP_ITEM -->
<table class="boxA" id="dra{VAR:oid}"

onmouseover="document.getElementById('dra{VAR:oid}').style.border='1px dotted #888888'"
onmouseout="document.getElementById('dra{VAR:oid}').style.border='1px solid transparent'"
oncontextmenu="return false;"
style="width:80px;{VAR:POS}"><tr><td align="center">
<a oncontextmenu="return buttonClick(event, 'context{VAR:oid}',-2);">
<img
class="menuButton"
href="" title="{VAR:title}"
ondblclick="javascript:pop('{VAR:default_url}',{VAR:xy},'{VAR:name}','{VAR:clid}');return false;"
onmouseover="buttonMouseover(event, 'context{VAR:oid}',-2);"
class="boxB"
onmousedown="dragStart(event,'dra{VAR:oid}');"
onmouseover="this.style.border='0px solid blue'"
onmouseout="this.style.border='0px'"
src="{VAR:icons_path}/class_{VAR:clid}.gif"
height="32" width="32" border="0"
style="z-index:1;" /></a>
</td></tr>
<tr><td align="center">
<span style="z-index:1;background-color:#1166C0;color:#ffffff;font-weight:bold;spacing:2px;">{VAR:icon_caption}</span>
</td></tr>
</table>
<div id="context{VAR:oid}" class="menu" onmouseover="menuMouseover(event)">
<!-- SUB: ICON_CONTEXT_ITEM -->
<a class="menuItem" href="" title="{VAR:title}"
onclick="javascript:pop('{VAR:url}',{VAR:wxy},'{VAR:name}','{VAR:class_id}');
hide('context{VAR:oid}');
return false;"><img src="{VAR:icons_path}/{VAR:iconfile}" height="16"/>
{VAR:caption}</a>
<!-- END SUB: ICON_CONTEXT_ITEM -->


</div>

<!-- END SUB: DESKTOP_ITEM -->



{VAR:main_menu}

<!-- kiirvalikuriba nupud -->
<!-- SUB: LAUNCHER -->
<a href="" onclick="javascript:pop('{VAR:url}',{VAR:xy},'{VAR:name}', '{VAR:clid}');return false;"
class="menuButton" title="{VAR:title}" style="z-index:80;"
oncontextmenu="return buttonClick(event, 'launche{VAR:oid}',80 + barheight + 5);"
onmouseover="buttonMouseover(event, 'launche{VAR:oid}',80 + barheight + 5);"><img style="position:relative;top:4px;z-index:81;height:16px"
src="{VAR:icons_path}/class_{VAR:clid}.gif" ALT="" />
</a>
<!-- END SUB: LAUNCHER -->


<!-- SUB: LAUNCHERCONTEXTS -->
<div id="launche{VAR:oid}" class="menu" onmouseover="menuMouseover(event)">
<a class="menuItem" href="" title="{VAR:title}" onclick="javascript:pop('{VAR:url}',{VAR:xy},'{VAR:title}','{VAR:oid}');hide('launche{VAR:oid}');return false;"><b>{VAR:title}</b></a>
<a class="menuItem" href="" title="Muuda Programm" onclick="javascript:pop('{VAR:change_object_type}',{VAR:xy},'Muuda programm','111');hide('launche{VAR:oid}');return false;">Muuda</a>
<a class="menuItem" href="" title="Kustuta Programm" 
onclick="javascript:pop('{VAR:delete_object_type}',{VAR:xy},'Kustuta programm','111');hide('launche{VAR:oid}');return false;"><img src="{VAR:icons_path}/small_delete.gif" height="16"/>
Kustuta</a>
<a class="menuItem" href="" title="Lisa Programm" onclick="javascript:pop('{VAR:add_object_type}',{VAR:xy},'Lisa programm','111');hide('launche{VAR:oid}');return false;">Lisa programm</a>
</div>



<!-- END SUB: LAUNCHERCONTEXTS -->

<div id="launche" class="menu" onmouseover="menuMouseover(event)">
<a class="menuItem" href="" title="Lisa Programm" onclick="javascript:pop('{VAR:add_object_type}',{VAR:xy},'Lisa programm','111');return false;">Lisa programm</a>
</div>


<!-- SUB: MENU_ITEM_SUB -->
<a class="menuItem" href=""
	onclick="return false;"
	onmouseover="menuItemMouseover(event, '{VAR:sub_menu_id}');"><span
	class="menuItemText"><IMG SRC="{VAR:icons_path}/class_{VAR:clid}.gif"
	HEIGHT="16" BORDER=0 ALT="" /> {VAR:caption}</span><span
	class="menuItemArrow">&gt;</span></a>
<!-- END SUB: MENU_ITEM_SUB -->

<!-- SUB: MENU_ITEM -->
<a class="menuItem" title="{VAR:title}"
href=""
onclick="javascript:pop('{VAR:url}',{VAR:xy},'{VAR:caption}', '{VAR:clid}');return false;"><IMG
SRC="{VAR:icons_path}/class_{VAR:clid}.gif"
HEIGHT="16" BORDER=0 ALT="" /> {VAR:caption}</a>
<!-- END SUB: MENU_ITEM -->

<!-- SUB: RUN_MENU_ITEM -->
<a class="menuItem" title="Käivita programm"
href=""
onclick="javascript:valu = prompt('Run...', ''); drun(valu); return false;"><IMG
SRC="{VAR:icons_path}/class_111.gif"
HEIGHT="16" BORDER=0 ALT="" />Run...</a>
<!-- END SUB: RUN_MENU_ITEM -->


<!--javascript:pop('{VAR:url}',{VAR:xy},'{VAR:caption}');<input type="radio" name="lang" style="height:8px;width:10"/>-->
<!-- SUB: MENU_ITEM_lang -->
<a class="menuItem" style="margin-left:0px;" title="{VAR:title}" href="{VAR:url}">{VAR:caption}</a>
<!-- END SUB: MENU_ITEM_lang -->

<!-- SUB: MENU_SEPARATOR -->
<div class="menuItemSep"></div>
<!-- END SUB: MENU_SEPARATOR -->

<!-- SUB: MENU -->
<div id="{VAR:name}" class="menu" onmouseover="menuMouseover(event)">
{VAR:content}
</div>
<!-- END SUB: MENU -->

<form name="test">
<input name="posxy" value="" type="text" style="visibility:hidden;">
</form>


<script type="text/javascript">
//<![CDATA[

var mvw = new Array();

//
//]]></script>


<img id="ow{VAR:cnt}" name="ow{VAR:cnt}" height="300" width="300" class="menuItem" src="{VAR:transgif}" style="position:absolute;z-index:0"
onload="javascript:
<!-- SUB: OPENSAVEDWINDOWS -->
mvw[{VAR:cnt}] = pop('{VAR:url}',{VAR:xy}, '{VAR:caption}', '{VAR:icon}');
<!-- END SUB: OPENSAVEDWINDOWS -->
return false;" />

<script type="text/javascript">

//<![CDATA[
if ({VAR:showclock})
{
	clock();
}
function moveit()
{
<!-- SUB: OPENSAVEDWINDOWS2 -->
document.getElementById(mvw[{VAR:cnt}]).style.top = '{VAR:top}';
document.getElementById(mvw[{VAR:cnt}]).style.left = '{VAR:left}';
<!-- END SUB: OPENSAVEDWINDOWS2 -->
}

setTimeout('moveit()', 1000);


//document.getElementById(winname).style.top = '{VAR:top}';
//document.getElementById(winname).style.left = '{VAR:left}';

//function donothing(){alert('ok');return true;}
//document.getElementById(winname).style.zIndex = {VAR:z};

//]]></script>


</body>
</html>
