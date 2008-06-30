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
<td>M&auml;rkused</td>
<!-- END SUB: HEADERS_PACKAGE -->
<!-- SUB: HEADERS_NO_PACKAGE -->
<td>Kuup&auml;ev / Kellaaeg</td>
<td>Ruum</td>
<td>Paigutus</td>
<td>In. arv</td>
<td>Hind</td>
<td>M&auml;rkused</td>
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
<!-- SUB: RESOURCES -->
<br /><br />
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
<td>{VAR:res_time}</td>
<td>{VAR:res_name}</td>
<td>{VAR:res_count}</td>
<td>{VAR:res_price}</td>
<td>{VAR:res_total}</td>
<td>{VAR:res_comment}</td>
</tr>
<!-- END SUB: RESOURCE -->
<tr><td colspan="4"><div align="right">Kokku:</div></td>
<td>{VAR:res_total}</td></tr>
</table>
<!-- END SUB: RESOURCES -->

<!-- SUB: PRODUCTS -->
<br /><br />
<table border="1" cellspacing="0" cellpadding="5">
<tr><th colspan="7">Toitlustus</th></tr>
<tr>
<td>Aeg</td>
<td>S&uuml;ndmus</td>
<td>Arv</td>
<td>Toit</td>
<td>Tüki hind</td>
<td>Hind kokku</td>
<td>Kommentaar</td>
</tr>
<!-- SUB: PRODUCT -->
<tr>
<td>{VAR:prod_time}</td>
<td>{VAR:prod_event}</td>
<td>{VAR:prod_count}</td>
<td>{VAR:prod_prod}</td>
<td>{VAR:prod_price}</td>
<td>{VAR:prod_sum}</td>
<td>{VAR:prod_comment}</td>
</tr>
<!-- END SUB: PRODUCT -->
<tr><td colspan="5"><div align="right">Kokku:</div></td>
<td>{VAR:prod_total}</td></tr>
</table>
<!-- END SUB: PRODUCTS -->

<!-- SUB: HOUSING -->
<br /><br />
<table border="1" cellspacing="0" cellpadding="5">
<tr><th colspan="8">Majutus</th></tr>
<tr>
<td>Alates</td>
<td>Kuni</td>
<td>Toa t&uuml;&uuml;p</td>
<td>Tubasid</td>
<td>Inimesi</td>
<td>Hind</td>
<td>Soodustus</td>
<td>Summa</td>
</tr>
<!-- SUB: ROOMS -->
<tr>
<td>{VAR:hs_from}</td>
<td>{VAR:hs_to}</td>
<td>{VAR:hs_type}</td>
<td>{VAR:hs_rooms}</td>
<td>{VAR:hs_people}</td>
<td>{VAR:hs_price}</td>
<td>{VAR:hs_discount}%</td>
<td>{VAR:hs_sum}</td>
</tr>
<!-- END SUB: ROOMS -->
<tr><td colspan="7"><div align="right">Kokku:</div></td>
<td>{VAR:hs_total}</td></tr>
</table>
<!-- END SUB: HOUSING -->

Tekst suunaviitadele:<br />
{VAR:pointer_text}<br /><br />

Maksmisviis:<br />
{VAR:payment_method}
<br /><br />
Orienteeruv maksumus kokku: {VAR:totalprice}
<br /><br />
{VAR:data_contact} 
{VAR:data_street} 
{VAR:data_city} 
{VAR:data_zip} 
{VAR:data_country} 
{VAR:data_name} 
{VAR:data_phone} 
{VAR:data_email} 