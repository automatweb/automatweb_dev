{VAR:header}
<a href='{VAR:back}'>Tagasi</a>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" width="100%">
<tr>
	<td class="fcaption2">Kokku selle saidi vigu:</td>
	<td class="fcaption2">{VAR:cnt}</td>
</tr>
<tr>
	<td class="fcaption2" colspan="2">vigade t&uuml;&uuml;bid selle saidi l&otilde;ikes:</td>
</tr>
<!-- SUB: TYPE_CNT -->
<tr>
	<td class="fcaption2">{VAR:type}</td>
	<td class="fcaption2">{VAR:type_cnt}</td>
</tr>
<!-- END SUB: TYPE_CNT -->
<tr>
	<td class="fcaption2" colspan="2">Nimekiri:</td>
</tr>
</table>

<div align="left" class="fgtext">
<!-- SUB: PAGE -->
<a href='{VAR:link}'>{VAR:from} - {VAR:to}</a> | 
<!-- END SUB: PAGE -->

<!-- SUB: SEL_PAGE -->
{VAR:from} - {VAR:to} | 
<!-- END SUB: SEL_PAGE -->
</div>
{VAR:table}
