<form action='/refcheck.{VAR:ext}' method=post>
<font color="red">{VAR:error}</font>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">New password:</td><td class="fform"><input type='password' NAME='pwd' ></td>
</tr>
<tr>
<td class="fcaption">New password again:</td><td class="fform"><input type='password' NAME='pwd2' ></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Change'></td>
</tr>
</table>
<input type="hidden" name="action" value="submit_change_pwd">
</form>
