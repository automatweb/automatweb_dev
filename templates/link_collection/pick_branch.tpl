<form method="POST">
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td colspan="2" class="fcaption2">
		<b>Step 2 of 2: Vali lingikogu oks
	</td>
</tr>
<tr>
	<td class="fcaption2">
		Aliase nimi:
	</td>
	<td class="fcaption2">
		<input type="text" name="name" value="{VAR:name}" size="30">
	</td>
</tr>
<tr>
	<td class="fcaption2">
		Aliase kommentaar:
	</td>
	<td class="fcaption2">
		<input type="text" name="comment" value="{VAR:comment}" size="30">
	</td>
</tr>
<!-- SUB: line -->
<tr>
	<td class="fcaption2">
		<input type="radio" name="branch" value="{VAR:key}" {VAR:checked}>
	</td>
	<td class="fcaption2">
		{VAR:value}
	</td>
</tr>
<!-- END SUB: line -->
<tr>
	<td class="fform" align="center" colspan="2">
		<input type="submit" value="Salvesta">
		{VAR:reforb}
	</td>
</tr>
</table>
</form>
