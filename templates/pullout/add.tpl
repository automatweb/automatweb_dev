<form method=POST action='reforb.{VAR:ext}'>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fcaption">Nimi:</td>
	<td class="fform"><input type="text" name="name" value="{VAR:name}"></td>
</tr>
<tr>
	<td class="fcaption">Laius:</td>
	<td class="fform"><input type="text" size="3" name="width" value='{VAR:width}'></td>
</tr>
<tr>
	<td class="fcaption">Align:</td>
	<td class="fform"><select class="small_button" name="align">{VAR:align}</select></td>
</tr>
<tr>
	<td class="fcaption">Paremalt:</td>
	<td class="fform"><input type="text" size="3" name="right" value='{VAR:right}'></td>
</tr>
<tr>
	<td class="fcaption">Template:</td>
	<td class="fform"><select class="small_button" name="template">{VAR:template}</select></td>
</tr>
<tr>
	<td class="fcaption">Vali dokument:</td>
	<td class="fform"><select class="small_button" name="docs">{VAR:docs}</select></td>
</tr>
<tr>
	<td class="fcaption">Vali grupid kellele pullouti n&auml;idatakse:</td>
	<td class="fform"><select name="groups[]" class="small_button" multiple size="10">{VAR:groups}</select></td>
</tr>
<tr>
	<td class="fform" colspan="2" align="center">
	{VAR:reforb}
	<input type="submit" value="Salvesta">
	</td>
</tr>
</table>
</form>
