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
											<td class="celltext" width=30%>Lingikogu nimi:</td><td class="celltext"><input type='text' NAME='name' VALUE='{VAR:name}' class="formtext"></td>
										</tr>
										<tr>
											<td class="celltext" width=30%>kommentaarid:</td><td class="celltext">
											<textarea name="comment" rows=5 cols=40  class="formtext">{VAR:comment}</textarea>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>juurmenüü (lingikogu asukoht)<br /><select NAME='lingiroot' class="formselect">{VAR:rootitems}
											</select></td>
										</tr>
										<tr>
											<td class="celltext">YAH riba näidatakse</td>
											<td class="celltext">
											<input type=checkbox {VAR:path} NAME="path" value='1' class="formcheckbox">
											</td>
										</tr>
										<tr>
											<td class="celltext">kuvada ainult aktiivsed kataloogid</td>
											<td class="celltext">
											<input type=checkbox {VAR:active_dirs} NAME='active_dirs' value='1' class="formcheckbox">
											</td>
										</tr>

										<tr>
											<td class="celltext" colspan=2>
												<fieldset>
												<legend align=center><b>kataloogi link</b></legend>
													<table border=0 width=100%>
													<tr>
														<td class="celltext" width=50%>
															<input type='radio' name='is_formentry' value='1' {VAR:is_formentry}>
														on vormisisestus?
														</td>
														<td class="celltext">
															<input type='radio' name='is_formentry' value='0' {VAR:is_not_formentry}>
														on tavaline link alamenüüle ja näidatakse kataloogis olevaid linke
														</td>
													</tr>
													<tr>
														<td class="celltext" valign=top width=50%>
														<fieldset>
															<legend><b>vormi seaded</b></legend>
														vali vorm
															<select NAME='forms' class="formselect">{VAR:forms}</select><br />
														vali otsinguvormi element
															<select NAME='felement' class="formselect">{VAR:felement}</select><br />
														täidetakse katloogi
															<select NAME='vordle' class="formselect">{VAR:vordle}</select>-ga <br />
															
															<br />
															<input type=checkbox {VAR:dir_is_form_result} NAME='dir_is_form_result' value='1' class="formcheckbox">
														katalogi lingi väärtus võetakse vormisisestusest<br />
															<input type='radio' name='form_output_is' value='1' {VAR:is_table_output}>
														tavaväljund tabel<br />
															<input type='radio' name='form_output_is' value='0' {VAR:is_default_output}>
														vormi määratud väljund
														<br />
														</td>
														<td class="celltext"valign=top>
														<fieldset>
															<legend><b>lingi attribuudid</b></legend>
															<input type=checkbox {VAR:active_links} NAME='active_links' value='1' class="formcheckbox">
														kuvada ainult aktiivsed lingid<br />
															<input type=checkbox {VAR:newwindow} NAME="newwindow" value='1' class="formcheckbox">
														lingid avanevad uues aknas<br />
															<input type=checkbox {VAR:show_links} NAME="show_links" value='1' class="formcheckbox">
														linke näidatakse <br />
														<table border=1>
															<tr>
																<td class="celltext">attribuut</td>
																<td class="celltext">järjekord</td>
																<td class="celltext">hüperlink</td>
																<td class="celltext">nähtav</td>
																<td class="celltext">vali stiil</td>
																<td class="celltext">tekst enne</td>
																<td class="celltext">tekst peale</td>
															</tr>
															<!-- SUB: klikitav -->
															<tr>
																<td class="celltext">{VAR:mis}</td>
																<td>
																	<input type=text NAME='lorder[{VAR:mis}]' class="formtext" value="{VAR:lorder}" size=4>
																</td>
																<td>
																	<input type=checkbox name='klikitav[{VAR:mis}]' value='{VAR:mis}' {VAR:is_hyper}>
																</td>
																<td>
																	<input type=checkbox name='nahtav[{VAR:mis}]' value='{VAR:mis}' {VAR:nahtav}>
																</td>
																<td>
																	<select NAME='stiil[{VAR:mis}]' class="formselect">{VAR:stiilid}</select>
																</td>
																<td>
																	<input type=text NAME='tbefore[{VAR:mis}]' class="formtext" value="{VAR:tbefore}" size=8>
																</td>
																<td>
																	<input type=text NAME='tafter[{VAR:mis}]' class="formtext" value="{VAR:tafter}" size=8>
																</td>
															</tr>
															<!-- END SUB: klikitav -->											
														</table>

														</fieldset>
														</td>
													</tr>
													</table>
												</fieldset>
											</td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>
											<fieldset>
												<legend><b>tasandite ja tulpade konf </b></legend>

												lisa tasand nr
													<input type=text NAME='add_level' class="formtext" size=4>

												<table border=1>
												<tr>
													<td>tasandi nr</td>
													<td>tulpasid</td>
													<td>tulpa by jrk</td>
													<td>templiit</td>
													<td>kataloogid sortida</td>
													<td>lingid sortida</td>
													<td>kustuta</td>
												</tr>
												<tr>
													<td>default</td>
													<td>
														<input type=text NAME='default_tulpi' class="formtext" value="{VAR:default_tulpi}" size=4>
													</td>
													<td>
														<input type=checkbox name=jrk_columns_default value=1 {VAR:jrk_columns_default}>
													</td>
													<td>
														<select NAME='default_template' class="formselect">{VAR:default_template}</select>
													</td>
													<td>
														<select NAME='default_sortby_dirs' class="formselect">{VAR:default_sortby_dirs}</select>
													</td>
													<td>
														<select NAME='default_sortby_links' class="formselect">{VAR:default_sortby_links}</select>
													</td>
													<td> </td>
												</tr>
												<!-- SUB: levels -->
												<tr>
													<td> {VAR:tas} </td>
													<td>
														<input type=text NAME='tulpi[{VAR:tas}]' class="formtext" value={VAR:tulpi} size=4>
													</td>
													<td>
														<input type=checkbox name=jrk_columns[{VAR:tas}] value=1 {VAR:jrk_columns}>
													</td>
													<td>
														<select NAME='level_template[{VAR:tas}]' class="formselect">{VAR:level_template}</select>
													</td>
													<td>
														<select NAME='sortby_dirs[{VAR:tas}]' class="formselect">{VAR:sortby_dirs}</select>
													</td>
													<td>
														<select NAME='sortby_links[{VAR:tas}]' class="formselect">{VAR:sortby_links}</select>
													</td>
													<td>
														<input type=checkbox name=kustuta[{VAR:tas}] value=1>
													</td>
											</tr>
											<!-- END SUB: levels -->
											</table>
											</fieldset>
{VAR:stat}
											<fieldset>
												<legend><select class="formselect"><option>1<option>2</select>. tasandi stiil</legend>
												<select><option>mingi stiilivalik
												</select>
												<select><option>mingi stiilivalik2
												</select>

											</fieldset>
											<input type=text name="test[1][2][8]" value="proov">

											{VAR:abix}
											</td>
										</tr>
									</table>
								</td>
							</tr>
										<tr>
										<td class="icontext" align="center" colspan=2>
										{VAR:_toolbar}
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


