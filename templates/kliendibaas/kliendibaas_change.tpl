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
											<td class="celltext" width=30%>kliendibaasi nimi</td>
											<td class="celltext">
												<input type='text' NAME='name' VALUE='{VAR:name}' class="formtext">
											</td>
										</tr>
										<tr>
											<td class="celltext" width=30%>kommentaarid:</td><td class="celltext">
											<textarea name="comment" rows=3 cols=40  class="formtext">{VAR:comment}</textarea>
											</td>
										</tr>
										<tr>
										<td>
										lisa uus firma</br>
										lisa uus tegevusala</br>
										lisa uus firmajuht</br></br>
										vaata firmasid tegevusalade kaupa</br>
										vaata firmasid nimede järgi</br>
										.........
										</td>
										
										</tr>
										<tr>
											<td class="celltext" width=30%>kliendibaasi üldkataloog</td>
											<td class="celltext">
											<select name="">
											<option>plaah
											</select>
											</td>
										</tr>

										<tr>
											<td class="celltext" width=30%>vali kataloogid kuhu salvestatakse firmad</td>
											<td class="celltext">
											<select name="">
											<option>plaah
											</select>
											</td>
										</tr>
										<tr>
											<td class="celltext" width=30%>vali kataloogid kuhu salvestatakse tegevusalad</td>
											<td class="celltext">
											<select name="">
											<option>plaah
											</select>
											</td>
										</tr>
										<tr>
											<td class="celltext" width=30%>vali kataloogid kus baasi näidatakse</td>
											<td class="celltext">
											<select name="">
											<option>plaah
											</select>
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


