<table border="0" cellspacing="0" cellpadding="0" width=100%>
	<tr>
		<td height="15" colspan="4" class="fgtitle">&nbsp;<b>GRAAFIK: <a href='{VAR:conf}'>Konfima</a>&nbsp;|&nbsp;<a href='{VAR:prev}'>Eelvaade</a>
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
	<TD class="fcaption">Nimi:<TD class="fcaption"><input type="text" name="name" value="{VAR:name}">
<TR>
	<TD class="fcaption">Kommentaar:<TD class="fcaption"><textarea name="comment" COLS=50 ROWS=5 wrap='soft'>{VAR:comment}</textarea></tr>
<TR>
	<TD class="fcaption">Graafiku tüüp:<td class="fcaption">&nbsp;{VAR:type}&nbsp;
<TR>
	<TD class="fcaption">Andmed:<td class="fcaption">&nbsp;{VAR:andmed}&nbsp;

	</TABLE>   
<input type="submit" name="Submit" value="Salvesta">
</TABLE>
{VAR:reforb}
</form>
</TABLE>  