{VAR:menu}
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<form name="syslist" method="POST" action="reforb.{VAR:ext}">
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td colspan="8" class="title">&nbsp;<b>Süsteemsed stiiligrupid</b>
|
<a href="{VAR:link_addgroup}">Lisa uus</a>
|
<a href="javascript:document.syslist.submit()"><font color="red">Salvesta</font></a>
</td>
</tr>
<tr>
<td align="center" class="title">&nbsp;#&nbsp;</td>
<td align="center" class="title">&nbsp;Grupi nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Kommentaar&nbsp;</td>
<td align="center" class="title">&nbsp;Autor&nbsp;</td>
<td align="center" class="title">&nbsp;Aktiivne&nbsp;</td>
<td align="center" class="title">&nbsp;Kasutusel&nbsp;</td>
<td align="center" class="title" colspan="2">&nbsp;Tegevus&nbsp;</td>
</tr>
<!-- SUB: line -->
<tr>
<td class="fgtext">{VAR:cnt}</td>
<td class="fgtext">{VAR:name}</td>
<td class="fgtext">&nbsp;{VAR:comment}</td>
<td class="fgtext">&nbsp;{VAR:modifiedby}</td>
<td class="fgtext" align="center">
	<input type="checkbox" name="active[]" value="{VAR:oid}" {VAR:active}>
</td>
<td class="fgtext" align="center">
	<input type="radio" name="use" value="{VAR:oid}" {VAR:use}>
</td>
<td class="fgtext" align="center">
	<a href="{VAR:link_edgroup}">Muuda</a>
</td>
<td class="fgtext" align="center">
	<a href="{VAR:link_prevgroup}">Eelvaade</a>
</td>
</tr>
<!-- END SUB: line -->
</td>
{VAR:reforb}
</form>
</tr>
</table>
