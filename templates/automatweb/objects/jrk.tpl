<form method="POST">
<font color="red"><strong>{VAR:message}</strong></font>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="ftitle">#</td>
	<td class="ftitle">Jrk</td>
	<td class="ftitle">Nimi</td>
	<td class="ftitle">Tyyp</td>
	<td class="ftitle">Muudetud</td>
	<td class="ftitle">Muutis</td>
	<td class="ftitle">Staatus</td>
	<td class="ftitle">Hitte</td>
	<td class="ftitle" colspan="2">Tegevus</td>
</tr>
<!-- SUB: line -->
<tr>
	<td class="fcaption" align="right">{VAR:expandurl}</td>
	<td class="fform"><input type="text" size="2" name="jrk[{VAR:oid}]" value="{VAR:rec}"></td>
	<td class="fcaption">{VAR:name}</td>
	<td class="fcaption">{VAR:class}&nbsp;</td>
	<td class="fcaption">{VAR:modified}</td>
	<td class="fcaption">{VAR:modifiedby}&nbsp;</td>
	<td class="fcaption" align="center">{VAR:status}</td>
	<td class="fcaption" align="center">{VAR:hits}</td>
	<td class="fcaption" align="center"><a href="{VAR:modifier}?docid={VAR:oid}">Muuda</a></td>
	<td class="fcaption" align="center"><a href="editacl.{VAR:ext}?oid={VAR:oid}">ACL</a></td>
</tr>
<!-- END SUB: line -->
<tr>
	<td colspan="10" class="fcaption">
		Kokku: <font color='red'><b>{VAR:total}</b></font>
	</td>
</tr>
</table>
<input type="submit" value="Salvesta">
<input type="hidden" name="action" value="saveorder">
<input type="hidden" name="parent" value="{VAR:parent}">
</form>
<!-- SUB: active -->
<a href="javascript:box2('Deaktiveerida see objekt?','{VAR:self}?action=deactivate&oid={VAR:oid}&parent={VAR:parent}')"><font color="green"><b>Akt</b></font></a>
<!-- END SUB: active -->
<!-- SUB: deactive -->
<a href="javascript:box2('Aktiveerida see objekt?','{VAR:self}?action=activate&oid={VAR:oid}&parent={VAR:parent}')"><font color="red"><b>Deakt</b></font></a>
<!-- END SUB: deactive -->

