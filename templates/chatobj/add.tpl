<form action='reforb.{VAR:ext}' method="POST">
<table>
	
	<tr>
		<td>Objekti nimi:</td>
		<td><input type='text' name='name' value='{VAR:name}'></td>
		<td><font color=red><b>*</b></font></td>
	</tr>
	<tr>
		<td colspan=3><hr size=1></td>
	</tr>
	<tr>
		<td>Serveri objekt:</td>
		<td><select type='text' name='serverObjId'>{VAR:servers}</select></td>
		<td><font color=red><b>*</b></font> Deemoni asukohta server.</td> 
	</tr>
	<tr>
		<td colspan=3><hr size=1></td>
	</tr>
	<tr>
		<td>Nupu text:</td>
		<td><input type='text' name='buttontext' value='{VAR:buttontext}'></td>
		<td><font color=red><b>&nbsp;</b></font> Sisesta siia teks, mida soovid näha lehele ilmuval nupul.</td>
	</tr>
	<tr>
		<td>Ikoon:</td>
		<td><input type='text' name='icon' value='{VAR:icon}'></td>
		<td><font color=red><b>&nbsp;</b></font> Sisesta siia ikooni url, mis tähistaks jutukat lehel.</td>
	</tr>
	<tr>
		<td>Mode:</td>
		<td><input type='text' name='mode' value='{VAR:mode}'></td>
		<td><font color=red><b>*</b></font> 0 - piirangud puuduvad, 1 - kanal + privad, 2 - priva, 4 - arco</td>
	</tr>
	<tr>
		<td>Kanal:</td>
		<td><input type='text' name='channel' value='{VAR:channel}'></td>
		<td><font color=red><b>&nbsp;</b></font> Kanal, millega automaatselt ühinetakse.</td>
	</tr>
	<tr>
		<td>Teade:</td>
		<td><input type='text' name='message' value='{VAR:message}'></td>
		<td><font color=red><b>&nbsp;</b></font> Teade, mis saadetakse jutuka käivitamisel.</td>
	</tr>
	<tr>
		<td>Privat:</td>
		<td><input type='text' name='privat' value='{VAR:privat}'></td>
		<td><font color=red><b>&nbsp;</b></font> Jutuka käivitamisel alustatakse automaatselt seda priva.</td>
	</tr>
	<tr>
		<td colspan=3><hr size=1></td>
	</tr>
	<tr>
		<td>Akna värv:</td>
		<td><input type='text' name='windowcolor' value='{VAR:windowcolor}'></td>
		<td><font color=red><b>&nbsp;</b></font></td>
	</tr>
	<tr>
		<td>Tausta värv:</td>
		<td><input type='text' name='backcolor' value='{VAR:backcolor}'></td>
		<td><font color=red><b>&nbsp;</b></font></td>
	</tr>
	<tr>
		<td>Teksti värv:</td>
		<td><input type='text' name='textcolor' value='{VAR:textcolor}'></td>
		<td><font color=red><b>&nbsp;</b></font></td>
	</tr>
	<tr>
		<td>Nupu värv:</td>
		<td><input type='text' name='buttoncolor' value='{VAR:buttoncolor}'></td>
		<td><font color=red><b>&nbsp;</b></font></td>
	</tr>
	<tr>
		<td colspan=3><hr size=1></td>
	</tr>
	<tr>
		<td colspan=2><input type='Submit' value='Salvesta &raquo;'></td>
		<td><font color=red><b>&nbsp;</b></font></td>
	</tr>
</table>
{VAR:reforb}
</form>
