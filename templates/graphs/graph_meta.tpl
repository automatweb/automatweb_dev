<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td height="15" colspan="4" class="fgtitle">&nbsp;<b>{VAR:LC_GRAPH_GRAPH1}: <a href='{VAR:conf}'>{VAR:LC_GRAPH_CONFIG}</a>&nbsp;|&nbsp;<a href='{VAR:prev}'>{VAR:LC_GRAPH_PREW}</a>
		</b></td>
	</tr>
	<tr><td>&nbsp;</tr></td>
<tr><td>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td bgcolor="#CCCCCC">
<form method="post" action="reforb.{VAR:ext}">
<TABLE border=0>
<TR>
	<TD class="fcaption2">{VAR:LC_GRAPH_NAME}:<TD class="fcaption2"><input type="text" name="name" value="{VAR:name}">
<TR>
	<TD class="fcaption2">{VAR:LC_GRAPH_COMM}:<TD class="fcaption2"><textarea name="comment" COLS=50 ROWS=5 wrap='soft'>{VAR:comment}</textarea></tr>
<TR>
	<TD class="fcaption2">{VAR:LC_GRAPH_TYPE}:<td class="fcaption2">&nbsp;{VAR:type}&nbsp;
<TR>
	<TD class="fcaption2">{VAR:LC_GRAPH_DATA}:<td class="fcaption2">&nbsp;{VAR:andmed}&nbsp;

	</TABLE>   
<input type="submit" name="Submit" value="{VAR:LC_GRAPH_SAVE}">
</TABLE>
{VAR:reforb}
</form>
</TABLE>  