<form action='reforb.{VAR:ext}' method="POST">
<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td bgcolor="#CCCCCC">
		<table border="0" cellspacing="1" cellpadding="2" width=100%>
			<tr>
				<td height="15" class="fgtext">&nbsp;Admin language:&nbsp;</td>
				<td height="15" class="fgtext">&nbsp;
					<!-- SUB: LANG -->
						{VAR:lang_name} <input type='radio' name='adminlang' VALUE='{VAR:lang_id}' {VAR:checked}>
					<!-- END SUB: LANG -->
				</td>
			</tr>
			<tr>
				<td class="fgtext">Kasutaja aktiivne alates:</td>
				<td class="fgtext">{VAR:act_from}</td>
			</tr>
			<tr>
				<td class="fgtext">Kasutaja aktiivne kuni:</td>
				<td class="fgtext">{VAR:act_to}</td>
			</tr>
			<tr>
				<td height="15" class="fgtext" colspan=2>&nbsp;<input class='small_button' type='submit' value='Save'>&nbsp;</td>
			</tr>
		</table>
		</td>
	</tr>
</table>
{VAR:reforb}
</form>
Kasutaja info:
{VAR:form}
