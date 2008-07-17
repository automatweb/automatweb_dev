<table width="100%">
<tr><td>
<img src="http://www.revalhotels.com/orb.aw/class=image/action=show/fastcall=1/file=b53b8bf948420450c303bfec1b36fdcd.gif/RH_logo.gif" alt="Reval Hotels">
</td>
<td>
<strong>Reval Hotel Central</strong><br />
Narva mnt 7C, 10117 Tallinn, Estonia<br />
Tel: +372 633 9800<br />
Fax: +372 633 9900<br />
e-mail: central.sales@revalhotels.com
</td></tr>
</table>
<!-- SUB: CONFIRMATION_ONLY -->
<h3>Tellimuskinnitus</h3>
<!-- END SUB: CONFIRMATION_ONLY -->
<!-- SUB: OFFER_ONLY -->
<h3>Pakkumine</h3>
{VAR:offer_preface}<br/>
{VAR:offer_price_comment}<br/>
kehtib: {VAR:offer_expire_date}
<!-- END SUB: OFFER_ONLY -->

<table border="1" cellspacing="0" cellpadding="5" width="100%" bgcolor="#efefef" bordercolor="#ffffff" style="border-collapse: collapse">
<tr bgcolor="#aaaaaa"><th colspan="{VAR:colspan}">{VAR:title}</td></th>
<tr bgcolor="#dddddd">
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
<td colspan="{VAR:total_colspan}"><div align="right"><strong>Kokku:</strong></div></td>
<td><strong>{VAR:bron_totalprice}</strong>.-</td>
</tr>
</table>
<!-- SUB: RESOURCES -->
<br /><br />
<table border="1" cellspacing="0" cellpadding="5" width="100%" bgcolor="#efefef" bordercolor="#ffffff" style="border-collapse: collapse">
<tr bgcolor="#aaaaaa"><th colspan="6">Tehnilised vahendid</th></tr>
<tr bgcolor="#dddddd">
<td>Aeg</td>
<td>Tehnilised vahendid</td>
<td>Kogus</td>
<td>Hind per kogus</td>
<td>Hind kokku</td>
<td>Kommentaar</td>
</tr>
<!-- SUB: RESOURCE -->
<tr>
<td>{VAR:res_from_hour}:{VAR:res_from_minute} - {VAR:res_to_hour}:{VAR:res_to_minute}</td>
<td>{VAR:res_name}</td>
<td>{VAR:res_count}</td>
<td>{VAR:res_price}</td>
<td>{VAR:res_total}</td>
<td>{VAR:res_comment}</td>
</tr>
<!-- END SUB: RESOURCE -->
<tr><td colspan="4"><div align="right"><strong>Kokku:</strong></div></td>
<td><strong>{VAR:res_total}</strong>.-</td></tr>
</table>
<!-- END SUB: RESOURCES -->

<!-- SUB: PRODUCTS -->
<br /><br />
<table border="1" cellspacing="0" cellpadding="5" width="100%" bgcolor="#efefef" bordercolor="#ffffff" style="border-collapse: collapse">
<tr bgcolor="#aaaaaa"><th colspan="7">Toitlustus</th></tr>
<tr bgcolor="#dddddd">
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
<td>{VAR:prod_from_hour}:{VAR:prod_from_minute} - {VAR:prod_to_hour}:{VAR:prod_to_minute}</td>
<td>{VAR:prod_event_and_room}</td>
<td>{VAR:prod_count}</td>
<td>{VAR:prod_prod}</td>
<td>{VAR:prod_price}</td>
<td>{VAR:prod_sum}</td>
<td>{VAR:prod_comment}</td>
</tr>
<!-- END SUB: PRODUCT -->
<tr><td colspan="5"><div align="right"><strong>Kokku:</strong></div></td>
<td><strong>{VAR:prod_total}</strong>.-</td></tr>
</table>
<!-- END SUB: PRODUCTS -->

<!-- SUB: HOUSING -->
<br /><br />
<table border="1" cellspacing="0" cellpadding="5" width="100%" bgcolor="#efefef" bordercolor="#ffffff" style="border-collapse: collapse">
<tr bgcolor="#aaaaaa"><th colspan="8">Majutus</th></tr>
<tr bgcolor="#dddddd">
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
<td>{VAR:hs_discount}</td>
<td>{VAR:hs_sum}</td>
</tr>
<!-- END SUB: ROOMS -->
<tr><td colspan="7"><div align="right"><strong>Kokku:</strong></div></td>
<td><strong>{VAR:hs_total}</strong>.-</td></tr>
</table>
<!-- END SUB: HOUSING -->

<br />
Tekst suunaviitadele: <strong>{VAR:pointer_text}</strong>
<br /><br />
Maksmisviis: <strong>{VAR:payment_method}</strong>
<br /><br />
Orienteeruv maksumus kokku: <strong>{VAR:totalprice}</strong>.-
<br /><br />
<table>
<tr><td>{VAR:cancel_and_payment_terms}</td></tr>
<tr><td>{VAR:accomondation_terms}</td></tr>
</table>
<table width="100%">
<tr>
<td>
Kontaktisik: {VAR:data_contact}<br />
Tänav: {VAR:data_street}<br />
Linn: {VAR:data_city}<br />
Indeks: {VAR:data_zip}<br />
Riik: {VAR:data_country}<br />
Nimi: {VAR:data_name}<br />
Telefon: {VAR:data_phone}<br />
E-mail: {VAR:data_email}
</td>
<td>
Kinnitus saadetud: <strong>{VAR:send_date}</strong>
<br />
Hotelli kontakt: <strong>{VAR:contactperson}</strong>
</td>
</tr>
</table>
