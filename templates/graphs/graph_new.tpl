<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td height="15" colspan="4" class="fgtitle">&nbsp;<b>
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
	<TD class="fcaption2">{VAR:LC_GRAPH_NAME}:<TD colspan=2 class="fcaption2"><input type="text" name="name" value="{VAR:LC_GRAPH_GRAPH}">
<TR>
	<TD class="fcaption2">{VAR:LC_GRAPH_COMM}:<TD colspan=2 class="fcaption2"><textarea name="comment" COLS=50 ROWS=5 wrap='soft'></textarea></tr>
<TR>
	<TD colspan=3 class="fcaption2">{VAR:LC_GRAPH_TYPE}:</TD>
</TR>
	<TD colspan=3 class="fcaption2">
		<select name="type" value="">
		<option value="2">{VAR:LC_GRAPH_POST}</option>
		<option value="1">{VAR:LC_GRAPH_LINE}</option>
		<option value="0">{VAR:LC_GRAPH_PIE}</option>
		</select>	

</TR>
</TABLE>   
<input type="submit" name="Submit" value="{VAR:LC_GRAPH_FOR}">
</TABLE>
{VAR:reforb}
</form>
</TABLE>  