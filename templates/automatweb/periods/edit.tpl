<form method=POST action="reforb.{VAR:ext}" enctype="multipart/form-data">
	<input type='hidden' name="MAX_FILE_SIZE"  value="10000000">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fform">ID</td>
	<td class="fform">{VAR:ID}</td>
</tr>
<tr>
	<td class="fform">Nimetus</td>
	<td class="fform"><input type="text" name="description" value="{VAR:description}"></td>
</tr>
<tr>
	<td class="fform">Arhiveeritud</td>
	<td class="fform"><select name="archived">
	{VAR:arc}
	</select>
	</td>
</tr>
<tr>
	<td class="fform">Pilt:</td>
	<td class="fform"><input type="file" name="image"> {VAR:image}</td>
</tr>
<tr>
	<td class="fform">Pildi link</td>
	<td class="fform"><input type="text" name="image_link" value="{VAR:image_link}"></td>
</tr>
<tr>
	<td class="fform" colspan="2">
	<input type="submit" value="Salvesta periood">
	{VAR:reforb}
	</td>
</tr>
</table>
</form>
