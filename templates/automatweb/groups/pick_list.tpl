<form action='reforb.{VAR:ext}' METHOD=POST name='foo'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td bgcolor="#CCCCCC">

			<table border="0" cellspacing="1" cellpadding="0" width=100%>
				<tr>
					<td height="15" colspan="11" class="fgtitle">&nbsp;<b>GRUPID: <a href="javascript:document.foo.submit()">Lisa grupid</a></b></td>
				</tr>
				<tr>
					<td align="center" class="title">&nbsp;Name&nbsp;</td>
					<td align="center" class="title">&nbsp;Choose&nbsp;</td>
					<td align="center" class="title">&nbsp;Prioritynbsp;</td>
					<td align="center" class="title">&nbsp;Type&nbsp;</td>
					<td align="center" class="title">&nbsp;Members&nbsp;</td>
					<td align="center" class="title">&nbsp;Changer&nbsp;</td>
					<td align="center" class="title">&nbsp;Changed&nbsp;</td>
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
					<td class="fgtext" align=center>&nbsp;
					<!-- SUB: CHECK -->
						<input type='checkbox' NAME='ga_{VAR:gid}' VALUE=1 {VAR:grp_check}>
						<input type='hidden' NAME='gb_{VAR:gid}' VALUE='{VAR:member}'>
					<!-- END SUB: CHECK -->
					&nbsp;</td>

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
<input type='submit' class='small_button' VALUE='Add groups'>
<input type='hidden' NAME='action' VALUE='submit_acl_groups'>
<input type='hidden' NAME='class' VALUE='acl'>
<input type='hidden' NAME='reforb' VALUE='1'>
<input type='hidden' NAME='from' VALUE='{VAR:from}'>
<input type='hidden' NAME='oid' VALUE='{VAR:oid}'>
</form>
{VAR:userlist}
