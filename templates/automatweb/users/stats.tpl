<table width="100%" cellspacing="0" cellpadding="1">
	<tr>
		<td class="tableborder">
			<!--tabelshadow-->
			<table width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
					<td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
						<!--tabelsisu-->
						<table width="100%" cellspacing="0" cellpadding="0">
							<tr>
								<td class="tableinside" height="29">
									<table border="0" cellpadding="0" cellspacing="0">
										<tr>
											<td width="5"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>

											<td class="celltitle">&nbsp;<b>Kasutajad:&nbsp;</td>
											<td align="left">
												<!-- SUB: ADD -->
												<IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="{VAR:add}" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('new','','{VAR:baseurl}/automatweb/images/blue/awicons/new_over.gif',1)"><img name="new" alt="Add" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/new.gif" width="25" height="25"></a>
												<!-- END SUB: ADD -->

												<!-- SUB: NO_SEARCH -->
												<!--search--><a href="{VAR:search}" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('search','','{VAR:baseurl}/automatweb/images/blue/awicons/search_over.gif',1)"><img name="search" alt="Search" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/search.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT="Search">
												<!-- END SUB: NO_SEARCH -->

												<!-- SUB: IS_SEARCH -->
												<!--search--><a href="{VAR:list}" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('search','','{VAR:baseurl}/automatweb/images/blue/awicons/search_over.gif',1)"><img name="search" alt="List" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/search.gif" width="25" height="25"></a><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT="Search">
												<!-- END SUB: IS_SEARCH -->

												<IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="{VAR:stats}" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('stats','','{VAR:baseurl}/automatweb/images/blue/awicons/show_over.gif',1)"><img name="stats" alt="Statistika" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/show.gif" width="25" height="25"></a>
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
<table border="0" cellspacing="1" cellpadding="2">
<form action='orb.{VAR:ext}' method="GET">
<!-- SUB: USER_ONLY -->
<tr>
	<td colspan="3" class="fgtext">Statistika kasutaja {VAR:s_uid} kohta. | <a href='{VAR:syslog_url}'>Kasutaja log</a></td>
</tr>
<!-- END SUB: USER_ONLY -->
<tr>
	<td class="fgtext">Mille kohta:</td>
	<td colspan="2" class="fgtext"><input type='radio' name='stat_type' value='join' {VAR:join_sel}>Liitumine &nbsp;&nbsp;&nbsp;<input type='radio' name='stat_type' value='login' {VAR:login_sel}>Sisse logimine</td>
</tr>
<tr>
	<td class="fgtext">Ajavahemik:</td>
	<td colspan="2" class="fgtext"><input type='radio' name='stat_span' value='hour' {VAR:hour_sel}>Tund &nbsp;&nbsp;&nbsp;<input type='radio' name='stat_span' value='day' {VAR:day_sel}>P&auml;ev &nbsp;&nbsp;&nbsp;<input type='radio' name='stat_span' value='week' {VAR:week_sel}>N&auml;dalap&auml;ev &nbsp;&nbsp;&nbsp;<input type='radio' name='stat_span' value='month' {VAR:month_sel}>Kuu &nbsp;&nbsp;&nbsp;<input type='radio' name='stat_span' value='year' {VAR:year_sel}>Aasta</td>
</tr>
<tr>
	<td class="fgtext">Alates:</td>
	<td colspan="2" class="fgtext">{VAR:from}</td>
</tr>
<tr>
	<td class="fgtext">Kuni:</td>
	<td colspan="2" class="fgtext">{VAR:to}</td>
</tr>
<tr>
	<td class="fgtext">Graafiku t&uuml;&uuml;p:</td>
	<td colspan="2" class="fgtext"><input type='radio' name='graph_type' value='BarGraph' {VAR:bar_sel}>Tulbad &nbsp;&nbsp;&nbsp;<input type='radio' name='graph_type' value='LineGraph' {VAR:line_sel}>Jooned &nbsp;&nbsp;&nbsp;<input type='radio' name='graph_type' value='PieGraph' {VAR:pie_sel}>Kook</td>
</tr>
<tr>
	<td class="fgtext" colspan="3"><input class="small_button" type='submit' value='N&auml;ita'></td>
</tr>
<!-- SUB: STATS -->
<tr>
	<td class="fgtext">Periood</td>
	<td class="fgtext">Arv</td>
	<td class="fgtext">%</td>
</tr>
<!-- SUB: STAT_LINE -->
<tr>
	<td class="fgtext">{VAR:time}</td>
	<td class="fgtext">{VAR:cnt}</td>
	<td class="fgtext"><img src="{VAR:baseurl}/automatweb/images/bar.gif" height="10" width="{VAR:width}"></td>
</tr>
<!-- END SUB: STAT_LINE -->
<tr>
	<td class="fgtext" colspan="3">Graafik:</td>
</tr>
<tr>
	<td class="fgtext" colspan="3"><img src='{VAR:graph}'></td>
</tr>
<!-- END SUB: STATS -->
{VAR:reforb}
</form>
</table>
