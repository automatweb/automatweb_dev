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
									{VAR:reset}<br>
									{VAR:ruul_test}	<br>
									{VAR:gogo}
									<table border=1 cellpadding=0 cellspacing=0 bordercolor=white>
									<!-- SUB: ruulbar -->
									<tr><td>reegel</td><td>attribuudi alguskood</td><td>kirjeldus<br>veeru nimi</td><td>attribuudi lõpukood</td><td>abx</td></tr>
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
												<td>
												<input class="formtext" type=text name='{VAR:mis}[{VAR:ruul}][desc]' value="{VAR:desc}"size=15><br>
												<b>*</b><input class="formtext" type=text name='{VAR:mis}[{VAR:ruul}][mk_field]' value="{VAR:mk_field}" size=10>
												</td>
											</tr>
										</table>
											</td>
											<td>
											<textarea cols=25 rows=4 name='{VAR:mis}[{VAR:ruul}][end]'>{VAR:end}</textarea>
											</td>
											<td><a href=#bottom>html</a>
											</td>
											</tr>
									<!-- END SUB: ruul -->
									<!-- SUB: fields -->
												<input class="formtext" type=text name='{VAR:mis}[{VAR:ruul}][desc]' value="{VAR:desc}"size=15><br>
												<b>*</b><input class="formtext" type=text name='{VAR:mis}[{VAR:ruul}][mk_field]' value="{VAR:mk_field}" size=10>
									<!-- END SUB: fields -->
									</table>
									<a name=bottom>
									<table border=2 bgcolor=white>
										<tr>
											<td>
												{VAR:source}
											</td>
										</tr>
									<table>
									faili unikaalne string siia: <textarea name="match" cols=30 rows=4>{VAR:match}</textarea>

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


