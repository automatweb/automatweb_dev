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
	<!-- SUB: ONAME2 -->
		var od_{VAR:doc_id} = "{VAR:doc_title_s}";
	<!-- END SUB: ONAME2 -->
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
		url = "documents.{VAR:ext}?docid="+sel_oid;

		setTimeout("s_submit(\""+url+"\")",1);
	}
	function prevju()
	{
		url = "documents.{VAR:ext}?mode=preview&docid="+sel_oid;

		setTimeout("s_submit(\""+url+"\")",1);
	}
	function fdelete()
	{
		name = eval("od_"+sel_oid);
		url = "doclist.{VAR:ext}?action=delete&remove="+sel_oid;

		var answer=confirm('Oled kindel, et soovid kustutada dokumenti \"'+name+'\"?')
		if (answer)
			setTimeout("s_submit(\""+url+"\")",1);
	}
	function acl()
	{
		url = "editacl.{VAR:ext}?file=document.xml&oid="+sel_oid;
		setTimeout("s_submit(\""+url+"\")",1);
	}
	function bro()
	{
		url = "documents.{VAR:ext}?type=bro&oid="+sel_oid;
		setTimeout("s_submit(\""+url+"\")",1);
	}
	</script>
<!-- SUB: SEARCH -->
<form action='list_docs.{VAR:ext}' METHOD="GET">
<table border="0">
<tr>
<td bgcolor="#CCCCCC" class="fgtitle">Otsi pealkirjast:</td><td bgcolor="#CCCCCC"><input type='text' NAME='sstring' VALUE='{VAR:sstring}'></td>
</tr>
<tr>
<td bgcolor="#CCCCCC" class="fgtitle">Otsi sisust:</td><td bgcolor="#CCCCCC"><input type='text' NAME='sstring2' VALUE='{VAR:sstring2}'></td>
</tr>
<tr>
<td bgcolor="#CCCCCC" class="fgtitle" colspan=2><input type='submit' VALUE='Otsi!'></td>
</tr>
</table>
<input type='hidden' NAME='search' VALUE='1'>
</form>
<!-- END SUB: SEARCH -->

<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<form action='refcheck.{VAR:ext}' METHOD=POST NAME='booyaka'>
<table border="0" cellspacing="1" cellpadding="0"  width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>DOKUMENDID:
<a href='javascript:booyaka.submit()'><b><font color="red">Salvesta</font></b></a>| <a href='list_docs.{VAR:ext}?action=adddoc&period={VAR:period}'>Lisa</a>
 | <a href='#' onClick="change()">Muuda</a>
 | <a href='#' onClick="prevju()">Eelvaade</a>
 | <a href='#' onClick="fdelete()">Kustuta</a>
 | <a href='#' onClick="acl()">&Otilde;igused</a>
</td>
</tr>

<tr>
<td align="center" class="title">&nbsp;ID&nbsp;</td>
<td align="center" class="title">&nbsp;Pealkiri/muuda&nbsp;</td>
<td align="center" class="title">&nbsp;Jrk&nbsp;</td>
<td align="center" class="title">&nbsp;Muutja&nbsp;</td>
<td align="center" class="title">&nbsp;Muudetud&nbsp;</td>
<td align="center" class="title">&nbsp;Aktiivne&nbsp;</td>
<td align="center" class="title">&nbsp;Lead?&nbsp;</td>
<td align="center" class="title">&nbsp;Foorum?&nbsp;</td>
<td align="center" class="title">&nbsp;Esilehel&nbsp;</td>
<td align="center" class="title">&nbsp;Paremal&nbsp;</td>
<td align="center" class="title">&nbsp;Link&nbsp;</td>
<td align="center" class="title">&nbsp;Text OK?&nbsp;</td>
<td align="center" class="title">&nbsp;Pildid OK?&nbsp;</td>
<td align="center" colspan="2" class="title">Vali</td>
</tr>

<!-- SUB: FLINE -->
<tr>
<td align="center" class="fgtext{VAR:gee}">&nbsp;{VAR:doc_id}&nbsp;</td>
<td class="fgtext{VAR:gee}">&nbsp;<a href="orb.{VAR:ext}?class=document&action=change&id={VAR:doc_id}">{VAR:doc_title}</a>&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}"><input class="small_button" type="text" name="jrk[{VAR:doc_id}]" size="2" maxlength="2" value="{VAR:jrk}"></td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;{VAR:modifiedby}&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;{VAR:modified}&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;<input type="checkbox" name="active[{VAR:doc_id}]" value="1" {VAR:active}>&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;<input type="checkbox" name="showlead[{VAR:doc_id}]" value="1" {VAR:showlead}>&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;<input type="checkbox" name="is_forum[{VAR:doc_id}]" value="1" {VAR:is_forum}>&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;<input type="checkbox" name="esilehel[{VAR:doc_id}]" value="1" {VAR:esilehel}>&nbsp;<input type='text' name='jrk1[{VAR:doc_id}]' size=2 class="small_button" maxlength=2 value='{VAR:jrk1}'>&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;<input type="checkbox" name="esilehel_uudis[{VAR:doc_id}]" value="1" {VAR:esilehel_uudis}>&nbsp;<input type='text' name='jrk2[{VAR:doc_id}]' size=2 class="small_button" maxlength=2 value='{VAR:jrk2}'>&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;{VAR:link}&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;<input type="checkbox" name="text_ok[{VAR:doc_id}]" value="1" {VAR:text_ok}>&nbsp;</td>
<td align="center" class="fgtext{VAR:gee}">&nbsp;<input type="checkbox" name="pic_ok[{VAR:doc_id}]" value="1" {VAR:pic_ok}>&nbsp;</td>

<!-- Tegevused -->
<!--
<td class="fgtext2">&nbsp;
<!-- SUB: D_CHANGE -->
<a href='documents.{VAR:ext}?docid={VAR:doc_id}'>Muuda</a>
<!-- END SUB: D_CHANGE -->
&nbsp;</td>

<td class="fgtext2">&nbsp;<a href='documents.{VAR:ext}?docid={VAR:doc_id}&mode=preview'>Eelvaade</a>&nbsp;</td>

<td class="fgtext2">&nbsp;
<!-- SUB: D_DELETE -->
<a href="javascript:box2('Oled kindel, et soovid seda dokumenti kustutada?','doclist.{VAR:ext}?action=delete&remove={VAR:doc_id}')">Kustuta</a>
<!-- END SUB: D_DELETE -->
&nbsp;</td>

<td class="fgtext2">&nbsp;
<!-- SUB: D_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:doc_id}&file=document.xml'>ACL</a>
<!-- END SUB: D_ACL -->
&nbsp;</td>-->
<td class="title" align=center>&nbsp;<input type='radio' NAME='id' VALUE="{VAR:doc_id}" onClick="sss('{VAR:doc_id}')">&nbsp;</td>
<td class="title" align=center>&nbsp;<input type='checkbox' NAME='do_{VAR:doc_id}' VALUE=1>&nbsp;</td>
</tr>

<!-- END SUB: FLINE -->

</table>
</td></tr></table>
<input type='hidden' NAME='saction' VALUE='move_documents'>
<input type="hidden" name="action" value="savedocuments2">
<input type="hidden" name="parent" value="{VAR:parent}">
<input type="hidden" name="period" value="{VAR:period}">
<input type="hidden" name="periodic" value="{VAR:periodic}">
</form>
<br><Br>