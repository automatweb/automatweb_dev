<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
<td class="header4">
<b>{VAR:LC_MSGBOARD_BIG_SUBJECT}:</b>
<a href="{VAR:newtopic_link}">Uus teema</a> |
<a href="{VAR:search_link}">Otsi</a> |
<a href="{VAR:forum_link}">Teemad</a> |
<a href="{VAR:flat_link}">Postitamise järjekorras</a>
</td>
</tr>
</table>
<!-- SUB: TOPIC -->
<table border="0" cellspacing="1" cellpadding="0" width="100%">
<tr>
<td bgcolor="#ffffff" class="header4"><b>Teema:</b> {VAR:topic}</td>
<td bgcolor="#ffffff" class="header4"><b>Postitas:</b> {VAR:from} @ {VAR:created}</td>
</tr>
<tr>
<td bgcolor="#FFFFFF" class="header4" colspan="2">
{VAR:text}
</td>
</tr>
</table>
<!-- END SUB: TOPIC -->
<br>
<!-- SUB: message -->
<table width="500" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>{VAR:spacer}
</td>
<td>
<table width="500" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td bgcolor="#CCCCCC" class="header3"><a name="c{VAR:id}">{VAR:LC_MSGBOARD_WHO}: <a href="mailto:{VAR:email}"><b>{VAR:from}</b></a> @ {VAR:time} p = {VAR:parent}</td>
  </tr>
  <tr>
    <td height="18" class="header4" valign="top">{VAR:LC_MSGBOARD_SUBJECT}: <b>{VAR:subj}</b></td>
  </tr>
  <tr>
    <td class="header4">{VAR:comment}</td>
	</tr>
  <tr>
    <td class="header4" valign="bottom" height="18"> <a href="{VAR:reply_link}"><b>{VAR:LC_MSGBOARD_ANSWER}</b></a>
		</td>
  </tr>
</table>
</td>
</tr>
</table>
<!-- END SUB: message -->
