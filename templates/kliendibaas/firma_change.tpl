<script language='javascript'>

function put_value(target,value)
{
	if (target == "linn")
		document.add.linn.value = value;
	else
	if (target == "maakond")
		document.add.maakond.value = value;
	else
	if (target == "riik")
		document.add.riik.value = value;
	else

		document.add.submit();
} 

function pop_select(url)
{
	aken=window.open(url,"selector","HEIGHT=220,WIDTH=310")
 	aken.focus()
}
</script>

<form action='reforb.{VAR:ext}' method=post name="add">
<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
	<tr>
		<td class="tableborder">
			<!--tabelshadow-->
			<table width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td width="1" class="tableshadow"><IMG SRC="images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
					<td class="tableshadow"><IMG SRC="images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
						<!--tabelsisu-->
						<table width="100%" cellspacing="0" cellpadding="0">
							<tr>
								<td class="tableinside" height="29">
									<table border="0" cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td width="5"><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>
											<td>
														{VAR:toolbar}
											</td>
										</tr>
									</table>
									<table class="aste01" cellpadding=3 cellspacing=1 border=0>
										<tr>
											<td class="celltext"><small>oid:{VAR:oid}</small>
											<fieldset><legend>põhiandmed </legend>
												<br />
												reg nr: <input class=formtext type=text NAME='firma[reg_nr]' value='{VAR:f_reg_nr}' size=8><br />
												firma nimetus: <input class=formtext type=text NAME='firma[firma_nimetus]' value='{VAR:f_firma_nimetus}' size=25> 
												ettevõtlusvorm: <input type=hidden NAME='firma[ettevotlusvorm]' value='{VAR:f_ettevotlusvorm}' size=3>
													<select class=formselect><option>vali</select>
												<br />
												tegevusala: <input type=hidden NAME='firma[tegevusala]' value='{VAR:f_tegevusala}'>
													<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_tegevusala_pop}')" value=vali>
												<br />
												põhitegevus: <input type=hidden NAME='firma[pohitegevus]' value='{VAR:f_pohitegevus}'>
													<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_pohitegevus_pop}')" value=vali>
												<br />
												kõrvaltegevus: <input type=hidden NAME='firma[korvaltegevus]' value='{VAR:f_korvaltegevus}'>
													<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_korvaltegevus_pop}')" value=vali>
											</fieldset>
											</td>
										</tr>
									</table>
									<table>
										<tr>
											<td class=celltext>
											<fieldset><legend>kontakt</legend>
												riik: <input type=hidden NAME='firma[riik]' value='{VAR:f_riik}'>
													<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_riik_pop}')" value=vali>
												<br />
												linn: <input type=hidden NAME='firma[linn]' value='{VAR:f_linn}'>{VAR:f_linn_text} 
													<input type=text NAME='linn' value='oo'>
													<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_linn_pop}')" value=vali>
												<br />
												maakond: <input type=hidden NAME='firma[maakond]' value='{VAR:f_maakond}' size=8>
													<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_maakond_pop}')" value=vali>
												<br />
												postiindeks: <input class=formtext type=text NAME='firma[postiindeks]' value='{VAR:f_postiindeks}' size=5>
												<br />
												tänav/maja: <input class=formtext type=text NAME='firma[aadress]' value='{VAR:f_aadress}'>
												<br />
												telefon: <input class=formtext type=text NAME='firma[telefon]' value='{VAR:f_telefon}' size=12>
												mobiil: <input class=formtext type=text NAME='firma[mobiil]' value='{VAR:f_mobiil}' size=12>
												fax: <input class=formtext type=text NAME='firma[faks]' value='{VAR:f_faks}' size=12>
												<br>
												e-mail: <input class=formtext type=text NAME='firma[e_mail]' value='{VAR:f_e_mail}'>
												kodulehekülg: <input class=formtext type=text NAME='firma[kodulehekylg]' value='{VAR:f_kodulehekylg}'>
											</fieldset>
											</td>
										</tr>
									</table>
									<table>
										<tr>
											<td class="celltext">
												firmajuht: {VAR:firmajuht} 
													<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_firmajuht_pop}')" value=vali>
												<br>
												andmete allikas: <a href=#>{VAR:sourcefile}</a>fail
												<br>
												olek: {VAR:olek}<select class=formselect><option>vali</select>
												<br>
												<!--lisa: <textarea NAME='firma_[more]' value='{VAR:f_more}'></textarea>-->
												<br>
												kommentaarid: <textarea name="comment" rows=3 cols=40  class="formtext">{VAR:comment}</textarea>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
{VAR:reforb}
</form>


