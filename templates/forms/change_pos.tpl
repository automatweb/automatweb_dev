<form method=POST action='reforb.{VAR:ext}'>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td colspan=2 class="fform">Vali kataloog, kus element asub:</td>
</tr>
<tr>
	<td colspan=2 class="fform"><select name='parent' class='small_button'>{VAR:folders}</select></td>
</tr>
<tr>
	<td class="fform">Elemendi nimi:</td>
	<td class="fform"><input type="text" name="name" value="{VAR:name}"></td>
</tr>
<tr>
	<td class="fform" colspan=2>Vali cell, kus element asub:</td>
</tr>
<tr>
	<td class="fform" colspan=2>
		<table border=1 cellpadding=0 cellspacing=0>
			<!-- SUB: ROW -->
			<tr>
				<!-- SUB: COL -->
					<td class="fform"><input type='radio' name='s_cell' value='{VAR:row}_{VAR:col}' {VAR:checked}></td>
				<!-- END SUB: COL -->
			</tr>
			<!-- END SUB: ROW -->
		</table>
	</td>
</tr>
<tr>
	<td class="fform" colspan="3" align="center">
	{VAR:reforb}
	<input type="submit" class='small_button' value="Salvesta">
	</td>
</tr>
</table>
</form>
