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
											<td class="celltext" colspan=2>
											<table border=1 cellpadding=0 cellspacing=0>
											<tr><td>kommentaar</td><td>sql veerg</td><td>unique</td><td>strip html</td><td>tyyp</td><td>size</td></tr>
										<!-- SUB: ruul -->
											<tr>
												<td>
												{VAR:desc}&nbsp;
												</td>
												<td>
												{VAR:mk_field}&nbsp;
												</td>
												<td>
												<input type=checkbox name='{VAR:mis}[{VAR:ruul}][unique]' {VAR:unique}>
												</td>
												<td>
												<input type=checkbox name='{VAR:mis}[{VAR:ruul}][strip_html]' {VAR:strip_html}>
												</td>
												<td>
												<select name='{VAR:mis}[{VAR:ruul}][type]'>
												{VAR:type}
												</select>
												</td>
												<td>
												<input class="formtext" name='{VAR:mis}[{VAR:ruul}][size]' value='{VAR:size}' type=text size=4>
												</td>
											</tr>
										<!-- END SUB: ruul -->
											</table>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>lisa id veerg (id int primary key auto_increment)
											<input type=checkbox name="add_id" value=1 {VAR:add_id}><br>
														<b>{VAR:create_table}</b>
														<pre>{VAR:mk_table}</pre><br><br>
														<b>{VAR:drop_table}</b>
														<pre>{VAR:got_table}</pre>
											</td>
										</tr>
									</table>
									{VAR:gogo}
											<table border=1 cellpadding=0 cellspacing=0>
									{VAR:some_data}
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


