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
												<table border="0" cellpadding="0" cellspacing="0">
													<tr>
														<td class="icontext" align="center"><input type='image' src="{VAR:baseurl}/automatweb/images/blue/big_save.gif" width="32" height="32" border="0" VALUE='submit' CLASS="small_button"><br>
														<a href="javascript:document.add.submit()">Salvesta</a></td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
									<br>

									<table class="aste01" cellpadding=3 cellspacing=1 border=0>
										
										<tr>
											<td class="celltext">Tootekataloogi pealkiri:</td><td class="celltext"><input type='text' NAME='pealnimi' VALUE='{VAR:pealkiri}' class="formtext"></td>
										</tr>
										<tr>
											<td class="celltext">Automaatne rootmenüü nimi?</td>
											<td class="celltext">
												<input type="radio" name="rootauto" value="yes" checked onClick="document.kaspealkiri.submit();">jah
												<input type="radio" name="rootauto" value="no" onClick="document.kaspealkiri.submit();">ei
											</td>
										</tr>
										<tr>
											<td class="celltext">Rootmenüü pealkiri:</td><td class="celltext"><input type='text' NAME='rootname' VALUE='{VAR:pealkiri}' class="formtext"></td>
										</tr>
										<tr>
											<td class="celltext">Ava tooted uues aknas?</td>
											<td class="celltext"><input type="checkbox" name="newwindow" value="1" {VAR:newwindow} ></td>
										</tr>
										
										<tr>
											<td class="celltext">Näita pealkirja ja joont?</td>	
											<td class="celltext">
										<form name="kaspealkiri" method="post" action="">	
												<input type="radio" name="headline" value="yes" checked onClick="document.kaspealkiri.submit();">jah
												<input type="radio" name="headline" value="no" onClick="document.kaspealkiri.submit();">ei
										</form>	
											</td>
										</tr>
										<tr>	
											<td class="celltext" colspan=2>			
										<?php
											if ($kaspealkiri == 'yes')
											{
										?>
											{VAR:joon}<font size=2><b>{VAR:pealkiri}:</b></font><br />
											
										<?php 
											}

											if ($kaspealkiri == 'no') 
											{
										?>
											<font size=2><b>yes:</b></font><br />
											
										<?php 
											}
										?>
										<form name="showdata" method="post" action="">	
												<select NAME='root' class="formselect" onChange="javascript:document.showdata.submit();">{VAR:rootitems}</select>
										</form>

										<?php
											if ($showdata == 'Kama/test') 
											{
											echo "tere";
											}
										?>	
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


