<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td align="left"><img src="{VAR:baseurl}/img/pealkiri_messenger.gif" align="" width="154" height="33" border="0" alt="Messenger"></td>

<td align="right" class="textpealkiri">Teate kirjutamine</td>
</tr></table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td bgcolor="#C8C8C8" align="left"><img src="{VAR:baseurl}/img/pealkiri_tyhi.gif" align="" width="54" height="4" border="0" alt=""></td></tr></table>

{VAR:menu}
<table border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="#DDDDDD">
<tr>
<td>
<table border="0" cellspacing="1" cellpadding="2" width="100%" bgcolor="#FFFFFF">
<tr>
	<td class="textsmall" bgcolor="#FFFFFF" colspan="3"><b>Kontaktid</b></td>
</tr>
<tr>
	<!--
	<td class="textsmall" bgcolor="#C3D0DC" align="center"> X </td>
	-->
	<td class="textsmall" bgcolor="#C3D0DC">Nimi</td>
	<td class="textsmall" bgcolor="#C3D0DC">E-mail</td>
	<td class="textsmall" bgcolor="#C3D0DC">Telefon</td>
</tr>
<!-- SUB: line -->
<tr>
	<!--
	<td class="textsmall" bgcolor="{VAR:color}" align="center"><input type="checkbox" name="check[{VAR:id}]" value="1"></td>
	-->
	<td class="textsmall" bgcolor="{VAR:color}"><a href="?class=contacts&action=edit&id={VAR:id}">{VAR:name}</a></td>
	<td class="textsmall" bgcolor="{VAR:color}">{VAR:email}</td>
	<td class="textsmall" bgcolor="{VAR:color}">{VAR:phone}</td>
</tr>
<!-- END SUB: line -->
<tr>
	<td colspan="3" class="textsmall">
	<!--
	<select name="group">
	{VAR:grouplist}
	</select>
	<input type="submit" value="Liiguta">
	-->
	</td>
</tr>
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
