<TABLE  BORDER="0" CELLSPACING="0" CELLPADDING="0" width="120">
<tr><td colspan="8">

	<table BORDER="0" CELLSPACING="0" CELLPADDING="2">
	<tr class="calmonthback">
	<td width="19" class="celltext"><a href="{VAR:prevorb}"><IMG border="0" SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_left.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT=""></a></td>
	<td width="73" class="celltext" align="center" colspan="5">{VAR:caption}</td>
	<td width="19" class="celltext"><a href="{VAR:nextorb}"><IMG border="0" SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_right.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT=""></a></td>
	</tr></table>

</td></tr>
<tr>
<td class="caldayname" width="8" height="17"><img src="{VAR:baseurl}/automatweb/images/blue/trans.gif" border="0" width="8" height="17" alt=""></td>
<td class="caldayname" width="16" height="17">E</td>
<td class="caldayname" width="16" height="17">T</td>
<td class="caldayname" width="16" height="17">K</td>
<td class="caldayname" width="16" height="17">N</td>
<td class="caldayname" width="16" height="17">R</td>
<td class="caldayname" width="16" height="17"><font color="red">L</font></td>
<td class="caldayname" width="16" height="17"><font color="red">P</font></td>
</tr>

<!-- SUB: week -->
<tr>
<td class="caldayname" width="8" height="17"><a href="{VAR:weekorblink}"><img src="{VAR:baseurl}/automatweb/images/blue/cal_nooleke.gif" border="0" width="8" height="17" alt=""></a></td>

<!-- SUB: empty -->
<td class="calday" align="center" valign="middle">&nbsp;</td>
<!-- END SUB: empty -->

<!-- SUB: cell -->
<td class="{VAR:markup_style}"  align="center" valign="middle"><a href="{VAR:dayorblink}">{VAR:nday}</a>&nbsp;</td>
<!-- END SUB: cell -->

<!-- SUB: activecell -->
<td class="caltoday" align="center" valign="middle"><a href="{VAR:dayorblink}">{VAR:nday}</a>&nbsp;</td>
<!-- END SUB: activecell -->

</tr>
<!-- END SUB: week -->


</table>
