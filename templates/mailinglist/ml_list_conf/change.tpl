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
											<td class="celltext">Nimi:</td><td class="celltext"><input type='text' NAME='name' VALUE='{VAR:name}' class="formtext"></td>
										</tr>
										<tr>
											<td class="celltext">Kasutaja lisamise form:</td><td class="celltext"><select NAME='user_form[]' size="10" multiple class="formselect">{VAR:forms}</select></td>
										</tr>
										<tr>
											<td class="celltext">Root kataloog:</td><td class="celltext"><select multiple NAME='folder[]' size="20" class="formselect">{VAR:folders}</select></td>
										</tr>
										<!-- SUB: CHANGE -->
										<tr>
											<td colspan="2" class="celltext"><Br><br>Vormide j&auml;rjekord listi liikme lisamisel:</td>
										</tr>
										<tr>
											<td class="celltext">Vorm</td>
											<td class="celltext">Jrk</td>
										</tr>
										<!-- SUB: FORM -->
										<tr>
											<td class="celltext">{VAR:form}</td>
											<td class="celltext"><input type="text" name="jrk[{VAR:form_id}]" class="formtext" size="2" value="{VAR:jrk}"></td>
										</tr>
										<!-- END SUB: FORM -->
										<tr>
											<td colspan="2" class="celltext"><br><br>Vali elemendid, mille v22rtus pannakse listi liikme objekti nimeks:</td>
										</tr>
										<!-- SUB: ELEMENT -->
										<tr>
											<td class="celltext">{VAR:elname}</td>
											<td class="celltext"><input type="checkbox" name="name_els[]" value="{VAR:elid}" {VAR:is_name_el}></td>
										</tr>
										<!-- END SUB: ELEMENT -->

										<tr>
											<td class="celltext">Element, kus on meiliaadress:</td>
											<td class="celltext"><select name='mailto_el' class='formselect'>{VAR:mailto_el}</select></td>
										</tr>

										<!-- END SUB: CHANGE -->
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


