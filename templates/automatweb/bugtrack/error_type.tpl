{VAR:header}
<a href='{VAR:back}'>Tagasi</a>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" width="100%">
<tr>
	<td class="fcaption2">Kokku seda t&uuml;&uuml;pi vigu:</td>
	<td class="fcaption2">{VAR:cnt}</td>
</tr>
<tr>
	<td class="fcaption2" colspan="2">Seda t&uuml;&uuml;pi vigu saitide l&otilde;ikes:</td>
</tr>
<!-- SUB: SITE_CNT -->
<tr>
	<td class="fcaption2">{VAR:site}</td>
	<td class="fcaption2">{VAR:site_cnt}</td>
</tr>
<!-- END SUB: SITE_CNT -->
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
