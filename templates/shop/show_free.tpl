<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
	<tr>
		<td class="fcaption2">Period start</td>
		<td class="fcaption2">Period end</td>
		<td class="fcaption2">Reserved</td>
		<td class="fcaption2">Free</td>
		<td class="fcaption2">View</td>
	</tr>

	<!-- SUB: LINE -->
	<tr>
		<td class="fcaption2">{VAR:period}</td>
		<td class="fcaption2">{VAR:period_end}</td>
		<td class="fcaption2">{VAR:num_sold}</td>
		<td class="fcaption2">{VAR:free}</td>
		<td class="fcaption2"><a href='{VAR:view}'>View reservations</a></td>
	</tr>
	<!-- END SUB: LINE -->
	<tr>
		<td colspan=2 class="fcaption2">Total reserved:</td>
		<td colspan=3 class="fcaption2">{VAR:t_sold}</td>
	</tr>
</table>
