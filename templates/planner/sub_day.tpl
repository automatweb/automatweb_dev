
<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<form method="POST">
<tr>
<td height="100%">





	<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
	<tr>
	<td class="caldayheaddate" style="text-align:center;" width="20" height="23">#</td>
	<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
	<td class="caldayheaddate" style="text-align:center;" width="100" height="23">{VAR:LC_PLANNER_TIME}</td>
	<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
	<td class="caldayheaddate" style="text-align:center;" width="200" height="23">{VAR:LC_PLANNER_TITLE}</td>
	<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
	<td class="caldayheaddate" style="text-align:center;" width="*" height="23">{VAR:LC_PLANNER_CONTENT}</td>
	</tr>
	<tr><td colspan="7" clasS="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
	<!-- SUB: line -->
	<tr class="aste00">
	<td width="20" align="center" valign="top">

			<table border="0" cellpadding="2" cellspacing="0"><tr><td class="celltext"><input type="checkbox" name="chk[{VAR:id}]" value="1"></td></tr></table>
	</td>
	<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>

	<td class="celltext" width="100" valign="top">

			<table border="0" cellpadding="5" cellspacing="0"><tr><td class="celltext">
			{VAR:time}
			</td></tr></table>
		
	</td>

	<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>

	<td width="200" valign="top">

			<table border="0" cellpadding="5" cellspacing="0"><tr><td class="celltext">
			<a href="{VAR:event_link}"><font color="{VAR:color}">{VAR:title}</font></a>{VAR:object}</td>
			</td></tr></table>

	<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>

	<td valign="top">

			<table border="0" cellpadding="5" cellspacing="0"><tr><td class="celltext">
			<font color="{VAR:color}">{VAR:contents}</font>
			</td></tr></table>

	</td></tr>
	<tr><td colspan="7" clasS="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
	<!-- END SUB: line -->

	<tr class="caldaysback">
	<td class="celltext" colspan="7">

			<table border="0" cellpadding="5" cellspacing="0"><tr><td class="celltext">
			<b>{VAR:total}</b> eventit
			</td></tr></table>

	</td>
	</tr>
	</table>
</td>
</tr>
<tr><td class="caltablebordertume"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
</form>
</table>

