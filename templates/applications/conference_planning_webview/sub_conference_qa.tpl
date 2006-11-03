<table>
	<tr>
		<td>
			{VAR:LC_SALUTATION}:
		</td>
		<td>
			<select name="sub[qa][salutation]">
				<option value="1">Mr</option>
				<option value="2">Mrs</option>
				<option value="3">Ms</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			{VAR:LC_FIRSTNAME}:
		</td>
		<td>
			<input name="sub[qa][firstname]" type="text"/>
		</td>
	</tr>
	<tr>
		<td>
			{VAR:LC_LASTNAME}:
		</td>
		<td>
			<input name="sub[qa][lastname]" type="text"/>
		</td>
	</tr>

	<tr>
		<td>
			{VAR:LC_COMPANY_ASSOCATION}:
		</td>
		<td>
			<input name="sub[qa][company_assocation]" type="text"/>
		</td>
	</tr>
	<tr>
		<td>
			{VAR:LC_TITLE}:
		</td>
		<td>
			<input name="sub[qa][title]" type="text"/>
		</td>
	</tr>
	<tr>
		<td>
			{VAR:LC_PHONE_NUMBER}:
		</td>
		<td>
			<input name="sub[qa][phone_number]" type="text"/>
		</td>
	</tr>
	<tr>
		<td>
			{VAR:LC_FAX_NUMBER}:
		</td>
		<td>
			<input name="sub[qa][fax_number]" type="text"/>
		</td>
	</tr>
	<tr>
		<td>
			{VAR:LC_EMAIL}:
		</td>
		<td>
			<input name="sub[qa][email]" type="text"/>
		</td>
	</tr>
	<tr>
		<td>
			{VAR:LC_CONTACT_PREFERENCE}:
		</td>
		<td>
			<select name="sub[qa][contact_preference]">
				<option value="1">{VAR:LC_EMAIL}</option>
				<option value="2">{VAR:LC_PHONE}</option>
				<option value="3">{VAR:LC_FAX}</option>
			</select>
		</td>
	</tr>
</table>
