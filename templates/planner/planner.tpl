<style>
.caltableborderhele {
background: #8AAABE;
}


.caltablebordertume {
background: #001D45;
}


/* kalender paremal */


.calmonthback {
background: #C6C7A3;
}

.caldaysback {
background: #F8F9D5;
}

.caldayname {
	font-family: Arial,Helvetica,sans-serif;
	font-size: 10px;
	text-decoration: none;
	text-align: center;
	vertical-align: middle;
	height: 17px;
	color: #000000;
	background: #FFFFFF;
}

.caldayname a {color: #000000; text-decoration: none;}
.caldayname a:hover {color: #000000; text-decoration: none;}
.caldayname a:visited {color: #000000; text-decoration: none;}
.caldayname a:active {color: #000000; text-decoration: none;}


.calday {
	font-family: Arial,Helvetica,sans-serif;
	font-size: 10px;
	text-decoration: none;
	text-align: center;
	vertical-align: middle;
	height: 17px;
	color: #000000;
}

.calday a {color: #000000; text-decoration: none;}
.calday a:hover {color: #450500; text-decoration: underline;}
.calday a:visited {color: #000000; text-decoration: none;}
.calday a:active {color: #450500; text-decoration: none;}


.caltoday {
	font-family: Arial,Helvetica,sans-serif;
	font-size: 10px;
	font-weight: bold;	
	text-decoration: none;
	text-align: center;
	vertical-align: middle;
	border: 1px;
	height: 17px;
	color: #000000;
	background-image: URL('/automatweb/images/blue/cal_paev_raam.gif');
}

.caltoday a {color: #000000; text-decoration: none;}
.caltoday a:hover {color: #450500; text-decoration: none;}
.caltoday a:visited {color: #6D7477; text-decoration: none;}
.caltoday a:active {color: #000000; text-decoration: none;}


.caltodayevent {
	font-family: Arial,Helvetica,sans-serif;
	font-size: 10px;
	font-weight: bold;	
	text-decoration: none;
	text-align: center;
	vertical-align: middle;
	border: 1px;
	height: 17px;
	color: #000000;
	background: #FFFFFF;
	background-image: URL('/automatweb/images/blue/cal_paev_raam.gif');
}

.caltodayevent a {color: #000000; text-decoration: none;}
.caltodayevent a:hover {color: #450500; text-decoration: none;}
.caltodayevent a:visited {color: #6D7477; text-decoration: none;}
.caltodayevent a:active {color: #000000; text-decoration: none;}


.caldayevent {
	font-family: Arial,Helvetica,sans-serif;
	font-size: 10px;
	font-weight: bold;	
	text-decoration: none;
	text-align: center;
	vertical-align: middle;
	height: 17px;
	color: #000000;
	background: #FFFFFF;
}

.caldayevent a {color: #000000; text-decoration: none;}
.caldayevent a:hover {color: #450500; text-decoration: underline;}
.caldayevent a:visited {color: #000000; text-decoration: none;}
.caldayevent a:active {color: #450500; text-decoration: none;}



/* end kalender paremal */


/* kalender nädala vaate head riba */

.caldayheadday {
	font-family: Arial,Helvetica,sans-serif;
	font-size: 20px;
	font-weight: normal;	
	text-decoration: none;
	text-align: left;
	vertical-align: middle;
	color: #000000;
	background: BCDCF0;
}

.caldayheadday a {color: #FFFFFF; text-decoration: none;}
.caldayheadday a:hover {color: #FFFFFF; text-decoration: underline;}
.caldayheadday a:visited {color: #FFFFFF; text-decoration: none;}
.caldayheadday a:active {color: #FFFFFF; text-decoration: none;}

.caldayheaddate {
	font-family: Arial,Helvetica,sans-serif;
	font-size: 11px;
	font-weight: bold;	
	text-decoration: none;
	text-align: right;
	vertical-align: middle;
	color: #000000;
	background: BCDCF0;
}

.caldayheaddate a {color: #002F8E; text-decoration: none;}
.caldayheaddate a:hover {color: #002F8E; text-decoration: underline;}
.caldayheaddate a:visited {color: #002F8E; text-decoration: none;}
.caldayheaddate a:active {color: #002F8E; text-decoration: none;}

/* END kalender nädala vaate head riba */






.caleventtext {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	line-height: 12px;
	color: #000000;
	text-decoration: none;
}
.caleventtext a {color: #000000; text-decoration: none;}
.caleventtext a:hover {color: #450500; text-decoration: underline;}
.caleventtext a:visited {color: #450500; text-decoration: underline;}
.caleventtext a:active {color: #450500; text-decoration: underline;}

.caleventtextsel {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	line-height: 12px;
	color: #FFFFFF;
	text-decoration: none;
	background: #A69080;
}
.caleventtextsel a {color: #FFFFFF; text-decoration: none;}
.caleventtextsel a:hover {color: #FFFFFF; text-decoration: underline;}
.caleventtextsel a:visited {color: #FFFFFF; text-decoration: underline;}
.caleventtextsel a:active {color: #FFFFFF; text-decoration: underline;}
</style>

{VAR:menubar}

<IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>

<table width="100%" cellpadding="3" cellspacing="0">
<tr class="aste02">
<td class="celltext" align="left">
<a href="{VAR:prev}"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_left.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT="&lt;&lt;"></a>  {VAR:caption}   <a href="{VAR:next}"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_right.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT="&gt;&gt;"></a></td>
<td align="right">

<table border="0" cellpadding="0" cellspacing="0">
<form method="POST" action="reforb.{VAR:ext}">
<tr><td>
<select name="month" class="formselect2">{VAR:mlist}</select>
<select name="year" class="formselect2">{VAR:ylist}</select>
<input type="submit" value="{VAR:LC_PLANNER_SHOW}" class="formbutton">
{VAR:mreforb}
</td></tr>
</form>
</table>

</td>
</tr>
</table>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td class="caltablebordertume"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
</td></tr></table>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td width="99%" valign="top">

{VAR:content}

</td>
<td width="1" class="caltablebordertume"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
<td valign="top" width="225">

<table width="225" border="0" cellpadding="0" cellspacing="0">	


<tr>
<td width="112" valign="top" class="caldaysback">
{VAR:navi1}
</td>
<td width="1" class="caltableborderhele"><IMG SRC="images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
<td width="112" valign="top" class="caldaysback">

<!-- begin järgmise kuu kalender -->
{VAR:navi2}
<!-- end järgmise kuu kalender -->


</td></tr>
</table>




<!-- other information-->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td class="caltablebordertume" colspan="3"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
<tr class="calmonthback">
<td width="25" align="center"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/cal_notes.gif" WIDTH="15" HEIGHT="15" BORDER=0 ALT=""></td>
<td width="180" height="23" class="celltext" align="left">Other notes / information</td>
<td width="20" align="center" valign="top"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/cal_config.gif" WIDTH="18" HEIGHT="18" BORDER=0 ALT=""></td>
</tr>
<tr><td class="caltableborderhele" colspan="3"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
<tr><td colspan="3">

</td></tr>
</table>




</td>
</tr>
</table>


<span class="header1">{VAR:menudef}</a>
<br>
<font color="red"><b>{VAR:status_msg}</b></font>
{VAR:navigator}
</span>
