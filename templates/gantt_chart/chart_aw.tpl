<table id="VclGanttChartTable{VAR:chart_id}" cellspacing="0">
<tr>
<td class="awmenuedittableframeclass">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr>
	<td class="awmenuedittablehead" align="center">{VAR:row_dfn}</td>
	<td colspan="{VAR:columns}" valign="top">

<table border="0" width="100%" style="height: 100%" cellspacing="0" cellpadding="0">
	<tr>
	<!-- SUB: column_head_link -->
	<td class="awmenuedittablehead VclGanttChartHeader" align="center" style="width: {VAR:column_width};"><a href="{VAR:uri}" target="{VAR:target}" style="white-space: nowrap;">{VAR:title}</a></td>
	<!-- END SUB: column_head_link -->
	<!-- SUB: column_head -->
	<td class="awmenuedittablehead VclGanttChartHeader" align="center" style="width: {VAR:column_width};">{VAR:title}</td>
	<!-- END SUB: column_head -->
	</tr>

	<!-- SUB: subdivision_row -->
	<tr>
	<!-- SUB: subdivision_head -->
	<td valign="top">

		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<!-- SUB: subdivision -->
				<td class="awmenuedittablehead VclGanttChartTimespan" align="left">{VAR:time}</td>
				<!-- END SUB: subdivision -->
			</tr>
		</table>

	</td>
	<!-- END SUB: subdivision_head -->
	</tr>
	<!-- END SUB: subdivision_row -->
</table>

	</td>
</tr>

<!-- SUB: data_row -->
<tr class="awmenuedittablerow">
<td class="awmenuedittabletext VclGanttChartRowName"><a href="{VAR:row_uri}" class="VclGanttChartLink" target="{VAR:row_uri_target}">{VAR:row_name}</a></td>

<!-- SUB: data_cell_column -->
<td class="awmenuedittabletext VclGanttChartColumn VclGanttChartCell">
<!-- SUB: cell_contents -->
<!-- SUB: bar_normal_start -->
<a href="{VAR:bar_uri}" target="{VAR:bar_uri_target}" class="VclGanttChartBarLink"><span>{VAR:title}</span><img src="{VAR:baseurl}/automatweb/images/ganttbar_empty.gif" width="{VAR:length}" class="VclGanttChartDataImg VclGanttChartStartBar" style="background-color: {VAR:bar_colour};"></a>
<!-- END SUB: bar_normal_start -->
<!-- SUB: bar_normal_continue -->
<a href="{VAR:bar_uri}" target="{VAR:bar_uri_target}" class="VclGanttChartBarLink"><span>{VAR:title}</span><img src="{VAR:baseurl}/automatweb/images/ganttbar_empty.gif" width="{VAR:length}" class="VclGanttChartDataImg" style="background-color: {VAR:bar_colour};"></a>
<!-- END SUB: bar_normal_continue -->
<!-- SUB: bar_empty -->
<img src="{VAR:baseurl}/automatweb/images/ganttbar_empty.gif" width="{VAR:length}" alt="" class="VclGanttChartDataImg">
<!-- END SUB: bar_empty -->
<!-- END SUB: cell_contents -->
</td>
<!-- END SUB: data_cell_column -->

<!-- SUB: data_cell_subdivision -->
<td class="awmenuedittabletext VclGanttChartSubdivision VclGanttChartCell">
<!-- SUB: cell_contents -->
<!-- SUB: bar_normal_start -->
<a href="{VAR:bar_uri}" target="{VAR:bar_uri_target}" class="VclGanttChartBarLink"><span>{VAR:title}</span><img src="{VAR:baseurl}/automatweb/images/ganttbar_empty.gif" width="{VAR:length}" class="VclGanttChartDataImg VclGanttChartStartBar" style="background-color: {VAR:bar_colour};"></a>
<!-- END SUB: bar_normal_start -->
<!-- SUB: bar_normal_continue -->
<a href="{VAR:bar_uri}" target="{VAR:bar_uri_target}" class="VclGanttChartBarLink"><span>{VAR:title}</span><img src="{VAR:baseurl}/automatweb/images/ganttbar_empty.gif" width="{VAR:length}" class="VclGanttChartDataImg" style="background-color: {VAR:bar_colour};"></a>
<!-- END SUB: bar_normal_continue -->
<!-- SUB: bar_empty -->
<img src="{VAR:baseurl}/automatweb/images/ganttbar_empty.gif" width="{VAR:length}" alt="" class="VclGanttChartDataImg">
<!-- END SUB: bar_empty -->
<!-- END SUB: cell_contents -->
</td>
<!-- END SUB: data_cell_subdivision -->

</tr>
<!-- END SUB: data_row -->



<!-- SUB: separator_row -->
<tr>
<td colspan="{VAR:colspan}" class="awmenuedittabletext VclGanttChartCell" style="height: 3px; font-size: 2px;">&nbsp;</td>
</tr>
<!-- END SUB: separator_row -->

</table>
</td>
</tr>
</table>
