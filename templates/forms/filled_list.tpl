<script language=javascript>
var st=1;
function selall()
{
<!-- SUB: SELLINE -->

	document.forms[1].elements[{VAR:row}].checked=st;

<!-- END SUB: SELLINE -->
st = !st;
return false;
}
</script>
<form action='forms.{VAR:ext}' METHOD=get>
<input type=hidden name=type value=filled_forms>
<input type=hidden name=level value=1>
<input type=hidden name=id value={VAR:form_id}>
<input type=hidden name=op_id value={VAR:op_id}>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0">
<tr>
<td height="15" colspan="9" class="fgtitle">&nbsp;<b>Kategooriad: <a href='forms.{VAR:ext}?type=add_cat&parent={VAR:parent}&id={VAR:form_id}&op_id={VAR:op_id}'>Lisa</a></b>
</td>
</tr>
<tr>
<td height="15" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Kirjeldus&nbsp;</td>
<td align="center" class="title">&nbsp;Muudetja&nbsp;</td>
<td align="center" class="title">&nbsp;Muudetud&nbsp;</td>
<td align="center" colspan="3" class="title">&nbsp;Tegevus&nbsp;</td>
</tr>

<!-- SUB: C_LINE -->
<tr>
<td height="15" class="fgtext">
<table border=0 cellspacing=0 cellpadding=0 bgcolor=#ffffff vspace=0 hspace=0>
<tr>
<td>{VAR:space_images}{VAR:image}</td>
<td valign=center class="fgtext">&nbsp;<a href='forms.{VAR:ext}?type=filled_forms&level=1&parent={VAR:menu_id}{VAR:op}&id={VAR:form_id}&op_id={VAR:op_id}'>{VAR:menu_name}</a></td>
</tr>
</table>
</td>

<td class="fgtext">&nbsp;{VAR:menu_comment}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext">&nbsp;{VAR:modified}&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: C_CAN_CHANGE -->
<a href='forms.{VAR:ext}?type=change_filled_cat&id={VAR:form_id}&op_id={VAR:op_id}&parent={VAR:parent}&cat_id={VAR:menu_id}'>Metainfo</a>
<!-- END SUB: C_CAN_CHANGE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: C_CAN_DELETE -->
<a href="javascript:box2('Oled kindel, et soovid seda kategooriat 
kustutada?','forms.{VAR:ext}?type=delete_filled_cat&id={VAR:form_id}&parent={VAR:parent}&op_id={VAR:op_id}&cat_id={VAR:menu_id}')">Kustuta</a>
<!-- END SUB: C_CAN_DELETE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: C_CAN_ACL -->
<a href='#'>ACL</a>
<!-- END SUB: C_CAN_ACL -->
&nbsp;</td>
</tr>
<!-- END SUB: C_LINE -->
</table>
</td>
</tr>
</table>

<br>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr bgcolor="#C9EFEF">
<td class="title"><a href='forms.{VAR:ext}?type=filled_forms&id={VAR:form_id}'>Valitud v&auml;ljund:</a></td>
<td class="plain">{VAR:op_name}</td>
</tr>
<tr>
<td class="title">Otsi t&auml;itja j&auml;rgi:</td>
<td class="plain"><input type='text' NAME='search_string' VALUE='{VAR:search_string}'><input type='submit' NAME='search' VALUE='Otsi'></td>
</tr>
</table>
</form>
<form action='refcheck.{VAR:ext}' method=post NAME=fiib>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#CCCCCC">

<table bgcolor="#CCCCCC" cellpadding=2 cellspacing=1 border=0>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>ANDMED: <a href='javascript:document.fiib.submit()'>Ekspordi</a></b></td>
</tr>

