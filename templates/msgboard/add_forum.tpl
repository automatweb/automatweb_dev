<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_MSGBOARD_NAME}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MSGBOARD_COMMENTARY}:</td><td class="fform"><input type='text' NAME='comment' VALUE='{VAR:comment}'></td>
</tr>
<!-- SUB: CHANGE -->
<tr>
<td class="fcaption">Foorum URL:</td><td class="fform">{VAR:url}</td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
<td class="fcaption">Kommenteeritav:</td><td class="fform"><input type="checkbox" name="comments" value=1 {VAR:comments}></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' VALUE='{VAR:LC_MSGBOARD_SAVE}' CLASS="small_button"></td>
</tr>
</table>
{VAR:reforb}
</form>
