<script language='javascript'>

function put_value(target,value)
{
	if (target == "linn")
		document.add.elements["contact[linn]"].value = value;
	else
	if (target == "maakond")
		document.add.elements["contact[maakond]"].value = value;
	else
	if (target == "riik")
		document.add.elements["contact[riik]"].value = value;
	else {}
		document.add.submit();
} 

function pop_select(url)
{
	aken=window.open(url,"selector","HEIGHT=300,WIDTH=310,TOP=400,LEFT=500")
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
											<td class="celltext">
											</td>
										</tr>
									</table>
									<table>
										<tr>
											<td class=celltext>
											<fieldset><legend>Kontaktandmed</legend>
											<table border=0 class=celltext>
											<tr>
												<td width=25%>
												name: <input class=formtext type=text NAME='contact[name]' value='{VAR:f_name}'>
												</td>
											
												<td>

												tüüp: <select class=formselect NAME='contact[tyyp]'>{VAR:f_tyyp}</select>
												</td>
											</tr>
											<tr>
												<td width=25%>
												riik: 
												</td>
											
												<td>
												<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_riik_pop}')" value="+">
												<input type="hidden" NAME='contact[riik]' value='{VAR:f_riik}'>
												<b>{VAR:s_riik}</b>
												</td>
											</tr>


												
											<tr>
												<td>
												linn: 
												</td>
											
												<td>
												<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_linn_pop}')" value="+">
												<input type=hidden NAME='contact[linn]' value='{VAR:f_linn}'>
												<b>{VAR:s_linn}</b>
												</td>
											</tr>
												
											<tr>
												<td>
												maakond:
												</td>
											
												<td>
												<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_maakond_pop}')" value="+">
												<input type=hidden NAME='contact[maakond]' value='{VAR:f_maakond}' size=8>
												<b>{VAR:s_maakond}</b>
												</td>
											</tr>
												
											<tr>
												<td colspan=2>

												tänav/maja:
												<input class=formtext type=text NAME='contact[aadress]' value='{VAR:f_aadress}'>


												postiindeks:
												<input class=formtext type=text NAME='contact[postiindeks]' value='{VAR:f_postiindeks}' size=5 maxlength=5>
												<br />


												telefon: <input class=formtext type=text NAME='contact[telefon]' value='{VAR:f_telefon}' size=12>
												mobiil: <input class=formtext type=text NAME='contact[mobiil]' value='{VAR:f_mobiil}' size=12>
												fax: <input class=formtext type=text NAME='contact[faks]' value='{VAR:f_faks}' size=12>
												piipar: <input class=formtext type=text NAME='contact[piipar]' value='{VAR:f_piipar}' size=12>
												<br />
												e-mail: <input class=formtext type=text NAME='contact[e_mail]' value='{VAR:f_e_mail}'>
												kodulehekülg: <input class=formtext type=text NAME='contact[kodulehekylg]' value='{VAR:f_kodulehekylg}'>
												</td>
											</tr>
											</table>
												kommentaarid: <textarea name="comment" rows=3 cols=40  class="formtext">{VAR:comment}</textarea>

											</fieldset>
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
[{VAR:abx}]

{VAR:reforb}
</form>
<!--<iframe name=vali width="100%" height="400" frameborder="1" src="">whee</iframe>-->