<br>
<table width="400" border="0" cellspacing="0" cellpadding="1">
<form method="post" action="reforb.{VAR:ext}">
  <tr>
    <td class="title" colspan="2"><b>{VAR:LC_MSGBOARD_ADD_NEW_COMM}:</b></td>
  </tr>
  <tr>
    <td align="right" class="fgtitle">{VAR:LC_MSGBOARD_NAME}:</td>
    <td><input type="fgtext" size="40" NAME="name"></td>
  </tr>
  <tr>
    <td align="right" class="fgtitle">E-mail:</td>
    <td><input type="fgtext" size="40" NAME="email"></td>
  </tr>
  <tr>
    <td align="right" class="fgtitle">{VAR:LC_MSGBOARD_SUBJECT}:</td>
    <td><input type="fgtext" NAME="subj" VALUE='{VAR:subj}' size="40"></td>
  </tr>
  <tr>
    <td align="right" class="fgtitle" valign="top">{VAR:LC_MSGBOARD_COMMENTARY}:</td>
    <td class="fgtext"><textarea name="comment" cols="40" rows="6" wrap="virtual">{VAR:comment}</textarea></td>

  </tr>
  <tr>
    <td align="right" class="fgtitle" valign="top">&nbsp;</td>
    <td class="fgtitle"><b><input type="submit" class="textSmall" value="{VAR:LC_MSGBOARD_ADD_COMM}!" size="20"></b></td>
  </tr>
</table>
	{VAR:reforb}
</form>
