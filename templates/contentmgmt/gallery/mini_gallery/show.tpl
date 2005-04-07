<style type="text/css">
.mgalimg td{
	font-family: Trebuchet MS,Tahoma,sans-serif;
	font-size: 10px;
	color: #000000;
}
</style>

<!-- SUB: PAGESELECTOR -->
Vali lehek&uuml;lg: 
<!-- SUB: PAGE -->
<a href='{VAR:page_link}'>{VAR:page_nr}</a>
<!-- END SUB: PAGE -->

<!-- SUB: PAGE_SEL -->
{VAR:page_nr}
<!-- END SUB: PAGE_SEL -->

<!-- SUB: PAGE_SEPARATOR -->
|
<!-- END SUB: PAGE_SEPARATOR -->

<!-- END SUB: PAGESELECTOR -->

<table border="0" cellpadding="0" cellspacing="10" width="100%" >
<!-- SUB: ROW -->
	<tr>
		<!-- SUB: COL -->
		<td valign="top" align="center" class="mgalimg">{VAR:imgcontent}</td>
		<!-- END SUB: COL -->
	</tr>
<!-- END SUB: ROW -->
</table>