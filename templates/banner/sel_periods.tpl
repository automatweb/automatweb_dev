<form method="POST" action="reforb.{VAR:ext}" name='b88' enctype="multipart/form-data">
	<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
		<tr>
			<td class="fcaption2" colspan=3>{VAR:image}</td>
		</tr>
		<tr>
			<td class="fcaption2">Nimi:</td>
			<td class="fform" colspan=2>{VAR:name}</td>
		</tr>
		<tr>
			<td class="fcaption"><input type='radio' name='type' VALUE='0' {VAR:type_0}>
			<td colspan=2 class="fcaption2">Banner on pidevalt aktiivne</td>
		</tr>
		<tr>
			<td rowspan=2 class="fcaption"><input type='radio' name='type' VALUE='1' {VAR:type_1}>
			<td class="fcaption2">Banner on aktiivne alates:</td>
			<td class="fform">{VAR:act_from}</td>
		</tr>
		<tr>
			<td class="fcaption2">Banner on aktiivne kuni:</td>
			<td class="fform">{VAR:act_to}</td>
		</tr>
		<tr>
			<td rowspan=3 class="fcaption"><input type='radio' name='type' VALUE='2' {VAR:type_2}>
			<td class="fcaption2">Banner on aktiivne n&auml;dalap&auml;eval:</td>
			<td class="fform"><select multiple name='wday[]'>{VAR:wday}</select></td>
		</tr>
		<tr>
			<td class="fcaption2">Kellast:</td>
			<td class="fform">{VAR:wday_from}</td>
		</tr>
		<tr>
			<td class="fcaption2">Kellani:</td>
			<td class="fform">{VAR:wday_to}</td>
		</tr>

		<tr>
			<td rowspan=2 class="fcaption"><input type='radio' name='type' VALUE='3' {VAR:type_3}>
			<td class="fcaption2">Banner on aktiivne kellast:</td>
			<td class="fform">{VAR:time_from}</td>
		</tr>
		<tr>
			<td class="fcaption2">Kellani:</td>
			<td class="fform">{VAR:time_to}</td>
		</tr>

		<tr>
			<td class="fform" align="center" colspan="3"><input type="submit" value="Salvesta"></td>
		</tr>
	</table>
	{VAR:reforb}
</form>
