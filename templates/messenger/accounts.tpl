<div class="text">
<b>Account manager</b>
</div>
<HR size="1" width="100%" color="#C8C8C8">
<form method="POST"  action='reforb.{VAR:ext}'>
<table border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="#EEEEEE">
<tr>
<td>
<table border=0 cellspacing=1 cellpadding=2 width="100%" bgcolor="#FFFFFF">
<tr>
<td class="textsmallbold" align="center"  bgcolor="#C3D0DC">
X
</td>
<td class="textsmallbold" align="center" bgcolor="#C3D0DC">
Def.
</td>
<td class="textsmallbold" align="center" bgcolor="#C3D0DC">
Nimi
</td>
<td class="textsmallbold" align="center" bgcolor="#C3D0DC">
Tüüp
</td>
<td class="textsmallbold" align="center" bgcolor="#C3D0DC" colspan="2">
Tegevus
</td>
</tr>
<!-- SUB: line -->
<tr>
	<td class="lefttab" align="center">
		<input type="checkbox" name="default_acc[{VAR:id}]" value="1" {VAR:checked}>
	</td>
	<td class="lefttab" align="center">
		<input type="radio" name="msg_default_account" value="{VAR:id}" {VAR:defcheck}>
	</td>
	<td class="textsmall">
		{VAR:name}
	</td>
	<td class="textsmall" align="center">
		{VAR:type}
	</td>
	<td class="textsmall" align="center">
		<a href="{VAR:change}">Muuda</a>
	</td>
	<td class="textsmall" align="center">
		<a href="{VAR:getmail}">Get mail</a>
	</td>
</tr>
<!-- END SUB: line -->
</table>
<span class="textsmall">
X-dega on märgitud see accoundid, millelt käiakse maili votmas, kui
vajutad "Get mail"
</span>
</td>
</tr>
</table>


