<form action = 'reforb.{VAR:ext}' method=post name="foo">
<table cellpadding=0 cellspacing=0 border=0>
<tr><td width=100%>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0 width=100%>
<!-- SUB: UBAR -->
<tr>
	<td colspan="2" class="fcaption2"><a href='{VAR:l_muuda}'>Muuda</a> | <a href='{VAR:l_queue}'>Queue</a></td>
</tr>
<!-- END SUB: UBAR -->
<tr>
	<td class="fcaption2">Listid:</td>
	<td class="fform" ><select size="10" name='lists[]' multiple>{VAR:listsel}</select></td>
</tr>
<tr>
	<td class="fcaption2">Mitu korraga:</td>
	<td class="fform" ><input type="text" name="patch_size" class="small_button" size="5"></td>
</tr>
<tr>
	<td class="fcaption2">Vahe:</td>
	<td class="fform" ><input type="text" name="delay" class="small_button" size="5"></td>
</tr>
<tr>
	<td class="fcaption2">Millal:</td>
	<td class="fform" >{VAR:date_edit}</td>
</tr>
<tr>
<td class="fcaption2" colspan="2" align="right"><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
</td></tr>
</table>
{VAR:reforb}
</form>
