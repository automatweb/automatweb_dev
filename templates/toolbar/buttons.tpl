<!-- SUB: start -->
<table border="0" cellpadding="0" cellspacing="0" bgcolor="#EEEEEE" height="30" width="100%">
	<tr>
	<td>
	
	<!--<table border="0" cellpadding="0" cellspacing="0" bgcolor="#EEEEEE" height="30" style="border-collapse: collapse;">-->
	<table border="0" cellpadding="0" cellspacing="0" bgcolor="#EEEEEE" height="30">
	<tr>
<!-- END SUB: start -->

<!-- SUB: button -->
	<td class="toolbarbutton" onMouseOver="this.className='toolbarbuttonhover'" onMouseOut="this.className='toolbarbutton'" onMouseDown="this.className='toolbarbuttondown'" onMouseUp="this.className='toolbarbuttonhover'" title="{VAR:tooltip}" alt="{VAR:tooltip}"><a href="{VAR:url}" target="{VAR:target}" onClick="{VAR:onClick}"><img src="{VAR:imgbase}/{VAR:img}" border="0"></a></td>
<!-- END SUB: button -->

<!-- SUB: menu_button -->
	<td class="toolbarbutton" valign="center" onMouseOver="this.className='toolbarbuttonhover'" onMouseOut="this.className='toolbarbutton'" onMouseDown="this.className='toolbarbuttondown'" onMouseUp="this.className='toolbarbuttonhover'" title="{VAR:tooltip}" alt="{VAR:tooltip}"><a href="{VAR:url}" target="{VAR:target}" onClick="{VAR:onClick}"><img src="{VAR:imgbase}/{VAR:img}" border="0"><img src="{VAR:imgbase}/downarr.png" border="0"></a></td>
<!-- END SUB: menu_button -->

<!-- SUB: text_button -->
	<td nowrap="1"><span class="toolbarbutton" onMouseOver="this.className='toolbarbuttonhover'" onMouseOut="this.className='toolbarbutton'" onMouseDown="this.className='toolbarbuttondown'" onMouseUp="this.className='toolbarbuttonhover'"><a href="{VAR:url}" target="{VAR:target}" onClick="{VAR:onClick}" style="text-decoration: none;">{VAR:tooltip}</a></span></td>

<!-- END SUB: text_button -->

<!-- SUB: separator -->
	<td style="width: 2px;">
	<td style="background-color: #CCC; width: 1px;"></td>
	<td style="background-color: #FFF; width: 1px;"></td>
<!-- END SUB: separator -->

<!-- SUB: cdata -->
	<td>{VAR:data}</td>
<!-- END SUB: cdata -->


<!-- SUB: end -->
	</tr>
	</table>
	</td>
<!-- END SUB: end -->

<!-- SUB: right_side -->
<td align="right">
	<table border="0" cellspacing="0" cellpadding="0" height="30">
	<tr>
	<td align="right" valign="center">
			{VAR:right_side_content}
	</td>
	</tr>
	</table>
</td>
<!-- END SUB: right_side -->

<!-- SUB: real_end -->
</tr>

</table>
<!-- END SUB: real_end -->

