<br>
<form method="POST" action="orb.{VAR:ext}">
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
	<td class="fform" width=70%><select class='small_button' name='s[parent]'>{VAR:parents}</select></td>
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
	<td class="fcaption2" colspan=10>Leitud objektid:</td>
</tr>
<tr>
	<td class="fcaption2" >ID</td>
	<td class="fcaption2" >Nimi</td>
	<td class="fcaption2" nowrap>&nbsp;T&uuml;&uuml;p&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Loodud&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Looja&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Muudetud&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Muutja&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Parent&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Vali&nbsp;</td>
	<td class="fcaption2" nowrap>&nbsp;Muuda&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td class="fcaption2" >{VAR:oid}</td>
	<td class="fcaption2" ><input type='text' class='tekstikast_n' name='text[{VAR:oid}]' value='{VAR:name}'><input type='hidden' name='old_text[{VAR:oid}]' value='{VAR:name}'><input type='hidden' name='class_id[{VAR:oid}]' value='{VAR:class_id}'></td>
	<td class="fcaption2" >{VAR:type}</td>
	<td class="fcaption2" nowrap>{VAR:created}</td>
	<td class="fcaption2" >{VAR:createdby}</td>
	<td class="fcaption2" nowrap>{VAR:modified}</td>
	<td class="fcaption2" >{VAR:modifiedby}</td>
	<td class="fcaption2" >{VAR:parent_parent_parent_name} / {VAR:parent_parent_name} / {VAR:parent_name}</td>
	<td class="fcaption2" ><input type='checkbox' name='sel[{VAR:oid}]' value=1></td>
	<td class="fcaption2" ><a href='{VAR:change}'>Muuda</a></td>
</tr>
<!-- END SUB: LINE -->
<tr>
	<td class='fcaption2'>Vali kuhu liigutada:</td>
	<td class='fcaption2' colspan=9><select name='moveto' class='small_button'>{VAR:moveto}</select></td>
</tr>
<tr>
	<td class='fcaption2' colspan=2><input class='small_button' type='submit' value='Salvesta'></td>
	<td class='fcaption2' colspan=10><input class='small_button' type='submit' value='Kustuta' name="delete"></td>
</tr>
</table>
{VAR:reforb}
</form>
