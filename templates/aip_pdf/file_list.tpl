<form name='q' method="POST" action='reforb.{VAR:ext}'>
{VAR:header}

<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#FFFFFF">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
	<tr class="aste05">
		<td class="celltext">Kataloog serveris:</td>
		<td colspan="2" class="celltext"><input type='text' class='formtext' name='folder' value='{VAR:folder}'></td>
	</tr>
	<tr class="aste05">
		<td class="celltext">Root kataloog AW's:</td>
		<td colspan="2" class="celltext"><select class='formselect' name='parent'>{VAR:folders}</select></td>
	</tr>
	<tr class="aste05">
		<td class="celltext" >Fail </td>
		<td colspan="2" class="celltext" >Staatus</td>
	</tr>
	<!-- SUB: FILE -->
	<tr class="aste05">
		<td class="celltext" >{VAR:file}</td>
		<td colspan="2" class="celltext" >{VAR:file_status}</td>
	</tr>
	<!-- END SUB: FILE -->
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr class="aste05">
		<td colspan="3" class="celltext">S&uuml;nkroniseerimiseks on vaja teha j&auml;rgmised muudatused:</td>
	</tr>
	<!-- SUB: CHANGE -->
	<tr class="aste05">
		<td class="celltext">{VAR:file}</td>
		<td class="celltext">{VAR:action}</td>
		<td class="celltext"><input type='checkbox' name='sactions[]' value='{VAR:action_id}' checked></td>
	</tr>
	<!-- END SUB: CHANGE -->
	<tr class="aste05">
		<td colspan="2" class="celltext">Kas teostame valitud muudatused?</td>
		<td class="celltext"><input type='checkbox' name='do_actions' value='1'></td>
	</tr>
</table>

</td>
</tr>
</table>
{VAR:reforb}
</form>