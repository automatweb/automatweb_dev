<form method="post" action="reforb.{VAR:ext}">

<table border=0 cellspacing=0 cellpadding=0>
<tr><td class="aste01">

<table border=0 cellspacing=0 cellpadding=2>
<tr>

	<TD class="celltext" align="right">{VAR:LC_GRAPH_NAME}:</td>
	<TD class="celltext"><input type="text" name="name" value="{VAR:LC_GRAPH_GRAPH}" class="formtext" size="40"></td>
</tr>

<TR>
	<TD class="celltext" align="right">{VAR:LC_GRAPH_COMM}:</td>
	<TD class="celltext"><textarea name="comment" COLS=40 ROWS=5 wrap='soft' class="formtext"></textarea></td>
</tr>
<TR>
	<TD class="celltext">{VAR:LC_GRAPH_TYPE}:</TD>
	<TD class="celltext">
		<select name="type" value="" class="formselect2">
		<option value="2">{VAR:LC_GRAPH_POST}</option>
		<option value="1">{VAR:LC_GRAPH_LINE}</option>
		<option value="0">{VAR:LC_GRAPH_PIE}</option>
		</select>
	</td>

</TR>
<TR>
	<td>&nbsp;</td>
	<TD class="celltext"><input type="submit" name="Submit" value="{VAR:LC_GRAPH_FOR}" class="formbutton"></TD>
</TR>
   

</table>
</td></tr></table>

{VAR:reforb}
</form>
