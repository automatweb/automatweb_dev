<script language=javascript>
	function doSubmit()
	{
		foo.exp_all.value=1;
		foo.submit();
	}
	function doSubmit2()
	{
		foo.close_all.value=1;
		foo.submit();
	}

	<!-- SUB: ONAME -->
		var od_{VAR:menu_id} = "{VAR:menu_name_s}";
	<!-- END SUB: ONAME -->
	var sel_oid = 0;

	function sss(id)
	{
		sel_oid = id;
	}

	function s_submit(url)
	{
		window.location.href=url;
	}

	function change()
	{
		url = "menuedit.{VAR:ext}?type=change_menu&id="+sel_oid;

		setTimeout("s_submit(\""+url+"\")",1);
	}

	function fdelete()
	{
//		name = eval("od_"+sel_oid);
		url = "menuedit.{VAR:ext}?type=delete_menu&id="+sel_oid;

		var answer=confirm('Oled kindel, et soovid test kustutada menüüd ?')
		if (answer)
			setTimeout("s_submit(\""+url+"\")",1);
	}

	function fperiod()
	{
		url = "periods.{VAR:ext}?oid="+sel_oid;
		setTimeout("s_submit(\""+url+"\")",1);
	}

	function acl()
	{
		url = "editacl.{VAR:ext}?file=menu.xml&oid="+sel_oid;
		setTimeout("s_submit(\""+url+"\")",1);
	}
</script>
<br>
<form action='refcheck.{VAR:ext}' METHOD=POST NAME='foo'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td height="15" colspan="12" class="fgtitle">&nbsp;<b>MEN&Uuml;&Uuml;EDITOR: 
<!-- SUB: ADD_CAT -->
<a href='{VAR:self}?type=add_menu&parent={VAR:parent}'>Lisa</a>
<!-- END SUB: ADD_CAT -->
<!-- SUB: ADD_CAT_L3 -->
<a href='{VAR:self}?type=add_menu_l3&parent={VAR:parent}'>{VAR:m_name}</a>
<!-- END SUB: ADD_CAT_L3 -->
<!-- SUB: ADD_CAT_ADMIN -->
<a href='{VAR:self}?type=add_menu_admin&parent={VAR:parent}'>Lisa</a>
<!-- END SUB: ADD_CAT_ADMIN -->
<!-- SUB: PASTE -->
| <a href='{VAR:self}?type=paste&parent={VAR:parent}'>Paste</a>
<!-- END SUB: PASTE -->
| <a href='javascript:foo.submit()'><font color="red"><B>Salvesta</b></font></a>
| <a href='javascript:doSubmit()'>Ava k&otilde;ik</a>
| <a href='javascript:doSubmit2()'>Sulge k&otilde;ik</a>
| <a href='{VAR:self}?type=prygikoll'>Pr&uuml;gikoll</a>
<!-- SUB: CAN_ADD_PROMO -->
| <a href='{VAR:self}?type=add_promo&parent={VAR:parent}'>Lisa promo kast</a>
<!-- END SUB: CAN_ADD_PROMO -->
| <a href='#' onClick="change()">Muuda</a>
| <a href='#' onClick="fdelete()">Kustuta</a>
| <a href='#' onClick="fperiod()">Periood</a>
| <a href='#' onClick="acl()">&Otilde;igused</a>
| <a href='#' onClick="templates()">Templated</a>
</b>
</td>
</tr>
<tr>
<td height="15" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;J&auml;rjekord&nbsp;</td>
<td align="center" class="title">&nbsp;Kirjeldus&nbsp;</td>
<td align="center" class="title">&nbsp;Muutja&nbsp;</td>
<td align="center" class="title">&nbsp;Muudetud&nbsp;</td>
<td align="center" class="title">&nbsp;Aktiivne&nbsp;</td>
<td align="center" class="title">&nbsp;Perioodiline&nbsp;</td>
<td align="center" class="title">&nbsp;Klikitav&nbsp;</td>
<td align="center" class="title">&nbsp;Uues&nbsp;</td>
<td align="center" class="title">&nbsp;MKDP&nbsp;</td>
<td align="center" class="title" colspan=2>&nbsp;Vali&nbsp;</td>
</tr>

<!-- SUB: LINE -->
<tr>
<td height="15" class="fgtext">
<table border=0 cellspacing=0 cellpadding=0 bgcolor=#ffffff vspace=0 hspace=0>
<tr>
<td>{VAR:space_images}{VAR:image}</td>
<td valign=center class="fgtext">&nbsp;<a href='{VAR:self}?parent={VAR:menu_id}{VAR:op}&menu=menu'>{VAR:menu_name}</a></td>
</tr>
</table>
</td>

