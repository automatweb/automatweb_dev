<form method="POST">
<table border="0">
<tr>
<td>
Name:
</td>
<td>
<input type="text" name="name" size="30">
</td>
</tr>
<tr>
<td>
E-mail
</td>
<td>
<input type="text" name="email" size="30">
</td>
</tr>
<!-- SUB: FOLDER -->
<tr>
<td>{VAR:folder_name}</td>
<td><input type="checkbox" name="subscr_folder[{VAR:folder_id}]" value="1" /></td>
</tr>
<!-- END SUB: FOLDER -->
<tr>
<td colspan="2">
<input type="submit" value="Subscribe">
</td>
</tr>
<input type="hidden" name="op" value="1">
{VAR:reforb}
</form>
</table>
