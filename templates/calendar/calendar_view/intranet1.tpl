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
<br><br>
			<a href="{VAR:prevlink}"><img SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_left.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT="&lt;&lt;"></a> {VAR:caption}  <a href="{VAR:nextlink}"><img SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_right.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT="&gt;&gt;"></a>
<table border="0" width="100%">
<tr>
<td bgcolor="#F5F5F5">
	{VAR:overview}
	<div class="midText">
	<a href="{VAR:today_url}"><b>Täna</b></a>
	</div>
	{VAR:content}
</td>
</tr>
</table>

