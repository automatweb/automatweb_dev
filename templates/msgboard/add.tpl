<br>
<table width="400" border="0" cellspacing="0" cellpadding="1">
<form method="post" action="reforb.{VAR:ext}">
  <tr>
    <td class="header3" colspan="2"><b>{VAR:LC_MSGBOARD_ADD_NEW_COMM}:</b></td>
  </tr>
  <tr>
    <td align="right" class="header4">{VAR:LC_MSGBOARD_NAME}:</td>
    <td><input type="text" size="40" NAME="name"></td>
  </tr>
  <tr>
    <td align="right" class="header4">E-mail:</td>
    <td><input type="text" size="40" NAME="email"></td>
  </tr>
  <tr>
    <td align="right" class="header4">{VAR:LC_MSGBOARD_SUBJECT}:</td>
    <td><input type="text" NAME="subj" VALUE='{VAR:subj}' size="40"></td>
  </tr>
  <tr>
    <td align="right" class="header4" valign="top">{VAR:LC_MSGBOARD_COMMENTARY}:</td>
    <td class="text"><textarea name="comment" cols="40" rows="6" wrap="virtual">{VAR:comment}</textarea></td>

  </tr>
  <tr>
    <td align="right" class="header4" valign="top">&nbsp;</td>
    <td class="header4"><b><input type="submit" class="header4" value="{VAR:LC_MSGBOARD_ADD_COMM}!" size="20"></b></td>
  </tr>
</table>
	{VAR:reforb}
</form>
