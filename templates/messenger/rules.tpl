{VAR:menu}


<form method="POST" action='reforb.{VAR:ext}'>
<table border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="#EEEEEE">
<tr>
<td>
<table border=0 cellspacing=1 cellpadding=2 width="100%" bgcolor="#FFFFFF">
<tr>
<td class="textsmallbold" align="center"  bgcolor="#C3D0DC">
X
</td>
<td class="textsmallbold" align="center" bgcolor="#C3D0DC">
Väli
</td>
<td class="textsmallbold" align="center" bgcolor="#C3D0DC">
Sisaldab
</td>
<td class="textsmallbold" align="center" bgcolor="#C3D0DC">
Sihtkoht
</td>
<td class="textsmallbold" align="center" bgcolor="#C3D0DC">
Tegevus
</td>
</tr>
<!-- SUB: line -->
<tr>
	<td class="lefttab">
		<input type="checkbox" name="check[{VAR:id}]" value="1">
	</td>
	<td class="textsmall">
		{VAR:field}
	</td>
	<td class="textsmall">
		{VAR:rule}
	</td>
	<td class="textsmall">
		{VAR:endpoint}
	</td>
	<td class="textsmall" align="center">
	<a href="{VAR:edit}">Muuda</a>
	</td>
</tr>
<!-- END SUB: line -->
<tr>
	<td colspan="5" align="right" bgcolor="#F1F1F1">
		<input type="submit" value="Kustuta" class="formbutton">
		{VAR:reforb}
	</td>
</tr>
</table>
</td>
</tr>
</table>


