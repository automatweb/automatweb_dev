<style type="text/css">
.minical_table {
	border-collapse: collapse;
	border: 1px solid #BCDCF0;
	font-family: Arial,sans-serif;
	font-size: 11px;
	padding: 3px;
	color: #000;
}
.minical_table a {
	color: #000;
	text-decoration: none;
}

.minical_table a:hover {
	color: #000;
}

.minical_header {
	background-color: #BCDCF0;
	text-align: center;
	border: 1px solid black;
}
.minical_cell {
	border: 1px solid #BCDCF0;
	padding: 3px;
	text-align: center;
}

.minical_cellact {
	border: 1px solid #BCDCF0;
	padding: 3px;
	background: #EEEEEE;
	text-align: center;
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

<table width="100%" border="0" cellpadding="3" cellspacing="0">
<tr>
<td align="right" style="font-size: 10px;">
<!-- SUB: PAGE -->
<a href="{VAR:link}">{VAR:text}</a> 
<!-- END SUB: PAGE -->

<!-- SUB: SEL_PAGE -->
[ {VAR:text} ] 
<!-- END SUB: SEL_PAGE -->
</td>
</tr>
<tr>
<td class="caldayheadday">
<a href="{VAR:prevlink}"><img SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_left.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT="&lt;&lt;"></a> {VAR:caption}  <a href="{VAR:nextlink}"><img SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_right.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT="&gt;&gt;"></a></td>
</tr>
</table>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td rowspan="2" valign="top" width="90%">
{VAR:content}
</td>
<td valign="top" width="10%">
{VAR:overview}
</td>
</tr>
<tr>
<td align="center" valign="top" width="10%">
<form id='naviform' style='display: inline'>
<select id='navi_month' name='month'>
{VAR:mnames}
</select>
<select id='navi_year' name='year'>
{VAR:years}
</select>
<input type="button" value="Go!" onClick='navigate_to()'>
<a href="{VAR:today_url}">Täna</a>
</form>
</center>
</td>
</tr>
</table>

