<form action='refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:stamp_name}'></td>
</tr>
<tr>
<td class="fcaption">Tekst:</td><td class="fform"><textarea cols=70 ROWS=10 NAME='value'>{VAR:stamp_value}</textarea></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' VALUE='Salvesta' CLASS="small_button"></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='admin_stamp'>
<input type='hidden' NAME='id' VALUE='{VAR:stamp_id}'>
</form>
