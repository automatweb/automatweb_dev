<script language="Javascript">
<!--
function remote(toolbar,width,height,file) {
	self.name = "root";
	var wprops = "toolbar=" + toolbar + ",location=0,directories=0,status=0, "+
	"menubar=0,scrollbars=1,resizable=1,width=" + width + ",height=" + height;
	openwindow = window.open(file,"remote",wprops);
}

function box2(caption,url){
var answer=confirm(caption)
if (answer)
window.location=url
}
// -->
</script>

<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">
<form action='reforb.{VAR:ext}' METHOD=POST NAME='foo'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="0" cellpadding="1" width=100%>
<tr>
<td bgcolor=#000000>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="11" class="fgtitle_new" background="images/uus_sinine.gif" >&nbsp;<b>GRUPID: 
<!-- SUB: ADD_CAT -->
<a href='{VAR:addgrp}' class="fgtitle_link">Lisa</a>
<!-- END SUB: ADD_CAT -->
| <a href='javascript:foo.submit()' class="fgtitle_link">Salvesta</a>
| <a href='#' onClick='window.location.reload()' class="fgtitle_link">V&auml;rskenda</a></b>
</td>
<td height="15" colspan="11" class="fgtitle_new" valign=center background="images/uus_sinine.gif" align=right><a href='http://www.automatweb.com' target="_new"><img border=0 src='images/jessss1.gif'></a>
</td>
</tr>
</table>
</td>
</tr>
</table>
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td class="title_hele" colspan=9 valign=center><table border=0 cellpadding=0 cellspacing=0><tr><td class="title_hele" >&nbsp;{VAR:yah}&nbsp;</td><td class="title_hele" ><img valign="center" src='images/transa.gif' height=18 width=1></td></tr></table></td>
</tr>

<tr>
<td height="15" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Prioriteet&nbsp;</td>
<td align="center" class="title">&nbsp;T&uuml;&uuml;p&nbsp;</td>
<td align="center" class="title">&nbsp;Liikmeid&nbsp;</td>
<td align="center" class="title">&nbsp;Muutja&nbsp;</td>
<td align="center" class="title">&nbsp;Muudetud&nbsp;</td>
<td align="center" colspan="3" class="title">&nbsp;Tegevus&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td height="15" class="fgtext">&nbsp;<a href='{VAR:chmembers}' target='objects' onClick="window.location='orb.{VAR:ext}?class=groups&action=list_grps&parent={VAR:gid}';return true;">{VAR:name}</a>&nbsp;</td>
<td class="fgtext" align=center>&nbsp;
<!-- SUB: NFIRST -->
<input class='small_button' type=text NAME='priority[{VAR:gid}]' VALUE='{VAR:priority}' SIZE=10>
<!-- END SUB: NFIRST -->
&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:type}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;<a href="#">{VAR:members}</a>&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext" nowrap>&nbsp;{VAR:modified}&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_CHANGE -->
<a href='{VAR:change}'>Muuda</a>
<!-- END SUB: CAN_CHANGE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('Oled kindel, et soovid seda gruppi 
kustutada?','{VAR:delete}')">Kustuta</a>
<!-- END SUB: CAN_DELETE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:oid}&file=group.xml'>ACL</a>
<!-- END SUB: CAN_ACL -->
&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
