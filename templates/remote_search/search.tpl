<form action='orb.{VAR:ext}' method=get name="add">
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
														<a href="javascript:document.add.submit()">Otsi</a></td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
									<br>

									<table class="aste01" cellpadding=3 cellspacing=1 border=0>
										<tr>
											<td class="celltext">Nimi:</td><td class="celltext"><input type="text" name="name" value="{VAR:name}" class="formtext"></td>
										</tr>
										<tr>
											<td class="celltext">Login objekt:</td><td class="celltext"><select name='login_obj' class="formselect">{VAR:login_objs}</select></td>
										</tr>
									</table>
									{VAR:search}

									{VAR:res}

<!--										<tr>
											<td class="celltext">T&uuml;&uuml;p:</td><td class="celltext"><select NAME='s_class' class="formselect">{VAR:classes}</select></td>
										</tr>
										<tr>
											<td class="celltext">Nimi:</td><td class="celltext"><input type="text" NAME='s_name' class="formtext" value="{VAR:s_name}"></td>
										</tr>
										<tr>
											<td class="celltext" colspan="2">Tulemused:</td>
										</tr>
										<tr>
											<td class="celltext" colspan="2">
												<table class="aste01" cellpadding=3 cellspacing=1 border=0>
													<tr>
														<td class="celltext">Nimi</td>
														<td class="celltext">Muudetud</td>
														<td class="celltext">Muutja</td>
														<td class="celltext">T&uuml;&uuml;p</td>
														<td class="celltext">Vali</td>
													</tr>
													<!-- SUB: LINE -->
													<tr>
														<td class="celltext">{VAR:name}</td>
														<td class="celltext">{VAR:modifiedby}</td>
														<td class="celltext">{VAR:modified}</td>
														<td class="celltext">{VAR:type}</td>
														<td class="celltext"><input type="checkbox" type="select" name="sel[]" value="{VAR:oid}"></td>
													</tr>
													<!-- END SUB: LINE -->
												</table>
											</td>
										</tr>
									</table>-->





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

