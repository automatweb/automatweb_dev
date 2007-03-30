{VAR:pop}
<table border=0>
	<tr>
		<td>Vali n&auml;dal</td>
		<td>{VAR:week_select}
<a href='#' onClick='window.location.reload()'><img src='{VAR:baseurl}/automatweb/images/icons/refresh.gif' border='0'></a></td>
		<td>
			{VAR:length_sel}
		</td>
	</tr>
	<tr>
		<td>Alates</td>
		<td>{VAR:date_from}</td>
		<td>{VAR:ts_buttons}</td>
	</tr>
	<tr>
		<td>Kuni</td>
		<td>{VAR:date_to}</td>
		<td>{VAR:to_button}</td>
	</tr>
	<tr>
		<td>Ilma detailse infota:</td>
		<td colspan="2"><input type="checkbox" value="1" name="no_det_info" {VAR:no_det_info}></td>
	</tr>
</table>
