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
											logi ekraanile
											</td>
											<td class="celltext">
											<input type=checkbox name="log[display]" value=1 {VAR:display}>
											</td>
										</tr>
										<tr>
											<td class="celltext">
											logi sql tabelisse
											</td>
											<td class="celltext">
											<input type=checkbox name="log[db_table]" value=1 {VAR:db_table}>
											</td>
										</tr>
										<tr>
											<td class="celltext">
											logi veateated ja hoiatused
											</td>
											<td class="celltext">
											<input type=checkbox name="log[log_warnings]" value=1 {VAR:log_warnings}>
											</td>
										</tr>
										<tr>
											<td class="celltext">
											kõik loodud objektid
											</td>
											<td class="celltext">
											<input type=checkbox name="log[made_objects]" value=1 {VAR:made_objects}>
											</td>
										</tr>

										<tr>
											<td class="celltext">
											logis näita infona andmeveergu
											</td>
											<td class="celltext">
											<select name="log[a_source_field]">
											{VAR:a_source_field}
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


