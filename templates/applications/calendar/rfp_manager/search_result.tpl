<!-- SUB: HAS_RESULT -->
<table>
<!-- SUB: HEADER -->
<tr>
	<td>Alates</td>
	<td>Kuni</td>
	<td>Aeg</td>
	<td>Ruum</td>
	<td>Inimesi</td>
	<td>T&uuml;&uuml;p</td>
	<td>{VAR:data_subm_name_caption}</td> <!-- any rfp property that starts with 'data_' -->
	<td>{VAR:data_mf_event_type_caption}</td>
</tr>
<!-- END SUB: HEADER -->
<!-- SUB: CLIENT_ROW -->
<tr>
	<td colspan="8">{VAR:data_subm_name} - {VAR:data_subm_organisation}</td>
</tr>
<!-- END SUB: CLIENT_ROW -->
<!-- SUB: ROW -->
<tr>
<!-- SUB: ROW_TYPE_RESOURCES -->
	<td>{VAR:from_date}</td>
	<td>{VAR:to_date}</td>
	<td>{VAR:from_time} - {VAR:to_time}</td>
	<td>{VAR:room}</td>
	<td>{VAR:people_count}</td>
	<td>{VAR:raport_type}</td>
	<td>{VAR:data_subm_name}</td>
	<td>{VAR:data_mf_event_type}</td>
<!-- END SUB: ROW_TYPE_RESOURCES -->
<!-- SUB: ROW_TYPE_ROOMS -->
	<td>{VAR:from_date}</td>
	<td>{VAR:to_date}</td>
	<td>{VAR:from_time} - {VAR:to_time}</td>
	<td>{VAR:room}</td>
	<td>{VAR:people_count}</td>
	<td>{VAR:raport_type}</td>
	<td>{VAR:data_subm_name}</td>
	<td>{VAR:data_mf_event_type}</td>
<!-- END SUB: ROW_TYPE_ROOMS -->
<!-- SUB: ROW_TYPE_CATERING -->
	<td>{VAR:from_date}</td>
	<td>{VAR:to_date}</td>
	<td>{VAR:from_time} - {VAR:to_time}</td>
	<td>{VAR:room}</td>
	<td>{VAR:people_count}</td>
	<td>{VAR:raport_type}</td>
	<td>{VAR:data_subm_name}</td>
	<td>{VAR:data_mf_event_type}</td>
<!-- END SUB: ROW_TYPE_CATERING -->
<!-- SUB: ROW_TYPE_CATERING_PRODUCT -->
	<td colspan="2">{VAR:product_name}</td>
	<td>{VAR:product_from_time} - {VAR:product_to_time}</td>
	<td>{VAR:room_name}</td>
	<td>{VAR:people_count}</td>
	<td>{VAR:amount}</td>
	<td colspan="2">{VAR:sum}</td>
<!-- END SUB: ROW_TYPE_CATERING_PRODUCT -->
<!-- SUB: ROW_TYPE_HOUSING -->
	<td>{VAR:from_date}</td>
	<td>{VAR:to_date}</td>
	<td>{VAR:from_time} - {VAR:to_time}</td>
	<td>{VAR:room}</td>
	<td>{VAR:people_count}</td>
	<td>{VAR:raport_type}</td>
	<td>{VAR:data_subm_name}</td>
	<td>{VAR:data_mf_event_type}</td>
<!-- END SUB: ROW_TYPE_HOUSING -->
</tr>
<!-- END SUB: ROW -->
</table>
<!-- END SUB: HAS_RESULT -->
<!-- SUB: HAS_NO_RESULT -->
no results at all!
<!-- END SUB: HAS_NO_RESULT -->
