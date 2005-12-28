<form method="POST">
<table border="0">
<tr>
<td>
<span class=text>Nimi</span>
</td>
<td>
<input type="text" name="name" size="12">
</td>
</tr>
<tr>
<td nowrap>
<span class=text>E-mail</span>
</td>
<td>
<input type="text" name="email" size="12">
</td>
</tr>
<!-- SUB: FOLDER -->
<tr>
<td colspan=2><input type="checkbox" name="subscr_folder[{VAR:folder_id}]" value="1" />&nbsp;<span class=text>{VAR:folder_name}</span></td>
</tr>
<!-- END SUB: FOLDER -->
<tr><td> </td></tr><tr>
<!-- SUB: LANGFOLDER -->
<td colspan=2>
<input type="checkbox" name="subscr_lang[{VAR:lang_id}]" value="1" />&nbsp;<span class=text>{VAR:lang_name}</span></td>
</tr>
<!-- END SUB: LANGFOLDER -->
<tr>
<td colspan="2">
<input type="submit" value="Liitun">
</td>
</tr>
<input type="hidden" name="op" value="1">
{VAR:reforb}
</form>
</table>
