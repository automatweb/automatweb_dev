<script language=javascript>
window.parent.parent.parent.frames[0].document.links[1].href="mail.{VAR:ext}?type=reply&id={VAR:id}";
window.parent.parent.parent.frames[0].document.links[2].href="mail.{VAR:ext}?type=forward&id={VAR:id}";
window.parent.parent.parent.frames[0].document.links[3].href="mail.{VAR:ext}?type=print&id={VAR:id}";
window.parent.parent.parent.frames[0].document.links[4].href="mail.{VAR:ext}?type=delete&id={VAR:id}";
</script>
<table border=0 cellpadding=0 cellspacing=0 width=100%>
<tr>
<td bgcolor=#a0a0a0>
<table border=0 cellpadding=0 cellspacing=0>
<tr>
	<td align=left bgcolor=#a0a0a0><b>From:</b>&nbsp;{VAR:from}&nbsp;&nbsp;&nbsp;<b>To:</b>&nbsp;{VAR:to}</td>
</tr>
<tr>
	<td align=left bgcolor=#a0a0a0><b>Subject:</b>&nbsp;{VAR:subject}</td>
</tr>
</table></td></tr></table><pre>{VAR:message}</pre>