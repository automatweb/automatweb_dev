{VAR:toolbar}
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<form action='reforb.{VAR:ext}' method=post name="add">
	<tr>
		<td class="fform">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}' class="formtext"></td>
	</tr>
	<tr>
		<td class="fform">V&auml;li:</td><td class="fform"><select NAME='field' class="formselect">{VAR:fields}</select></td>
	</tr>
</table>
{VAR:reforb}
</form>


