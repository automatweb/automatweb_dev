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
											<fieldset><legend>põhiandmed </legend>
												riigi nimi <input type=text NAME='riik[name]' value='{VAR:name}' size=25>
												<br>
												inglise keelne nimetus: <input type=text NAME='riik[name_en]' value='{VAR:name_en}' size=25>
												<br>
												kohalik nimetus: <input type=text NAME='riik[name_native]' value='{VAR:name_native}' size=25>
												<br>
												keeled:<input type=text NAME='riik[languages]' value='{VAR:languages}' size=25>
												<br>
												asukoht: <input type=text NAME='riik[location]' value='{VAR:location}' size=25>
												<br>
												lühend: <input type=text NAME='riik[lyhend]' value='{VAR:lyhend}' size=5>
												<br>
												kommentaarid: <textarea name="comment" rows=3 cols=40  class="formtext">{VAR:comment}</textarea>

											</fieldset>

											{VAR:list}
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


