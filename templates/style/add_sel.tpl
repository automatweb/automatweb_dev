<form action='reforb.{VAR:ext}' METHOD=post>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_NAME}:</td>
		<td class="fform"><input type="text" name="name" VALUE=''></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_COMMENT}:</td>
		<td class="fform"><textarea name=comment cols=50 rows=5></textarea></td>
	</tr>
	<tr>
		<td class="fcaption" colspan=2>{VAR:LC_STYLE_CHOOSE_TYPE}:</td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_TABLE_STYLE}</td>
		<td class="fform"><input type="radio" name="type" VALUE='0' CHECKED></td>
	</tr>
	<tr>
		<td class="fcaption">{VAR:LC_STYLE_CELL_STYLE}</td>
		<td class="fform"><input type="radio" name="type" VALUE='1'></td>
	</tr>
<!--	<tr>
		<td class="fcaption">Elemendi stiil</td>
		<td class="fform"><input type="radio" name="type" VALUE='2'></td>
	</tr>-->
	<tr>
		<td class="fform" colspan=2><input type="submit" VALUE='{VAR:LC_STYLE_SAVE}' class='small_button'></td>
	</tr>
</table>
{VAR:reforb}
</form>
								