<form action='reforb.{VAR:ext}' method=post name=add>
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
						
									<table class="aste01" width=90% cellpadding=3 cellspacing=1 border=0>
										<tr>
											<td colspan=2>
												<table border=1 cellpadding=0 cellspacing=0>
													<tr>
														<td>
															alates:{VAR:from_date}<br />
															kuni{VAR:till_date}<br />
														</td>
													</tr>
												</table>
											</td>
										<tr>

										<tr>
											<td class="celltext">
												<input checked type='checkbox' name='from' VALUE='1'></td>
											<td class="celltext">
											
											</td>
										</tr>
										<tr>											
											<td class="celltext">
												<input checked type='checkbox' name='from' VALUE='1'></td>
											<td class="celltext">
											
											</td>
										</tr>
										<tr>
											<td class="celltext">
												<input checked type='checkbox' name='from' VALUE='1'></td>
											<td class="celltext">
											
											</td>
										</tr>

										</tr>
										<tr>
											<td class="fform" colspan="2">
																						<a href="{VAR:link}">kuu ülevaade</a>
																						<a href="{VAR:link}">nädala stat</a>
																						<a href="{VAR:link}">päeva stat</a>
																						<a href="{VAR:link}">aasta stat</a>

											<input type="submit" value="otsi">
											</td>
											<td>
												
											</td>
										</tr>

										<tr>
											<td>
											{VAR:kuu_date}
											</td>
											<td>
												<input type=submit name=x value="kuu statistika">
											</td>
										</tr>
										<tr>
											<td>
											{VAR:aasta_date}
											</td>
											<td>
												<input type=submit name=x value="aasta statistika">
											</td>
										</tr>

										

										<tr>
											<td class="celltext" colspan=2>

										{VAR:abix}
											<fieldset>
									
												<legend>linikogu statistika</legend>
												select count from linikogu_stat: {VAR:caunt}<br />
												select count from linikogu_stat where action=1(brausitud): {VAR:caunt_dirs} <br />
												select count from linikogu_stat  where action=2(linke vaadatud): {VAR:caunt_links} <br />
												select count from linikogu_stat where oid=666:_ {VAR:caunt_linke} <br />
											</fieldset>
{VAR:stat_out}
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


