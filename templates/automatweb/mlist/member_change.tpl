{VAR:editform}
<form action="reforb.aw" method="POST">
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="title">Listid:</td></tr>
<tr>
<td class="fcaption2"><select multiple class='small_button' name='lists[]'">
{VAR:listsel}
</select></td></tr>
<tr>
<td class="fcaption2" colspan=2>
<input class='small_button' type='submit' VALUE='Salvesta'>
</td></tr>
<tr><td class="fcaption2"><a href="{VAR:l_sent}">Saadetud meilid</a></td></tr>
</table>
{VAR:reforb}
</form>