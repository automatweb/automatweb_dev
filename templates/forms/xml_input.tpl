<form action='reforb.{VAR:ext}' METHOD=post>
<!-- SUB: admin -->
<a href="{VAR:adminurl}">Administreeri</a>
<!-- END SUB: admin -->
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Alias:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">Kommentaar:</td><td class="fform"><textarea cols=50 rows=5 NAME=comment>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fcaption">Vali vormid:</td><td class="fform"><select name='forms[]' size="20" multiple>{VAR:forms}</select></td>
</tr>
<tr>
<td colspan=2 class="fcaption"><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
{VAR:reforb}
</form>
