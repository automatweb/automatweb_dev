<form action='reforb.{VAR:ext}' method=post enctype="multipart/form-data">
<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">{VAR:LC_FORMS_FILE} (.csv):</td><td class="fform"><input type='file' NAME='file'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Uploadi'></td>
</tr>
</table>
{VAR:reforb}
</form>
