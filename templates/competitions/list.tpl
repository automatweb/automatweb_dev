<strong><a href="{VAR:baseurl}/?class=competitions&action=toplist">TOP PLAYERS</strong><p />
<table border="0" cellpadding="0" cellspacing="2">
<tr>
	<td class="textsmall">Nimi</td>
	<td class="textsmall">Status</td>
	<td class="textsmall">Algus</td>
	<td class="textsmall">L&otilde;pp</td>
	<td class="textsmall">H&auml;&auml;letamise l&otilde;pp</td>
	<td class="textsmall">Soovitaja</td>
	<td class="textsmall">Vaata</td>
</tr>
<!-- SUB: LINE -->
<tr>
	<td  class="textsmall">{VAR:name}</td>
	<td class="textsmall">{VAR:status}</td>
	<td class="textsmall">{VAR:start}</td>
	<td class="textsmall">{VAR:end}</td>
	<td class="textsmall">{VAR:vote_end}</td>
	<td class="textsmall">{VAR:proposed_by}</td>
	<td class="textsmall">
		<!-- SUB: HAS_STARTED -->
		<a href='{VAR:view}'>Vaata</a>
		<!-- END SUB: HAS_STARTED -->
	</td>
</tr>
<!-- END SUB: LINE -->
</table>
<a href='{VAR:add}'>Lisa</a>
