<form action='reforb.{VAR:ext}' method=post >
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td colspan=2 class="fcaption"><input type='radio' name='act' value='new' checked>&nbsp;Lisa uus grupp:</td>
</tr>
<tr>
<td class="fcaption">Nimi:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td colspan=2 class="fcaption"><input type='radio' name='act' value='ag'>&nbsp;Lisa olemasolevasse gruppi:</td>
</tr>
<tr>
<td class="fcaption">Grupp:</td><td class="fform"><select name='grp'>{VAR:grps}</select></td>
</tr>
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
</table>
{VAR:reforb}
</form>
