<table width="520" border=0 align="center" cellpadding=3 cellspacing=2 class="text">
	<!-- SUB: COMPANY -->
	<tr>
		<td colspan="2" valing="top">
			{VAR:company}
		</td>
	</tr>
	<!-- END SUB: COMPANY -->
	<!-- SUB: SECT -->
	<tr>
		<td colspan="2" valing="top">
			{VAR:sect}
		</td>
	</tr>
	<!-- END SUB: SECT -->
	<!-- SUB: PROFESSION -->
	<tr>
		<td>
			<b>Pakutav ametikoht</b>
		</td>
		<td>
			<b>{VAR:profession}</b>
		</td>
	</tr>
	<!-- END SUB: PROFESSION -->
	<!-- SUB: WORKINFO -->
	<tr>
		<td valign="top">
			<b>Töö sisu</b>
		</td>
		<td>
			{VAR:workinfo}
		</td>
	</tr>
	<!-- END SUB: WORKINFO -->
	<!-- SUB: REQUIREMENTS -->
	<tr>
		<td valign="top">
			<b>Nõudmised kandidaadile</b>
		</td>
		<td>
			{VAR:requirements}
		</td>
	</tr>
	<!-- END SUB: REQUIREMENTS -->
	<!-- SUB: WEOFFER -->
	<tr>
		<td valign="top">
			<b>Omaltpoolt pakume</b>
		</td>
		<td>
			{VAR:weoffer}
		</td>
	</tr>
	<!-- END SUB: WEOFFER -->
	<!-- SUB: INFO -->
	<tr>
		<td valign="top">
			<b>Lisainfo</b>
		</td>
		<td>
			{VAR:info}
		</td>
	</tr>
	<!-- END SUB: INFO -->
	<!-- SUB: LOC_AREA -->
	<tr>
		<td valign="top">
			<b>Piirkond</b>
		</td>
		<td>
			{VAR:loc_area}
		</td>
	</tr>
	<!-- END SUB: LOC_AREA -->
	<!-- SUB: LOC_COUNTY -->
	<tr>
		<td valign="top">
			<b>Maakond</b>
		</td>
		<td>
			{VAR:loc_county}
		</td>
	</tr>
	<!-- END SUB: LOC_COUNTY -->
	<!-- SUB: LOC_CITY -->
	<tr>
		<td valign="top">
			<b>Linn</b>
		</td>
		<td>
			{VAR:loc_city}
		</td>
	</tr>
	<!-- END SUB: LOC_CITY -->
	<!-- SUB: START_WORKING -->
	<tr>
		<td valign="top">
			<b>Tööleasumise aeg</b>
		</td>
		<td>
			{VAR:start_working}
		</td>
	</tr>
	<!-- END SUB: START_WORKING -->
	<!-- SUB: JOB_OFFER_FILE -->
	<tr>
		<td valign="top">
			<b>Tööpakkumine failina</b>
		</td>
		<td>
			{VAR:job_offer_file}
		</td>
	</tr>
	<!-- END SUB: JOB_OFFER_FILE -->
	<!-- SUB: END -->
	<tr class="Grey">
		<td  valign="top" colspan="2" style="padding-left: 10px; padding-right:10px; padding-top: 10px; padding-bottom:10px;">
			Kandideerimise tähtaeg: {VAR:end}
		</td>
	</tr>
	<!-- END SUB: END -->
	<!-- SUB: APPLY -->
	<tr>
		<td colspan="2">
			<a href='{VAR:apply_link}'>Kandideeri</a>
		</td>
	</tr>
	<!-- END SUB: APPLY -->
</table>
