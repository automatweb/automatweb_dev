<form method=POST action='reforb.{VAR:ext}'>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fform">P&auml;rja nimi:</td>
	<td colspan="3" class="fform"><input type="text" name="name" value="{VAR:name}"></td>
</tr>
<tr>
	<td colspan="4" class="fform">Valitud objektid:</td>
</tr>
<!-- SUB: OBJECT -->
<tr>
	<td class="fform"><a href='{VAR:change}'>{VAR:name}</a></td>
	<td colspan="3" class="fform"><input type='checkbox' name='objs[{VAR:oid}]' value='1' checked></td>
</tr>
<!-- END SUB: OBJECT -->

<!-- SUB: SEARCH -->
<tr>
	<td colspan="4" class="fform">Otsi objekte:</td>
</tr>
<tr>
	<td class="fform">Otsi nimest:</td>
	<td colspan="3" class="fform"><input type="text" name="s_name" value="{VAR:s_name}"></td>
</tr>
<tr>
	<td class="fform">Otsi kommentaarist:</td>
	<td colspan="3" class="fform"><input type="text" name="s_comment" value="{VAR:s_comment}"></td>
</tr>
<tr>
	<td class="fform">T&uuml;&uuml;p:</td>
	<td colspan="3" class="fform"><select multiple class="small_button" size="15" name='s_type[]'>{VAR:types}</select><input type='hidden' name='search' value='1'></td>
</tr>
<tr>
	<td class="fform">Nimi</td>
	<td class="fform">T&uuml;&uuml;p</td>
	<td class="fform">Asukoht</td>
	<td class="fform">Vali</td>
</tr>
<!-- SUB: S_RESULT -->
<tr>
	<td class="fform">{VAR:name}</td>
	<td class="fform">{VAR:type}</td>
	<td class="fform">{VAR:place}</td>
	<td class="fform"><input type="checkbox" name="sel[{VAR:oid}]" value="1" {VAR:sel}></td>
</tr>
<!-- END SUB: S_RESULT -->

<!-- END SUB: SEARCH -->
<tr>
	<td class="fform" colspan="4" align="center"><input type="submit" value="Salvesta"></td>
</tr>
</table>
{VAR:reforb}
</form>
