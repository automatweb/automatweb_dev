<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
</td>
</tr>
</table>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<form action = 'refcheck.{VAR:ext}' method=post enctype="multipart/form-data">
<input type="hidden" NAME="MAX_FILE_SIZE" VALUE="100000">
<tr>
<td class="fcaption2">Name:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption2">URL to gallery:</td><td class="fform"><input type='text' NAME='url' VALUE='{VAR:url}'></td>
</tr>
<tr>
<td class="fcaption2">Category:</td><td class="fform"><select name="category">{VAR:categories}</select></td>
</tr>
<tr>
<td class="fcaption2">Upload picture:</td><td class="fform" valign=center><img src="{VAR:imgurl}"><Br><input type='file' NAME='img'></td>
</tr>
<tr>
<td class="fcaption2" colspan=2><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_nagu'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
</form>
