<table border="0" cellpadding=0 cellspacing=0 width="100%">
	<tr>
		<td align="center">
			Toote nimi: {VAR:prod_name}
		</td>
		<td align="center">
			Toote kaubakood: {VAR:prod_code}
		</td>
	</tr>
</table>
<br>

<table border="0" width="100%">
	<tr>
		<td>&nbsp;</td>
		<td >J&auml;&auml;k periooodi l&otilde;pus (kell 24:00)</td>
		<td >Sellest ettev&otilde;tte omandis olev</td>
	</tr>
	<!-- SUB: J_LINE -->
	<Tr>
		<td>{VAR:period}</td>
		<td >{VAR:j_amt} kg</td>
		<td >{VAR:o_amt} kg</td>
	</tr>
	<!-- END SUB: J_LINE -->
	<tr>
		<td colspan="2" align="right">Keskmine j&auml;&auml;k:</td>
		<td align="left">{VAR:avg_left} kg</td>
	</tr>
</table>
<br><br>
<table border="0" width="100%">
	<tr>
		<td>&nbsp;</td>
		<td>&Uuml;leliigsed varud:</td>
	</tr>
	<tr>
		<td>Varud 30.04.2004 seisuga (kell 24:00) -  keskmine j&auml;&auml;k x 1,1 =</td>
		<td align="right">{VAR:sp_avg_left} kg</td>
	</tr>
	<tr>
		<td>Sellest ettev&otilde;ttes hoiul olev</td>
		<td align="right">{VAR:sp_avg_com} kg</td>
	</tr>

	<tr>
		<td colspan="2">Varude suurenemise p&otilde;hjused:</td>
	</tr>

	<!-- SUB: D_LINE -->
	<tr>
		<td>{VAR:d_text}</td>
		<td align="right">{VAR:d_amt} kg</td>
	</tr>
	<!-- END SUB: D_LINE -->
</table>

<br>
Selgitused:<Br>
<table border="1" width="100%">
<tr><td>{VAR:desc}</td></tr>
</table><Br>

<table border="0" cellpadding=0 cellspacing=0 width="100%">
	<tr>
		<td align="center">
			Aruandva isiku allkiri: _______________________________
		</td>
		<td align="center">
			Kuup&auml;ev: {VAR:date}
		</td>
	</tr>
</table>
