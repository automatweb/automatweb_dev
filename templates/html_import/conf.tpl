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
									<table border=0>
										<tr>
											<td>
												{VAR:reset}<br>
												{VAR:ruul_test}	<br>
											</td>
											<td>
												faili unikaalne string siia: <br>
												<textarea name="match" cols=30 rows=4>{VAR:match}</textarea>
											</td>
										</tr>
									</table>
									<table border=2 bgcolor=white>
										<tr>
											<td>
												{VAR:source}
											</td>
										</tr>
									<table>


									<table border=1 cellpadding=0 cellspacing=0 bordercolor=white>
									<!-- SUB: ruulbar -->
										<tr><td>reegel</td><td>attribuudi alguskood</td><td>kirjeldus<br>veeru nimi</td>
											<td>attribuudi 	lõpukood</td>
										</tr>
									<!-- END SUB: ruulbar -->

									<!-- SUB: ruul -->
										<tr>
											<td>
											{VAR:ruul}
											</td>
											<td>
											<textarea cols=25 rows=4 name='{VAR:mis}[{VAR:ruul}][begin]'>{VAR:begin}</textarea>
											</td>
											<td>
										<table border=0 cellpadding=0 cellspacing=0>
											<tr>
												<td>kirjeldus<br>
												<input class="formtext" type=text name='{VAR:mis}[{VAR:ruul}][desc]' value="{VAR:desc}"size=15><br />
												<b>sql veerg</b><input class="formtext" type=text name='{VAR:mis}[{VAR:ruul}][mk_field]' value="{VAR:mk_field}" size=10><br />
												</td>
											</tr>
										</table>
											</td>
											<td>
											<textarea cols=25 rows=4 name='{VAR:mis}[{VAR:ruul}][end]'>{VAR:end}</textarea>
											</td>
											</tr>
									<!-- END SUB: ruul -->
									<!-- SUB: fields -->
												kirjeldus<br />
												<input class="formtext" type=text name='{VAR:mis}[{VAR:ruul}][desc]' value="{VAR:desc}"size=15><br />
												sql veerg<br />
												<input class="formtext" type=text name='{VAR:mis}[{VAR:ruul}][mk_field]' value="{VAR:mk_field}" size=10><br />
									<!-- END SUB: fields -->
									</table>
											{VAR:abx}
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


