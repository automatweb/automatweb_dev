<html>
	<body>
		{VAR:LC_TABLE_CHANGE_TABLE} {VAR:table_name}.<br>

		<form action='{VAR:baseurl}/automatweb/refcheck.{VAR:ext}' method=post ENCTYPE="multipart/form-data">

			<table>
				<tr>
					<td align=right>{VAR:LC_TABLE_TEXT}:</td>
					<td><input type='text' NAME='tekst' VALUE='{VAR:admin_table_cell}'></td>
				</tr>
				<tr>
					<td align=right>{VAR:LC_TABLE_PICTURE}:</td>
					<td valign=center> {VAR:table_cell_image_preview} <input type=hidden NAME='MAX_FILE_SIZE' VALUE=200000><input type='file' NAME='image'></td>
				</tr>
				<tr>
					<td align=right>{VAR:LC_TABLE_DEL_PICT}:</td>
					<td><input type='checkbox' NAME='erase_pic' VALUE=1></td>
				</tr>
				<tr>
					<td align=right>{VAR:LC_TABLE_STYLE}:</td>
					<td><select NAME='style'>
								<!-- SUB: STYLES -->
									<option VALUE='{VAR:admin_table_style_id}' {VAR:admin_table_style_selected}>{VAR:admin_table_style}
								<!-- END SUB: STYLES -->
							</select>
					</td>
				</tr>
				<tr>
					<td align=right>Align:</td>
					<td>
						<input type='radio' NAME='align' VALUE="left" {VAR:align_left}>{VAR:LC_TABLE_LEFT} 
						<input type='radio' NAME='align' VALUE="center" {VAR:align_center}>{VAR:LC_TABLE_MIDDLE} 
						<input type='radio' NAME='align' VALUE="right" {VAR:align_right}>{VAR:LC_TABLE_RIGHT}</td>
				</tr>
				<tr>
					<td align=right>Vertical align:</td>
					<td>
						<input type='radio' NAME='valign' VALUE="top" {VAR:valign_top}>{VAR:LC_TABLE_UP}
						<input type='radio' NAME='valign' VALUE="center" {VAR:valign_center}>{VAR:LC_TABLE_MIDDLE} 
						<input type='radio' NAME='valign' VALUE="bottom" {VAR:valign_bottom}>{VAR:LC_TABLE_DOWN}</td>
				</tr>
				<tr>
					<td align=right>{VAR:LC_TABLE_WITDH}:</td>
					<td><input type='text' NAME='c_width' VALUE='{VAR:cell_width}'></td>
				</tr>
				<tr>
					<td align=right>{VAR:LC_TABLE_HEIGTH}</td>
					<td><input type='text' NAME='c_height' VALUE='{VAR:cell_height}'></td>
				</tr>
			</table>
			<br>
			<input type='submit' NAME='save_table' VALUE='{VAR:LC_TABLE_SAVE}'>
			<input type='hidden' NAME='action' VALUE='admin_table_style'>
			<input type='hidden' NAME='id' VALUE='{VAR:table_id}'>
			<input type='hidden' NAME='cell_id' VALUE='{VAR:pop_cell_id}'>
		</form>
		
	</body>
</html>
