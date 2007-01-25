<table>
	<tr style="font-size:10px;font-weight:bold;">
		<td>
			nimi
		</td>
		<td>
			aeg
		</td>
		<td>
			attendees
		</td>
		<td>
			hotel
		</td>
		<td>
			copy and update
		</td>
		<td>
			remove from
		</td>
	</tr>
	<!-- SUB: RFP -->
	<tr style="font-size:10px;">
		<td>
			{VAR:name}
		</td>
		<td>
			{VAR:time}
		</td>
		<td>
			{VAR:attendees}
		</td>
		<td>
			<!-- SUB: HOTEL -->
			{VAR:hotel}
			<!-- END SUB: HOTEL -->
			<!-- SUB: HOTEL_SEP -->
			,
			<!-- END SUB: HOTEL_SEP -->
		</td>
		<td>
			<a href="{VAR:copy_url}">copy</a>
		</td>
		<td>
			<a href="{VAR:remove_url}">remove</a>
		</td>
	</tr>
	<!-- END SUB: RFP -->
</table>