<!-- generic calendar template -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td valign="top">

			
<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
	<td>


<!-- <header> -->
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<!-- header is a separate subtemplate because this way we can switch it off if we need to -->
<!-- SUB: header -->
<tr>
	<!-- spacer for timetamps -->
		<td width="5%" class="caldayheaddate">Time</td>
	<!-- header cells are repeated for each column -->
	<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
		<td class="caldayheadday" width="95%">{VAR:hcell}</td>
</tr>
<!-- END SUB: header -->
<!-- </header -->

<!-- contents -->
<!-- SUB: content -->
<tr class="caldayname">
	<!-- separate column for timestamps -->
	<td valign="top" width="5%" class="caldayname">

		<table border="0">
	<!-- SUB: timestamp -->
		<tr>
		<td>
		<a href="{VAR:add_link}">{VAR:time}</a>
		</td>
		</tr>
	<!-- END SUB: timestamp -->
	<!-- SUB: timestamp2 -->
		<tr>
		<td><small>
		<a href="{VAR:add_link}">{VAR:time}</a>
		</small></td>
		</tr>
	<!-- END SUB: timestamp2 -->
		</table>

	</td>
	<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
	<td valign="top" width="95%">
	<!-- nested table gives a greater flexibility -->
	<table border="0" width="100%" height="100%">
	<tr>
	<td valign="top">
	{VAR:cell}
	</td>
	</tr>
	</table>
	</td>
</tr>
<!-- END SUB: content -->
</table>




	</td>
</tr>
</table>

			

		</td>
		</tr>
	<tr><td class="caltablebordertume"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
</table>
