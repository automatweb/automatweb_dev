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
											<td class="celltext" width=30% colspan=2>
										</tr>
										<tr>
											<td class="celltext" width=30%>name</td><td class="celltext"><input type='text' NAME='name' VALUE='{VAR:name}' class="formtext"></td>
										</tr>
										<tr>
											<td class="celltext" width=30%>kommentaarid:</td><td class="celltext">
											<textarea name="comment" rows=3 cols=40  class="formtext">{VAR:comment}</textarea>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>html source kataloog<br />
											<input type=text name="source_path" value="{VAR:source_path}" size=50>
										</tr>
										<tr>
											<td class="celltext" colspan=2>näitefail(id)<br />
											<textarea cols=60 rows=3 name='example'>{VAR:example}</textarea>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>
											<input type=radio name='single' value=1 {VAR:singleon}>										
											leheküljel on üks kirje, millel elemendid võivad paikneda suvalises kohas
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>
											<input type=radio name='single' value=0 {VAR:singleoff}>										
											leheküljel on mitu kirjet tabeli kujul, üks rida = üks kirje
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>tulevikus
											<input type=radio name='single' value=0 {VAR:singleoff_}>
											mingi kolmas spetsiifiline variant, mida ma pole veel teinud
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>
											<hr>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2><input type=radio name=output value="mk_my_table" {VAR:is_my_table}> luuakse sql andmetabel
											<b>"html_import_<b/>
											<input type=text size=10 name='mk_my_table' value='{VAR:mk_my_table}' class="formtext">"<br>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2><input type=radio name=output  value="mk_my_query" {VAR:is_my_query}> luuakse sql insert laused faili
											<input type=text size=20 name='mk_my_query' value='{VAR:mk_my_query}' class="formtext"><br>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>tulevikus<input type=radio name=output value="mk_my_csv" {VAR:is_my_csv}> luuakse csv tyypi fail
											<input type=text size=20 name='mk_my_csv' value='{VAR:mk_my_csv}' class="formtext"><br>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>tulevikus<input type=radio name=output value="mk_my_xml" {VAR:is_my_xml}> luuakse xml tyypi fail
											<input type=text size=20 name='mk_my_xml' value='{VAR:mk_my_xml}' class="formtext"><br>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>
											väike docu:<br />
											{VAR:docu}
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


