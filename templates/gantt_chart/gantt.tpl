<table class="VclGanttChartTable" id="VclGanttChartTable{VAR:chart_id}" cellspacing="0">
<tr>
	<td class="VclGanttChartRowDfn">{VAR:row_dfn}</td>
	<td class="VclGanttChartColDfn">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" >
			<tr>
				<!-- SUB: TIMESPAN -->
				<td align="{VAR:align}" class="VclGanttChartTimeDfn">{VAR:time}</td>
				<!-- END SUB: TIMESPAN -->
			</tr>
		</table>
	</td>
</tr>

<!-- SUB: data_row0 -->
<tr>
	<td class="VclGanttChartRowName"><a href="{VAR:row_uri}" class="VclGanttChartLink" target="{VAR:row_uri_target}">{VAR:row_name}</a></td>
	<!-- SUB: data_cell -->
	<td class="VclGanttChartDataCell">
	<!-- SUB: cell_contents -->
		<!-- SUB: bar_0 -->
			<a href="{VAR:bar_uri}" target="{VAR:bar_uri_target}" title="{VAR:title}" class="VclGanttChartLink"><img src="{VAR:baseurl}/automatweb/images/ganttbar00.gif" width="{VAR:length}" alt="{VAR:title}" title="{VAR:title}" class="VclGanttChartDataImg"></a>
		<!-- END SUB: bar_0 -->

		<!-- SUB: bar_1 -->
		<a href="{VAR:bar_uri}" target="{VAR:bar_uri_target}" title="{VAR:title}" class="VclGanttChartLink"><img src="{VAR:baseurl}/automatweb/images/ganttbar01.gif" width="{VAR:length}" alt="{VAR:title}" title="{VAR:title}" class="VclGanttChartDataImg"></a>
		<!-- END SUB: bar_1 -->

		<!-- SUB: bar_hilighted -->
		<a href="{VAR:bar_uri}" title="{VAR:title}" target="{VAR:bar_uri_target}" class="VclGanttChartLink"><img src="{VAR:baseurl}/automatweb/images/ganttbar_hilighted.gif" width="{VAR:length}" alt="{VAR:title}" title="{VAR:title}" class="VclGanttChartDataImg"></a>
		<!-- END SUB: bar_hilighted -->

		<!-- SUB: bar_empty -->
			<img src="{VAR:baseurl}/automatweb/images/ganttbar_empty.gif" width="{VAR:length}" alt="" class="VclGanttChartDataImg">
		<!-- END SUB: bar_empty -->

		<!-- END SUB: cell_contents -->
	</td>
	<!-- END SUB: data_cell -->
</tr>
<!-- END SUB: data_row0 -->

<!-- SUB: data_row1 -->
<tr>
	<td class="VclGanttChartRowName"><a href="{VAR:row_uri}" class="VclGanttChartLink" target="{VAR:row_uri_target}">{VAR:row_name}</a></td>
	<!-- SUB: data_cell -->
	<td class="VclGanttChartDataCell">
		<!-- SUB: cell_contents -->

		<!-- SUB: bar_0 -->
			<a href="{VAR:bar_uri}" target="{VAR:bar_uri_target}" title="{VAR:title}" class="VclGanttChartLink"><img src="{VAR:baseurl}/automatweb/images/ganttbar10.gif" width="{VAR:length}" title="{VAR:title}" alt="{VAR:title}" class="VclGanttChartDataImg"></a>
		<!-- END SUB: bar_0 -->

		<!-- SUB: bar_1 -->
			<a href="{VAR:bar_uri}" target="{VAR:bar_uri_target}" title="{VAR:title}" class="VclGanttChartLink"><img src="{VAR:baseurl}/automatweb/images/ganttbar11.gif" width="{VAR:length}" alt="{VAR:title}" title="{VAR:title}" class="VclGanttChartDataImg"></a>
		<!-- END SUB: bar_1 -->

		<!-- SUB: bar_hilighted -->
			<a href="{VAR:bar_uri}" target="{VAR:bar_uri_target}" title="{VAR:title}" class="VclGanttChartLink"><img src="{VAR:baseurl}/automatweb/images/ganttbar_hilighted.gif" width="{VAR:length}" alt="{VAR:title}" title="{VAR:title}" class="VclGanttChartDataImg"></a>
		<!-- END SUB: bar_hilighted -->

		<!-- SUB: bar_empty -->
			<img src="{VAR:baseurl}/automatweb/images/ganttbar_empty.gif" width="{VAR:length}" alt="" class="VclGanttChartDataImg">
		<!-- END SUB: bar_empty -->

		<!-- END SUB: cell_contents -->

	</td>
	<!-- END SUB: data_cell -->
</tr>
<!-- END SUB: data_row1 -->
</table>
