<form method=POST action="reforb.{VAR:ext}" enctype="multipart/form-data">
	<input type='hidden' name="MAX_FILE_SIZE"  value="10000000">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fgtext">ID</td>
	<td class="fgtext">{VAR:ID}</td>
</tr>
<tr>
	<td class="fgtext">Nimetus (aasta, kuu)</td>
	<td class="fgtext"><input type="text" name="description" value="{VAR:description}"></td>
</tr>
<tr>
	<td class="fgtext">Kommentaar (teema)</td>
	<td class="fgtext"><input type="text" name="comment" value="{VAR:comment}"></td>
</tr>
<tr>
	<td class="fgtext">Arhiveeritud</td>
	<td class="fgtext"><select name="archived">
	{VAR:arc}
	</select>
	</td>
</tr>
<tr>
	<td class="fgtext">Pilt:</td>
	<td class="fgtext"><input type="file" name="image"> {VAR:image}</td>
</tr>
<tr>
	<td class="fgtext">Pildi link</td>
	<td class="fgtext"><input type="text" name="image_link" value="{VAR:image_link}"></td>
</tr>
<tr>
	<td class="fgtext">Aasta</td>
	<td class="fgtext"><select name="pyear">{VAR:pyear}</select></td>
</tr>
<tr>
	<td class="fgtext" colspan="2">
	<input type="submit" value="Salvesta periood">
	{VAR:reforb}
	</td>
</tr>
</table>
</form>
