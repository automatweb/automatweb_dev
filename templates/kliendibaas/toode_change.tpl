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
											<td class="celltext" width=30%>kood: </td><td class="celltext">
												<input type='text' NAME='toode[kood]' VALUE='{VAR:kood}' class="formtext">
											</td>
										</tr>

										<tr>
											<td class="celltext" width=30%>eestikeelne nimetus: </td><td class="celltext">
											<textarea name="toode[toode]" rows=3 cols=40  class="formtext">{VAR:toode}</textarea>
											</td>
										</tr>

										<tr>
											<td class="celltext" width=30%>inglisekeelne nimetus: </td><td class="celltext">
											<textarea name="toode[toode_en]" rows=3 cols=40  class="formtext">{VAR:toode_en}</textarea>
											</td>
										</tr>

										<tr>
											<td class="celltext" width=30%>kirjeldus: </td><td class="celltext">
											<textarea name="toode[kirjeldus]" rows=3 cols=40  class="formtext">{VAR:kirjeldus}</textarea>
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


