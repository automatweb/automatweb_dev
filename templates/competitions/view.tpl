<span class="text">
<b>
<a href='{VAR:list}'>Tagasi nimekirja</a>
</b>
</span>

<form action='{VAR:baseurl}/reforb.{VAR:ext}' method="POST" enctype="multipart/form-data">

<table border="0" cellpadding="2" cellspacing="0">
<tr>
<td class="textsmall" align="right">Nimi:</td>
<td class="textsmall"><b>{VAR:name}</b></td>
</tr>
<td class="textsmall" align="right">Soovitas:</td>
<td class="textsmall">{VAR:proposed_by}</td>
</tr>
<tr>
<td class="textsmall" align="right">Algus:</td>
<td class="textsmall">{VAR:start}</td>
</tr>
<tr>
<td class="textsmall" align="right">L&otilde;pp:</td>
<td class="textsmall">{VAR:end}</td>
</tr>
<tr>
<td class="textsmall" align="right">H&auml;&auml;letamise l&otilde;pp:</td>
<td class="textsmall">{VAR:vote_end}</td>
</tr>
<tr>
<td class="textsmall" align="right">Raskusaste:</td>
<td class="textsmall"><b>{VAR:raskus}</b></td>
</tr>
<tr>
<td class="textsmall" align="right">&Uuml;esanne:</td>
<td class="textsmall">{VAR:content}</td>
</tr>

<!-- SUB: IN_PROGRESS -->
<tr>
	<td colspan="2" class="textsmall">V&otilde;istlus k&auml;ib!</td>
</tr>
<tr>
	<td class="textsmall">Kommentaar lahendusele:</td>
	<td><textarea cols="30" rows="10" name='comment'>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="textsmall">Uploadi zip:</td>
	<td><input type='file' name='entry'></td>
</tr>
<!-- SUB: ENTRY -->
<tr>
	<td colspan="2" class="textsmall">Sa oled juba lahenduse uploadinud, vaadata saab seda <a href='{VAR:entry}'>siit</a>. Kui uploadid uue, siis uus kirjutab vana yle.</td>
</tr>
<!-- END SUB: ENTRY -->
<tr>
	<td colspan="2"><input type='submit' value='Salvesta'></td>
</tr>

<!-- END SUB: IN_PROGRESS -->


</table>
<br>


<!-- SUB: IN_VOTING -->
<table border="0" cellpadding="3" cellspacing="0">
<tr>
	<td colspan="2" class="textsmall">V&otilde;istlus on h&auml;&auml;letamisfaasis. Siin on nimekiri v&auml;lja pakutud lahendustest, saad neid downloadida ja vaadata ja h22letada et mis sa neist arvad, kuid aint teiste lahendusi, mitte enda omi.</td>
</tr>
<tr>
	<td colspan="2">

		<table width="100%" border="0" cellpadding="2" cellspacing="0">
			<tr bgcolor="#EFEFEF">
				<td class="textsmall">Kes</td>
				<td class="textsmall">Millal</td>
				<td class="textsmall">Kaaskiri</td>
				<td class="textsmall">Download</td>
				<td class="textsmall">H&auml;&auml;leta</td>
			</tr>
			<!-- SUB: VOTE_LINE -->
				<tr>
					<td class="textsmall">{VAR:user}</td>
					<td class="textsmall">{VAR:when}</td>
					<td class="textsmall"><a href="{VAR:baseurl}/?class=competitions&action=vcomment&cid={VAR:commentlink}">Vaata</a></td>
					<td class="textsmall"><a href="{VAR:sol_url}">Download</a></td>
					<td class="textsmall">
					<!-- SUB: CAN_VOTE -->
						<select name='votes[{VAR:sol_id}]'>{VAR:votes}</select>
					<!-- END SUB: CAN_VOTE -->
					&nbsp;</td>
				</tr>
			<!-- END SUB: VOTE_LINE -->
				<tr>
					<td colspan="4"><input type="submit" value="H&auml;&auml;leta"></td>
				</tr>
		</table>
	</td>
</tr>
</table>
<!-- END SUB: IN_VOTING -->






<!-- SUB: CLOSED -->
<table width="100%" border="0" cellpadding="3" cellspacing="0">
<tr>
	<td colspan="2" class="textsmall">V&otilde;istlus on l&otilde;ppenud, siin on edetabel:</td>
</tr>
<tr>
	<td colspan="2">
		<table width="100%" border="0" cellpadding="2" cellspacing="0">
			<tr bgcolor="#EFEFEF">
				<td class="textsmall">Koht</td>
				<td class="textsmall">Kes</td>
				<td class="textsmall">Millal</td>
				<td class="textsmall">Kaaskiri</td>
				<td class="textsmall">Download</td>
				<td class="textsmall">H&auml;&auml;li</td>
			</tr>
			<!-- SUB: VOTE_LINE -->
				<tr>
					<td class="textsmall">{VAR:cnt}</td>
					<td class="textsmall">{VAR:user}</td>
					<td class="textsmall">{VAR:when}</td>
					<td class="textsmall"><a href="{VAR:baseurl}/?class=competitions&action=vcomment&cid={VAR:commentlink}">Vaata</a></td>
					<td class="textsmall"><a href='{VAR:sol_url}'>Download</a></td>
					<td class="textsmall">{VAR:vote}</td>
				</tr>
			<!-- END SUB: VOTE_LINE -->
		</table>
</tr>
</table>
<!-- END SUB: CLOSED -->

{VAR:reforb}
</form>