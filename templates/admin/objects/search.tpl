<br>
<form method="GET" action="orb.{VAR:ext}">
<table border="0" cellspacing="0" cellpadding="0" >
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
	<td colspan=2 class="title">Otsi objekte</td>
</tr>
<tr>
	<td class="fcaption2">Otsi nimest:</td>
	<td class="fform" width=70%><input type="text" name="s[name]" size="40" value='{VAR:s_name}'></td>
</tr>
<tr>
	<td class="fcaption2">Otsi kommentaarist:</td>
	<td class="fform" width=70%><input type="text" name="s[comment]" size="40" value='{VAR:s_comment}'></td>
</tr>
<tr>
	<td class="fcaption2">Objekti t&uuml;&uuml;p:</td>
	<td class="fform" width=70%><select name='s[class_id]'>{VAR:types}</select></td>
</tr>
<tr>
	<td class="fcaption2">Mis men&uuml;&uuml; all objekt on:</td>
	<td class="fform" width=70%><select name='s[parent]'>{VAR:parents}</select></td>
</tr>
<tr>
	<td class="fcaption2">Kelle poolt loodud:</td>
	<td class="fform" width=70%><select name='s[createdby]'>{VAR:createdby}</select></td>
</tr>
<tr>
	<td class="fcaption2">Kelle poolt muudetud:</td>
	<td class="fform" width=70%><select name='s[modifiedby]'>{VAR:modifiedby}</select></td>
</tr>
<tr>
	<td class="fcaption2">Aktiivne?</td>
	<td class="fform" width=70%><input type='checkbox' name='s[active]' value=1 {VAR:active}></td>
</tr>
<tr>
	<td class="fcaption2">Alias:</td>
	<td class="fform" width=70%><input type='text' name='s[alias]' value="{VAR:alias}"></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Otsi"></td>
</tr>
</table>
<table border="0" cellspacing="1" cellpadding="2"  width=100%>
<tr>
	<td class="fcaption2" colspan=3>Leitud objektid:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Nimi</td>
	<td class="fcaption2" nowrap>&nbsp;T&uuml;&uuml;p&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td class="fcaption2" colspan=2><a href='{VAR:change}'>{VAR:name}</a></td>
	<td class="fcaption2" nowrap>{VAR:type}</td>
</tr>
<!-- END SUB: LINE -->
</table>
{VAR:reforb}
</form>
