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
											<td class="celltext" width=30%>name</td><td class="celltext"><input type='text' NAME='name' VALUE='{VAR:name}' class="formtext"></td>
										</tr>
										<tr>
											<td class="celltext" width=30%>kommentaarid:</td><td class="celltext">
											<textarea name="comment" rows=3 cols=40  class="formtext">{VAR:comment}</textarea>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>html source kataloog<br />
											<input type=text name="source_path" value="{VAR:source_path}">
										</tr>
										<tr>
											<td class="celltext" colspan=2>näitefail<br />
											<input type=text size=70 name='example' value='{VAR:example}'>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>leheküljel on üks kirje
											<input type=radio name='single' value=1 {VAR:singleon}>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>leheküljel on mitu kirjet
											<input type=radio name='single' value=0 {VAR:singleoff}>
											<table><tr><td></td><td>kirje alguskood</td><td></td><td>kirje lõpu kood</td></tr>{VAR:separators}</table>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>esimene rida on kirjeldus?
											<input type=checkbox name='first_row' {VAR:first_row}>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2><input type=checkbox> loodav andmetabel:
											<b>"html_import_<b/><input type=text size=10 name='mk_my_table' value='{VAR:mk_my_table}'>"<br>
											{VAR:mk_table}<br>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2><input type=checkbox> loodav fail:
											<input type=text size=10 name='mk_my_file' value='{VAR:mk_my_file}'><br>
											{VAR:mk_file}<br>
											</td>
										</tr>

									</table>
									{VAR:go_go}<br>
									{VAR:reset}<br>
									{VAR:ruul_test}	<br>
									failis peab sisalduma html kood:<br>
									<textarea cols=25 rows=4 name='match'>{VAR:match}</textarea>
									{VAR:gogo}
									<table border=1 cellpadding=0 cellspacing=0 bordercolor=white>
									<!-- SUB: ruulbar -->
									<tr><td>reegel</td><td>attribuudi alguskood</td><td>kirjeldus<br>loodava veeru nimi</td><td>attribuudi lõpukood</td><td>abx</td></tr>
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
										<!-- SUB: fields -->
										<table border=0 cellpadding=0 cellspacing=0>
											<tr>
												<td>
												desc:<input class="formtext" type=text name='{VAR:mis}[{VAR:ruul}][desc]' value="{VAR:desc}"size=15><br>
												sqlveerg:<input class="formtext" type=text name='{VAR:mis}[{VAR:ruul}][mk_field]' value="{VAR:mk_field}" 	size=10><br>
												unikaalne?<input type=checkbox>striptags?<input type=checkbox><br>
												tyyp<select><option>varchar()<option>text<option>char<option>int</select><br>
												size<input class="formtext" type=text size=4>
												</td>
											</tr>
										</table>
										<!-- END SUB: fields -->
											</td>
											<td>
											<textarea cols=25 rows=4 name='{VAR:mis}[{VAR:ruul}][end]'>{VAR:end}</textarea>
											</td>
											<td><a href=#bottom>html</a>
											</td>
											</tr>
									<!-- END SUB: ruul -->
									</table>
									<a name=bottom>
									<table border=2 bgcolor=white>
										<tr>
											<td>
												{VAR:source}
											</td>
										</tr>
									<table>
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


