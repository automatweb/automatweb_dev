<table width="100%" border="0" cellpadding="5" cellspacing="0">
<tr><td class="aste05">


<span class="celltext"><b>
Page: 
<!-- SUB: PAGE -->
&nbsp;&nbsp;<a href='{VAR:to_page}'>{VAR:page}</a>&nbsp;&nbsp;
<!-- END SUB: PAGE -->
<!-- SUB: SEL_PAGE -->
&nbsp;&gt;{VAR:page}&lt;&nbsp;
<!-- END SUB: SEL_PAGE -->


&nbsp;&nbsp;<a href='{VAR:add_page}'>{VAR:LC_GALLERY_ADD}</a> | <a href='{VAR:del_page}'>{VAR:LC_GALLERY_DEL_PAGE}</a><br>
</b></span>

</td></tr>
<tr><td>

<span class="celltext">

<form action='reforb.{VAR:ext}' METHOD=POST enctype='multipart/form-data'>
<input type='checkbox' name='is_slideshow' value='1' {VAR:is_slideshow}> {VAR:LC_GALLERY_IS_GALLERY_SLIDESHOW}
 <input type='checkbox' name='is_automatic_slideshow' value='1' {VAR:is_automatic_slideshow}> {VAR:LC_GALLERY_IS_SLIDESHOW_AUTO}<br>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='1000000'>

</span>

<table border=0 cellpadding=2 bgcolor="#FFFFFF" cellspacing=1>

<!-- SUB: LINE -->
<tr>
	<!-- SUB: CELL -->
	<td align=center class="aste01">
		<table border=0 cellpadding=1 cellspacing=1>
			<tr>
				<td colspan=2 align=center class="celltext"><img src='{VAR:imgurl}'><input type='checkbox' name='erase_{VAR:row}_{VAR:col}' value=1>{VAR:LC_GALLERY_DELETE}</td>
			</tr>
			<!-- SUB: BIG -->
			<tr>
				<td colspan=2 align=center class="celltext"><a href='{VAR:bigurl}'>{VAR:LC_GALLERY_IMAGE}</a></td>
			</tr>
			<!-- END SUB: BIG -->
			<tr>
				<td align=right class="celltext">{VAR:LC_GALLERY_SIGNATURE}:</td>
				<td><input type='text' NAME='caption_{VAR:row}_{VAR:col}' VALUE='{VAR:caption}' size="28" class="formtext"></td>
			</tr>
			<tr>
				<td align=right class="celltext">{VAR:LC_GALLERY_DATE}:</td>
				<td><input type='text' NAME='date_{VAR:row}_{VAR:col}' VALUE='{VAR:date}' size=10 class="formtext"></td>
			</tr>
			<tr>
				<td align=right class="celltext">Thumbnail:</td>
				<td><input type='file' NAME='tn_{VAR:row}_{VAR:col}' class="formfile"></td>
			</tr>
			<tr>
				<td align=right class="celltext">{VAR:LC_GALLERY_IMAGE}:</td>
				<td><input type='file' NAME='im_{VAR:row}_{VAR:col}' class="formfile"></td>
			</tr>
		</table>
	</td>
	<!-- END SUB: CELL -->
</tr>
<!-- END SUB: LINE -->
</table>
<input type='submit' VALUE='Save' class="formbutton">
{VAR:reforb}
</form>
<table border=0 cellpadding=0 cellspacing=3>
<tr>
<Td class="celltext">
<form action='orb.{VAR:ext}' METHOD=GET>
<input type='submit' VALUE='Add' class="formbutton"> <input type='text' NAME='rows' SIZE=2 class="formtext"> {VAR:LC_GALLERY_ROWS}.
<input type='hidden' NAME='action' VALUE='add_row'>
<input type='hidden' NAME='class' VALUE='gallery'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
<input type='hidden' NAME='page' VALUE='{VAR:page}'>
</form>
</td>
<Td class="celltext">
<form action='orb.{VAR:ext}' METHOD=GET>
<input type='submit' VALUE='Add' class="formbutton"> <input type='text' NAME='cols' SIZE=2 class="formtext"> {VAR:LC_GALLERY_COLUMNS}.
<input type='hidden' NAME='action' VALUE='add_col'>
<input type='hidden' NAME='class' VALUE='gallery'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
<input type='hidden' NAME='page' VALUE='{VAR:page}'>
</form>
</td>
</tr>
<tr>
<td>

</td>
<td>

</td>
</tr>
</table>

<span class="celltext"><b>

<a href='{VAR:del_row}'>{VAR:LC_GALLERY_DEL_ROW}</a> |

<a href='{VAR:del_col}'>{VAR:LC_GALLERY_DEL_COL}</a>

</b></span>

</td></tr></table>