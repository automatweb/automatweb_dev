<table border="0" cellspacing="1" cellpadding="2">
<form method="POST" action="reforb.{VAR:ext}">
<tr>
<td class="fgtitle" height="16"> 
<div align="left">{VAR:LC_MSGBOARD_SUBJECT}</div>
</td>
<td class="fgtext">
<input type="text" name="topic" size="40" value='{VAR:name}'>
</td>
</tr>
<tr>
<td valign="top" class="fgtitle">
{VAR:LC_MSGBOARD_COMMENTARY}
</td>
<td class="fgtext">
<textarea name="comment" cols="40" rows="5">{VAR:comment}</textarea>
</td>
</tr>
<tr>
<td class="fgtitle" colspan="2" align="center">
<input type="submit" value="{VAR:LC_MSGBOARD_SAVE}">
{VAR:reforb}
</td>
</tr>
</form>
</table>

