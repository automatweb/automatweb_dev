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
												<table border="0" cellpadding="0" cellspacing="0">
													<tr>
														<td class="icontext" align="center"><input type='image' src="{VAR:baseurl}/automatweb/images/blue/big_save.gif" width="32" height="32" border="0" VALUE='submit' CLASS="small_button"><br>
														<a href="javascript:document.add.submit()">Salvesta</a></td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
									<br>
									<table class="aste01" cellpadding=3 cellspacing=1 border=1>
										<tr>
											<td class="celltext" width=25%>Lingikogu nimi:</td><td class="celltext"><input type='text' NAME='name' VALUE='{VAR:name}' class="formtext"></td>
										</tr>
										<tr>
											<td class="celltext" colspan=2>juurmenüü (lingikogu asukoht)<br /><select NAME='lingiroot' class="formselect">{VAR:rootitems}
											</select></td>
										<tr>
											<td class="celltext">Lingikogu templiit</td>
											<td class="celltext">
											<select NAME='tpls' class="formselect">{VAR:tpls}
											</select></td>
										</tr>
										<tr>
											<td class="celltext">lingiks on vormisisestus</td>
											<td class="celltext">
											<input type=checkbox {VAR:vormisisestus} NAME='vormisisestus' value='1' class="formcheckbox">
											</td>
										</tr>
<!-- SUB: if_formisisestus -->
										<tr>
											<td class="celltext">vali form</td>
											<td class="celltext">
											<select NAME='forms' class="formselect">{VAR:forms}
											</select>
											</td>
										</tr>
										<tr>
											<td class="celltext">vali formisisestus</td>
											<td class="celltext">
											<select NAME='felement' class="formselect">{VAR:felement}
											</select> 
											== 
											<select NAME='vordle' class="formselect">{VAR:vordle}
											</select> <br />

											<select NAME='vstiil' class="formselect">
											<option>väljundi stiilide valiku panen siia varsti
											</select>

											</td>
										</tr>
<!-- END SUB: if_formisisestus -->
										<tr>
											<td class="celltext">lingid avanevad uues aknas</td>
											<td class="celltext">
											<input type=checkbox {VAR:newwindow} NAME="newwindow" value='1' class="formcheckbox">
											</td>
										</tr>
										<tr>
											<td class="celltext">näidata pathi</td>
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
											<td class="celltext">kuvada ainult aktiivsed lingid</td>
											<td class="celltext">
											<input type=checkbox {VAR:active_links} NAME='active_links' value='1' class="formcheckbox">
											</td>
										</tr>
										<tr>
											<td class="celltext">lingi attribuudid</td>
											<td class="celltext">
												<table border=1>
													<tr>
														<td> attribuut</td><td> hüperlink</td><td>nähtav</td>
													</tr>
													<!-- SUB: klikitav -->
													<tr>
														<td>{VAR:mis}</td>
														<td><input type=checkbox name=klikitav[{VAR:mis}] value={VAR:mis} {VAR:kas_kliki}></td>
														<td><input type=checkbox name=naidata[{VAR:mis}] value={VAR:mis} {VAR:kas_naita}></td>
													<tr>
													<!-- END SUB: klikitav -->											
												</table>
											</td>
										</tr>
										<tr>
											<td class="celltext">vaikimisi kataloogid sorteerida </td>
											<td class="celltext">
											<select NAME='default_sortby_dirs' class="formselect">{VAR:default_sortby_dirs}
											</select> järgi</td>
										</tr>
										<tr>
											<td class="celltext">vaikimisi lingid sorteerida </td>
											<td class="celltext">
											<select NAME='default_sortby_links' class="formselect">{VAR:default_sortby_links}
											</select> järgi</td>
										</tr>
										<tr>
											<td class="celltext">vaikimisi on tasandis tulpasid</td>
											<td class="celltext">
											<select NAME='default_tulpi' class="formselect">{VAR:default_tulpi}
											</select>
											</td>
										</tr>
										<tr>
											<td class="celltext" valign=top>tasandite ja tulpade konf</td>
											<td class="celltext">
											<select NAME='tasand' class="formselect">{VAR:tasandid}
											</select> | 
											<select NAME='tegevus' class="formselect">
											<option>vali tegevus
											<option value=lisa>lisa
											<option value=kustuta>kustuta
											</select><br />
											<table border=1>
												<tr>
												<td>tasand</td><td> tulpasid</td><td>kataloogid sorteeritakse</td><td>lingid sorteeritakse</td>
												</tr>
											<!-- SUB: tasandids -->
											<tr><td>tasand nr. {VAR:tas}</td>
											<td>
											<select NAME='tulpi[{VAR:tas}]' class="formselect">{VAR:tulpi}</select></td>
											<td>
											<select NAME='sortby_dirs[{VAR:tas}]' class="formselect">{VAR:sortby_dirs}</select></td><td>
											<select NAME='sortby_links[{VAR:tas}]' class="formselect">{VAR:sortby_links}</select></td>
											</tr>
											<!-- END SUB: tasandids -->
											</table>
											<br /><br /> {VAR:abix}
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


