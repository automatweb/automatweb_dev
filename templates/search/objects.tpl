<form method="GET" action="reforb.{VAR:ext}">
<table border=0 cellspacing=1 cellpadding=2>
<!-- SUB: f_name -->
<tr>
	<td class="celltext">Nimi:</td>
	<td class="celltext"><input type="text" class="formtext" size="40" name="name" value="{VAR:name}"></td>
</tr>
<!-- END SUB: f_name -->
<!-- SUB: f_comment -->
<tr>
	<td class="celltext">Kommentaar:</td>
	<td class="celltext"><input type="text" class="formtext" size="40" name="comment" value="{VAR:comment}"></td>
</tr>
<!-- END SUB: f_comment -->
<!-- SUB: f_class_id -->
<tr>
	<td class="celltext">Tüüp:</td>
	<td class="celltext"><select class="formtext" name="class_id">{VAR:class_id}</select></td>
</tr>
<!-- END SUB: f_class_id -->
<!-- SUB: f_parent -->
<tr>
	<td class="celltext">Asukoht:</td>
	<td class="celltext"><select class="formtext" name="parent">{VAR:parent}</select></td>
</tr>
<!-- END SUB: f_parent -->
<!-- SUB: f_createdby -->
<tr>
	<td class="celltext">Looja:</td>
	<td class="celltext"><input type="text" name="createdby" size="40" value="{VAR:createdby}"></td>
</tr>
<!-- END SUB: f_createdby -->
<!-- SUB: f_modifiedby -->
<tr>
	<td class="celltext">Muutja:</td>
	<td class="celltext"><input type="text" name="modifiedby" size="40" value="{VAR:modifiedby}"></td>
</tr>
<!-- END SUB: f_modifiedby -->
<!-- SUB: f_active -->
<tr>
	<td class="celltext">Aktiivsus:</td>
	<td class="celltext"><input type="checkbox" class="formtext" name="active" {VAR:active}></td>
</tr>
<!-- END SUB: f_active -->
<!-- SUB: f_alias -->
<tr>
	<td class="celltext">Alias:</td>
	<td class="celltext"><input type="text" class="formtext" size="40" name="alias" value="{VAR:alias}"></td>
</tr>
<!-- END SUB: f_alias -->
<tr>
	<td class="celltext" colspan="2" align="center">
	{VAR:reforb}
	<input type="submit" value="Otsi">
	</td>
</tr>
</table>
</form>
{VAR:table}
