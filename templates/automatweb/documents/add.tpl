<form method="POST" action=doclist.{VAR:ext}>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
<td class="fcaption">
{VAR:LC_DOCUMENT_HEADLINE}:
</td>
<td class="fform">
<input type="text" name="name" size="40">
</td>
</tr>
<tr>
<td class="fcaption">
{VAR:LC_DOCUMENT_PERIOD}:
</td>
<td class="fform">
{VAR:pername}
</td>
</tr>
<tr>
<td class="fcaption">
{VAR:LC_DOCUMENT_SECTION}:
</td>
<td class="fform">
{VAR:section}
</td>
</tr>
<tr>
<td class="fform" colspan="2" align="center">
<input type="submit" value="{VAR:LC_DOCUMENT_EDITING} &gt;&gt;">
<input type="hidden" name="action" value="editdoc">
<input type="hidden" name="period" value="{VAR:period}">
<input type="hidden" name="parent" value="{VAR:parent}">
</td>
</tr>
</table>
</form>
