<form method="POST" action="orb.{VAR:ext}" name="aa">
<table border=0 cellpadding=2 cellspacing=1>
<tr>
	<td align=center>


<table border=0 cellspacing=1 cellpadding=1>
<tr class="aste05">
	<td colspan=2 class="celltext">Otsi objekte</td>
</tr>
<tr>
	<td class="celltext">Otsi nimest:</td>
	<td class="celltext" width=70%><input class="formtext" type="text" name="s[name]" size="40" value='{VAR:s_name}'></td>
</tr>
<tr>
	<td class="celltext">Otsi kommentaarist:</td>
	<td class="celltext" width=70%><input type="text" class="formtext" name="s[comment]" size="40" value='{VAR:s_comment}'></td>
</tr>
<tr>
	<td class="celltext">Objekti t&uuml;&uuml;p:</td>
	<td class="celltext" width=70%><select class="small_button" name='s[class_id]'>{VAR:types}</select></td>
</tr>
<tr>
	<td class="celltext">Mis men&uuml;&uuml; all objekt on:</td>
	<td class="celltext" width=70%><select class="small_button" name='s[parent]'>{VAR:parents}</select></td>
</tr>
<tr>
	<td class="celltext">Kelle poolt loodud:</td>
	<td class="celltext" width=70%><select class="small_button" name='s[createdby]'>{VAR:createdby}</select></td>
</tr>
<tr>
	<td class="celltext">Kelle poolt muudetud:</td>
	<td class="celltext" width=70%><select class="small_button" name='s[modifiedby]'>{VAR:modifiedby}</select></td>
</tr>
<tr>
	<td class="celltext">Aktiivne?</td>
	<td class="celltext" width=70%><input type='checkbox' name='s[active]' value=1 {VAR:active}></td>
</tr>
<tr>
	<td class="celltext">Alias:</td>
	<td class="celltext" width=70%><input class="formtext" type='text' name='s[alias]' value="{VAR:alias}"></td>
</tr>
<tr>
	<td class="celltext" colspan="2" align="center">
		<input type="submit" value="Otsi">
		<input type="submit" name="attach" value="Lisa valitud objektid & sulge aken">
	</td>
</tr>
</table>
<!-- SUB: FOUND -->
<table border="0" cellspacing="1" cellpadding="2"  width=100%>
<tr class="aste05">
	<td class="celltext" colspan=10>Leitud objektid:</td>
</tr>
<tr class="aste05">
	<td class="celltext" >ID</td>
	<td class="celltext" >Nimi</td>
	<td class="celltext" nowrap>&nbsp;T&uuml;&uuml;p&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;Loodud&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;Looja&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;Muudetud&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;Muutja&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;Parent&nbsp;</td>
	<td class="celltext" nowrap>&nbsp;Vali&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td class="celltext" >{VAR:oid}</td>
	<td class="celltext" >{VAR:name}</td>
	<td class="celltext" >{VAR:type}</td>
	<td class="celltext" nowrap>{VAR:created}</td>
	<td class="celltext" >{VAR:createdby}</td>
	<td class="celltext" nowrap>{VAR:modified}</td>
	<td class="celltext" >{VAR:modifiedby}</td>
	<td class="celltext" >{VAR:parent_parent_parent_name} / {VAR:parent_parent_name} / {VAR:parent_name}</td>
	<td class="celltext" ><input type='checkbox' name='sel[{VAR:oid}]' value=1></td>
</tr>
<!-- END SUB: LINE -->
</table>
<!-- END SUB: FOUND -->
{VAR:reforb}
</form>
