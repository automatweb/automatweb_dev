<form method="POST" action=doclist.{VAR:ext}>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
<td class="fcaption">
Pealkiri:
</td>
<td class="fform">
<input type="text" name="name" size="40">
</td>
</tr>
<tr>
<td class="fcaption">
Periood:
</td>
<td class="fform">
{VAR:pername}
</td>
</tr>
<tr>
<td class="fcaption">
Sektsioon:
</td>
<td class="fform"><select name='parent'>{VAR:section}</select></td>
</tr>
<tr>
<td class="fform" colspan="2" align="center">
<input type="submit" value="Dokumendi muutmine &gt;&gt;">
<input type="hidden" name="action" value="editdoc">
<input type="hidden" name="period" value="{VAR:period}">
</td>
</tr>
</table>
</form>
