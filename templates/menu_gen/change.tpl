{VAR:toolbar}
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<form action='reforb.{VAR:ext}' method=post name="add">
	<tr>
		<td class="fform">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}' class="formtext">
		</td>
	</tr>
	<tr>
		<td class="fform">tüüp:</td><td class="fform">{VAR:tyyp}
		</td>
	</tr>
	<tr>
		<td class="fform">kataloog</td><td class="fform">{VAR:source}
		</td>
	</tr>

	<tr>
		<td class="fform">analüüsi objekte</td><td class="fform">{VAR:analyse}
		</td>
	</tr>

	<tr>
		<td class="fform">uued pane kataloogi</td><td class="fform">{VAR:destination}
		</td>
	</tr>
	<tr>
		<td class="fform" colspan=2> 
		{VAR:abx}
		</td>
	</tr
</table>
{VAR:reforb}
</form>