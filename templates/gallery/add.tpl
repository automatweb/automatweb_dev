<form action = 'refcheck.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">Kommentaar:</td><td class="fform"><input type='text' NAME='comment' VALUE='{VAR:comment}'></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
<input type='hidden' NAME='action' VALUE='submit_gallery'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
<input type='hidden' NAME='from' VALUE='{VAR:self}'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
<input type='hidden' NAME='alias_doc' VALUE='{VAR:alias_doc}'>
</form>
