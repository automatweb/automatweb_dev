<form enctype="multipart/form-data" method=POST action='reforb.{VAR:ext}'>
<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
<table border=0 cellspacing=1 cellpadding=2>
<tr>
	<td class="celltext">{VAR:LC_FILE_CHOOSE}:</td>
	<td class="celltext"><input type="file" class="formfile" size="40" name="file"></td>
</tr>
<tr>
	<td class="celltext">{VAR:LC_FILE_SIGN}</td>
	<td class="celltext"><input type="text" class="formtext" size="40" name="comment" value="{VAR:comment}"></td>
</tr>
<tr>
	<td class="celltext">{VAR:LC_FILE_NOW}?</td>
	<td class="celltext"><input type="checkbox" class="formtext" name="show" value=1 {VAR:checked}></td>
</tr>
<tr>
	<td class="celltext">{VAR:LC_FILE_NEW_WIN}?</td>
	<td class="celltext"><input type="checkbox" class="formtext" name="newwindow" value=1 {VAR:newwindow}></td>
</tr>
<tr>
	<td class="celltext">J&otilde;ustumise kuup&auml;ev:</td>
	<td class="celltext">{VAR:act_date}</td>
</tr>
<tr class="aste01">
	<td class="celltext">Avaldamise kuup&auml;ev:</td>
	<td class="celltext">{VAR:j_date}</td>
</tr>
<tr>
	<td class="celltext" colspan="2" align="center">
	{VAR:reforb}
	<input type="submit" value="{VAR:LC_FILE_ADD}">
	</td>
</tr>
</table>
</form>
