<!-- seda kasutatakse olemasolevate signatuuride kuvamiseks, ning kustutamisvoimaluse andmiseks -->
<table border="0" cellspacing="1" cellpadding="2" width="100%">
<tr>
<td bgcolor="#C3D0DC" class="textsmall" align="center"><b>X</b></td>
<td bgcolor="#C3D0DC" class="textsmall" align="center"><b>Nimi</b></td>
<td bgcolor="#C3D0DC" class="textsmall" align="center"><b>Sisu</b></td>
<td bgcolor="#C3D0DC" class="textsmall" align="center"><b>Def.</b></td>
<td bgcolor="#C3D0DC" class="textsmall" align="center"><b>Tegevus</b></td>
</tr>
<!-- SUB: sig -->
<tr>
	<td class="textsmall" align="center"><input type="checkbox" name="delsig[{VAR:signum}]" value="1"></td>
	<td class="textsmall">{VAR:signame}</td>
	<td class="textsmall">{VAR:signature}</td>
	<td class="textsmall" align="center"><input type="radio" name="defsig" value="{VAR:signum}" {VAR:default}></td>
	<td class="textsmall"><a href="{VAR:edit}">Muuda</a></td>
</tr>
<!-- END SUB: sig -->
</table>
