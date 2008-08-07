<!-- SUB: PRINT_HEADER -->
<html>
<head>
<link rel="stylesheet" type="text/css" href="/orb.aw?class=minify_js_and_css&amp;action=get_css&amp;name=aw_admin.css">
<style>
BODY
{
	background-color: #ffffff;
}
</style>
</head>
<body>
{VAR:current_date} {VAR:current_time}
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
	<td class="awmenuedittablehead">Aeg</td>
	<td class="awmenuedittablehead">Tellija</td>
	<td class="awmenuedittablehead">Kontaktisik</td> <!-- any rfp property that starts with 'data_' -->
	<td class="awmenuedittablehead">Ruum</td>
	<td class="awmenuedittablehead">Inimesi</td>
	<td class="awmenuedittablehead">{VAR:confirmed_caption}</td>
	<td class="awmenuedittablehead">T&uuml;&uuml;p</td>
	<td class="awmenuedittablehead">{VAR:data_mf_event_type_caption}</td>
	<td class="awmenuedittablehead">Koostaja</td>
</tr>
<!-- END SUB: HEADER -->
<!-- SUB: CLIENT_ROW -->
<tr class="awmenuedittablerow">
	<td colspan="10" class="awmenuedittabletext">{VAR:data_subm_name} - {VAR:data_subm_organisation}</td>
</tr>
<!-- END SUB: CLIENT_ROW -->
<!-- SUB: ROW -->
<tr class="awmenuedittablerow">

<!-- SUB: ROW_TYPE_RESOURCES -->
	<td class="awmenuedittabletext">{VAR:from_date}</td>
	<td class="awmenuedittabletext">{VAR:from_time} - {VAR:to_time}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_organisation}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_name}</td>
	<td class="awmenuedittabletext">{VAR:room}</td>
	<td class="awmenuedittabletext">{VAR:people_count}</td>
	<td class="awmenuedittabletext">{VAR:confirmed_str}</td>
	<td class="awmenuedittabletext">{VAR:raport_type}</td>
	<td class="awmenuedittabletext">{VAR:data_mf_event_type}</td>
	<td class="awmenuedittabletext">{VAR:rfp_createdby_name}</td>
<!-- END SUB: ROW_TYPE_RESOURCES -->
<!-- SUB: ROW_TYPE_RESOURCES_HAS_PRODUCTS -->
<td colspan="10" style="border: 2px solid white;">
<table border="0" width="100%" cellspacing="1" cellpadding="3" class="awmenuedittabletag">
<tr>
	<th class="awmenuedittabletext">Ressurss</th>
	<th class="awmenuedittabletext">Aeg</th>
	<th class="awmenuedittabletext">Kogus</th>
	<th class="awmenuedittabletext">Hind</th>
	<th class="awmenuedittabletext">Kommentaar</th>
	<th class="awmenuedittabletext">Summa</th>
</tr>
<!-- SUB: ROW_TYPE_RESOURCES_PRODUCT -->
	<td class="awmenuedittabletext">{VAR:resource_name}</td>
	<td class="awmenuedittabletext">{VAR:resource_from_time} - {VAR:resource_to_time}</td>
	<td class="awmenuedittabletext">{VAR:count}</td>
	<td class="awmenuedittabletext">{VAR:price}</td>
	<td class="awmenuedittabletext">{VAR:comment}</td>
	<td class="awmenuedittabletext">{VAR:sum}</td>
<!-- END SUB: ROW_TYPE_RESOURCES_PRODUCT -->
</table>
</td>
<!-- END SUB: ROW_TYPE_RESOURCES_HAS_PRODUCTS -->

