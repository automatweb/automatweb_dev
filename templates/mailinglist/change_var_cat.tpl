<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_NAME}:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_COMMENTARY}:</td><td class="fform"><textarea NAME='comment' cols=50 rows=5>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_CREATED}:</td><td class="fform">{VAR:created}, {VAR:createdby}</td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_MAILINGLIST_CHANGED}:</td><td class="fform">{VAR:modified}, {VAR:modifiedby}</td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_MAILINGLIST_SAVE}'></td>
</tr>
</table>
{VAR:reforb}
</form>
