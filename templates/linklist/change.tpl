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
											<input type=checkbox {VAR:YAH} NAME="YAH" value='1' class="formcheckbox">

											(kuda ja kas üldse saab konstuida Yah riba kui kataloogid leitakse formsisestusest?...cookie?)
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
														<td class="celltext" valign=top>
														<fieldset>
															<legend><b>lingi attribuudid</b></legend>
															<input type=checkbox {VAR:active_links} NAME='active_links' value='1' class="formcheckbox">
														kuvada ainult aktiivsed lingid<br />
<!--															<input type=checkbox {VAR:show_links} NAME="show_links" value='1' class="formcheckbox">
														linke näidatakse <br />-->
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
													<td class="celltext">tasandi nr</td>
													<td class="celltext">tulpasid</td>
													<td class="celltext">tulpa by jrk</td>
													<td class="celltext">templiit</td>
													<td class="celltext">kataloogid sortida</td>
													<td class="celltext">näita linke</td>
													<td class="celltext">uues aknas</td>
													<td class="celltext">lingid sortida</td>
													<td class="celltext">kustuta tase</td>
													<td class="celltext">stiil</td>
												</tr>
												<!-- SUB: levels -->
												<tr>
													<td> {VAR:level} </td>
													<td>
														<input type=text NAME='dir[{VAR:level}][tulpi]' class="formtext" value='{VAR:tulpi}' size=4>
													</td>
													<td>
														<input type=checkbox name='dir[{VAR:level}][jrk_columns]' value=1 {VAR:jrk_columns}>
													</td>
													<td>
														<select NAME='dir[{VAR:level}][level_template]' class="formselect">{VAR:level_template}</select>
													</td>
													<td>
														<select NAME='dir[{VAR:level}][sortby_dirs]' class="formselect">{VAR:sortby_dirs}</select>
													</td>
													<td>
														<input type=checkbox NAME="dir[{VAR:level}][show_links]" value='1' class="formcheckbox" {VAR:show_links}>
													</td>
													<td>
														<input type=checkbox NAME="dir[{VAR:level}][newwindow]" value='1' class="formcheckbox" {VAR:newwindow}>
													</td>
													<td>
														<select NAME='dir[{VAR:level}][sortby_links]' class="formselect">{VAR:sortby_links}</select>
													</td>
													<td>
														<input type=checkbox name='dir[{VAR:level}][kustuta]' value=1>
													</td>
													<td>
														<a href=#l{VAR:level}>stiil</a>
													</td>
											</tr>
											<!-- END SUB: levels -->
											</table>
											</fieldset>
											<!-- SUB: level_styles -->
											<fieldset>
												<legend><b>{VAR:level} tasandi linkide stiil ja konf</b></legend>
														<a name="l{VAR:level}">
														<table border=1>
															<tr>
																<td class="celltext">attribuut</td>
																<td class="celltext">järjekord</td>
																<td class="celltext">nähtav</td>
																<td class="celltext">hüperlink</td>
																<td class="celltext">vali stiil</td>
																<td class="celltext">tekst + linebreak</td>
															</tr>

															<!-- SUB: level_style -->
															<tr>
																<td class="celltext">{VAR:mis}</td>
																<td>
																	<input type=text NAME='link[{VAR:level}][{VAR:mis}][jrk]' class="formtext" value="{VAR:jrk}" size=4>
																</td>
																<td>
																	<input type=checkbox name='link[{VAR:level}][{VAR:mis}][show]' value=1 {VAR:show}>
																</td>
																<td>
																	<input type=checkbox name='link[{VAR:level}][{VAR:mis}][hyper]' value=1 {VAR:hyper}>
																</td>
																<td>
																	<select NAME='link[{VAR:level}][{VAR:mis}][style]' class="formselect">{VAR:stiilid}</select>
																</td>
																<td>
<!-- SUB: add_text -->
																	<input type=text NAME='link[{VAR:level}][{VAR:mis}][text]' class="formtext" value="{VAR:text}" size=14>
<!-- END SUB: add_text -->
																	<input type=checkbox name='link[{VAR:level}][{VAR:mis}][br]' value=1 {VAR:br}>
																</td>
															</tr>
															<!-- END SUB: level_style -->
														</table>
											</fieldset>
											<!-- END SUB: level_styles -->

<!--														<input type=text name='level' value="{VAR:level}">-->
											{VAR:abix} miks krt see asi siin sellist värvi on, aru ma ei saa
											</td>
										</tr>
									</table>
								</td>
							</tr>
										<tr>
										<td class="icontext" align="center" colspan=2>
										{VAR:toolbar}
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


