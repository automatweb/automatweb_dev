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
											<td class="celltext" width=30% colspan=2 align=right>
											<b><a href="http://aw.struktuur.ee/index.aw?section=53471" target=_blank>help?</a></b>
											<td>
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
											<td class="celltext" colspan=2>
											<input type="radio" name="file_list" value=0 {VAR:file_list_off}> 
											html source kataloog<br />
											<input type=text name="source_path" value="{VAR:source_path}" size=50>
										</tr>
										<tr>
											<td class="celltext" colspan=2>
											<input type="radio" name="file_list" value=1 {VAR:file_list_on}>
											failide nimekiri<br />
											<textarea cols=60 rows=3 name='files'>{VAR:files}</textarea>
											</td>
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


