<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type="text" NAME='name' SIZE=40 class='small_button' value="{VAR:name}"></td>
</tr>
<tr>
<td class="fcaption">Menüüd:</td><td class="fform"><select name="menus[]" size="30" multiple>{VAR:menus}</select></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input type='submit' VALUE='Salvesta' CLASS="small_button"></td>
</tr>
</table>
{VAR:reforb}
</form>
