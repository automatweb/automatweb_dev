<form action='reforb.{VAR:ext}' method=post name=ffrm>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform" colspan=2>Kataloog kuhu salvestatakse formi sisestatud info:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select name='ff_folder' class='small_button'>{VAR:ff_folder}</select></td>
</tr>
<tr>
<td class="fform" colspan=2>Vali kataloogid kuhu saab uusi elemente salvestada:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select class='small_button' NAME='el_menus[]' size=20 multiple>{VAR:el_menus}</select></td>
</tr>
<tr>
<td class="fform" colspan=2>Vali kataloogid kust elemendid v&otilde;etakse:</td>
</tr>
<tr>
<td colspan=2 class="fform"><select class='small_button' NAME='el_menus2[]' size=20 multiple>{VAR:el_menus2}</select></td>
</tr>
<tr>
<td class="fform" colspan=2><input class='small_button' type='submit' NAME='save_form_settings' VALUE='Salvesta form'></td>
</table>
{VAR:reforb}
</form>
  
