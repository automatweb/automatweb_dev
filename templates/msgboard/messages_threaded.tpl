<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td class="fgtitle">
<b>{VAR:LC_MSGBOARD_BIG_SUBJECT}:</b>
<a href="{VAR:flat_link}">Flat</a>
</td>
</tr>
</table>
<br>
<!-- SUB: message -->
<tr>
<td>
{VAR:spacer}
<table width="500" border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td rowspan="5">{VAR:spacer}</td>
	</tr>
  <tr>
    <td class="header3" bgcolor="#CCCCCC"><a name="c{VAR:id}">{VAR:LC_MSGBOARD_WHO}: <a href="mailto:{VAR:email}"><b>{VAR:from}</b></a> @ {VAR:time} p = {VAR:parent}</td>
  </tr>
  <tr>
    <td height="18" valign="top" class="header4">{VAR:LC_MSGBOARD_SUBJECT}: <b>{VAR:subj}</b></td>
  </tr>
  <tr>
    <td class="header4">{VAR:comment}</td>
	</tr>
  <tr>
    <td valign="bottom" height="18" class="header4"> <a href="{VAR:reply_link}"><b>{VAR:LC_MSGBOARD_ANSWER}</b></a>
		</td>
  </tr>
</table>
<!-- END SUB: message -->
