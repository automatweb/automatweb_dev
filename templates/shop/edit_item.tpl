<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
	<tr>
		<td colspan=2 class="fcaption2">{VAR:item}</td>
	</tr>
	<tr>
		<td colspan=2 class="fcaption2">Vali mis men&uuml;&uuml;de all see toode veel n&auml;ha on:</td>
	</tr>
	<tr>
		<td class="fcaption2">
			<form action='reforb.{VAR:ext}' method=POST>
				<select class="small_button" size=10 name='menus[]' multiple>{VAR:menus}</select><br>
				<input class="small_button" type='submit' value='Salvesta'>
				{VAR:reforb}
			</form>
		</td>
		<td rowspan=3 class="fcaption" valign="top">
			<form action='reforb.{VAR:ext}' method=POST>
			Vali itemi v&otilde;imalused:
			<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" width=100% >
				<tr>
					<td class="fcaption2"><input type='checkbox' name='has_max' value=1 {VAR:has_max}></td>
					<td class="fcaption2">Kogusega item</td>
				</tr>
				<tr>
					<td class="fcaption2">&nbsp;</td>
					<td class="fcaption2"><input size=3 type='text' name='max_items' VALUE='{VAR:max_items}'> itemit kokku</td>
				</tr>
				<tr>
					<td class="fcaption2"><input type='checkbox' name='has_period' value=1 {VAR:has_period}></td>
					<td class="fcaption2">Perioodiga item <a href='javascript:remote("no",500,500,"{VAR:sel_period}")'>Vali periood</a></td>
				</tr>
				<tr>
					<td class="fcaption2">&nbsp;</td>
					<td class="fcaption2">Alates: {VAR:per_from}</td>
				</tr>
				<tr>
					<td class="fcaption2">&nbsp;</td>
					<td class="fcaption2">Mitu kordust n&auml;idatakse: <input type='text' name='per_cnt' class='small_button' size=3 VALUE='{VAR:per_cnt}'></td>
				</tr>
				<tr>
					<td class="fcaption2">&nbsp;</td>
					<td class="fcaption2"><a href='{VAR:per_prices}'>M&auml;&auml;ra perioodide hinnad</a></td>
				</tr>
				<tr>
					<td class="fcaption2"><input type='checkbox' name='has_objs' value=1 {VAR:has_objs}></td>
					<td class="fcaption2">Tee iga itemi jaoks objekt (igal on erinev kalender)</td>
				</tr>
				<tr>
					<td class="fcaption2" colspan=2>Hinna arvutamise valem: (kui see on t&uuml;hi, siis kasutatakse tavalist hinda)</td>
				</tr>
				<tr>
					<td class="fcaption2" colspan=2>{VAR:price_eq}</td>
				</tr>
				<tr>
					<td class="fcaption2" colspan=2>Kauba t&uuml;&uuml;p:</td>
				</tr>
				<tr>
					<td class="fcaption2" colspan=2>{VAR:type}</td>
				</tr>
			</table>
				<input class="small_button" type='submit' value='Salvesta'>
				{VAR:reforb3}
			</form>
		</td>
	</tr>
	<tr>
		<td class="fcaption2">Vali mis men&uuml;&uuml; alla minnakse p2rast toote tellimist:</td>
	</tr>
	<tr>
		<td class="fcaption2">
			<form action='reforb.{VAR:ext}' method=POST>
				<select class="small_button" size=10 name='redir'>{VAR:redir}</select><br>
				<input class="small_button" type='submit' value='Salvesta'>
				{VAR:reforb2}
			</form>
		</td>
	</tr>
</table>
