<form action='reforb.{VAR:ext}' method='post' name='changeform' enctype='multipart/form-data'>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='500000'>
<table border='0' width='100%' cellspacing='1' cellpadding='1' bgcolor='#FFFFFF'>


{VAR:content}

<!-- SUB: LINE -->
<tr>
	<td class='chformleftcol' width='160' nowrap>
	{VAR:caption}
	</td>
	<td class='chformrightcol'>
	{VAR:element}
	</td>
</tr>
<!-- END SUB: LINE -->



<!-- SUB: HEADER -->
<tr>
	<td class='chformsubheader' width='160'>
	&nbsp;
	</td>
	<td class='chformsubheader'>
	{VAR:caption}
	</td>
</tr>
<!-- END SUB: HEADER -->

<!-- SUB: SUB_TITLE -->
<tr>
	<td colspan='2' class='chformsubtitle' width='160'>
	{VAR:value}
	</td>
</tr>
<!-- END SUB: SUB_TITLE -->

<!-- SUB: CONTENT -->
<tr>
	<td colspan='2' class='chformrightcol'>
	{VAR:value}
	</td>
</tr>
<!-- END SUB: CONTENT -->


<!-- SUB: SUBMIT -->
<tr>
	<td class='chformleftcol' align='center' width='160'>&nbsp;</td>
	<td class='chformrightcol'>
		<input type='submit' value='Salvesta' class='small_button'>
	</td>
</tr>
<!-- END SUB: SUBMIT -->
</table>
{VAR:reforb}
</form>


