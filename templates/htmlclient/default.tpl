<form action='orb.{VAR:ext}' method='post' name='changeform' enctype='multipart/form-data' {VAR:form_target}>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='100000000'>
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
	<td colspan='2' class='chformsubtitle'>
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

<!-- SUB: XCONTENT -->
<tr>
	<td blah="xcontent" colspan='2' class='chformrightcol'>
	{VAR:caption}:<br>
	{VAR:value}
	</td>
</tr>
<!-- END SUB: XCONTENT -->

<!-- SUB: BLOCK -->
	<!-- block starts -->
	{VAR:value}
	<!-- block ends -->
<!-- END SUB: BLOCK -->

<!-- SUB: IFRAME -->
<iframe id='contentarea' name='contentarea' src='{VAR:src}' style='width: 100%; height: 95%; border-top: 1px solid black;' frameborder='no' scrolling="yes"></iframe>
<!-- END SUB: IFRAME -->


<!-- SUB: SUBMIT -->
<tr>
	<td class='chformleftcol' align='center' width='160'>&nbsp;</td>
	<td class='chformrightcol'>
		<input type='submit' value='Salvesta' class='small_button' onClick='submit_changeform(); return false;'>
	</td>
</tr>
<!-- END SUB: SUBMIT -->

{VAR:reforb}
<script type="text/javascript">
function submit_changeform()
{
	{VAR:submit_handler}
	document.changeform.submit();
}
</script>
</form>
</table>

<!-- SUB: iframe_body_style -->
body {
        background-color: #FFFFFF;
        margin: 0px;
        overflow-y: hidden;
        overflow:hidden;
}
<!-- END SUB: iframe_body_style -->
