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

											<td class="cellcelltext">&nbsp;<b>Kasutajad:&nbsp;</td>
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
<!-- SUB: IS_SEARCH2 -->
<table border="0" cellspacing="1" cellpadding="2">
<form action='orb.{VAR:ext}' method="GET">
<tr>
	<td class="celltext">UID:</td>
	<td class="celltext"><input type='text' name='s_uid' VALUE='{VAR:s_uid}' class="small_button"></td>
</tr>
<tr>
	<td class="celltext">E-mail:</td>
	<td class="celltext"><input type='text' name='s_email' VALUE='{VAR:s_email}' class="small_button"></td>
</tr>
<tr>
	<td class="celltext">Loodud:</td>
	<td class="celltext">{VAR:created_from} - {VAR:created_to}</td>
</tr>
<tr>
	<td class="celltext" colspan="2"><input class="small_button" type='submit' value='Otsi'></td>
</tr>
{VAR:reforb}
</form>
</table>
<!-- END SUB: IS_SEARCH2 -->

<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#FFFFFF">



<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr class="aste05">


<td align="left" class="celltext">
<!-- SUB: LETTER -->
<a href='{VAR:l_url}'>{VAR:letter}</a>&nbsp;&nbsp;
<!-- END SUB: LETTER -->

<!-- SUB: SEL_LETTER -->
{VAR:letter}&nbsp;&nbsp;
<!-- END SUB: SEL_LETTER -->

<a href='{VAR:all_url}'>K&otilde;ik</a>
</td>
</tr>
</table>
</td>
</tr>
</table>

<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#FFFFFF">



<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr class="aste05">
<td align="left" class="celltext" colspan="10">&nbsp;
{VAR:LC_PAGE}: 
<!-- SUB: SEL_PAGE -->
{VAR:from} - {VAR:to} |
<!-- END SUB: SEL_PAGE -->

<!-- SUB: PAGE -->
<a href='{VAR:link}'>{VAR:from} - {VAR:to}</a> |
<!-- END SUB: PAGE -->
&nbsp;</td>
</tr>

<tr class="aste05">
<td align="center" class="celltext">&nbsp;UID&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_LOGS}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_ONLINE}&nbsp;</td>
<td align="center" class="celltext">&nbsp;{VAR:LC_LAST_LOGIN}&nbsp;</td>
<td align="center" colspan="6" class="celltext">{VAR:LC_ACTIONS}</td>
</tr>
<!-- SUB: LINE -->
<tr class="aste07">
<td align="center" class="celltext">&nbsp;{VAR:uid}&nbsp;</td>
<td class="celltext">&nbsp;{VAR:logs}&nbsp;</td>
<td class="celltext">&nbsp;{VAR:online}&nbsp;</td>
<td class="celltext">&nbsp;{VAR:last}&nbsp;</td>
<td class="celltext">&nbsp;
<!-- SUB: CAN_CHANGE -->
<a href='{VAR:change}'>{VAR:LC_CHANGE}</a>
<!-- END SUB: CAN_CHANGE -->
</td>
<td class="celltext">&nbsp;
<a href='{VAR:settings}'>{VAR:LC_PROPERTIES}</a>
</td>
<td class="celltext">&nbsp;
<!-- SUB: CAN_PWD -->
<a href='{VAR:change_pwd}'>{VAR:LC_CHANGE_PWD}</a>
<!-- END SUB: CAN_PWD -->
</td>
<td class="celltext">&nbsp;
<!-- SUB: CAN_DEL -->
<a href='{VAR:delete}'>{VAR:LC_DELETE}</a>
<!-- END SUB: CAN_DEL -->
</td>
<td class="celltext">&nbsp;
<a href='{VAR:log}'>Log</a>
</td>
<td class="celltext">&nbsp;
<a href='{VAR:acl}'>ACL</a>
</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
</tr>
</table>
