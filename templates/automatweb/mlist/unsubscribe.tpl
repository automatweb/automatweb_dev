<form method="POST">
<table border="0">
<tr>
<td nowrap>
<span class=text>E-mail</span>
</td>
<td>
<input type="text" name="email" size="30">
</td>
</tr>
<!-- SUB: FOLDER -->
<tr>
<td colspan=2><input type="checkbox" name="subscr_folder[{VAR:folder_id}]" value="1" />&nbsp;<span class=text>{VAR:folder_name}</span></td>
</tr>
<!-- END SUB: FOLDER -->
<tr><td> </td></tr><tr>

<tr>
<td colspan="2">
<input type="submit" value="Unsubscribe">
</td>
</tr>
<input type="hidden" name="op" value="2">
{VAR:reforb}
</form>
</table>
