<!-- SUB: start -->
<table border="0" cellpadding="0" cellspacing="0" bgcolor="#EEEEEE" height="32" width="100%">
	<tr>
	<td>
	
	<table border="0" cellpadding="0" cellspacing="0" bgcolor="#EEEEEE">
	<tr>
<!-- END SUB: start -->

<!-- SUB: button -->
	<td><span class="toolbarbutton" onMouseOver="this.className='toolbarbuttonhover'" onMouseOut="this.className='toolbarbutton'" onMouseDown="this.className='toolbarbuttondown'" onMouseUp="this.className='toolbarbuttonhover'"><a alt="{VAR:tooltip}" href="{VAR:url}" target="{VAR:target}" onClick="{VAR:onClick}"><img src="{VAR:imgbase}/{VAR:img}" title="{VAR:tooltip}" alt="{VAR:tooltip}" border="0"></a></span></td>

<!-- END SUB: button -->

<!-- SUB: text_button -->
	<td nowrap="1"><span class="toolbarbutton" onMouseOver="this.className='toolbarbuttonhover'" onMouseOut="this.className='toolbarbutton'" onMouseDown="this.className='toolbarbuttondown'" onMouseUp="this.className='toolbarbuttonhover'"><a href="{VAR:url}" target="{VAR:target}" onClick="{VAR:onClick}" style="text-decoration: none;">{VAR:tooltip}</a></span></td>

<!-- END SUB: text_button -->

<!-- SUB: separator -->
	<td style="margin-left: 2px; border-right: 1px dotted #999; width: 2px; height: 25px;">&nbsp;</td>
<!-- END SUB: separator -->

<!-- SUB: cdata -->
	<td>{VAR:data}</td>
<!-- END SUB: cdata -->


<!-- SUB: end -->
	</tr>
	        </table>
		        </td>
<!-- END SUB: end -->

<!-- SUB: end_sep -->
<td align="right">
	<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td align="right" valign="center">
			{VAR:data}
	</td>
	</tr>
	</table>
</td>
<!-- END SUB: end_sep -->

<!-- SUB: real_end -->
</tr>

</table>
<!-- END SUB: real_end -->

