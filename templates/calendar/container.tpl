<style type="text/css">
.minical_table {
	border-collapse: collapse;
	border: 0px;
	font-family: Arial,sans-serif;
	font-size: 11px;
	padding: 3px;
	text-align: center;
	color: #000;
	background-color: #EFEFEF;

}
.minical_table a {
	color: #000;
	text-decoration: none;
}

.minical_table a:hover {
	color: #000;
}

.minical_header {
	font-family: Arial,sans-serif;
	font-size: 11px;
	background-color: #FFFFFF;
	text-align: center;
	border: 0px solid black;
}
.minical_cell {
	font-family: Arial,sans-serif;
	font-size: 11px;
	background-color: #FFFFFF;
	border: 0px solid #BCDCF0;
	padding: 3px;
	text-align: center;
}

.minical_cellact {
	font-family: Arial,sans-serif;
	font-size: 11px;
	background-color: #FFFFFF;
	border: 0px solid #BCDCF0;
	padding: 3px;
	background: #E1E1E1;
	text-align: center;
}

.minical_cell_deact {
	font-family: Arial,sans-serif;
	font-size: 11px;
	background-color: #FFFFFF;
	border: 0px solid #BCDCF0;
	padding: 3px;
	text-align: center;
	color: #BDBDBD;
}

.minical_cell_today {
	font-family: Arial,sans-serif;
	font-size: 11px;
	border: 0px solid #BCDCF0;
	padding: 3px;
	text-align: center;
	background: #5FC000;
	color: #000000;
}
</style>

<script type="text/javascript">
function navigate_to()
{
	var m = document.getElementById('navi_month').value;
	var y = document.getElementById('navi_year').value;
	// now that I have got that .. uh .. what do I do now?
	// window.location changes url ..and contains the current url
	var naviurl = '{VAR:naviurl}' + '&date=' + m + '-' + y;
	window.location = naviurl;
};
</script>


<div class="aw04kalender01">


<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="aw04kalender001" colspan="3" align="right" valign="bottom">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
	
		  <td><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_back.gif"><a href="{VAR:today_url}"><b>Täna</b></a></td>
		  <td><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>

			<!-- SUB: PAGE -->
		  <td><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_back.gif"><a href="{VAR:link}"><b>{VAR:text}</b></a></td>
		  <td><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
			<!-- END SUB: PAGE -->

			<!-- SUB: SEL_PAGE -->
		  <td><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_back.gif"><b>{VAR:text}</b></td>
		  <td><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
			<!-- END SUB: SEL_PAGE -->
			</tr>
			</table>

		</td>
	</tr>
	<tr>
		<td class="aw04kalender02">
			<a href="{VAR:prevlink}"><img SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_left.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT="&lt;&lt;"></a> {VAR:caption}  <a href="{VAR:nextlink}"><img SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_right.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT="&gt;&gt;"></a>
		</td>
	<td valign="bottom">


	</td>
	<td align="right" valign="middle" class="aw04kalender02">

	<select id='navi_month' name='month' style="border: 1px solid gray">{VAR:mnames}</select>
	<select id='navi_year' name='year'>{VAR:years}</select>
	<input type="button" class="aw04formbutton" value="Go!" onClick='navigate_to()'>
	
	</td>
	</tr>
</table>
</div>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
	<td rowspan="1" valign="top" width="90%">

	{VAR:content}

	</td>

	<td valign="top" width="10%"  class="aw04kalenderkast01">

	{VAR:overview}


	<!-- SUB: TASKS -->

	<span style="font-size: 10px;">
		<div class="minical_header">
		<center>{VAR:tasks_title}</center>
		</div>

		<!-- SUB: TASK -->
		<div style="margin: 2px; border: 1px solid #CCC; background: #EEE;">
		<a href="{VAR:task_url}" style="text-decoration: none;">{VAR:task_name}</a>
		</div>
		<!-- END SUB: TASK -->
	</span>

	<!-- END SUB: TASKS -->

</td>
</tr>
</table>

