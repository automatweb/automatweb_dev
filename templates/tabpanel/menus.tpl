<style type="text/css">
.awtab {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #1664B9;
background-color: #CDD5D9;
}
.awtab a {color: #1664B9; text-decoration:none;}
.awtab a:hover {color: #000000; text-decoration:none;}

.awtabdis {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #686868;
background-color: #CDD5D9;
}

.awtabsel {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #FFFFFF;
background-color: #478EB6;
}
.awtabsel a {color: #FFFFFF; text-decoration:none;}
.awtabsel a:hover {color: #000000; text-decoration:none;}

.awtabseltext {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #FFFFFF;
background-color: #478EB6;
}
.awtabseltext a {color: #FFFFFF; text-decoration:none;}
.awtabseltext a:hover {color: #000000; text-decoration:none;}

.awtablecellbackdark {
font-family: verdana, sans-serif;
font-size: 10px;
background-color: #478EB6;
}

.awtablecellbacklight {
background-color: #DAE8F0;
}

.awtableobjectid {
font-family: verdana, sans-serif;
font-size: 10px;
text-align: left;
color: #DBE8EE;
background-color: #478EB6;
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
  padding: 4px 0px 2px 0px;
  text-align: left;
height:17px;
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



</style>
<script src="/automatweb/js/popup_menu.js" type="text/javascript">
</script>
{VAR:toolbar}




<div class="menuBar" style="white-space:nowrap">
<!-- SUB: menubutton -->
<a href=""
class="menuButton" title="{VAR:title}"
onclick="return buttonClick(event, '{VAR:id}');">
<u>{VAR:caption}</u>
</a>
<!-- END SUB: menubutton -->

<!-- SUB: selected_menubutton -->
<a href=""
class="menuButton" title="{VAR:title}"
style="font-weight:bold;"
onclick="return buttonClick(event, '{VAR:id}');">
<u>{VAR:caption}</u>
</a>
<!-- END SUB: selected_menubutton -->

<!-- SUB: disabled_menubutton -->
<a href=""
class="menuButton" title="{VAR:title}"
style="color:gray;"
onclick="false">
{VAR:caption}
</a>
<!-- END SUB: disabled_menubutton -->

<!-- SUB: menubutton_nosub -->
<a href="{VAR:link}"
class="menuButton" title="{VAR:title}"
onmouseover="buttonMouseover(event);">
{VAR:caption}
</a>
<!-- END SUB: menubutton_nosub -->

<!-- SUB: selected_menubutton_nosub -->
<a href="{VAR:link}"
class="menuButton" title="{VAR:title}"
style="font-weight:bold;"
onmouseover="buttonMouseover(event);">
{VAR:caption}
</a>
<!-- END SUB: selected_menubutton_nosub -->

<!-- SUB: disabled_menubutton_nosub -->
<a href=""
class="menuButton" title="{VAR:title}"
style="color:gray;"
onclick="return false;"
onmouseover="buttonMouseover(event);">
{VAR:caption}
</a>
<!-- END SUB: disabled_menubutton_nosub -->

</div>


<!-- SUB: menu -->
<div id="{VAR:parent}" class="menu" style="visibility:visile;" onmouseover="menuMouseover(event)">
		<!-- SUB: menuitem -->
			<a class="menuItem" href="{VAR:link}">{VAR:caption}</a>
		<!-- END SUB: menuitem -->
		<!-- SUB: selected_menuitem -->
			<a class="menuItem" style="font-weight:bold;" href="{VAR:link}">{VAR:caption}</a>
		<!-- END SUB: selected_menuitem -->
		<!-- SUB: disabled_menuitem -->
			<a class="menuItem" style="color:gray;" href="" onclick="return false;">{VAR:caption}</a>
		<!-- END SUB: disabled_menuitem -->
</div>
<!-- END SUB: menu -->

{VAR:mainmenu}


<!-- content start -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td class="awtableobjectid"><div style="width:6px;height:5px" /></td></tr></table>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td rowspan="2" align="left" valign="bottom" width="6" class="awtablecellbackdark"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/awtable_nurk.gif" WIDTH="6" HEIGHT="5" BORDER=0 ALT=""></td>
<td align="left" valign="top" width="99%" bgcolor="#FFFFFF">
{VAR:content}
</td>
</tr>
<tr>
<td class="awtablecellbacklight"><div style="width:85px;height:5px" /></td>
</tr>
</table>
{VAR:toolbar2}
<br>

<!-- content ends  -->
