<pre>
Tellimus nr: {VAR:id}
Tellija    : {VAR:person_name} / {VAR:company_name}</pre>

<table border="0">
	<tr>
		<td><pre>Toode</pre></td>
		<td><pre>Kogus</pre></td>
		<td><pre>Hind</pre></td>
	</tr>
	<tr>
		<td colspan="3"><pre>-------------------------------------------</pre></td>
	</tr>
	<!-- SUB: PROD -->
	<tr>
		<td><pre>{VAR:name}</pre></td>
		<td><pre>{VAR:quant}</pre></td>
		<td><pre>{VAR:price}</pre></td>
	</tr>
	<!-- END SUB: PROD -->
	<tr>
		<td colspan="3"><pre>-------------------------------------------</pre></td>
	</tr>
	<tr>
		<td colspan="2" align="right"><pre>Kokku:</pre></td>
		<td><pre>{VAR:total}</pre></td>
	</tr>
</table>