<tr bgcolor="#C9EFEF">
<td class="title"><a href='forms.{VAR:ext}?type=filled_forms&level=1&id={VAR:form_id}&op_id={VAR:op_id}&parent={VAR:parent}&sort_col=oid&sort_order={VAR:sort_order}'>ID</a>{VAR:sort_id}</td>
<td class="title"><a href='forms.{VAR:ext}?type=filled_forms&level=1&id={VAR:form_id}&op_id={VAR:op_id}&parent={VAR:parent}&sort_col=modified&sort_order={VAR:sort_order}'>Millal t&auml;ideti</a>{VAR:sort_when}</td>
<td class="title"><a href='forms.{VAR:ext}?type=filled_forms&level=1&id={VAR:form_id}&op_id={VAR:op_id}&parent={VAR:parent}&sort_col=modifiedby&sort_order={VAR:sort_order}'>Kes T&auml;itis</a>{VAR:sort_who}</td>
<td class="title"><a href='forms.{VAR:ext}?type=filled_forms&level=1&id={VAR:form_id}&op_id={VAR:op_id}&parent={VAR:parent}&sort_col=hits&sort_order={VAR:sort_order}'>Vaatamisi</a>{VAR:sort_hits}</td>
<td class="title" align=center colspan=3>Tegevus</td>
<!-- SUB: ACTIONS -->
<td class="title" align=center colspan={VAR:colspan}>{VAR:action_name}</td>
<!-- END SUB: ACTIONS -->
<td class="title" align=center><a href='#' onClick="selall();return false;">K&otilde;ik</a></td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">{VAR:entry_id}</td>
<td class="fgtext">{VAR:entry_when}</td>
<td class="fgtext">{VAR:entry_who}</td>
<td class="fgtext">{VAR:views}</td>
<td class="plain">
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('Oled kindel, et soovid seda sisestust kustutada?','forms.{VAR:ext}?type=delete_entry&id={VAR:form_id}&entry_id={VAR:entry_id}&parent={VAR:parent}&op_id={VAR:op_id}')">Kustuta</a>
<!-- END SUB: CAN_DELETE -->
&nbsp;</td>
<td class="plain">
<!-- SUB: CAN_CHANGE -->
<a href='forms.{VAR:ext}?type=change_entry&id={VAR:form_id}&entry_id={VAR:entry_id}&parent={VAR:parent}&op_id={VAR:op_id}'>Muuda</a>
<!-- END SUB: CAN_CHANGE -->
&nbsp;</td>
<td class="plain"><a href='forms.{VAR:ext}?type=show_entry&id={VAR:form_id}&parent={VAR:parent}&entry_id={VAR:entry_id}&op_id={VAR:op_id}'>N&auml;ita</a></td>
<!-- SUB: ACTION_LINE -->
<td class="plain" align="center"><a href='forms.{VAR:ext}?type=move_filled&from={VAR:entry_id}&to={VAR:action_target}&id={VAR:form_id}&parent={VAR:parent}&entry_id={VAR:entry_id}&op_id={VAR:op_id}'>{VAR:action_target_name}</a></td>
<!-- END SUB: ACTION_LINE -->
<td class="chkbox">
<!-- SUB: CAN_EXPORT -->
<input type='checkbox' NAME='ch_{VAR:entry_id}' VALUE=1>
<!-- END SUB: CAN_EXPORT -->
&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td></tr></table>

<input type='hidden' NAME='action' VALUE='export_data'>
<input type='hidden' NAME='id' VALUE='{VAR:form_id}'>
<input type='hidden' NAME='op_id' VALUE='{VAR:op_id}'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
<br>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr bgcolor="#C9EFEF"><td class="title" align=left colspan=10>Vali lehek&uuml;lg:</td></tr>
<!-- SUB: PAGES_LINE -->
<tr>
<!-- SUB: PAGES -->
<td class="plain"><a href='forms.{VAR:ext}?type=filled_forms&level=1&id={VAR:form_id}&op_id={VAR:op_id}&page_top={VAR:page_top}&parent={VAR:parent}'>{VAR:page_top} - {VAR:page_bottom}</a></td>
<!-- END SUB: PAGES -->
</tr>
<!-- END SUB: PAGES_LINE -->
</table>

<!-- SUB: SEL_PAGE -->
<td class="plain" bgcolor=#f00000><font color=#f00000>{VAR:page_top} - {VAR:page_bottom}</font></td>
<!-- END SUB: SEL_PAGE -->
</form>
