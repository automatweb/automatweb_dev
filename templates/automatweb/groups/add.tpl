<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Name:</td><td class="fform"><input type='text' NAME='name' ></td>
</tr>
<tr>
<td class="fcaption">Type:</td><td class="fform"><select name='type'><option value=0>Group<option value=2>Dyn. Group</select></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_group'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
<input type='hidden' NAME='grp_level' VALUE='{VAR:grp_level}'>
</form>
