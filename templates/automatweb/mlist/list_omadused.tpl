{VAR:toolbar}
<table cellpadding=0 cellspacing=0 border=0>
<form action = 'reforb.{VAR:ext}' method=post name="foo">
<tr><td width=100%>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0 width=100%>
<tr>
<td class="fcaption2">Nimi:</td><td class="fform" colspan="2"><input type='text' class='small_button' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption2">Kommentaar:</td><td class="fform" colspan="2"><input type='text' class='small_button' NAME='comment' VALUE='{VAR:comment}'></td>
</tr>










<tr>
	<td class="fform">Vali konfiguratsioon:</td>
	<td colspan="2" class="fform"><select NAME='user_form_conf' class="formselect">{VAR:ufc}</select></td>
</tr>
<tr>
	<td class="title" colspan="3">Muutujad</td></tr>
<!-- SUB: variable -->
<tr height="10"><td class="fform">{VAR:name}</td>
<td class="fform"><input type='checkbox' name="vars[]" value="{VAR:vid}" {VAR:checked}></td>
<td class="fform"><a href="{VAR:l_acl}">{VAR:acl}</a></td>
</tr>
<!-- END SUB: variable -->
</tr>
<tr>
	<td colspan="3" class="fform">Vali kataloogid, kust alt v&otilde;etakse listi liikmed:</td>
</tr>
<tr>
	<td colspan="3" class="fform"><select NAME='user_folders[]' multiple size="20" class="formselect">{VAR:user_folders}</select></td>
</tr>
<tr>
	<td colspan="3" class="fform">Vali kataloog kuhu pannakse automaatselt lisatud liikmed:</td>
</tr>
<tr>
	<td colspan="3" class="fform"><select NAME='def_user_folder' class="formselect">{VAR:def_user_folder}</select></td>
</tr>
<tr>
	<td colspan="2" class="fform">Vali form, mille sisestustest tehakse automaatselt liikmed:</td>
	<td class="fform"><select name="automatic_form" class="small_button">{VAR:automatic_form}</select></td>
</tr>

</table>
</td></tr>
</table>
{VAR:reforb}
</form>
