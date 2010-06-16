<form action="#">

	<table class="form">
		<tr>
			<th>{VAR:LC_BRONEERITUD}:</th>
			<td class="data">{VAR:room_name} - {VAR:time_str}</td>
		</tr>
		<tr>
			<th>{VAR:LC_KYLASTUSAEG}:</th>

			<td class="data">{VAR:hours} h</td>
		</tr>
		<tr>
			<th>{VAR:LC_KYLASTAJAID}:</th>
			<td class="data">{VAR:people_value}</td>
		</tr>
		<tr class="subheading">
			<th class="sauna" colspan="2">{VAR:LC_HIND}:</th>
		</tr>
		<tr>
			<th>{VAR:LC_IR_SAUN}:</th>
			<td class="data">{VAR:sum_wb}</td>
		</tr>
		<tr>
			<th>{VAR:LC_SOODUSTUS}:</th>
			<td class="data">{VAR:bargain}</td>
		</tr>
		<tr>
			<th>{VAR:LC_TASUDA}:</th>
			<td class="data bold red">{VAR:sum}</td>
		</tr>

		<tr class="subheading">
			<th class="sauna" colspan="2">{VAR:LC_ERISOOVID}:</th>
		</tr>
		<tr>
			<th></th>
			<td class="data">{VAR:comment_value}</td>
		</tr>
		<tr class="subheading">
			<th class="sauna" colspan="2">{VAR:LC_TELLIJA_ANDMED}:</th>
		</tr>
		<tr>
			<th>{VAR:LC_NIMI}:</th>
			<td class="data">{VAR:name_value}</td>
		</tr>
		<tr>

			<th>{VAR:LC_TELEFON}:</th>
			<td class="data">{VAR:phone_value}</td>
		</tr>
		<tr>
			<th>{VAR:LC_EMAIL}:</th>
			<td class="data">{VAR:email_value}</td>
		</tr>

		<tr>
			<th>{VAR:LC_PANK}:</th>
			<td class="data">{VAR:bank_value}</td>
		</tr>
	</table>
</form>
