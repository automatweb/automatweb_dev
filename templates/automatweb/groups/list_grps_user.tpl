<form action='reforb.{VAR:ext}' METHOD=POST>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td bgcolor="#CCCCCC">

			<table border="0" cellspacing="1" cellpadding="0" width=100%>
				<tr>
					<td height="15" colspan="11" class="fgtitle">&nbsp;<b>GRUPID: <a href='{VAR:addgrp}'>Lisa</a></b></td>
				</tr>
				<tr>
					<td align="center" class="title">&nbsp;Name&nbsp;</td>
					<td align="center" class="title">&nbsp;Priority&nbsp;</td>
					<td align="center" class="title">&nbsp;Type&nbsp;</td>
					<td align="center" class="title">&nbsp;Members&nbsp;</td>
					<td align="center" class="title">&nbsp;Changer&nbsp;</td>
					<td align="center" class="title">&nbsp;Changed&nbsp;</td>
					<td align="center" colspan="3" class="title">&nbsp;Action&nbsp;</td>
				</tr>

			<!-- SUB: LINE -->
				<tr>
					<td class="fgtext">&nbsp;<a href='{VAR:grpmembers}'>{VAR:name}</a>&nbsp;</td>
					<td class="fgtext">&nbsp;
					<input type='text' size=10 class='small_button' NAME='gp[{VAR:gid}]' VALUE='{VAR:priority}'><input type='hidden' NAME='gl_{VAR:gid}' VALUE='{VAR:level}'>&nbsp;</td>
					<td class="fgtext">&nbsp;{VAR:type}&nbsp;</td>
					<td class="fgtext">&nbsp;{VAR:members}&nbsp;</td>
					<td align="center" class="fgtext">&nbsp;{VAR:modifiedby}&nbsp;</td>
					<td align="center" class="fgtext">&nbsp;{VAR:modified}&nbsp;</td>
					<td class="fgtext2">&nbsp;<a href='{VAR:change}'>Muuda</a>&nbsp;</td>
					<td class="fgtext2">&nbsp;<a href="javascript:box2('Oled kindel, et soovid seda gruppi  kustutada?','{VAR:delete}')">Kustuta</a>&nbsp;</td>
					<td class="fgtext2">&nbsp;<a href="editacl.{VAR:ext}?oid={VAR:goid}&file=default.xml">ACL</a>&nbsp;</td>
				</tr>
			<!-- END SUB: LINE -->
				<tr>
					<td class="fgtext">&nbsp;</td>
					<td class="fgtext"><input type='submit' VALUE='Save' class='small_button'></td>
					<td class="fgtext">&nbsp;</td>
					<td class="fgtext">&nbsp;</td>
					<td class="fgtext">&nbsp;</td>
					<td class="fgtext">&nbsp;</td>
					<td class="fgtext">&nbsp;</td>
					<td class="fgtext">&nbsp;</td>
					<td class="fgtext">&nbsp;</td>
				</tr>
			</table>

		</td>
	</tr>
</table>
{VAR:reforb}
</form>
{VAR:userlist}
