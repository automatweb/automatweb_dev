<form method=POST action="reforb.{VAR:ext}">
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td class="fcaption">Nimetus</td>
	<td class="fform"><input type="text" size="40" name="description"></td>
</tr>
<tr>
	<td class="fcaption">Algus</td>
	<td class="fform">P‰ev: <input type="text" name="sday" size="2" maxlength="2">
	Kuu: <select name="smonth">
	<option value="0"> </option>
	<option value="1">jaanuar</option>
	<option value="2">veebruar</option>
	<option value="3">m‰rts</option>
	<option value="4">aprill</option>
	<option value="5">mai</option>
	<option value="6">juuni</option>
	<option value="7">juuli</option>
	<option value="8">august</option>
	<option value="9">september</option>
	<option value="10">oktoober</option>
	<option value="11">november</option>
	<option value="12">detsember</option>
	</select>
	Aasta: <input type="text" name="syear" size="4" maxlength="4"> Kellaaeg:
	<input type="text" name="stime[0]" size="2" maxlength="2">:<input type="text" name="stime[1]" size="2" maxlength="2">
	</td>
</tr>
<tr>
	<td class="fcaption">Lopp</td>
	<td class="fform">P‰ev: <input type="text" name="eday" size="2" maxlength="2">
	Kuu: <select name="emonth">
	<option value="0"> </option>
	<option value="1">jaanuar</option>
	<option value="2">veebruar</option>
	<option value="3">m‰rts</option>
	<option value="4">aprill</option>
	<option value="5">mai</option>
	<option value="6">juuni</option>
	<option value="7">juuli</option>
	<option value="8">august</option>
	<option value="9">september</option>
	<option value="10">oktoober</option>
	<option value="11">november</option>
	<option value="12">detsember</option>
	</select>
	Aasta: <input type="text" name="eyear" size="4" maxlength="4">
	Kellaaeg:
	<input type="text" name="etime[0]" size="2" maxlength="2">:<input type="text" name="etime[1]" size="2" maxlength="2">
</td>
</tr>
<tr>
	<td class="fcaption">M‰‰rangud</td>
	<td class="fform">Aktiveerub ise: <input type="checkbox" name="autoactivate"></td>
</tr>
<tr>
	<td class="fform" colspan="2" align="center">
	<input type="submit" value="Lisa periood">
	{VAR:reforb}
</tr>
</table>
</form>
