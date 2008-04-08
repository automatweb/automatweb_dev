<h3>Tellimuskinnitus</h3>
Kinnitus saadetud: {VAR:send_date}<br />
Hotelli kontakt: {VAR:contactperson}<br />
<table border="1" cellspacing="0" cellpadding="5">
<tr><th colspan="{VAR:colspan}">{VAR:title}</td></th>
<tr>
<!-- SUB: HEADERS_PACKAGE -->
<td>Kuupäev / Kellaaeg</td>
<td>Pakett</td>
<td>Ruum</td>
<td>Paigutus</td>
<td>In. arv</td>
<td>Hind/in</td>
<td>Hind</td>
<td>Märkused</td>
<!-- END SUB: HEADERS_PACKAGE -->
<!-- SUB: HEADERS_NO_PACKAGE -->
<td>Kuupäev / Kellaaeg</td>
<td>Ruum</td>
<td>Paigutus</td>
<td>In. arv</td>
<td>Hind</td>
<td>Märkused</td>
<!-- END SUB: HEADERS_NO_PACKAGE -->
</tr>
<!-- SUB: BRON -->
<tr>
<!-- SUB: VALUES_PACKAGE -->
<td>{VAR:datefrom} {VAR:timefrom} - {VAR:timeto}</td>
<td>{VAR:package}</td>
<td>{VAR:room}</td>
<td>{VAR:tables}</td>
<td>{VAR:people}</td>
<td>{VAR:unitprice}</td>
<td>{VAR:price}</td>
<td>{VAR:comments}</td>
<!-- END SUB: VALUES_PACKAGE -->
<!-- SUB: VALUES_NO_PACKAGE -->
<td>{VAR:datefrom} {VAR:timefrom} - {VAR:timeto}</td>
<td>{VAR:room}</td>
<td>{VAR:tables}</td>
<td>{VAR:people}</td>
<td>{VAR:price}</td>
<td>{VAR:comments}</td>
<!-- END SUB: VALUES_NO_PACKAGE -->
</tr>
<!-- END SUB: BRON -->
<tr>
<td colspan="{VAR:total_colspan}"><div align="right">Kokku:</div></td>
<td>{VAR:bron_totalprice}</td>
</tr>
</table>
<br /><br />
<!-- SUB: RESOURCES -->
<table border="1" cellspacing="0" cellpadding="5">
<tr><th colspan="6">Tehnilised vahendid</th></tr>
<tr>
<td>Aeg</td>
<td>Tehnilised vahendid</td>
<td>Kogus</td>
<td>Hind per kogus</td>
<td>Hind kokku</td>
<td>Kommentaar</td>
</tr>
<!-- SUB: RESOURCE -->
<tr>
<td>{VAR:time}</td>
<td>{VAR:name}</td>
<td>{VAR:count}</td>
<td>{VAR:price}</td>
<td>{VAR:total}</td>
<td>{VAR:comment}</td>
</tr>
<!-- END SUB: RESOURCE -->
<tr><td colspan="4"><div align="right">Kokku:</div></td>
<td>{VAR:rtotal}</td></tr>
</table>
<!-- END SUB: RESOURCES -->

Tekst suunaviitadele:<br />
{VAR:pointer_text}<br /><br />

Maksmisviis:<br />
{VAR:payment_method}