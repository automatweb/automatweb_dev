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
											<td class="celltext" colspan=2>
											kataloogi link <br />
											<input type='radio' name='is_formentry' value='1' {VAR:is_formentry}> on formisisestus, näidatakse vormi väljastust</br>
											<input type='radio' name='is_formentry' value='0' {VAR:is_not_formentry}> on tavaline link alamenüüle ja näidatakse kataloogis olevaid linke
											</td>
										</tr>
<!-- SUB: vormisisestus -->
										<tr>
											<td class="celltext">vali form</td>
											<td class="celltext">
											<select NAME='forms' class="formselect">{VAR:forms}
											</select>
											</td>
										</tr>
										<tr>
											<td class="celltext">vali vormisisestus</td>
											<td class="celltext">
											otsinguvormi element
											<select NAME='felement' class="formselect">{VAR:felement}
											</select> 
											 täidetakse katloogi
											<select NAME='vordle' class="formselect">{VAR:vordle}
											</select>-ga <br />

											<select NAME='vstiil' class="formselect">
											<option>väljundi stiilide valik?
											</select>

											</td>
										</tr>
<!-- END SUB: vormisisestus -->
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
<!-- SU_B: lingid-->
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
														<td>attribuut</td><td>hüperlink</td>
													</tr>
													<!-- SUB: klikitav -->
													<tr>
														<td>{VAR:mis}</td>
														<td><input type=checkbox name=klikitav[{VAR:mis}] value={VAR:mis} {VAR:is_hyper}></td>
													<tr>
													<!-- END SUB: klikitav -->											
												</table>
											</td>
										</tr>
<!-- E_ND SU_B: lingid-->
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
											<input type=text NAME='default_tulpi' class="textselect" value="{VAR:default_tulpi}" size=4>
											</td>
										</tr>
										<tr>
											<td class="celltext" valign=top>tasandite ja tulpade konf</td>
											<td class="celltext">
											lisa tasand nr
											<input type=text NAME='add_level' class="formtext" size=4>
											<table border=1>
												<tr>
												<td>tasandi nr</td><td> tulpasid</td><td>kataloogid sorteeritakse</td><td>lingid sorteeritakse</td><td>kustuta</td>
												</tr>
											<!-- SUB: levels -->
												<tr>
													<td> {VAR:tas} </td>
													<td>
														<!--<select NAME='tulpi[{VAR:tas}]' class="formselect">{VAR:tulpi}</select>-->
														<input type=text NAME='tulpi[{VAR:tas}]' class="formtext" value={VAR:tulpi} size=4>
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
											{VAR:abix}
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


