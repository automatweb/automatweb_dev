<!-- generic calendar template -->
<!--
	there have to be 2 kinds of events, linked and a non-linked one
-->



<!-- <header> -->
<!-- 1 -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td valign="top" class="caltableborderhele">

			
<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<!-- header is a separate subtemplate because this way we can switch it off if we need to -->

<!-- SUB: header -->
<tr>
	<td width="1%" class="caldayheadday">&nbsp;</td>

	<!-- header cells are repeated for each column -->

	<!-- SUB: header_cell -->
	<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
			<td width="{VAR:cellwidth}">

				<table width="100%"  border="0" cellpadding="0" cellspacing="0">
				<td class="caldayheadday">&nbsp;<a href="{VAR:dayorblink}">{VAR:hcell_weekday}</a></td>
				<td class="caldayheaddate"><a href="{VAR:dayorblink}">{VAR:hcell_date}</a>&nbsp;</td>
				</tr>
				<tr><td colspan="2" class="caltableborderhele"><IMG SRC="images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr></table>
			
			
			</td>
			<!--<td>{VAR:hcell}</td>-->
	<!-- END SUB: header_cell -->
</tr>
<!-- END SUB: header -->
<!-- </header> -->

<!-- contents -->
<!-- SUB: content_row -->
<tr>
	<td width="1%" class="caldayheadday">&nbsp;</td>

	<!-- SUB: content_cell -->
	<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>

			<td width="{VAR:cellwidth}" bgcolor="#FFFFFF" valign="top"><!--{VAR:bgcolor}-->

				<!-- nested table gives a greater flexibility -->
				<table width="100%" border="0" cellpadding="0" cellspacing="5">
				<tR><td>

				{VAR:cell}
				</td></tr></table>

			</td>
	<!-- END SUB: content_cell -->
</tr>
<!-- END SUB: content_row -->
</table>



			

		</td>
		</tr>
	<tr><td class="caltablebordertume"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
</table>

<!-- end 1-->
