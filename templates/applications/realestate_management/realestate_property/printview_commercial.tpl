<div class="navLink">
<a class="navLink" href="javascript:window.print();" style="margin-right: 20px;">Prindi</a>
<a class="navLink" href="{VAR:return_url}">Tagasi</a>
</div>

<hr width="100%" size="1" noshade>

<!-- SUB: property_cell -->
<td width="50%" class="txt11px">{VAR:prop_caption}: <strong>{VAR:prop_value}</strong> {VAR:prop_suffix}</td>
<!-- END SUB: property_cell -->

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<!-- SUB: property_row -->
<tr valign="top">
	{VAR:property_cells}
</tr>
<!-- END SUB: property_row -->
</table>


	



<table id="detailotsing">
<tr class="pealkiri">
	<td>
	<table style="margin-bottom: 5px;">
	<tr>
		<td id="pealkiri"><strong>{VAR:address}</strong></td>
		<td id="id">ID: {VAR:city24_object_id}</td>
	</tr>
	</table>
	</td>
</tr><!-- pealkiri -->

<tr class="alapealkiri">
	<td>
	<table>
	<tr>
		<td class="pealkiri" style="padding-right: 10px;">Objekti andmed</td>
		<td class="joon" style="background: url('{VAR:baseurl}/img/1x1.gif') repeat-x center;"></td>
	</tr>
	</table>
	</td>
</tr><!-- alapealkiri -->

<tr class="sisu">
	<td>
	<table>
	<tr>

		<td style="padding-right: 30px;">
		<!-- SUB: re_transaction_type -->
		{VAR:caption}: <strong>{VAR:value},
		<!-- END SUB: re_transaction_type -->
		{VAR:class_name}</strong><br>
		
		<!-- SUB: re_floor -->
		{VAR:caption}: <strong>{VAR:value}</strong><br>
		<!-- END SUB: re_floor -->

		<!-- SUB: re_usage_purpose -->
		{VAR:caption}: <strong>{VAR:value}</strong><br>
		<!-- END SUB: re_usage_purpose -->

		<!-- SUB: re_condition -->
		{VAR:caption}: <strong>{VAR:value}</strong><br>
		<!-- END SUB: re_condition -->
		</td>
		
		<td style="padding-right: 30px;">
		<!-- SUB: re_number_of_floors -->
		{VAR:caption}: <strong>{VAR:value}</strong><br>
		<!-- END SUB: re_number_of_floors -->
		
		<!-- SUB: re_year_built -->
		{VAR:caption}:<strong>{VAR:value}</strong><br>
		<!-- END SUB: re_year_built -->

		<!-- SUB: re_transaction_price2 -->
		{VAR:caption}: <strong>{VAR:value} kr</strong><br>
		<!-- END SUB: re_transaction_price2 -->
		
		<td>
		<!-- SUB: re_total_floor_area -->
		{VAR:caption}: <strong>{VAR:value} m<sup>2</sup></strong><br>
		<!-- END SUB: re_total_floor_area -->
		
		<!-- SUB: re_transaction_price -->
		{VAR:caption}: <strong>{VAR:value} kr</strong>
		<!-- END SUB: re_transaction_price -->
		</td>
	</tr>
	</table>
	<br><br>

	<strong>Lisaandmed:</strong> {VAR:extras}<br>
	<br>
	<strong>Objekti laenuinfo:</strong> {VAR:additional_info}<br>
	<br>
	
	<table class="pildid_joondusega_alla">
	<tr>
	<!-- SUB: pictures -->
	<td><img src="{VAR:picture_url}" width="280"></td>
	<!-- END SUB: pictures -->
	</tr>
	</table><!-- pildid_joondusega_alla -->
	
	<!--
	<table class="pildid_joondusega_yles">
	<tr>
		<td><img src="{VAR:baseurl}/img/img-no-disain/uks.jpg" alt="" width="159" height="120" border="0"></td>
		<td><img src="{VAR:baseurl}/img/img-no-disain/kamin.jpg" alt="" width="159" height="120" border="0"></td>
		<td><a href="#"><img src="{VAR:baseurl}/img/img-no-disain/dush.jpg" alt="" width="120" height="159" border="0"></a></td>
	</tr>
	</table>--><!-- pildid_joondusega_yles -->
	
	</td>
</tr><!-- sisu -->

<tr class="alapealkiri">
	<td>
	<table>
	<tr>
		<td class="pealkiri" style="padding-right: 10px; ">Maaklerid</td>
		<td class="joon" style="background: url('{VAR:baseurl}/img/1x1.gif') repeat-x center;"></td>
	</tr>
	</table>
	</td>
</tr><!-- alapealkiri -->

<tr class="sisu">
	<td>
	<table style="width: 100%;">
	<tr>
		<td class="foto"><img src="{VAR:baseurl}/img/img-no-disain/raimond_irjas.jpg" alt="" width="70" height="105" border="0"></td>
		<td class="isiku_kontakt" style="padding: 12px 11px;">
		<strong>Raimond Irjas</strong><br>
		Elamispindade maakler<br>
		<br>
		GSM: +372 51 18 337<br>
		Telefon: +372 626 64 55<br>
		Faks: +372 626 64 56<br>
		E-post: raimond.irjas@eri.ee
		</td><!-- isiku_kontakt -->
		<td class="foto"><img src="{VAR:baseurl}/img/img-no-disain/aigar_mets.jpg" alt="" width="70" height="105" border="0"></td>
		<td class="isiku_kontakt" style="padding: 12px 11px;">
		<strong>Aigar Mets</strong><br>
		Elamispindade maakler<br>
		<br>
		GSM: +372 51 18 337<br>
		Telefon: +372 626 64 55<br>
		Faks: +372 626 64 56<br>
		E-post: raimond.irjas@eri.ee
		</td><!-- isiku_kontakt -->
		<td id="logo"><div><img src="{VAR:company_logo_url}" alt="{VAR:company_logo_alt}" border="0"></div></td>
	</tr>
	</table>
	</td>
</tr><!-- sisu -->

<tr class="alapealkiri">
	<td>
	<table>
	<tr>
		<td class="pealkiri" style="padding-right: 10px;">M&uuml;&uuml;ja</td>
		<td class="joon" style="background: url('{VAR:baseurl}/img/1x1.gif') repeat-x center;"></td>
	</tr>
	</table>
	</td>
</tr><!-- alapealkiri -->

<tr class="sisu">
	<td>{VAR:contact_data}
	<!-- SUB: contact_email -->
	E-mail: <a href="mailto:{VAR:contact_email}">{VAR:contact_email}</a>
	<!-- END SUB: contact_email -->
	<!-- -{VAR:contact_picture_url} -->
	</td>
</tr><!-- sisu -->
</table><!-- detailotsing -->


