<!-- SUB: PAGE -->
<a href='{VAR:goto_page}'>{VAR:from} - {VAR:to}</a> |
<!-- END SUB: PAGE -->

<!-- SUB: SEL_PAGE -->
{VAR:from} - {VAR:to} |
<!-- END SUB: SEL_PAGE -->

<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">Millal</td>
	<td class="fcaption2">Kasutaja</td>
	<td class="fcaption2">IP</td>
	<td class="fcaption2">Hind</td>
	<td class="fcaption2">Vaata</td>
	<td class="fcaption2">M&auml;rgi makstuks</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td class="fcaption2">{VAR:when}</td>
	<td class="fcaption2">{VAR:user}</td>
	<td class="fcaption2">{VAR:ip}</td>
	<td class="fcaption2">{VAR:price}</td>
	<td class="fcaption2"><a href='{VAR:view}'>Vaata</a></td>
	<td class="fcaption2">
		<!-- SUB: IS_F -->
		<a href='{VAR:fill}'>M&auml;rgi makstuks</a>
		<!-- END SUB: IS_F -->
		<!-- SUB: FILLED -->
		Tellimus on makstud.
		<!-- END SUB: FILLED -->
	</td>
</tr>
<!-- END SUB: LINE -->
</table>
