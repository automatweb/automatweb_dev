<form action = 'reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption2">Nimi:</td><td class="fgtext"><input type='text' class='small_button' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption2">Kommentaar:</td><td class="fgtext"><input type='text' class='small_button' NAME='comment' VALUE='{VAR:comment}'></td>
</tr>
<tr>
	<td class="celltext">Vali konfiguratsioon:</td><td class="celltext"><select NAME='user_form_conf' class="formselect">{VAR:ufc}</select></td>
</tr>
<td class="fcaption2" colspan="2" align="right"><input class='small_button' type='submit' VALUE='Edasi >>'></td>
</tr>
</table>
{VAR:reforb}
</form>
