
	<table border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" width="100%">
	<!--<tr>
	<td width="2%" class="caldayheadday">&nbsp;</td>-->
	<!-- SUB: header -->
		<!--<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
		<td width="14%" height="23" class="caldayheaddate" style="text-align: center; text-transform: uppercase;">
			<b>{VAR:headline}</b>
		</td>-->
	<!-- END SUB: header -->
	    
	<!--</tr>-->
	<!-- SUB: line -->
	<tr>
	<td valign="top" class="caldayheadday" style="vertical-align: top;">

		<a href="?class=planner&action=view&type=week&id={VAR:did}&date={VAR:date}"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/cal_nadal.gif" WIDTH="18" HEIGHT="19" BORDER=0 ALT="{VAR:LC_PLANNER_SHOW_WEEK}"></a>
	</td>
	<!-- SUB: subline -->
	<td width="1" class="caltableborderhele"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
		<td width="14%" bgcolor="{VAR:bgcolor}" valign="top">

				<table width="100%"  border="0" cellpadding="0" cellspacing="0">
				<td class="caldayheadday">&nbsp;<a href="#">R</a></td>
				<td class="caldayheaddate"><a href="?class=planner&action=view&type=month&id={VAR:did}&date={VAR:date}"
				>{VAR:dayname}</a>&nbsp;</td>
				
				</tr>
				<tr><td colspan="2" class="caltableborderhele"><IMG SRC="images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
				</table>


			<!-- SUB: showday -->
			<!--<div align="right"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="2" BORDER=0 ALT=""><br><a href="?class=planner&action=view&id={VAR:did}&date={VAR:date}"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/cal_notes.gif" WIDTH="15" HEIGHT="15" BORDER=0 ALT="{VAR:LC_PLANNER_SHOW_DAY}"></a><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></div>-->
			<!-- END SUB: showday -->


			<table width="100%" border="0" cellpadding="0" cellspacing="5">

				<!-- SUB: element -->
				<tr><td class="caleventtext"><font color="{VAR:color}">{VAR:time}</font><br>
				<a href="{VAR:event_link}"><font color="{VAR:color}"><b>{VAR:title}</font></a>{VAR:object}</b></td></tr>

				<!-- END SUB: element -->

					
	
				
				</table>
			



			

				<!--<font color="{VAR:color}"><i>{VAR:time}</i></font><br>"<a href="{VAR:event_link}"><font color="{VAR:color}">{VAR:title}</font></a>"{VAR:object}
				<p>-->

			
			


		</td>
		<!-- END SUB: subline -->
		
		
	</tr>
	<!-- END SUB: line -->
	</table>

<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr><td colspan="2" class="caltablebordertume"><IMG SRC="images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
</table>