{VAR:toolbar}
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<form action='reforb.{VAR:ext}' method=post name="add">
	<tr>
		<td class="fform">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}' class="formtext"></td>
	</tr>
	<tr>
		<td class="fform">T&uuml;&uuml;p:</td><td class="fform"><select NAME='type' class="formselect">{VAR:type}</select></td>
	</tr>
	<tr>
		<td class="fform">Pikkus:</td><td class="fform"><input type='text' NAME='length' VALUE='{VAR:length}' class="formtext"></td>
	</tr>
	<tr>
		<td class="fform">NULL:</td><td class="fform"><input type='checkbox' NAME='null' VALUE='1' {VAR:null}></td>
	</tr>
	<tr>
		<td class="fform">Default:</td><td class="fform"><input type='text' NAME='default' VALUE='{VAR:default}' class="formtext"></td>
	</tr>
	<tr>
		<td class="fform">Extra:</td><td class="fform"><select NAME='extra' class="formselect">{VAR:extra}</select></td>
	</tr>
</table>
{VAR:reforb}
</form>


