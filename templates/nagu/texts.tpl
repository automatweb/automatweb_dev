<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>{VAR:LC_NAGU_BIG_FACE}:&nbsp;<a href='nagu.{VAR:ext}?type=add&id={VAR:id}'>{VAR:LC_NAGU_ADD}</a> | <a href="nagu.{VAR:ext}?type=texts&id={VAR:id}">{VAR:LC_NAGU_TEXTS}</a> | <a href="nagu.{VAR:ext}?type=change_ooc&id={VAR:id}">{VAR:LC_NAGU_ACTIONS}</a>
</b></td>
</tr>
</table>
</td>
</tr>
</table>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<form action = 'refcheck.{VAR:ext}' method=post enctype="multipart/form-data">
<input type="hidden" NAME="MAX_FILE_SIZE" VALUE="100000">
<tr>
<td class="fcaption">{VAR:LC_NAGU_TEXT}:</td><td class="fform">({VAR:LC_NAGU_TEXT_INSIDE_NIMI_LAST_WINNER})<br><textarea name="text" cols=40 rows=10>{VAR:text}</textarea></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_NAGU_MIDDLE_TEXT}:</td><td class="fform"><textarea name="text3" cols=40 rows=10>{VAR:text3}</textarea></td>
</tr>
<tr>
<td class="fcaption">{VAR:LC_NAGU_LOWER_TEXT}:</td><td class="fform"><textarea name="text2" cols=40 rows=10>{VAR:text2}</textarea></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_NAGU_SAVE}'></td>
</tr>
<input type='hidden' NAME='action' VALUE='submit_nagu'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
<input type='hidden' NAME='type' VALUE='textonly'>
</form>
