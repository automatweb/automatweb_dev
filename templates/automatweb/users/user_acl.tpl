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
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<form action="reforb.{VAR:ext}" method="POST">
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td align="left" class="title" colspan="100">&nbsp;
Page: 
<!-- SUB: SEL_PAGE -->
{VAR:from} - {VAR:to} |
<!-- END SUB: SEL_PAGE -->

<!-- SUB: PAGE -->
<a href='{VAR:link}'>{VAR:from} - {VAR:to}</a> |
<!-- END SUB: PAGE -->
&nbsp;</td>
</tr>
<tr>
<td align="left" class="title">&nbsp;Objekt</td>
<!-- SUB: ACL_TITLE -->
<td align="left" class="title">&nbsp;{VAR:acl_name}</td>
<!-- END SUB: ACL_TITLE -->
</tr>
<!-- SUB: ACL_LINE -->
<tr>
	<td class="fgtext">{VAR:o_name}</td>
	<!-- SUB: ACL_CELL -->
	<td align="center" class="fgtext"><input type="checkbox" name="acls[{VAR:oid}][{VAR:acl_name}]" value="1" {VAR:checked}><input type="hidden" name="old_acls[{VAR:oid}][{VAR:acl_name}]" value="{VAR:acl_value}"></td>
	<!-- END SUB: ACL_CELL -->
</tr>
<!-- END SUB: ACL_LINE -->
<tr>
	<td colspan="50" class="fgtext"><input type="submit" value="Salvesta"></td>
</tr>
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>