<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td align="left"><img src="{VAR:baseurl}/automatweb/images/pealkiri_messenger.gif" align="" width="154" height="33" border="0" alt="Messenger"></td>

<td align="right" class="textpealkiri">Kontaktigrupid</td>
</tr></table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td bgcolor="#C8C8C8" align="left"><img src="{VAR:baseurl}/automatweb/images/pealkiri_tyhi.gif" align="" width="54" height="4" border="0" alt=""></td></tr></table>

{VAR:menu}
<br>
<table border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="#DDDDDD">
<tr>
<td>
<table border="0" cellspacing="1" cellpadding="2" width="100%" bgcolor="#FFFFFF">
<tr>
	<td class="textsmall" bgcolor="#FFFFFF" colspan="4"><b><a href="?class=messenger&action=contacts">Grupid:</a></b> {VAR:fullpath}</td>
</tr>
<tr>
	<td class="textsmall" bgcolor="#C3D0DC" align="center" width="5%">#</td>
	<td class="textsmall" bgcolor="#C3D0DC" width="70%">Nimi</td>
	<td class="textsmall" bgcolor="#C3D0DC" width="15">Tegevus</td>
	<td class="textsmall" bgcolor="#C3D0DC">Liikmeid</td>
</tr>
<!-- SUB: gline -->
<tr>
	<td class="textsmall" bgcolor="{VAR:color}">{VAR:jrk}</td>
	<td class="textsmall" bgcolor="{VAR:color}"><a href="?class=contacts&action=groups&parent={VAR:id}">{VAR:name}</a></td>
	<td class="textsmall" bgcolor="{VAR:color}"><a href="?class=contacts&action=edit_group&id={VAR:id}">Muuda</a></td>
	<td class="textsmall" bgcolor="{VAR:color}"><a href="?class=contacts&folder={VAR:id}">Liikmed</a></td>
</tr>
<!-- END SUB: gline -->
</table>
</td>
</tr>
</table>
