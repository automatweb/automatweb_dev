<!-- index --><?xml-stylesheet href="#internalStyle" type="text/css"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="et">
<head>


<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<title>{VAR:name}</title>

<style type="text/css" id="internalStyle">
/*<![CDATA[*/

.window {
  background-color: #c0c0c0;
  border-color: #f0f0f0 #606060 #404040 #d0d0d0;
  border-style: solid;
  border-width: 3px;
  margin: 0px;
  padding: 0px;
  position: absolute;
  text-align: left;
  visibility: hidden;
}

.titleBar {
  background-color: #008080;
  cursor: default;
  color: #ffffff;
  font-family: "MS Sans Serif", "Arial", "Helvetica", sans-serif;
  font-size: 8pt;
  font-weight: bold;
  margin: 0px;
  padding: 2px 2px 2px .5em;
  text-align: right;
  white-space: nowrap;
}

.titleBarText {
  float: left;
  overflow: hidden;
  text-align: left;
}

.titleBarButtons {
  border-style: none;
  border-width: 0px;
  vertical-align: middle;
  width: 50px;
  height: 14px;
}

.clientArea {
  background-color: #ffffff;
  border-color: #404040 #e0e0e0 #f0f0f0 #505050;
  border-style: solid;
  border-width: 2px;
  color: #000000;
  font-family: "Arial", "Helvetica", sans-serif;
  font-size: 10pt;
  margin: 0px 0px 0px 0px;
  overflow: auto;
  padding: .0em;
}

body {
  font-family: "Arial", "Helvetica", sans-serif;
  font-size: 10pt;
  margin: 0px 0px 0px 0px;

	color: #{VAR:backgroundtextcolor};
	background-color: #{VAR:backgroundcolor};
	background-image: url({VAR:backgroundimage});
	background-attachment:fixed;
	{VAR:bgstyle}
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
  padding: 2px 3px 2px 3px;
  position: relative;
  text-decoration: none;
  font-weight: bold;
  top: 0px;
  z-index: 100;

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
  left: 1px;
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
  z-index: 101;
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
height:29px;
}

div.menu div.menuItemSep {
  border-top: 1px solid #909090;
  border-bottom: 1px solid #f0f0f0;
  margin: 4px 2px;
}


/*]]>*/
</style>
<script type="text/javascript">
//<![CDATA[

//------------clock------------------------------------

var currenttime;

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


setTimeout("clock()", 60000);
}


//------------windows------------------------------------

//*****************************************************************************
// Do not remove this notice.
//
// Copyright 2001 by Mike Hall.
// See http://www.brainjar.com for terms of use.
//*****************************************************************************

// Determine browser and version.

var menufix = new Array;

menufix['filemenu{VAR:datadir}'] = {VAR:filemenufix}	;	//1 - filemenu
menufix['filemenu_lang'] = {VAR:langmenufix};			//2 - language
//				3 -

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

  mapName = this.titleBarButtons.useMap.substr(1);
  mapList = document.getElementsByTagName("MAP");
  for (i = 0; i < mapList.length; i++)
    if (mapList[i].name == mapName)
      this.titleBarMap = mapList[i];

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

  for (i = 0; i < this.titleBarMap.childNodes.length; i++)
    if (this.titleBarMap.childNodes[i].tagName == "AREA")
      this.titleBarMap.childNodes[i].parentWindow = this;

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
  if (this.inactiveButtonsImage)
    this.titleBarButtons.src = this.activeButtonsImage;
  this.frame.style.zIndex = ++winCtrl.maxzIndex;
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
    target = window.event.srcElement.tagName;
  if (browser.isNS)
    target = event.target.tagName;

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
  winCtrl.resizeCornerSize                 =  16;
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

function buttonClick(event, menuId) {

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
    depressButton(button,menuId);
    activeButton = button;
  }
  else
    activeButton = null;

  return false;
}

function buttonMouseover(event, menuId) {

  var button;

  // Find the target button element.

  if (browser.isIE)
    button = window.event.srcElement;
  else
    button = event.currentTarget;

  // If any other button menu is active, make this one active instead.

  if (activeButton != null && activeButton != button)
    buttonClick(event, menuId);
}

