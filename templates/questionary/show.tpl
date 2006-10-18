<form method="post">
{VAR:name}<br>
<!-- SUB: GROUP -->

	<table>
	<tr><td colspan="{VAR:span}" bgcolor="silver">{VAR:name}</td></tr>
	<!-- SUB: HEADER -->
		<tr>
			<td>
				{VAR:corner_caption}
			</td>
		<!-- SUB: QUESTION -->
			<td style="font-size:10px;border:1px solid black;">
				{VAR:question_name}
			</td>
		<!-- END SUB: QUESTION -->
		</tr>
	<!-- END SUB: HEADER -->
	<!-- SUB: TOPIC -->
		<tr>
			<td style="font-size:10px;border:1px solid gray;">
				{VAR:topic_name}
			</td>
			<!-- SUB: ANSWER -->
				<td style="text-align:center;">
					{VAR:answer_element}
				</td>
			<!-- END SUB: ANSWER -->
		</tr>
	<!-- END SUB: TOPIC -->
	</table>
<!-- END SUB: GROUP -->
<!-- SUB: PERS_DATA -->
<table style="font-size:12px;">
	Sugu:<br/>
	<input type="radio" value="1" name="pers[gender]"/>mees
	<input type="radio" value="2" name="pers[gender]"/>naine
	<br/>
	Vanus:<br/>
	<input type="radio" value="1" name="pers[age]"/>18 v&otilde;i noorem<br/>
	<input type="radio" value="2" name="pers[age]"/>19-29<br/>
	<input type="radio" value="3" name="pers[age]"/>30-39<br/>
	<input type="radio" value="4" name="pers[age]"/>40-49<br/>
	<input type="radio" value="5" name="pers[age]"/>50-59<br/>
	<input type="radio" value="6" name="pers[age]"/>60 v&otilde; vanem<br/>
	<br/>
	Tegevusala:<br/><br/>
	<table>
		<tr>
			<td>
				tegevusala	
			</td>
			<td>
				tallinnast
			</td>
			<td>
				mujalt
			</td>
		</tr>
		<!-- SUB: PERS_AREA -->
		<tr>
			<td>
				{VAR:caption}
			</td>
			<td>
				<input type="radio" value="{VAR:value}" name="pers[area_radio]"/>
			</td>
			<td>
				<input type="textbox" name="pers[area_text][{VAR:value}]"/>
			</td>
		</tr>
		<!-- END SUB: PERS_AREA -->


</table>
<br/>
Kui &otilde;pite v&otilde;i t&ouml;&ouml;tate &uuml;likoolis, siis millises?<br/><br/>
<table>
		<tr>
			<td>&uuml;likool</td>
			<td colspan="2">teaduskond(instituut/kolledz)</td>
		<tr>
		<!-- SUB: PERS_SCHOOL -->
		<tr>
			<td>{VAR:caption}</td>
			<td>
				<input type="radio" value="{VAR:value}" name="pers[school_radio]"/>
			</td>
			<td>
				<input type="textbox" name="pers[school_text]"/>
			</td>
		</tr>
		<!-- END SUB: PERS_SCHOOL -->
</table>
<br/>
Teie t&ouml;&ouml;-, &otilde;pingu v&otilde;i huvivaldkond (t&auml;psustage)?<br/><br/>
<table>
		<tr>
			<td>valdkond</td>
			<td colspan="2">t&auml;psustus</td>
		<tr>
		<!-- SUB: S_AREA -->
		<tr>
			<td>{VAR:caption}</td>
			<td>
				<input type="radio" value="{VAR:value}" name="pers[intrest_radio]"/>
			</td>
			<td>
				<input type="textbox" name="pers[intrest_text]"/>
			</td>
		</tr>
		<!-- END SUB: S_AREA -->
</table>
<br/>
Kui sageli k&uuml;lastate Rahvusraamatukogu?<br/><br/>
<table>
		<!-- SUB: VISITS -->
		<tr>
			<td>{VAR:caption}</td>
			<td>
				<input type="radio" value="{VAR:value}" name="pers[visits]"/>
			</td>
		</tr>
		<!-- END SUB: VISITS -->
</table>
<br/>
Kuidas raamatukogu teenuseid kasutate?<br/><br/>
<table>
		<!-- SUB: USAGE -->
		<tr>
			<td>{VAR:caption}</td>
			<td>
				<input type="radio" value="{VAR:value}" name="pers[usage]"/>
			</td>
		</tr>
		<!-- END SUB: USAGE -->
</table>
<!-- END SUB: PERS_DATA -->
<input type="submit" value="{VAR:submit_caption}">
{VAR:reforb}
</form>
<!-- SUB: A_ELEMENT -->
	<table>
		<!-- SUB: INPUT -->
			<td style="font-size:11px;text-align:center;">
			{VAR:nr}<br/>{VAR:html_element}
			</td>
		<!-- END SUB: INPUT -->
	</table>
<!-- END SUB: A_ELEMENT -->
