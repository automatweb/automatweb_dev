<script language='javascript'>

function put_value(target,value)
{
	if (target == "tegevusala")
		document.add.elements["firma[tegevusala]"].value = value;
	else
	if (target == "korvaltegevus")
		document.add.elements["firma[korvaltegevus]"].value = document.add.elements["firma[korvaltegevus]"].value + ';' + value;
	else
	if (target == "pohitegevus")
		document.add.elements["firma[pohitegevus]"].value = value;
	else
	if (target == "ettevotlusvorm")
		document.add.elements["firma[ettevotlusvorm]"].value = value;
//	else
//	if (target == "firmajuht")
//		document.add.elements["firma[firmajuht]"].value = value;
	else {}

		document.add.submit();
} 

function pop_select(url)
{
	aken=window.open(url,"selector","HEIGHT=300,WIDTH=510,TOP=400,LEFT=500")
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
											<fieldset><legend>Ettevõtte üldandmed</legend>
											<table border=0 class=celltext>
											<tr><td width=25%>
													reg nr:
												</td>
											
												<td>
													<input class=formtext type=text NAME='firma[reg_nr]' value='{VAR:f_reg_nr}' size=8>
												</td>
											</tr>
												
											<tr>
												<td>
													firma nimetus:
												</td>
											
												<td>
													<input class=formtext type=text NAME='firma[firma_nimetus]' value='{VAR:f_firma_nimetus}' size=25>
												</td>
											</tr>											<tr>
												<td>
														kommentaarid: 
												</td>
											
												<td>
													<textarea name="comment" rows=3 cols=40  class="formtext">{VAR:comment}</textarea>
												</td>
											</tr>

											
											<tr>
												<td>
			
												ettevõtlusvorm: 
												</td>
											
												<td>
												<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_ettevotlusvorm_pop}')" value="+">
												<input type="hidden" NAME='firma[ettevotlusvorm]' value='{VAR:f_ettevotlusvorm}' size=3>
												<b>{VAR:s_ettevotlusvorm}</b>
												</td>
											</tr>
												
											<tr>
												<td>
												tegevusala:
												</td>
											
												<td>
												<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_tegevusala_pop}')" value="+">
												<input type="hidden" NAME='firma[tegevusala]' value='{VAR:f_tegevusala}'>
												<b>{VAR:s_tegevusala}</b>
												</td>
											</tr>
												
											<tr>
												<td>
												põhitegevus: 
												</td>
											
												<td>
												<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_pohitegevus_pop}')" value="+">
												<input type="hidden" NAME='firma[pohitegevus]' value='{VAR:f_pohitegevus}'>
												<b>{VAR:s_pohitegevus}</b>
												</td>
											</tr>
												
											<tr>
												<td>
												kõrvaltegevused:
												</td>
											
												<td>
												<input type="hidden" NAME='firma[korvaltegevus]' value='{VAR:f_korvaltegevus}'>
												<input class=formbutton type=button onclick="javascript:pop_select('{VAR:f_korvaltegevus_pop}')" value="+">	
												</td>
											</tr>
												
											<tr>
												<td>
												</td>
											
												<td>
												<ul>
												<!-- SUB: s_korvaltegevus -->
												 <small><a href="{VAR:delete}">kustuta</a></small> <b>{VAR:nimetus}</b><br />
												<!-- END SUB: s_korvaltegevus -->
												</ul>
												</td>
											</tr>
											<tr>
												<td>
												ettevõtte tegevuse kirjeldus
												</td>
												<td>
													<textarea name="fima[tegevusala_kirjeldus]" rows=3 cols=40  
													class="formtext">{VAR:f_tegevusala_kirjeldus}</textarea>
												</td>
											</tr>


											
											</table>
											</fieldset>
											</td>
										</tr>
									</table>
									<table border=1>
										<tr>
											<td class=celltext>
												<a href="{VAR:contact_change}" target=muuda title="muuda">kontakt </a>
												</td><td>
												<input class=formtext type="hidden" NAME='firma[contact]' value='{VAR:f_contact}'>
												<b>{VAR:s_contact}</b>
											</td>
										</tr>
										<tr>
											<td class="celltext">
												<a href="{VAR:firmajuht_change}" target=muuda>firmajuht </a>
												</td><td>								
												<input class=formtext type="hidden" NAME='firma[firmajuht]' value='{VAR:f_firmajuht}'>
												<b>{VAR:s_firmajuht}</b>
											</td>
										</tr>
									</table>
									<iframe src="" name=muuda width=100% height=800></iframe>
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