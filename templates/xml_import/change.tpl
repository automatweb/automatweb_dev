<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fgtext">Nimi:</td><td class="fgtext">
	<input type="text" NAME='name' SIZE=40 class='small_button' value="{VAR:name}">
</td>
</tr>
<tr>
<td class="fgtext">XML datasource:</td><td class="fgtext">
	<select name="datasource">{VAR:datasources}</select>
</td>
</tr>
<tr>
<td class="fgtext">Vali funktsioon, mis importi sooritab:</td><td class="fgtext">
	<select name="import_function">{VAR:import_functions}</select>
</td>
</tr>
<tr>
<td class="fgtext" colspan=2><a href="{VAR:run_import}" target="_blank">Käivita import</a></td>
</tr>
<tr>
<td class="fgtext" colspan=2><input type='submit' VALUE='Salvesta' CLASS="small_button"></td>
</tr>
</table>
{VAR:reforb}
</form>
