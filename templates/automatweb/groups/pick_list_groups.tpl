<form action='refcheck.{VAR:ext}' METHOD=POST>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td bgcolor="#CCCCCC">

			<table border="0" cellspacing="1" cellpadding="0" width=100%>
				<tr>
					<td height="15" colspan="11" class="fgtitle">&nbsp;<b>USERS:
					<!-- SUB: CAN_EDIT -->
						<a href='{VAR:urlgrp}'>This group</a>&nbsp;|&nbsp;<a href='{VAR:urlall}'>Add new</a>&nbsp;|&nbsp;<a href='{VAR:urlgrps}'>Groups</a>
					<!-- END SUB: CAN_EDIT -->
					</b></td>
				</tr>
				<tr>
					<td align="center" class="title">&nbsp;Choose&nbsp;</td>
					<td align="center" class="title">&nbsp;Name&nbsp;</td>
					<td align="center" class="title">&nbsp;Priority&nbsp;</td>
					<td align="center" class="title">&nbsp;Type;</td>
					<td align="center" class="title">&nbsp;Members&nbsp;</td>
					<td align="center" class="title">&nbsp;Changer&nbsp;</td>
					<td align="center" class="title">&nbsp;Changed&nbsp;</td>
				</tr>

			<!-- SUB: LINE -->
				<tr>
					<td class="fgtext" align=center>&nbsp;
					<!-- SUB: CHECK -->
						<input type='checkbox' NAME='gs_{VAR:gid}' VALUE=1 {VAR:grp_check}>
						<input type='hidden' NAME='gm_{VAR:gid}' VALUE='{VAR:member}'>
					<!-- END SUB: CHECK -->
					&nbsp;</td>
					<td height="15" class="fgtext">
						<table border=0 cellspacing=0 cellpadding=0 bgcolor=#ffffff vspace=0 hspace=0>
							<tr>
								<td>{VAR:space_images}{VAR:image}</td>
								<td valign=center class="fgtext">&nbsp;{VAR:name}&nbsp;</td>
							</tr>
						</table>
					</td>

					<td class="fgtext">&nbsp;{VAR:priority}&nbsp;</td>
					<td class="fgtext">&nbsp;{VAR:type}&nbsp;</td>
					<td class="fgtext">&nbsp;{VAR:members}&nbsp;</td>
					<td align="center" class="fgtext">&nbsp;{VAR:modifiedby}&nbsp;</td>
					<td align="center" class="fgtext">&nbsp;{VAR:modified}&nbsp;</td>
				</tr>
			<!-- END SUB: LINE -->
			</table>

		</td>
	</tr>
</table>
<input type='submit' class='small_button' VALUE='Save'>
<input type='hidden' NAME='action' VALUE='submit_grp_groups'>
<input type='hidden' NAME='from' VALUE='{VAR:from}'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
</form>
{VAR:userlist}
