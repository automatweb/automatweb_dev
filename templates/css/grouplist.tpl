{VAR:menu}
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2">
<tr>
<td colspan="6" class="title">&nbsp;Süsteemsed stiiligrupid</td>
</tr>
<tr>
<td align="center" class="title">&nbsp;#&nbsp;</td>
<td align="center" class="title">&nbsp;Grupi nimi&nbsp;</td>
<td align="center" class="title">&nbsp;Kommentaar&nbsp;</td>
<td align="center" class="title">&nbsp;Autor&bbsp;</td>
<td align="center" class="title">&nbsp;Aktiivne&nbsp;</td>
<td align="center" class="title">&nbsp;Tegevus&nbsp;</td>
</tr>
<!-- SUB: line -->
<tr>
<td class="fgtext">{VAR:cnt}</td>
<td class="fgtext">{VAR:gname}</td>
<td class="fgtext">&nbsp;</td>
<td class="fgtext">duke</td>
<td class="fgtext" align="center">
	<input type="radio" name="use" value="{VAR:gid}" {VAR:checked}>
</td>
<td class="fgtext" align="center">
	<a href="{VAR:link_prevgroup}">Eelvaade</a>
</td>
</tr>
<!-- END SUB: line -->
<tr>
<td colspan="6" class="title">&nbsp;Minu stiiligrupid</td>
</tr>
<tr>
<td class="fgtext" colspan="6">
<input type="submit" value="Salvesta">
</td>
</tr>
</table>
</td>
</tr>
{VAR:reforb}
</form>
</table>