function depressButton(button,celem) {

  var x, y;

  // Update the button's style class to make it look like it's
  // depressed.

  button.className += " menuButtonActive";

  // Position the associated drop down menu under the button and
  // show it.

  x = getPageOffsetLeft(button);
  y = getPageOffsetTop(button) + button.offsetHeight - (menufix[celem] ? (menufix[celem] + 22) : 0);//<<<<

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



var WINDOW = new Array;
var win = 0;

function pop(url,w,h,capt)
{
win = win + 1;

prop = 'width=' + w + ',height=' + h + ',scrollbars=yes,toolbar=no,menubar=no,resizable=yes';
label = 'aken' + win ;

el = 'w[' + win + ']';

//parent.document.focus();

document.getElementById(el).innerHTML = '<a class="menuButton" onclick="WINDOW[' + win + '].window.close();return false;"><img src="{VAR:aw_icon}" height="16" />' + capt + '</a>';

//<a class="menuButton" onclick="WINDOW[1].window.close();return false;"><img src="{VAR:aw_icon}" height="16" />blaa</a>

WINDOW[win] = window.open(url,label,prop);



//return false;
}

//]]></script></head>

<body>


<form name="page">

<!--<a href="" onclick="if (winList['sample3']) winList['sample3'].open(); return false;">Window 3</a>-->

<div id="sample3" class="window" style="border-color: rgb(255, 224, 176) rgb(160, 112, 64) rgb(128, 80, 32) rgb(224, 176, 128); top: 50px; background-color: rgb(240, 192, 144); z-index: 3; width: 300px; left: 350px;">
  <div class="titleBar" style="background-color: rgb(192, 128, 96); color: rgb(255, 255, 204);">
    <span class="titleBarText" style="width: 242px;">Spirits of the Dead</span>
    <img class="titleBarButtons" alt="" src="http://axel.struktuur.ee/br/buttons.gif" longdesc="http://axel.struktuur.ee/br/altbuttons.gif" usemap="#sampleMap3">
    <map id="sampleMap3" name="sampleMap3"><area shape="rect" coords="0,0,15,13" href="" alt="" title="Minimize" onclick="this.parentWindow.minimize();return false;"><area shape="rect" coords="16,0,31,13" href="" alt="" title="Restore" onclick="this.parentWindow.restore();return false;"><area shape="rect" coords="34,0,49,13" href="" alt="" title="Close" onclick="this.parentWindow.close();return false;"></map>
  </div>
  <div class="clientArea" style="border-color: rgb(128, 80, 32) rgb(224, 176, 128) rgb(255, 224, 176) rgb(160, 112, 64); height: 150px; background-color: rgb(255, 255, 204); color: rgb(128, 96, 64);">
<iframe src="http://www.neti.ee/" width="90%" height="90%" style="z-index:3">
</iframe>
  </div>
</div>

<div class="menuBar" style="width:100%;height:25;position:absolute;bottom:0px;left:0px;z-index:9"><img
src="{VAR:transgif}" width="100%" height="20" /></div>

<div class="menuBar" style="position:absolute;bottom:0px;left:0px;white-space:nowrap"><a class="menuButton"
href="" title="Start" onclick="return buttonClick(event, 'filemenu{VAR:datadir}');"
onmouseover="buttonMouseover(event, 'filemenu{VAR:datadir}');">AW</a>
{VAR:launchbar}
</div>
<!--<img style="position:relative;top:4px;bottom:4px;z-index:0" src="{VAR:aw_icon}" height="16" border="0"/>-->


<div class="menuBar" style="position:absolute;bottom:0px;left:100px;height:20px;white-space:nowrap">
<span id="w[1]" name="w[1]"></span><span id="w[2]" name="w[2]"></span><span id="w[3]" name="w[3]"></span><span id="w[4]" name="w[4]"></span>
</div>



<div class="menuBar" style="position:absolute;bottom:0px;right:0px;white-space:nowrap">
<img src="{VAR:transgif}"  width="1" height="16" />
<a class="menuButton" style="background-color:#0000b0;color:#eeeeee;border-left:0px"
href="" onclick="return buttonClick(event, 'filemenu_lang');" onmouseover="buttonMouseover(event, 'filemenu_lang');" title="{VAR:active_lang}">{VAR:active_acceptlang}</a>

<!-- SUB: CALENDAR_BUTTON -->
<!--pop('{VAR:calendar_url}',200,400,'Kalender')-->
<a class="menuButton" title="{VAR:date}" href="" onclick="
kal = document.getElementById('minikal');
if (kal.style.visibility == 'hidden')
kal.style.visibility = 'visible';
else
kal.style.visibility = 'hidden';
return false;">CAL</a>
<!-- END SUB: CALENDAR_BUTTON -->

{VAR:calendar}
<!-- SUB: CLOCK -->
<a class="menuButton" title="{VAR:date}"><span name="clock" style="font-weight: normal;" id="clock"></span></a>
<!-- END SUB: CLOCK -->
</div>

<div id="minikal" style="position:absolute;bottom:27px;right:0px;visibility:hidden">
<table border="1" style="background-color:#eeeeee">
<tr><td>
{VAR:minikal}
</td><tr>
</table>
</div>

<div id="minikal" style="position:absolute;top:0px;right:0px;">
<a href="{VAR:REQUEST_URI}"><img src="{VAR:refresh_icon}" border="0"/></a>
</div>

{VAR:desktop_items}
<!-- SUB: DESKTOP_ITEM -->
<table style="float:left;z-index:10;"><tr><td align="center">
<a class="menuButton"
href="" title="{VAR:title}" onclick="javascript:pop('{VAR:url}',600,500,'{VAR:name}');return false;" oncontextmenu="return buttonClick(event, 'context');"
onmouseover="buttonMouseover(event, 'context');"><img
onmouseover="this.style.border='1px solid blue'" onmouseout="this.style.border='0px'" src="{VAR:icon}" height="32" width="32" border="0" /></a>
</td></tr>
<tr><td align="center">
<span style="z-index:10;background-color:#1166C0;color:#ffffff;font-weight:bold;spacing:2px;">{VAR:icon_caption}</span>
</td></tr>
</table>
<!-- END SUB: DESKTOP_ITEM -->



<div id="context" class="menu" onmouseover="menuMouseover(event)">
<a class="menuItem" title="whee" ><b>Muuda</b></a>
<a class="menuItem" title="whee" >Kustuta</a>
<a class="menuItem" title="whee" >Ava</a>
</div>


<a style="position:absolute;right:0px;top:0px" href="" oncontextmenu="buttonClick(event, 'bodycontext');
document.getElementById('bodycontext').style.left = '0px';
document.getElementById('bodycontext').style.top = '0px';
return false;">Properties</a>

<div id="bodycontext" class="menu" onmouseover="menuMouseover(event)">
<a class="menuItem" title="whee" >Lisa Kaust</a>
<a class="menuItem" title="whee" >Muuda Desktopi</a>
<a class="menuItem" title="whee" >Refresh</a>
<a class="menuItem" title="whee" >Log of</a>
<a class="menuItem" title="whee" >Sulge Desktop</a>
</div>




{VAR:main_menu}

<!-- SUB: MENU_ITEM_SUB -->
<a class="menuItem" href=""
	onclick="return false;"
	onmouseover="menuItemMouseover(event, '{VAR:sub_menu_id}');"><span class="menuItemText">{VAR:icon} {VAR:caption}</span><span class="menuItemArrow">&gt;</span></a>
<!-- END SUB: MENU_ITEM_SUB -->

<!-- SUB: MENU_ITEM -->
<a class="menuItem" title="{VAR:title}" href="javascript:pop('{VAR:url}',600,500,'{VAR:caption}');" {VAR:more}>{VAR:icon} {VAR:caption}</a>
<!-- END SUB: MENU_ITEM -->

<!--javascript:pop('{VAR:url}',600,500,'{VAR:caption}');<input type="radio" name="lang" style="height:8px;width:10"/>-->
<!-- SUB: MENU_ITEM_lang -->
<a class="menuItem" style="margin-left:0px;" title="{VAR:title}" href="{VAR:url}" {VAR:more}>{VAR:caption}</a>
<!-- END SUB: MENU_ITEM_lang -->

<!-- SUB: MENU_SEPARATOR -->
<div class="menuItemSep"></div>
<!-- END SUB: MENU_SEPARATOR -->

<!-- SUB: LAUNCHER -->
<a href="{VAR:url}" target="_blank" class="menuButton" title="{VAR:name}"  {VAR:more}><img style="position:relative;top:4px;" src="{VAR:icon}" alt="{VAR:name}" border="0" height="16" /></a>
<!-- END SUB: LAUNCHER -->

<!-- SUB: MENU -->
<div id="{VAR:name}" class="menu" onmouseover="menuMouseover(event)">
{VAR:content}
</div>
<!-- END SUB: MENU -->

</form>

<script type="text/javascript">
//<![CDATA[
if ({VAR:showclock})
{
	clock();
}
//]]></script>
</body></html>
