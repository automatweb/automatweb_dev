<table width="400" border="0" cellspacing="0" cellpadding="1">
<form method="post" action="refcheck.aw">
  <tr>
    <td align="right" class="textSmall">{VAR:LC_MSGBOARD_NAME}:</td>
    <td><input type="text" size="30" NAME="from"></td>
  </tr>
  <tr>
    <td align="right" class="textSmall">E-mail:</td>
    <td><input type="text" size="30" NAME="email"></td>
  </tr>
  <tr>
    <td align="right" class="textSmall">{VAR:LC_MSGBOARD_SUBJECT}:</td>
    <td><input type="text" NAME="subj" VALUE='{VAR:subj}' size="30"></td>
  </tr>
  <tr>
    <td align="right" class="textSmall" valign="top">{VAR:LC_MSGBOARD_COMMENTARY}:</td>
    <td><textarea name="comment" cols="30" rows="6" wrap="virtual">{VAR:comment}</textarea></td>

  </tr>
  <tr>
    <td align="right" class="textSmall" valign="top">&nbsp;</td>
    <td><b><input type="submit" class="textSmall" value="{VAR:LC_MSGBOARD_ADD_COMM}!" size="20"></b></td>
  </tr>
</table>
	<input type='hidden' NAME='action' VALUE='addcomment'>
	<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
	<input type='hidden' NAME='section' VALUE='{VAR:section}'>
	<input type='hidden' NAME='page' VALUE='{VAR:page}'>
</form>