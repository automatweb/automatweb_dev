<!-- SUB: start -->
<!--<div class="aw04toolbar">-->

<table class="aw04toolbar" width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
	<td>

	<table border="0" cellpadding="0" cellspacing="0">
	<tr>
<!-- END SUB: start -->

<!-- SUB: button -->
 	<td nowrap class="aw04toolbarbutton" onMouseOver="this.className='aw04toolbarbuttonhover'" onMouseOut="this.className='aw04toolbarbutton'" onMouseDown="this.className='aw04toolbarbuttondown'" onMouseUp="this.className='aw04toolbarbuttonhover'" title="{VAR:tooltip}" alt="{VAR:tooltip}"><a href="{VAR:url}" target="{VAR:target}" onClick="{VAR:onClick}"><img src="{VAR:imgbase}/{VAR:img}" border="0"></a></td>
<!-- END SUB: button -->

<!-- SUB: menu_button -->
	<td nowrap class="aw04toolbarbutton" valign="center" onMouseOver="this.className='aw04toolbarbuttonhover'" onMouseOut="this.className='aw04toolbarbutton'" onMouseDown="this.className='aw04toolbarbuttondown'" onMouseUp="this.className='aw04toolbarbuttonhover'" title="{VAR:tooltip}" alt="{VAR:tooltip}">
		<table cellpadding=0 cellspacing=0>
			<tr>
				<td valign='bottom'>
				<a href="{VAR:url}" target="{VAR:target}" onClick="{VAR:onClick}"><img src="{VAR:imgbase}/{VAR:img}" border="0"></a>
				</td>
				<td valign='bottom'>
					<a href="{VAR:url}" target="{VAR:target}" onClick="{VAR:onClick}"><img src="{VAR:imgbase}/downarr.png" border="0"></a></td>
				</a>
			</tr>
		</table>
	</td>
<!-- END SUB: menu_button -->

<!-- SUB: text_button -->
 <td nowrap class="aw04toolbarbutton" valign="center" onMouseOver="this.className='aw04toolbarbuttonhover'" onMouseOut="this.className='aw04toolbarbutton'" onMouseDown="this.className='aw04toolbarbuttondown'" onMouseUp="this.className='aw04toolbarbuttonhover'" title="{VAR:tooltip}" alt="{VAR:tooltip}"><a href="{VAR:url}" target="{VAR:target}" onClick="{VAR:onClick}" style="text-decoration: none;">{VAR:tooltip}</a></td>
<!-- END SUB: text_button -->

<!-- SUB: separator -->
	<td class="aw04buttonsep" nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/trans.gif" width="1" height="1" border="0" alt="------"></td>
<!-- END SUB: separator -->

<!-- SUB: cdata -->
	 <td class="aw04toolbardata">{VAR:data}</td>
<!-- END SUB: cdata -->


<!-- SUB: end -->
	</tr>
	</table>
</td>
<!--</div>-->
<!-- END SUB: end -->

<!-- SUB: right_side -->
<td align="right"  class="aw04buttongroup">
	<table border="0" cellspacing="0" cellpadding="0">
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

