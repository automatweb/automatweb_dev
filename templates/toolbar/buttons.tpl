<!-- SUB: start -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
	<td width="1" background="{VAR:baseurl}/automatweb/images/awmenueditor_iconbar_back.gif"><div style="width:5px;height:32px" /></td>
	<td height="32" background="{VAR:baseurl}/automatweb/images/awmenueditor_iconbar_back.gif" align="{VAR:align}">
	<table border="0" cellpadding="0" cellspacing="0">
	<tr>
<!-- END SUB: start -->

<!-- SUB: button -->
	<td valign='middle'><a title="{VAR:tooltip}" alt="{VAR:tooltip}" href="{VAR:url}" target="{VAR:target}" class="{VAR:class}" onClick="{VAR:onClick}" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('{VAR:name}','','{VAR:imgbase}/{VAR:imgover}',1)"><IMG name="{VAR:name}" SRC="{VAR:imgbase}/{VAR:img}" WIDTH="23" HEIGHT="22" BORDER=0 ALT="{VAR:tooltip}" title="{VAR:tooltip}" /></a></td>
	<td><div style="width:5px;height:1px" /></td>
<!-- END SUB: button -->

<!-- SUB: separator -->
	<td valign='middle'><div style="width:1px;height:22px;border-right:2px solid #ccbbbb;" /></td>
	<td><div style="width:5px;height:1px" /></td>
<!-- END SUB: separator -->

<!-- SUB: cdata -->
	<td valign='middle'>{VAR:data}</td>
	<td><div style="width:5px;height:1px" /></td>
<!-- END SUB: cdata -->


<!-- SUB: end -->
	</tr>
	</table>
	</td>
<!-- END SUB: end -->

<!-- SUB: end_sep -->
<td height="32" background="{VAR:baseurl}/automatweb/images/awmenueditor_iconbar_back.gif" align="right">
	<table background="{VAR:baseurl}/automatweb/images/trans.gif" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td align="right">{VAR:data}</td>
		</tr>
	</table>
</td>
<!-- END SUB: end_sep -->

<!-- SUB: real_end -->
</tr>

</table>
<!-- END SUB: real_end -->
