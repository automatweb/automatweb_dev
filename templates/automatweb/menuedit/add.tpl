<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">
	{VAR:LC_MENUEDIT_NAME}
</td>
<td class="fform">
	<input type='text' NAME='name' VALUE='{VAR:name}' size="50">
</td>
</tr>
<tr>
<td class="fcaption">
	Alias:
</td>
<td class="fform">
	<input type="text" name="alias" value="{VAR:alias}" size="50">
</td>
</tr>
<tr>
<td class="fcaption">
	{VAR:LC_MENUEDIT_CLASS}
</td>
<td class="fform">
	<select name="class_id">
	{VAR:class_select}
	</selecT>
</td>
<tr>
<td class="fcaption">
	{VAR:LC_MENUEDIT_COMMENT}
</td>
<td class="fform">
<textarea NAME='comment' cols=50 rows=5>
{VAR:comment}
</textarea>
</td>
</tr>
<tr>
<td class="fcaption" colspan=2>
<input class='small_button' type='submit' VALUE='{VAR:LC_MENUEDIT_SAVE}'>
</td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_menu'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
</form>
