<style type="text/css" title="Vcl Gantt Chart Default">
.VclGanttChartLink
{
	text-decoration: none;
}

a.VclGanttChartBarLink
{
	z-index: 1000;
	position: relative;
}

a.VclGanttChartBarLink:hover
{
	z-index: 1001;
	background-color: white;
}

td.VclGanttChartHeader
{
	border-left: 1px solid white;
	padding-bottom: 2px;
	padding-left: 3px;
	white-space: nowrap;
}

a.VclGanttChartHeader
{
	white-space: nowrap;
}

a.VclGanttChartBarLink span
{
	display: none;
	color: black;
	text-decoration: none;
	top: 2em;
	width: 180px;
	padding: 2px;
	border: 1px solid black;
	background-color: white;
}

a.VclGanttChartBarLink:hover span
{
	position: absolute;
	display: block;
}

.VclGanttChartRowName
{
	background-color: #EEEEEE;
	font-family : Verdana, Arial, Helvetica, Geneva, sans-serif;
	font-size: {VAR:row_text_height}px;
	white-space: nowrap;
	padding-right: 3px;
	padding-left: 3px;
	border-bottom: 1px solid;
	border-right: none;
	border-top: none;
}

.VclGanttChartDataImg
{
	position: relative;
	border: none;
	height: {VAR:row_height}px;
	margin: 0px;
}

#VclGanttChartTable{VAR:chart_id}
{
	width: {VAR:chart_width};
}

.VclGanttChartCell
{
	border-color: #CCC;
}

.VclGanttChartColumn
{
	border: none;
	border-bottom: 1px solid ;
	border-left: 1px solid #CCCCCC;
}

.VclGanttChartSubdivision
{
	border: none;
	border-bottom: 1px solid;
	border-left: 1px solid #EEEEEE;
}

.VclGanttChartTimespan
{
	font-size: 9px;
	font-weight: normal;
	border-left: 1px solid white;
	padding-left: 2px;
}

img.VclGanttChartStartBar
{
	border-left: 1px solid #DF0D12;
	margin-right: -1px;
}
</style>
