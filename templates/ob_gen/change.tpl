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
											<td class="celltext">Nimi:</td><td class="celltext">
											<input type='text' NAME='name' VALUE='{VAR:name}' class="formtext">
											</td>
										</tr>											

										<tr>
											<td class="celltext">kommentaar:</td><td class="celltext">
											
											<textarea name="comment">{VAR:comment}</textarea>
											</td>
										</tr>											
										<tr>
											<td class="celltext">andmed võetakse tabelist</td>
											<td class="celltext">
											
											<select name="pick_table"  class="formselect">
											{VAR:list_tables}
											</select><br>
											korraga loe tabelist 
													<select name="limit" class="formselect">
													{VAR:chunks}
													</select>kirjet, 
													<small> seda selleks et "select * from table" ei loeks kogu tabelit mällu, <br>
													vaid pannakse limit ja loetakse korraga ainult näiteks sada kirjet "limit 100, 200", "limit 200, 300" ... </small>
											</td>
										</tr>											
										<tr>
											<td class="celltext">tehakse selle klassi objektid</td>
											<td class="celltext">
											<select name="create_object"  class="formselect">
											{VAR:list_classes}
											</select>
											</td>
										</tr>

										<tr>
											<td class="celltext">loodava objekti sisutabel</td>
											<td class="celltext">
											<select name="extra_table"  class="formselect">
											{VAR:extra_table}
											</select>
											</td>
										</tr>



										<tr>
											<td class="celltext">uute objektide aktiivsus vaikimisi</td>
											<td class="celltext">
											<select name="status" class="formselect">
											{VAR:status}
											</select>
											</td>
										</tr>
										<tr>
											<td class="celltext"></td>
											<td class="celltext">
											

											</td>
										</tr>
										<tr>
											<td class="celltext">uued objektid salvesta kataloogi:</td>
											<td class="celltext">
											<select name="save_to_parent"  class="formselect">
											{VAR:parents}
											</select>
											</td>
										</tr>
										<tr>
											<td class="celltext"></td>
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


