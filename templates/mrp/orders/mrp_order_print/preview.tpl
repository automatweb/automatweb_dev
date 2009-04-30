<style type="text/css">
#contact { border: 0; font-family: Verdana; font-size: 10px; margin: 2em; width: 80%; }
#contact tr.even td.value { background: #f6f6f6; }
#contact tr td { }
#contact tr td.caption { font-weight: bold; padding: 4px 8px; text-align: right; width: 20%; }
#contact tr td.value { padding: 4px 8px; width: 80%; }
#contact tr td.logo { font-weight: bold; padding: 4px 8px 4em 8px;; text-align: right; width: 20%; }

#mrp { border: 0; font-family: Verdana; font-size: 10px; margin: 2em; width: 80%; }
#mrp tr.even td.value { background: #f6f6f6; }
#mrp tr td {  }
#mrp tr td.caption { font-weight: bold; padding: 4px 8px; text-align: right; width: 20%; }
#mrp tr td.value { padding: 4px 8px; width: 80%; }
#mrp tr td.price { color: red; }
</style>

<table id="contact" cellpadding="0" cellspacing="0">
	<tr>
		<td class="logo">{VAR:orderer_logo}</td>
		<td></td>
	</tr>
	<tr class="even">
		<td class="caption">TELLIJA:</td>
		<td class="value">{VAR:orderer_name}</td>
	</tr>
	<tr>
		<td class="caption">AADRESS:</td>
		<td class="value">{VAR:orderer_address}</td>
	</tr>
	<tr class="even">
		<td class="caption">TELEFON:</td>
		<td class="value">{VAR:orderer_phone}</td>
	</tr>
	<tr>
		<td class="caption">FAX:</td>
		<td class="value">{VAR:orderer_fax}</td>
	</tr>
	<tr class="even">
		<td class="caption">KONTAKTISIK:</td>
		<td class="value">{VAR:orderer_contact}</td>
	</tr>
</table>

<table id="mrp" cellpadding="0" cellspacing="0">
	<tr>
		<td class="caption">TELLIMUSE NIMI:</td>
		<td class="value">{VAR:name}</td>
	</tr>
	<tr class="even">
		<td class="caption">TR&Uuml;KIARV:</td>
		<td class="value">{VAR:amount}</td>
	</tr>
	<tr>
		<td class="caption">MAHT:</td>
		<td class="value">{VAR:e_num_pages}</td>
	</tr>
	<tr class="even">
		<td class="caption">FORMAAT:</td>
		<td class="value">{VAR:e_format}</td>
	</tr>
	<tr>
		<td class="caption">KAANED:</td>
		<td class="value">{VAR:e_covers}</td>
	</tr>
	<tr class="even">
		<td class="caption">V&Auml;RVILISUS (kaaned):</td>
		<td class="value">{VAR:e_cover_colour}</td>
	</tr>
	<tr>
		<td class="caption">V&Auml;RVILISUS (sisu):</td>
		<td class="value">{VAR:e_main_colour}</td>
	</tr>
	<tr class="even">
		<td class="caption">PABER (kaaned):</td>
		<td class="value">{VAR:e_cover_paper}</td>
	</tr>
	<tr>
		<td class="caption">PABER (sisu):</td>
		<td class="value">{VAR:e_main_paper}</td>
	</tr>
	<tr class="even">
		<td class="caption">K&Ouml;IDE V&Otilde;I KINNITUS:</td>
		<td class="value">{VAR:e_binding}</td>
	</tr>
	<tr>
		<td class="caption">M&Otilde;&Otilde;DUD:</td>
		<td class="value">{VAR:e_measures}</td>
	</tr>
	<tr class="even">
		<td class="caption">MATERIALID:</td>
		<td class="value">{VAR:e_materials}</td>
	</tr>
	<tr>
		<td class="caption">J&Auml;RELT&Ouml;&Ouml;TLUS:</td>
		<td class="value">{VAR:e_post_processing}</td>
	</tr>
	<tr class="even">
		<td class="caption price">HIND:</td>
		<td class="value price">{VAR:price}</td>
	</tr>
</table>
