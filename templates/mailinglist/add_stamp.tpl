<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_NAME}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:stamp_name}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_TEXT}:</td><td class="fform"><textarea cols=70 ROWS=10 NAME='value'>{VAR:stamp_value}</textarea></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' VALUE='{VAR:LC_MAILINGLIST_SAVE}' CLASS="small_button"></td>
</tr>
</table>
{VAR:reforb}
</form>
