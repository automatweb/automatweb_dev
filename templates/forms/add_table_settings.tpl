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
															<td class="icontext" align="center"><input type='image' src="{VAR:baseurl}/automatweb/images/blue/big_save.gif" width="32" height="32" border="0" VALUE='submit' CLASS="small_button"><br><a href="javascript:document.add.submit()">Salvesta</a></td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
										<br>
										<table class="aste01" cellpadding=3 cellspacing=1 border=0>
											<tr>
												<td class="celltext" colspan="2">{VAR:menu}</td>
											</tr>
											<tr>
												<td class="celltext">Nimi:</td><td class="celltext"><input type='text' NAME='name' VALUE='{VAR:name}' class="formtext"></td>
											</tr>
											<tr>
												<td class="celltext">Kommentaar:</td><td class="celltext"><textarea class="formtext" name="comment">{VAR:comment}</textarea></td>
											</tr>
											<tr>
												<td class="celltext">Keeled:</td><td class="celltext"><select class="formselect" NAME='settings[languages][]' multiple>{VAR:languages}</select></td>
											</tr>
											<tr>
												<td colspan="2" class="celltext">Vormid, kust võetakse elemendid:</td>
											</tr>
											<tr>
												<td colspan="2" class="celltext"><select size="20" class="formselect" NAME='settings[forms][]' multiple>{VAR:forms}</select></td>
											</tr>
											<tr>
												<td colspan="2" class="celltext">Kataloogid, kuhu saab sisestusi liigutada:</td>
											</tr>
											<tr>
												<td colspan="2" class="celltext"><select class="formselect" NAME='settings[folders][]' size="20" multiple>{VAR:folders}</select></td>
											</tr>
											<tr>
												<td colspan="2" class="celltext"><input type="checkbox" name="settings[has_yah]" value="1" class="formcheck" {VAR:has_yah}> YAH riba</td>
											</tr>
											<tr>
												<td colspan="2" class="celltext"><input type="checkbox" name="settings[has_aliasmgr]" value="1" class="formcheck" {VAR:has_aliasmgr}> Aliastehaldur</td>
											</tr>
											<tr>
												<td colspan="2" class="celltext"><input type="checkbox" name="settings[select_default]" value="1" class="formcheck" {VAR:select_default}> _Vali_ tulba default</td>
											</tr>
											<tr>
												<td colspan="2" class="celltext"><input type="checkbox" name="settings[has_textels]" value="1" class="formcheck" {VAR:has_textels}> Näita tekst tüüpi elemente</td>
											</tr>
											<tr>
												<td colspan="2" class="celltext"><input {VAR:has_groupacl} type="checkbox" name="settings[has_groupacl]" value="1" class="formcheck"> Õigused tulpadele piiratud gruppide kaupa</td>
											</tr>
											<tr>
												<td colspan="2" class="celltext"><input type="checkbox" {VAR:has_grpnames} name="settings[has_grpnames]" value="1" class="formcheck">Näita tulpade nimesid iga grupeerimiselemendi all</td>
											</tr>
											<tr>
												<td colspan="2" class="celltext"><input type="checkbox" {VAR:has_print_button} name="settings[print_button]" value="1" class="formcheck">Prindi nupp &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Browse ikoon&nbsp;<input type="file" class="formfile" name="settings[print_button_file]"></td>
											</tr>
