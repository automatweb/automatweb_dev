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

function cut()
{
	foo.action.value="cut";
	foo.submit();
}

function paste()
{
	foo.action.value="paste";
	foo.submit();
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
<td height="15" colspan="11" class="fgtitle_new" background="images/uus_sinine.gif" >&nbsp;<b>{VAR:LC_MENUEDIT_MENU_CAPS}: 
<!-- SUB: ADD_CAT -->
<a href='{VAR:addmenu}' class="fgtitle_link">Lisa</a>
<!-- END SUB: ADD_CAT -->
 | <a href='{VAR:addpromo}' class="fgtitle_link">{VAR:LC_MENUEDIT_ADD_PROMO_BOX}</a>
 | <a href='javascript:foo.submit()' class="fgtitle_link">{VAR:LC_MENUEDIT_SAVE}</a>
 | <a href='#' onClick='window.location.reload()' class="fgtitle_link">{VAR:LC_MENUEDIT_REFRESH}</a>
 | <a href='{VAR:import}' class="fgtitle_link">{VAR:LC_MENUEDIT_IMPORT}</a>
 | <a href='javascript:cut()' class="fgtitle_link">Cut</a>
<!-- SUB: PASTE -->
 | <a href='javascript:paste()' class="fgtitle_link">Paste</a>
<!-- END SUB: PASTE -->
</b>
</td>
<td height="15" colspan="11" class="fgtitle_new" valign=center background="images/uus_sinine.gif" align=right><a href='orb.aw?action=list&class=bugtrack&filt=all' class='fgtitle_link'>BugTrack</a></td>
<td height="15" class="fgtitle_new" valign=center background="images/uus_sinine.gif" align=right><a href='http://www.automatweb.com' target="_new"><img border=0 src='images/jessss1.gif'></a>
</td>
</tr>
</table>
</td>
</tr>
</table>
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td class="title_hele" colspan=12 valign=center><table border=0 cellpadding=0 cellspacing=0><tr><td class="title_hele" >&nbsp;{VAR:yah}&nbsp;</td><td class="title_hele" ><img valign="center" src='images/transa.gif' height=18 width=1></td></tr></table></td>
</tr>

<tr>
<td height="15" class="title">&nbsp;</td>
<td height="15" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=menus&sortby=name&order={VAR:order1}&period={VAR:period}'>{VAR:LC_MENUEDIT_NAME}</a>{VAR:sortedimg1}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=menus&sortby=jrk&order={VAR:order2}&period={VAR:period}'>{VAR:LC_MENUEDIT_ORDER}</a>{VAR:sortedimg2}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=menus&sortby=status&order={VAR:order5}&period={VAR:period}'>{VAR:LC_MENUEDIT_ACTIVE}</a>{VAR:sortedimg5}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=menus&sortby=modifiedby&order={VAR:order3}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED_BY}</a>{VAR:sortedimg3}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=menus&sortby=modified&order={VAR:order4}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED}</a>{VAR:sortedimg4}&nbsp;</td>
<td align="center" class="title">&nbsp;<a href='menuedit.{VAR:ext}?parent={VAR:parent}&type=menus&sortby=periodic&order={VAR:order6}&period={VAR:period}'>{VAR:LC_MENUEDIT_PERIODIC}</a>{VAR:sortedimg6}&nbsp;</td>
<td align="center" colspan="4" class="title">&nbsp;{VAR:LC_MENUEDIT_ACTION}&nbsp;</td>
<td align="center" class="title">&nbsp;{VAR:LC_MENUEDIT_CHOOSE}&nbsp;</td>
</tr>
<!-- SUB: CUT -->
fgtext2
<!-- END SUB: CUT -->
<!-- SUB: NORMAL -->
fgtext
<!-- END SUB: NORMAL -->

<!-- SUB: LINE -->
<tr>
<td height="15" class="{VAR:is_cut}" align=center>&nbsp;&nbsp;<a href='menuedit_right.{VAR:ext}?parent={VAR:menu_id}&period={VAR:period}' target='list'><img border=0 src='images/ftv2folderclosed.gif'></a>&nbsp;</td>
<td height="15" class="{VAR:is_cut}">&nbsp;<a href='menuedit_right.{VAR:ext}?parent={VAR:r_menu_id}&period={VAR:period}' target='list'>{VAR:name}</a>&nbsp;</td>
<td class="{VAR:is_cut}" align=center>&nbsp;
<!-- SUB: NFIRST -->
<input class='small_button' type=text NAME='ord[{VAR:menu_id}]' VALUE='{VAR:menu_order}' SIZE=2 MAXLENGTH=3><input type='hidden' name='old_ord[{VAR:menu_id}]' value='{VAR:menu_order}'>
<!-- END SUB: NFIRST -->
&nbsp;</td>
<td align="center" class="{VAR:is_cut}">&nbsp;
<!-- SUB: CAN_ACTIVE -->
<input type='checkbox' NAME='act[{VAR:menu_id}]' {VAR:menu_active}><input type='hidden' NAME='old_act[{VAR:menu_id}]' VALUE='{VAR:menu_active2}'>
<!-- END SUB: CAN_ACTIVE -->
&nbsp;</td>
<td align="center" class="{VAR:is_cut}" nowrap>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="{VAR:is_cut}" nowrap>&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="{VAR:is_cut}">&nbsp;
<!-- SUB: PERIODIC -->
<input type='checkbox' NAME='prd[{VAR:menu_id}]' {VAR:prd1}><input type='hidden' NAME='old_prd[{VAR:menu_id}]' VALUE='{VAR:prd2}'>
<!-- END SUB: PERIODIC -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_CHANGE -->
<a href='{VAR:properties}'>{VAR:LC_MENUEDIT_PROPERTIES}</a>
<!-- END SUB: CAN_CHANGE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('{VAR:LC_MENUEDIT_SURE_DELETE_MENU}?','{VAR:delete}')">{VAR:LC_MENUEDIT_DELETE}</a>
<!-- END SUB: CAN_DELETE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_SEL_PERIOD -->
<a href="periods.{VAR:ext}?oid={VAR:menu_id}">{VAR:LC_MENUEDIT_PERIOD}</a>
<!-- END SUB: CAN_SEL_PERIOD -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:r_menu_id}&file=menu.xml'>ACL</a>
<!-- END SUB: CAN_ACL -->
&nbsp;</td>
<td class="fgtext2">&nbsp;<input type='checkbox' NAME='sel[{VAR:menu_id}]' VALUE=1>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
</tr>
</table>
<input type="hidden" name="period" value="{VAR:period}">
{VAR:reforb}
</form>
