<br>
<form method="GET" action="pickobject.{VAR:ext}">
<table border="0" cellspacing="0" cellpadding="0" >
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
	<td colspan=2 class="title">Otsi objekte | <a href='pickobject.{VAR:ext}?docid={VAR:docid}'>Objektide nimekiri</a></td>
</tr>
<tr>
	<td class="fcaption2">Otsi nimest:</td>
	<td class="fform" width=70%><input type="text" name="s_name" size="40" value='{VAR:s_name}'></td>
</tr>
<tr>
	<td class="fcaption2">Otsi kommentaarist:</td>
	<td class="fform" width=70%><input type="text" name="s_comment" size="40" value='{VAR:s_comment}'></td>
</tr>
<tr>
	<td class="fcaption2">Objekti t&uuml;&uuml;p:</td>
	<td class="fform" width=70%><select name='s_type'>{VAR:types}</select></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Otsi"></td>
</tr>
</table>
<table border="0" cellspacing="1" cellpadding="2"  width=100%>
<tr>
	<td class="fcaption2" colspan=8>Leitud dokumendid:</td>
</tr>
<tr>
	<td class="fcaption2">Nimi</td>
	<td class="fcaption2" nowrap>&nbsp;T&uuml;&uuml;p&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Loodud&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Looja&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Muudetud&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Muutja&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Parent&nbsp;</td>
	<td class="fform" nowrap>&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td class="fcaption2">{VAR:name}</td>
	<td class="fcaption2" nowrap>{VAR:type}</td>
	<td class="fcaption2" nowrap>{VAR:created}</td>
	<td class="fcaption2" nowrap>{VAR:createdby}</td>
	<td class="fcaption2" nowrap>{VAR:modified}</td>
	<td class="fcaption2" nowrap>{VAR:modifiedby}</td>
	<td class="fcaption2" nowrap>{VAR:parent_name}</td>
	<td class="fform" nowrap>{VAR:pickurl}</td>
</tr>
<!-- END SUB: LINE -->
</table>
<input type='hidden' name='docid' value='{VAR:docid}'>
<input type='hidden' name='type' value='search'>
</form>
