<table border="0" cellspacing="1" cellpadding="3" bgcolor="#CCCCCC">
<!-- SUB: directory -->
<tr>
<td class="fgtext" align="center"><img src="images/ftv2folderclosed.gif"></td>
<td class="fgtext" colspan="7"><a href="{VAR:dirlink}">{VAR:name}</a></td>
<td class="fgtext">{VAR:date}
</tr>
<!-- END SUB: directory -->
</table>
<br>
<script language="JavaScript">
function submit_files()
{
	document.browse.submit();
};
</script>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#CCCCCC">
<form method="POST" name="browse">
<tr>
<td>
<table border="0" width="100%" cellspacing="1" cellpadding="2">
<tr bgcolor="#DDDDDD">
<td class="title" colspan="7" bgcolor="#DDDDDD"><b>Failid:</b>
<a href="javascript:submit_files()"><span style="color: red; font-weight: bold">Salvesta</span></a>
</td>
</tr>
{VAR:files}
</table>
</td>
</tr>
{VAR:reforb}
</form>
</table>
