<form method="POST" action="reforb.{VAR:ext}" name='b88' enctype="multipart/form-data">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">ID:</td>
	<td class="fform">{VAR:id}</td>
</tr>
<tr>
	<td class="fcaption2">Nimi:</td>
	<td class="fform"><input type="text" name="name" size="40" value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Kommentaar:</td>
	<td class="fform"><textarea name="comment" rows=5 cols=50>{VAR:comment}</textarea></td>
</tr>
<!-- SUB: CHANGE -->
<tr>
	<td class="fcaption2" colspan=2 valign="top"><a href='{VAR:html}'>Muuda banneri HTML'i</a></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2 valign="top"><a href='{VAR:stats}'>Kliendi statistika</a></td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Salvesta">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
