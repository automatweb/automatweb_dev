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
<td colspan="2" class="fform"><strong>Vormid</strong></td>
</tr>
<!-- SUB: line -->
<tr>
<td class="fform" align="center"><input type="radio" name="select" value="{VAR:oid}" {VAR:checked}></td>
<td class="fform">&nbsp;{VAR:ename}</td>
</tr>
<!-- END SUB: line -->
<tr>
<td colspan="2" class="fform"><strong>Vormipärjad</strong></td>
</tr>
<!-- SUB: line2 -->
<tr>
<td class="fform" align="center"><input type="radio" name="select" value="{VAR:oid}" {VAR:checked}></td>
<td class="fform">&nbsp;{VAR:ename}</td>
</tr>
<!-- END SUB: line2 -->
<tr>
<td colspan=2 class="fcaption"><input class='small_button' type='submit' VALUE='Salvesta'></td>
</tr>
</table>
{VAR:reforb}
</form>