<td class="fgtext{VAR:gee}" align=center>&nbsp;
<!-- SUB: NFIRST -->
<input class='small_button' type=text NAME='ord[{VAR:menu_id}]' VALUE='{VAR:menu_order}' SIZE=2 MAXLENGTH=3><input type='hidden' name='old_ord[{VAR:menu_id}]' value='{VAR:menu_order}'>
<!-- END SUB: NFIRST -->
&nbsp;</td>
<td class="fgtext{VAR:gee}">&nbsp;{VAR:menu_comment}&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}" nowrap>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}" nowrap>&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;
<!-- SUB: CAN_ACTIVE -->
<input type='checkbox' NAME='act[{VAR:menu_id}]' {VAR:menu_active}><input type='hidden' NAME='old_act[{VAR:menu_id}]' VALUE='{VAR:menu_active2}'>
<!-- END SUB: CAN_ACTIVE -->
&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;
<!-- SUB: PERIODIC -->
<input type='checkbox' NAME='prd[{VAR:menu_id}]' {VAR:prd1}><input type='hidden' NAME='old_prd[{VAR:menu_id}]' VALUE='{VAR:prd2}'>
<!-- END SUB: PERIODIC -->
&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;<input type='checkbox' NAME='clk[{VAR:menu_id}]' {VAR:clk1}><input type='hidden' NAME='old_clk[{VAR:menu_id}]' VALUE='{VAR:clk2}'>&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;<input type='checkbox' NAME='new[{VAR:menu_id}]' {VAR:new1}><input type='hidden' NAME='old_new[{VAR:menu_id}]' VALUE='{VAR:new2}'>&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;<input type='checkbox' NAME='mkd[{VAR:menu_id}]' {VAR:mkd1}><input type='hidden' NAME='old_mkd[{VAR:menu_id}]' VALUE='{VAR:mkd2}'>&nbsp;</td>
<!-- <td class="fgtext2">&nbsp;
<!-- SUB: CAN_CHANGE -->
<a href='{VAR:self}?type=change_menu&id={VAR:menu_id}&parent={VAR:parent}'>Properties</a>
<!-- END SUB: CAN_CHANGE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('Oled kindel, et soovid seda men&uuml;&uuml;d 
kustutada?','{VAR:self}?type=delete_menu&id={VAR:menu_id}&parent={VAR:parent}')">Kustuta</a>
<!-- END SUB: CAN_DELETE -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_SEL_PERIOD -->
<a href="periods.{VAR:ext}?oid={VAR:menu_id}">Periood</a>
<!-- END SUB: CAN_SEL_PERIOD -->
&nbsp;</td>
<td class="fgtext2">&nbsp;
<!-- SUB: CAN_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:menu_id}&file=menu.xml'>ACL</a>
<!-- END SUB: CAN_ACL -->
&nbsp;</td>-->
<td align="center" class="title">&nbsp;<input type='radio' NAME='id' onClick="sss('{VAR:menu_id}')" value='{VAR:menu_id}'>&nbsp;</td>
<td class="title">&nbsp;
<!-- SUB: COPY -->
<input type='checkbox' NAME='cp[{VAR:menu_id}]' VALUE=1 {VAR:copied}>
<!-- END SUB: COPY -->
&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
</tr>
</table>
<input type="hidden" name="period" value="{VAR:period}">
<input type='hidden' NAME='action' VALUE='save_menu_dox'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
<input type='hidden' NAME='exp_all' VALUE='0'>
<input type='hidden' NAME='close_all' VALUE='0'>
</form>

<form action='refcheck.{VAR:ext}' METHOD=POST NAME="dox">
<input type='hidden' NAME='action' VALUE='save_menu_dox'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="0"  width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>DOKUMENDID:
<!-- SUB: ADD_DOC -->
<a href='doclist.{VAR:ext}?action=adddoc&period={VAR:period}&parent={VAR:parent}'>Lisa</a> |
<!-- END SUB: ADD_DOC -->
 <a href="javascript:document.dox.submit()"><font color="red"><B>Salvesta</b></font></a>
 | <a href="documents.{VAR:ext}?docid={VAR:parent}"><b>Alusdokument</b></a></td>
</tr>

<tr>
<td align="center" class="title">&nbsp;ID&nbsp;</td>
<td align="center" class="title">&nbsp;Pealkiri&nbsp;</td>
<td align="center" class="title">&nbsp;Muutja&nbsp;</td>
<td align="center" class="title">&nbsp;Muudetud&nbsp;</td>
<td align="center" class="title">&nbsp;Aktiivsus&nbsp;</td>
<td align="center" class="title">&nbsp;Link&nbsp;</td>
<td align="center" class="title">&nbsp;Default&nbsp;</td>
</tr>

<!-- SUB: FLINE -->
<tr>
<td align="center" class="fgtext{VAR:gee}">&nbsp;{VAR:doc_id}&nbsp;</td>
<td class="fgtext{VAR:gee}">&nbsp;<a href="documents.{VAR:ext}?docid={VAR:doc_id}">{VAR:doc_title}</a>&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;{VAR:active}&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;{VAR:link}&nbsp;</td>
<td align="center" class="title">&nbsp;<input type='radio' NAME='default_doc' VALUE='{VAR:doc_id}' {VAR:doc_default}>&nbsp;</td>
</tr>

<!-- END SUB: FLINE -->
<tr>
<td class="fgtext" align=center><b>V&otilde;i</b></td>
<Td class="fgtext" colspan=5>
<select name='default_doc2'  class='small_button' >{VAR:default_doc}</select></td>
<td class="title" align=center><input type='radio' NAME='default_doc' VALUE='-1' {VAR:doc_default}></td>
</tr>
</table>
</td></tr></table>
</form>
<br><br>
