<table border="0" cellspacing="0" cellpadding="0">
	<form method="get" action="reforb.{VAR:ext}">
	<tr>
		<td align="right" class="fgtitle">{VAR:LC_MSGBOARD_NAME}:&nbsp;</td>
		<td class="fgtext"><input type="text" name="from" size="25"></td>
	</tr>

	<tr>
		<td align="right" class="fgtitle">E-mail:&nbsp;</td>
		<td class="fgtext"><input type="text" NAME="email" size="25"></td>
	</tr>

	<tr>
		<td align="right" class="fgtitle">Subjekt:&nbsp;</td>
		<td class="fgtext"><input type="text" NAME="subj" size="25" ></td>
	</tr>
	
	<tr>
		<td align="right" valign="top" class="fgtitle">{VAR:LC_MSGBOARD_COMMENTARY}:&nbsp;</td>
		<td class="fgtext" valign="top"><textarea  cols="30" name="comment" rows="8" wrap="virtual"></textarea></td>
	</tr>

	<tr>
		<td class="fgtitle">&nbsp;</td>
		<td class="fgtitle" align="center">&nbsp;<input type="submit" value="{VAR:LC_MSGBOARD_SEARCH}" class="textSmall"></td>
		{VAR:reforb}
	</form>
	</tr>
</table>
