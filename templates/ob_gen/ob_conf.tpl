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
											{VAR:ob_conf_table}

											<!-- SUB: object -->
											<select name='object[{VAR:field_name}][]'  class="formselect">
											{VAR:object_f}
											</select>
											<input {VAR:dejoin_object} type="checkbox" name="dejoin[object][{VAR:field_name}][]" title="leia id teisest tabelist">
											{VAR:dejoin_object_conf}
											<input {VAR:add} type="checkbox" name="add[object][{VAR:field_name}][]" title="extra rida"><br />
											<!-- END SUB: object -->

											<!-- SUB: meta -->
											<input value="{VAR:meta_f}" type="text" name="meta[{VAR:field_name}][]" size=8 class="formtext">
											<input {VAR:dejoin_meta} type="checkbox" name="dejoin[meta][{VAR:field_name}][]" title="leia id teisest tabelist">
											{VAR:dejoin_meta_conf}
											<input {VAR:add} type="checkbox" name="add[meta][{VAR:field_name}][]" title="extra rida"><br />
											<!-- END SUB: meta -->

											<!-- SUB: sisu -->
											<select name="sisu[{VAR:field_name}][]"  class="formselect">
												{VAR:sisu_f}
											</select>
											<input {VAR:dejoin_sisu} type="checkbox" name="dejoin[sisu][{VAR:field_name}][]" title="leia id teisest tabelist">
											{VAR:dejoin_sisu_conf}
											<input {VAR:add} type="checkbox" name="add[sisu][{VAR:field_name}][]" title="extra rida"><br />
											<!-- END SUB: sisu -->
																						
											<!-- SUB: dejoin -->
												<br>
												<select name="dejoin_table[{VAR:what}][{VAR:field_name}][]" class="formselect" title="sellest tabelist">
													{VAR:dejoin_tables}
												</select>
												<select name="dejoin_field[{VAR:what}][{VAR:field_name}][]" class="formselect" title="see veerg">
													{VAR:dejoin_fields}
												</select><br />
											<!-- END SUB: dejoin -->

											<!-- SUB: remember -->
												<input {VAR:remember} type="checkbox" name="remember[{VAR:what}][{VAR:field_name}][]">
												<small>remember join result</small>
											<!-- END SUB: remember -->

											<!-- SUB: unique -->
											<input {VAR:unique} type="checkbox" name="unique[{VAR:field_name}]">
											<!-- END SUB: unique -->
											</td>
										</tr>											
										<tr>
											<td class="celltext">
												<a href="{VAR:genereeri}" target=_blank>GENEREERI TERVEST ANDMETABELIST OBJEKTID</a><br />
												<a href="{VAR:genereeri5}" target=_blank>GENEREERI ANDMETABELIST 5 esimest OBJEKTI</a><br />

												<a href="{VAR:normalizer}" target=_blank>normalizer - don't touch that !!!</a><br />

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


