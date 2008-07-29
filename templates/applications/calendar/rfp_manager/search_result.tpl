<!-- SUB: PRINT_HEADER -->
<html>
<head>
<link rel="stylesheet" type="text/css" href="http://tarvo.dev.struktuur.ee/orb.aw?class=minify_js_and_css&amp;action=get_css&amp;name=aw_admin.css">
<style>
BODY
{
	background-color: #ffffff;
}
</style>
</head>
<body>
<!-- END SUB: PRINT_HEADER -->

<!-- SUB: HAS_FILTERS_USED -->
<table>
	<tr><th colspan="2">FILTRID</th></tr>
	<!-- SUB: FILTER -->
	<tr>
		<td>{VAR:filter_caption}</td>
		<td>{VAR:filter_value}</td>
	</tr>
	<!-- END SUB: FILTER -->
</table>
<!-- END SUB: HAS_FILTERS_USED -->
<!-- SUB: HAS_RESULT -->
<table border="0" width="100%" cellspacing="1" cellpadding="3" class="awmenuedittabletag">
<!-- SUB: HEADER -->
<tr>
	<td class="awmenuedittablehead">Alates</td>
	<td class="awmenuedittablehead">Kuni</td>
	<td class="awmenuedittablehead">Aeg</td>
	<td class="awmenuedittablehead">Ruum</td>
	<td class="awmenuedittablehead">Inimesi</td>
	<td class="awmenuedittablehead">T&uuml;&uuml;p</td>
	<td class="awmenuedittablehead">{VAR:data_subm_name_caption}</td> <!-- any rfp property that starts with 'data_' -->
	<td class="awmenuedittablehead">{VAR:data_mf_event_type_caption}</td>
</tr>
<!-- END SUB: HEADER -->
<!-- SUB: CLIENT_ROW -->
<tr class="awmenuedittablerow">
	<td colspan="8" class="awmenuedittabletext">{VAR:data_subm_name} - {VAR:data_subm_organisation}</td>
</tr>
<!-- END SUB: CLIENT_ROW -->
<!-- SUB: ROW -->
<tr class="awmenuedittablerow">
<!-- SUB: ROW_TYPE_RESOURCES -->
	<td class="awmenuedittabletext">{VAR:from_date}</td>
	<td class="awmenuedittabletext">{VAR:to_date}</td>
	<td class="awmenuedittabletext">{VAR:from_time} - {VAR:to_time}</td>
	<td class="awmenuedittabletext">{VAR:room}</td>
	<td class="awmenuedittabletext">{VAR:people_count}</td>
	<td class="awmenuedittabletext">{VAR:raport_type}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_name}</td>
	<td class="awmenuedittabletext">{VAR:data_mf_event_type}</td>
<!-- END SUB: ROW_TYPE_RESOURCES -->
<!-- SUB: ROW_TYPE_ROOMS -->
	<td class="awmenuedittabletext">{VAR:from_date}</td>
	<td class="awmenuedittabletext">{VAR:to_date}</td>
	<td class="awmenuedittabletext">{VAR:from_time} - {VAR:to_time}</td>
	<td class="awmenuedittabletext">{VAR:room}</td>
	<td class="awmenuedittabletext">{VAR:people_count}</td>
	<td class="awmenuedittabletext">{VAR:raport_type}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_name}</td>
	<td class="awmenuedittabletext">{VAR:data_mf_event_type}</td>
<!-- END SUB: ROW_TYPE_ROOMS -->
<!-- SUB: ROW_TYPE_CATERING -->
	<td class="awmenuedittabletext">{VAR:from_date}</td>
	<td class="awmenuedittabletext">{VAR:to_date}</td>
	<td class="awmenuedittabletext">{VAR:from_time} - {VAR:to_time}</td>
	<td class="awmenuedittabletext">{VAR:room}</td>
	<td class="awmenuedittabletext">{VAR:people_count}</td>
	<td class="awmenuedittabletext">{VAR:raport_type}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_name}</td>
	<td class="awmenuedittabletext">{VAR:data_mf_event_type}</td>
<!-- END SUB: ROW_TYPE_CATERING -->
<!-- SUB: ROW_TYPE_CATERING_PRODUCT -->
	<td class="awmenuedittabletext" colspan="2">{VAR:product_name}</td>
	<td class="awmenuedittabletext">{VAR:product_from_time} - {VAR:product_to_time}</td>
	<td class="awmenuedittabletext">{VAR:room_name}</td>
	<td class="awmenuedittabletext">{VAR:people_count}</td>
	<td class="awmenuedittabletext">{VAR:amount}</td>
	<td class="awmenuedittabletext" colspan="2">{VAR:sum}</td>
<!-- END SUB: ROW_TYPE_CATERING_PRODUCT -->
<!-- SUB: ROW_TYPE_HOUSING -->
	<td class="awmenuedittabletext">{VAR:from_date}</td>
	<td class="awmenuedittabletext">{VAR:to_date}</td>
	<td class="awmenuedittabletext">{VAR:from_time} - {VAR:to_time}</td>
	<td class="awmenuedittabletext">{VAR:room}</td>
	<td class="awmenuedittabletext">{VAR:people_count}</td>
	<td class="awmenuedittabletext">{VAR:raport_type}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_name}</td>
	<td class="awmenuedittabletext">{VAR:data_mf_event_type}</td>
<!-- END SUB: ROW_TYPE_HOUSING -->
</tr>
<!-- END SUB: ROW -->
</table>
<!-- END SUB: HAS_RESULT -->
<!-- SUB: HAS_NO_RESULT -->
no results at all!
<!-- END SUB: HAS_NO_RESULT -->

<!-- SUB: PRINT_FOOTER -->
</body>
</html>
<!-- END SUB: PRINT_FOOTER -->

