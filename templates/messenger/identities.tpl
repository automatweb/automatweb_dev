<div class="text">
<b>Identity manager</b>
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
Nimi
</td>
<td class="textsmallbold" align="center" bgcolor="#C3D0DC">
Aadress
</td>
<td class="textsmallbold" align="center" bgcolor="#C3D0DC">
Tegevus
</td>
</tr>
<!-- SUB: line -->
<tr>
	<td class="lefttab" align="center">
		<input type="checkbox" name="default_acc[{VAR:id}]" value="1" {VAR:checked}>
	</td>
	<td class="textsmall">
		{VAR:name} {VAR:surname}
	</td>
	<td class="textsmall" align="center">
		{VAR:email}
	</td>
	<td class="textsmall" align="center">
		<a href="/?class=messenger&action=edit_identity&id={VAR:id}">Muuda</a>
	</td>
</tr>
<!-- END SUB: line -->
</table>
</td>
</tr>
</table>


