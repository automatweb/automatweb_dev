<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<form action="reforb.aw" method="POST">

<!-- SUB: listrida -->
<tr>
<td class="fgtext2" colspan="2">{VAR:title}</td>
</tr>
<tr>
<td class="fcaption2">Millal saata:</td>
<td class="fgtext" >{VAR:date_edit}</td>
</tr>
<tr>
<td class="fcaption2">Mitu teadet korraga:</td>
<td class="fgtext" ><input type="text" name="patch_size" value="100" class='small_button'></td>
</tr>
<tr>
<td class="fcaption2">Saatmiste vahel oota (min):</td>
<td class="fgtext"><input type="text" name="delay" value="0" class='small_button'></td>
</tr>
<!-- END SUB: listrida -->

<tr>
<td class="fgtext2" colspan="3" align="right"><input class='small_button' type='submit' VALUE='Saada'></td></tr>
{VAR:reforb}
</form>
</table>
