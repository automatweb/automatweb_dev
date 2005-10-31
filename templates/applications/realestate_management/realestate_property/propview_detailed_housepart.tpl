	<style type="text/css">
	#objekt101 { border-collapse: collapse;}
	#objekt101 * {font-family: verdana; font-size: 11px; line-height: 16px;}
	#objekt101 td { padding: 0; vertical-align: top;}
	#objekt101 .tagasi {vertical-align: top; text-align: right; height: 17px; }
	#objekt101 .sisu a {color: #006600; text-decoration: none; }
	#objekt101 .sisu img {border: 1px solid #006600;}
	#objekt101 .sisu .teised_majad a {font-weight: bold;} 
	#objekt101 .sisu a:hover {text-decoration: underline;}
	#objekt101 .sisu .vasak_tulp {vertical-align: top;}
	#objekt101 .sisu .vasak_tulp img {margin-bottom: 5px;}
	#objekt101 .sisu .vasak_tulp .pilte_kokku {color: #006600;}
	</style>

<table id="objekt101">
<tr class="sisu">
	<td class="vasak_tulp" style="padding-right: 12px;"><a href="{VAR:open_pictureview_url}" target="_blank"><img src="{VAR:picture_icon}" alt="" width="118" height="88" border="0"></a><br>
	<span class="pilte_kokku"><a href="{VAR:open_pictureview_url}" target="_blank">Pilte kokku: {VAR:picture_count}</a> </span>
	</td><!-- vasak_tulp -->
	<td class="parem_tulp">
	
	<table>
	<tr>
		<td style="padding-right: 20px;">{VAR:transaction_type_caption}: <strong>{VAR:transaction_type}, {VAR:class_name} </strong>   <br>
		Tubade arv: <strong>{VAR:number_of_rooms}</strong>  <br> 
		Valmidusaste: <strong>{VAR:condition}</strong><br>
		Krundi suurus: <strong>{VAR:property_area}</strong><br>
		Hind: <strong>{VAR:transaction_price} kr</strong>
		</td>
		<td>
		Magamistubade arv: <strong>{VAR:number_of_bedrooms}</strong><br>
		Vannitubade arv: <strong>{VAR:number_of_bathrooms}</strong><br><br>
		Omandivorm: <strong>{VAR:legal_status}</strong><br>
		Üldpind: <strong>{VAR:total_floor_area}</strong><br>
		Koguhind: <strong>{VAR:transaction_price2} kr</strong><br>
		</td>
	</tr>
	</table>
	<br>
	<strong>Lisaandmed:</strong> {VAR:extras} <br><br>
	 
	<strong>Selle objekti laenumakse:</strong> <br><br>
	 
	<strong>Info:</strong> {VAR:additional_info_et} <br><br>
	 
	<strong>Kontakt:</strong> {VAR:agent_name}, {VAR:agent_phone}, <a href="mailto:{VAR:agent_email}">{VAR:agent_email}</a><br><br>
	 
	<span class="teised_majad"><a href="{VAR:show_agent_properties_url}">Näita ka selle maakleri teisi maju</a></span>  <br><br>
	 
	ID: {VAR:city24_object_id} <br><br>
		
	
	
	</td><!-- parem_tulp -->
</tr><!-- sisu -->
<tr>
	<td class="tagasi"><a href="javascript:history.back()"><img src="{VAR:baseurl}/img/tagasi.gif" alt="" width="56" height="17" border="0"></a></td>
	<td><img src="{VAR:baseurl}/img/objekt101_joon3.gif" alt="" width="1" height="17" border="0"></td>
</tr>
<tr>
	<td style="background: url({VAR:baseurl}/img/objekt101_joon2.gif) no-repeat top right; "></td>
	<td style="background: url({VAR:baseurl}/img/objekt101_joon.gif) no-repeat top;">
	<a href="{VAR:open_printview_url}&print=1" target="_blank"><img src="{VAR:baseurl}/img/tryki.gif" alt="" width="57" height="18" border="0"></a><a href="{VAR:baseurl}/?class=document&action=send&section={VAR:docid}"><img src="{VAR:baseurl}/img/saada_s6brale.gif" alt="" width="114" height="18" border="0"></a></td>
</tr>
</table>
