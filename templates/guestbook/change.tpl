<form method="POST" action="reforb.{VAR:ext}">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">Nimi</td>
	<td class="fform"><input type="text" name="name" size="40" value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Kommentaar:</td>
</tr>
<tr>
	<td colspan=2 class="fform"><textarea name="comment" rows=5 cols=50>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Sissekanded:</td>
</tr>
<!-- SUB: ENTRY -->
<tr>
	<td class="fcaption2">Kuup&auml;ev:</td>
	<td class="fcaption2">{VAR:date}</td>
</tr>
<tr>
	<td class="fcaption2">Nimi:</td>
	<td class="fcaption2">{VAR:name}</td>
</tr>
<tr>
	<td class="fcaption2">Email:</td>
	<td class="fcaption2">{VAR:email}</td>
</tr>
<tr>
	<td valign=top class="fcaption2">Kommentaar:</td>
	<td class="fcaption2"><textarea name='comments[{VAR:id}]' cols=30 rows=10>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="fcaption2">Kustuta:</td>
	<td class="fcaption2"><input type='checkbox' name='erase[{VAR:id}]' value='1'></td>
</tr>
<!-- END SUB: ENTRY -->
<tr>
	<td class="fform" align="center" colspan="2">
		<input type="submit" value="Salvesta">
		{VAR:reforb}
	</td>
</tr>
</table>
</form>
