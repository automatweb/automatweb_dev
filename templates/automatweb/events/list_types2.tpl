<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width="500">
<tr>
	<td class="fgtitle" colspan="4">
	<strong>{VAR:caption}</strong><b>
	<a href="orb.{VAR:ext}?class=events&action=add_type&type={VAR:type}">Lisa uus</a></b>
	</td>
</tr>
<tr>
	<td class="title" align="center">Nimi</td>
	<td class="title" align="center" colspan="3">Tegevus</td>
</tr>
<!-- SUB: line -->
<tr>
	<td class="fgtext">
	{VAR:name}
	<!-- SUB: active -->
	<strong><font color="red">{VAR:name}</font></strong>
	<!-- END SUB: active -->
	<!-- SUB: plain -->
	<strong><a href="{VAR:link}">{VAR:name}</a></strong>
	<!-- END SUB: plain -->
	</td>
	<td align="center" class="fgtext2"><a href="orb.{VAR:ext}?class=events&action=edit_type&id={VAR:id}">Muuda</a></td>
	<td align="center" class="fgtext2"><a href="orb.{VAR:ext}?class=events&action=add_type&type={VAR:type}&parent={VAR:id}">Lisa alamtüüp</a></td>
	<td align="center" class="fgtext2"><a href="#">Kustuta</a></td>
</tr>
<!-- END SUB: line -->
</table>
</td>
</tr>
</table>
