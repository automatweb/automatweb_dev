<form action='reforb.{VAR:ext}' METHOD=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fcaption">Alias:</td><td class="fform"><input type='text' NAME='name' VALUE='{VAR:name}'></td>
</tr>
<tr>
<td class="fcaption">Kommentaar:</td><td class="fform"><textarea cols=50 rows=5 NAME=comment>{VAR:comment}</textarea></td>
</tr>
<tr>
<td class="fcaption">Vali formid:</td><td class="fform"><select name='forms[]' multiple>{VAR:forms}</select></td>
</tr>
<!-- SUB: admin -->
<tr>
<td colspan=2 class="fcaption"><a href="{VAR:adminurl}">Koosta väljundit</a></td>
</tr>
<!-- END SUB: admin -->
<tr>
<td colspan=2 class="fcaption"><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
</table>
{VAR:reforb}
</form>
