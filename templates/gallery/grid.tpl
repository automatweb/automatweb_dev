Page: 
<!-- SUB: PAGE -->
&nbsp;&nbsp;<a href='{VAR:to_page}'>{VAR:page}</a>&nbsp;&nbsp;
<!-- END SUB: PAGE -->
<!-- SUB: SEL_PAGE -->
&nbsp;&gt;{VAR:page}&lt;&nbsp;
<!-- END SUB: SEL_PAGE -->
&nbsp;&nbsp;<a href='{VAR:add_page}'>{VAR:LC_GALLERY_ADD}</a><br>
<form action='reforb.{VAR:ext}' METHOD=POST enctype='multipart/form-data'>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='1000000'>
<table border=1 cellpadding=2 cellspacing=0>
<!-- SUB: LINE -->
<tr>
	<!-- SUB: CELL -->
	<td align=center bgcolor=#e0e0e0>
		<table border=0 cellpadding=0 cellspacing=1>
			<tr>
				<td colspan=2 align=center><img src='{VAR:imgurl}'> <input type='checkbox' name='erase_{VAR:row}_{VAR:col}' value=1>{VAR:LC_GALLERY_DELETE}</td>
			</tr>
			<!-- SUB: BIG -->
			<tr>
				<td colspan=2 align=center><a href='{VAR:bigurl}'>{VAR:LC_GALLERY_IMAGE}</a></td>
			</tr>
			<!-- END SUB: BIG -->
			<tr>
				<td align=right>{VAR:LC_GALLERY_SIGNATURE}:</td><td><input type='text' NAME='caption_{VAR:row}_{VAR:col}' VALUE='{VAR:caption}'></td>
			</tr>
			<tr>
				<td align=right>{VAR:LC_GALLERY_DATE}:</td><td><input type='text' NAME='date_{VAR:row}_{VAR:col}' VALUE='{VAR:date}' size=10></td>
			</tr>
			<tr>
				<td align=right>Thumbnail:</td><td><input type='file' NAME='tn_{VAR:row}_{VAR:col}'></td>
			</tr>
			<tr>
				<td align=right>{VAR:LC_GALLERY_IMAGE}:</td><td><input type='file' NAME='im_{VAR:row}_{VAR:col}'></td>
			</tr>
		</table>
	</td>
	<!-- END SUB: CELL -->
</tr>
<!-- END SUB: LINE -->
</table>
<input type='submit' VALUE='Save'>
{VAR:reforb}
</form>
<table border=0 cellpadding=0 cellspacing=3>
<tr>
<Td>
<form action='orb.{VAR:ext}' METHOD=GET>
<input type='submit' VALUE='Add'> <input type='text' NAME='rows' SIZE=2> {VAR:LC_GALLERY_ROWS}.
<input type='hidden' NAME='action' VALUE='add_row'>
<input type='hidden' NAME='class' VALUE='gallery'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
<input type='hidden' NAME='page' VALUE='{VAR:page}'>
</form>
</td>
<Td>
<form action='orb.{VAR:ext}' METHOD=GET>
<input type='submit' VALUE='Add'> <input type='text' NAME='cols' SIZE=2> {VAR:LC_GALLERY_COLUMNS}.
<input type='hidden' NAME='action' VALUE='add_col'>
<input type='hidden' NAME='class' VALUE='gallery'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
<input type='hidden' NAME='page' VALUE='{VAR:page}'>
</form>
</td>
</tr>
<tr>
<td>
<a href='{VAR:del_row}'>{VAR:LC_GALLERY_DEL_ROW}</a>
</td>
<td>
<a href='{VAR:del_col}'>{VAR:LC_GALLERY_DEL_COL}</a>
</td>
</tr>
</table>