<!-- SUB: ROW_TYPE_ROOMS -->
	<td class="awmenuedittabletext">{VAR:from_date}</td>
	<td class="awmenuedittabletext">{VAR:from_time} - {VAR:to_time}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_organisation}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_name}</td>
	<td class="awmenuedittabletext">{VAR:room}</td>
	<td class="awmenuedittabletext">{VAR:people_count}</td>
	<td class="awmenuedittabletext">{VAR:confirmed_str}</td>
	<td class="awmenuedittabletext">{VAR:raport_type}</td>
	<td class="awmenuedittabletext">{VAR:data_mf_event_type}</td>
	<td class="awmenuedittabletext">{VAR:rfp_createdby_name}</td>
<!-- END SUB: ROW_TYPE_ROOMS -->

<!-- SUB: ROW_TYPE_CATERING -->
	<td class="awmenuedittabletext">{VAR:from_date}</td>
	<td class="awmenuedittabletext">{VAR:from_time} - {VAR:to_time}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_organisation}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_name}</td>
	<td class="awmenuedittabletext">{VAR:room}</td>
	<td class="awmenuedittabletext">{VAR:people_count}</td>
	<td class="awmenuedittabletext">{VAR:confirmed_str}</td>
	<td class="awmenuedittabletext">{VAR:raport_type}</td>
	<td class="awmenuedittabletext">{VAR:data_mf_event_type}</td>
	<td class="awmenuedittabletext">{VAR:rfp_createdby_name}</td>
<!-- END SUB: ROW_TYPE_CATERING -->
<!-- SUB: ROW_TYPE_CATERING_HAS_PRODUCTS -->
<td colspan="11" style="border: 2px solid white;">
<table border="0" width="100%" cellspacing="1" cellpadding="3" class="awmenuedittabletag">
<tr>
	<th class="awmenuedittabletext">T&uuml;&uuml;p</th>
	<th class="awmenuedittabletext">Toode</th>
	<th class="awmenuedittabletext">Hind</th>
	<th class="awmenuedittabletext">Kogus</th>
	<th class="awmenuedittabletext">Summa</th>
</tr>
<!-- SUB: ROW_TYPE_CATERING_PRODUCT -->
	<td class="awmenuedittabletext">{VAR:product_event}</td>
	<td class="awmenuedittabletext">{VAR:product_name}</td>
	<td class="awmenuedittabletext">{VAR:price}</td>
	<td class="awmenuedittabletext">{VAR:amount}</td>
	<td class="awmenuedittabletext">{VAR:sum}</td>
<!-- END SUB: ROW_TYPE_CATERING_PRODUCT -->
</table>
</td>
<!-- END SUB: ROW_TYPE_CATERING_HAS_PRODUCTS -->
<!-- SUB: ROW_TYPE_HOUSING -->
	<td class="awmenuedittabletext">{VAR:from_date}</td>
	<td class="awmenuedittabletext">{VAR:from_time} - {VAR:to_time}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_organisation}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_name}</td>
	<td class="awmenuedittabletext">{VAR:room}</td>
	<td class="awmenuedittabletext">{VAR:people_count}</td>
	<td class="awmenuedittabletext">{VAR:confirmed_str}</td>
	<td class="awmenuedittabletext">{VAR:raport_type}</td>
	<td class="awmenuedittabletext">{VAR:data_mf_event_type}</td>
	<td class="awmenuedittabletext">{VAR:rfp_createdby_name}</td>
<!-- END SUB: ROW_TYPE_HOUSING -->
<!-- SUB: ROW_TYPE_ADDITIONAL_SERVICES -->
	<td class="awmenuedittabletext">{VAR:from_date}</td>
	<td class="awmenuedittabletext">{VAR:from_time}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_organisation}</td>
	<td class="awmenuedittabletext">{VAR:data_subm_name}</td>
	<td class="awmenuedittabletext">-</td>
	<td class="awmenuedittabletext">-</td>
	<td class="awmenuedittabletext">{VAR:confirmed_str}</td>
	<td class="awmenuedittabletext">{VAR:raport_type}</td>
	<td class="awmenuedittabletext">{VAR:data_mf_event_type}</td>
	<td class="awmenuedittabletext">{VAR:rfp_createdby_name}</td>
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

