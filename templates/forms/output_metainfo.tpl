<br>
<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td>
<td class="fform"><input type='text' NAME='name' VALUE='{VAR:op_name}'></td>
</tr>
<tr>
<td class="fcaption">Kommentaar:</td>
<td class="fform"><textarea NAME='comment' COLS=50 ROWS=5 wrap='soft'>{VAR:op_comment}</textarea></td>
</tr>
<tr>
<td class="fcaption">Loodud:</td>
<td class="fform">{VAR:created_by} @ {VAR:created}</td>
</tr>
<tr>
<td class="fcaption">Muudetud:</td>
<td class="fform">{VAR:modified_by} @ {VAR:modified}</td>
</tr>
<tr>
<td class="fcaption">Vaadatud:</td>
<td class="fform">{VAR:views} korda</td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
{VAR:reforb}
</form>
