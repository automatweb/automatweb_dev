<form method="POST" action="reforb.{VAR:ext}">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="title" colspan="2">
		<strong>{VAR:caption}</strong>
	</td>
</tr>
<tr>
	<td class="fform">ID</td>
	<td class="fform"><strong>{VAR:id}</td>
</tr>
<!-- SUB: parent -->
<tr>
	<td class="fform">Vanem</td>
	<td class="fform"><strong>{VAR:parentname}</td>
</tr>
<!-- END SUB: parent -->

<tr>
	<td class="fcaption2">Nimi</td>
	<td class="fform"><input type="text" name="name" size="40" value="{VAR:name}"></td>
</tr>

<tr>
<td class="fform" colspan="2">
<input type="submit" value="Salvesta">
{VAR:reforb}
</td>
</tr>
</table>
</form>
