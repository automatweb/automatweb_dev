
<form action='refcheck.{VAR:ext}' METHOD=POST name='boo'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td bgcolor="#CCCCCC">

			<table border="0" cellspacing="1" cellpadding="0" width=100%>
				<tr>
					<td height="15" colspan="11" class="fgtitle">&nbsp;<b>GRUPID: 
						<!-- SUB: CAN_ADD -->
							<a href='groups.{VAR:ext}?type=add&parent={VAR:parent}&grp_level={VAR:grp_level}'>Add</a> | 
						<!-- END SUB: CAN_ADD -->		
							<a href='javascript:boo.submit()'>Save</a>
					</b></td>
				</tr>
				<tr>
					<td align="center" class="title">&nbsp;Nimi&nbsp;</td>
					<td align="center" class="title">&nbsp;Prioriteet&nbsp;</td>
					<td align="center" class="title">&nbsp;Tüüp&nbsp;</td>
					<td align="center" class="title">&nbsp;Liikmeid&nbsp;</td>
					<td align="center" class="title">&nbsp;Muutja&nbsp;</td>
					<td align="center" class="title">&nbsp;Muudetud&nbsp;</td>
					<td align="center" colspan="3" class="title">&nbsp;Tegevus&nbsp;</td>
				</tr>

			<!-- SUB: LINE -->
				<tr>
					<td height="15" class="fgtext">
						<table border=0 cellspacing=0 cellpadding=0 bgcolor=#ffffff vspace=0 hspace=0>
							<tr>
								<td>{VAR:space_images}{VAR:image}</td>
								<td valign=center class="fgtext">&nbsp;{VAR:name}&nbsp;</td>
							</tr>
						</table>
					</td>

					<td class="fgtext">&nbsp;
					<!-- SUB: CAN_PRIORITY --> 
					<input type='text' size=10 class='small_button' NAME='gp_{VAR:gid}' VALUE='{VAR:priority}'><input type='hidden' NAME='gl_{VAR:gid}' VALUE='{VAR:level}'>
					<!-- END SUB: CAN_PRIORITY --> 
					&nbsp;</td>
					<td class="fgtext">&nbsp;{VAR:type}&nbsp;</td>
					<td class="fgtext">&nbsp;{VAR:members}&nbsp;</td>
					<td align="center" class="fgtext">&nbsp;{VAR:modifiedby}&nbsp;</td>
					<td align="center" class="fgtext">&nbsp;{VAR:modified}&nbsp;</td>
					<td class="fgtext2">&nbsp;
						<!-- SUB: CAN_CHANGE -->
						<a href='groups.{VAR:ext}?type=change&gid={VAR:gid}&parent={VAR:parent}'>Muuda</a>
						<!-- END SUB: CAN_CHANGE -->
					&nbsp;</td>
					<td class="fgtext2">&nbsp;
						<!-- SUB: CAN_DELETE -->
						<a href="javascript:box2('Are You sure You wish to delete this group?','groups.{VAR:ext}?type=delete&gid={VAR:gid}&parent={VAR:parent}')">Delete</a>
						<!-- END SUB: CAN_DELETE -->
					&nbsp;</td>
					<td class="fgtext2">&nbsp;
						<!-- SUB: CAN_ACL -->
						<a href="editacl.{VAR:ext}?oid={VAR:goid}&file=group.xml">ACL</a>
						<!-- END SUB: CAN_ACL -->
					&nbsp;</td>
				</tr>
			<!-- END SUB: LINE -->
			</table>

		</td>
	</tr>
</table>
<input type='hidden' NAME='action' VALUE='update_grp_priorities'>
<input type='hidden' NAME='from' VALUE='{VAR:from}'>
</form>
{VAR:userlist}