<!-- SUB: CHANGE -->
											<tr>
												<td colspan="2" class="celltext">Näitamise m&auml;&auml;rangud</td>
											</tr>

											<tr>
												<td colspan="2" class="celltext">Vaatamine:</td>
											</tr>
											<tr>
												<td colspan="2" class="celltext"><select size="10" class="formselect" NAME='settings[view_cols][]' multiple>{VAR:view_cols}</select></td>
											</tr>
											<tr>
												<td colspan="2" class="celltext">Muutmine:</td>
											</tr>
											<tr>
												<td colspan="2" class="celltext"><select size="10" class="formselect" NAME='settings[change_cols][]' multiple>{VAR:change_cols}</select></td>
											</tr>
											<tr>
												<td colspan="2" class="celltext">
													<table border="0">
														<tr>
															<td colspan="3" class="celltext">Vaikimisi sorteeritakse:</td>
														</tr>
														<tr>
															<td class="celltext">Jrk:</td>
															<td class="celltext">Element:</td>
															<td class="celltext">Asc/Desc:</td>
														</tr>
														<!-- SUB: DEFSORT_LINE -->
														<tr>
															<td class="celltext"><input type="text" name="defsort[{VAR:ds_nr}][ord]" value="{VAR:ds_ord}" class="formtext" size="4"></td>
															<td class="celltext"><select class="formselect" name="defsort[{VAR:ds_nr}][el]"><option value=''>{VAR:ds_els}</select></td>
															<td class="celltext"><input type="radio" class="formradio" name="defsort[{VAR:ds_nr}][type]" value="asc" {VAR:ds_asc}> Asc. <input type="radio" name="defsort[{VAR:ds_nr}][type]" value="desc" {VAR:ds_desc}> Desc.</td>
														</tr>
														<!-- END SUB: DEFSORT_LINE -->
													</table>
												</td>
											</tr>

											<tr>
												<td colspan="2" class="celltext">
													<table border="0">
														<tr>
															<td colspan="4" class="celltext">Jäta välja mitmekordsed:</td>
														</tr>
														<tr>
															<td class="celltext">Element:</td>
															<td class="celltext">Koondatud tulp:</td>
															<td class="celltext">Eraldaja:</td>
															<td class="celltext">Järjekorraelement:</td>
														</tr>
														<!-- SUB: GRPLINE -->
														<tr>
															<td class="celltext"><SELECT CLASS="formselect" name="grps[{VAR:grp_nr}][gp_el]"><option value=''>{VAR:gp_els}</select></td>
															<td class="celltext"><SELECT CLASS="formselect" name="grps[{VAR:grp_nr}][collect_el]"><option value=''>{VAR:collect_els}</select></td>
															<td class="celltext"><input type="text" class="formtext" name="grps[{VAR:grp_nr}][sep]" value="{VAR:gp_sep}" size="4"></td>
															<td class="celltext"><SELECT CLASS="formselect" name="grps[{VAR:grp_nr}][ord_el]"><option value=''>{VAR:ord_els}</select></td>
														</tr>
														<!-- END SUB: GRPLINE -->
													</table>
												</td>
											</tr>

											<tr>
												<td colspan="2" class="celltext">
													<table border="0">
														<tr>
															<td colspan="3" class="celltext">Grupeerimise elemendid:</td>
														</tr>
														<tr>
															<td class="celltext">Jrk:</td>
															<td class="celltext">Element:</td>
															<td class="celltext">Summeeri:</td>
														</tr>
														<!-- SUB: GRP2LINE -->
														<tr>
															<td class="celltext"><input type="text" class="formtext" name="rgrps[{VAR:grp_nr}][ord]" value="{VAR:gp_ord}" size="4"></td>
															<td class="celltext"><SELECT CLASS="formselect" name="rgrps[{VAR:grp_nr}][el]"><option value=''>{VAR:els}</select></td>
															<td class="celltext"><input type="checkbox" class="formcheck" name="rgrps[{VAR:grp_nr}][count]" value="1" {VAR:gp_count}></td>
														</tr>
														<!-- END SUB: GRP2LINE -->
													</table>
												</td>
											</tr>

											<tr>
												<td class="celltext" colspan="2"><input type="checkbox" name="settings[has_pages]" value="1" {VAR:has_pages}> Kirjeid näidatakse lehekülgede kaupa</td>
											</tr>
											<tr>
												<td class="celltext" colspan="2"><input type="radio" name="settings[has_pages_type]" value="text" {VAR:has_pages_text}> Tekstipõhine leheküljevalik</td>
											</tr>
											<tr>
												<td class="celltext" colspan="2"><input type="radio" name="settings[has_pages_type]" value="lb" {VAR:has_pages_lb}> Dropdown leheküljevalik</td>
											</tr>
											<tr>
												<td class="celltext" colspan="2">
													<table border="0">
														<tr>
															<td class="celltext"><input type="checkbox" name="settings[user_entries]" value="1" {VAR:has_user_entries}> Näita ainult Useri enda sisestusi </td>
															<td class="celltext">Näita kõiki sisestusi gruppidele:<br>
																<select name="user_entries_except_grps[]" multiple  size="10" class="formselect">{VAR:uee_grps}</select>
															</td>
														</tr>
													</table>
												</td>
											</tr>

											<tr>
												<td class="celltext" colspan="2">
													<table border="0">
														<tr>
															<td class="celltext">Nupud:</td>
															<td class="celltext">Nupu tekst:</td>
															<td class="celltext">Jrk.:</td>
															<td class="celltext">Üleval/all:</td>
														</tr>
														<!-- SUB: BUTTON -->
														<tr>
															<td class="celltext"><input type="checkbox" name="buttons[{VAR:bt_id}][check]" value="1" {VAR:button_check}> {VAR:bt_name}</td>
															<td class="celltext"><input class="formtext" type="text" name="buttons[{VAR:bt_id}][text]" value="{VAR:button_text}" ></td>
															<td class="celltext"><input class="formtext"  size="4" type="text" name="buttons[{VAR:bt_id}][ord]" value="{VAR:button_ord}"></td>
															<td class="celltext"><input type="checkbox" name="buttons[{VAR:bt_id}][pos][up]" value="1" {VAR:button_up}> Üleval <input type="checkbox" name="buttons[{VAR:bt_id}][pos][down]" value="1" {VAR:button_down}> All</td>
														<!-- END SUB: BUTTON -->
													</table>
												</td>
											</tr>
<!-- END SUB: CHANGE -->

										</table>
										<table border="0" cellpadding="0" cellspacing="0" width="100%">
											<tr>
												<td width="5"><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>
												<td>
													<table border="0" cellpadding="0" cellspacing="0">
														<tr>
															<td class="icontext" align="center"><input type='image' src="{VAR:baseurl}/automatweb/images/blue/big_save.gif" width="32" height="32" border="0" VALUE='submit' CLASS="small_button"><br><a href="javascript:document.add.submit()">Salvesta</a></td>
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
			</td>
		</tr>
	</table>
	{VAR:reforb}
</form>


