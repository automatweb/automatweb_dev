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
									<br>

									<table class="aste01" cellpadding=3 cellspacing=1 border=0>
										<tr>
											<td class="celltext">
											<table class="celltext" border=1>
											<tr>
												<th>välja nimi andmetabelis<br>
													{VAR:tab}
												</th>
											
												<th>välja nimi <br>objektitabelis</th>
												<th>välja nimi <br>objecti tabeli metas</th>
												<th>välja nimi sisutabelis</th>
												<th>võta tabelist oid</th>
												<th>unikaalne<br>
													<small>
													kui välja sisu kordub<br>siis duplikaate ei looda
													</small>
												</th>
											</tr>
											{VAR:list_fields}
											<!-- SUB: fields -->
											<tr><td>{VAR:field_name} </td>
											<td>
<!--												<input value="{VAR:object}" type="text" name="object[{VAR:field_name}]" size=8 class="formtext">-->
												<select name='object[{VAR:field_name}]'  class="formselect">
												{VAR:object_fields}
												</select>
											</td>
											<td><input value="{VAR:object_meta}" type="text" name="object_meta[{VAR:field_name}]" size=8 class="formtext"></td>

											<td>
												<select name="extra_table_data[{VAR:field_name}]"  class="formselect">
													{VAR:extra_table_data}
												</select>
											</td>
											<td>
												<input {VAR:dejoin} type="checkbox" name="dejoin[{VAR:field_name}]">
												<!-- SUB: dejoini -->
												<select name="dejoin_table[{VAR:field_name}]" class="formselect">
													{VAR:dejoin_tables}
												</select>
												<select name="dejoin_field[{VAR:field_name}]" class="formselect">
													{VAR:dejoin_fields}
												</select>
												<!-- END SUB: dejoini -->
											</td>
											<td><input {VAR:unique} type="checkbox" name="unique[{VAR:field_name}]"></td>
											<td></td>
											</tr>
											<!-- END SUB: fields -->
											</table>
											
											</td>
										</tr>											
										<tr>
											<td class="celltext"><a href="{VAR:genereeri}" target=_blank>GENEREERI OBJEKTID</a>
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


