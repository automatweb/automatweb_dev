

<!-- begin header menu -->
<table width="660" border="0" cellpadding="5" cellspacing="0">
<tr>
	<td class="menuheader">
		<!-- SUB SEARCH_SEL -->
		<table border="0" cellspacing="0" cellpadding="0">
		<form method="get" action="{VAR:baseurl}/index.{VAR:ext}" name="search">
			<tr>
			<td>
				<input type="text" size="17" class="formtext" name="str" value="  Otsing" onFocus="if(this.value=='  Otsing')this.value = ''" onblur="if(this.value=='')this.value='  Otsing';"></td>
				<td><input type="image" value="submit" src="{VAR:baseurl}/img/icon_search.gif" WIDTH="26" HEIGHT="20" BORDER="0" title="Otsi" ALT="Otsi"></td>
			</tr>
			<input type="hidden" name="class" value="site_search_content">
			<input type="hidden" name="action" value="do_search">
			<input type="hidden" name="id" value="41">
		</form>
		</table>
<!-- END SUB SEARCH_SEL -->
	</td>
  <td class="menuheader">


		<!-- SUB: MENU_YLEMINE_L1_ITEM_BEGIN -->
		<a href="{VAR:link}" {VAR:target}>{VAR:text}</a> 
		<!-- END SUB: MENU_YLEMINE_L1_ITEM_BEGIN -->

		<!-- SUB: MENU_YLEMINE_L1_ITEM -->
		| <a href="{VAR:link}" {VAR:target}>{VAR:text}</a> 
		<!-- END SUB: MENU_YLEMINE_L1_ITEM -->



  </td>
</tr>
</table>
<!-- end header menu -->


<!-- begin logo and search -->
<table width="634" border="0" cellpadding="0" cellspacing="0">
<tr>
  <td><a href="{VAR:baseurl}"><img src="{VAR:baseurl}/img/automatweb.gif" border="0" alt="" /></a></td>
  <td align="right">



  </td>
</tr>
</table>
<!-- end logo and search -->

<!-- begin YAH -->
<table width="660" border="0" cellpadding="0" cellspacing="0">
<tr>
  <td class="yah">You Are Here: <a href="{VAR:baseurl}">Frontpage</a>

<!-- SUB: YAH_LINK -->
/ <a href="{VAR:link}">{VAR:text}</a>
<!-- END SUB: YAH_LINK -->

  </td>
</tr>
</table>
<!-- end YAH -->

<img src="{VAR:baseurl}/img/trans.gif" width="1" height="7" border="0" alt="" /><br />

<table width="660" border="0" cellpadding="0" cellspacing="0">
<tr>
  <!-- SUB: LEFT_PANE -->
  <td width="155" valign="top">



<table width="155" border="0" cellpadding="2" cellspacing="0">

<!-- SUB: MENU_VASAK_L1_ITEM -->
<tr><td colspan="2" class="menuleftl1" width="155"><a href="{VAR:link}" {VAR:target}>{VAR:text}</a></td></tr>
<!-- END SUB: MENU_VASAK_L1_ITEM -->

<!-- SUB: MENU_VASAK_L1_ITEM_SEL -->
<tr><td colspan="2" class="menuleftl1sel" width="155"><a href="{VAR:link}" {VAR:target}>{VAR:text}</a></td></tr>

			<!-- SUB: MENU_VASAK_L2_ITEM -->
			<tr>
			<td class="menuleftl2" width="10">»</td>
			<td class="menuleftl2" width="145"><a href="{VAR:link}" {VAR:target}>{VAR:text}</a></td>
			</tr>
			<!-- END SUB: MENU_VASAK_L2_ITEM -->


			<!-- SUB: MENU_VASAK_L2_ITEM_SEL -->
			<tr>
			<td class="menuleftl2sel" width="10">»</td>
			<td class="menuleftl2sel" width="145"><a href="{VAR:link}" {VAR:target}>{VAR:text}</a></td>
			</tr>
				<!-- SUB: MENU_VASAK_L3_ITEM -->
				<tr>
				<td class="menuleftl3" width="10"></td>
				<td class="menuleftl3" width="145">&gt;&nbsp;<a href="{VAR:link}" {VAR:target}>{VAR:text}</a></td>
				</tr>
				<!-- END SUB: MENU_VASAK_L3_ITEM -->

				<!-- SUB: MENU_VASAK_L3_ITEM_SEL -->
				<tr>
				<td class="menuleftl3sel" width="10"></td>
				<td class="menuleftl3sel" width="145">&gt;&nbsp;<a href="{VAR:link}" {VAR:target}>{VAR:text}</a></td>
				</tr>
				<!-- END SUB: MENU_VASAK_L3_ITEM_SEL -->

			<!-- END SUB: MENU_VASAK_L2_ITEM_SEL -->

<!-- END SUB: MENU_VASAK_L1_ITEM_SEL -->

</table>
<img src="{VAR:baseurl}/img/trans.gif" width="1" height="7" border="0" alt="" /><br />



<!-- SUB: login -->
<table width="155" border="0" cellpadding="5" cellspacing="0">

	<!-- box_title -->
	<tr><td class="boxtitle">Log In</td></tr>
	<!-- end_box_title -->

	<!-- box_content -->
	<tr><td class="boxcontent">
	
<form action="reforb.{VAR:ext}" method="POST">
			<table>
				<tr>
					<td class="textsmall" align="right">Username:</td>
					<td><input type="text" name="uid" size="8" class="formtext" /></td>
				</tr>
				<tr>
					<td class="textsmall" align="right">Password:</td>
					<td><input type="password" name="password" size="8" class="formtext" /></td>
				</tr>
				<tr>
				    <td></td>
					<td><input type="submit" value="Log In" class="formbutton" /></td>
				</tr>
			</table>
			<input type="hidden" name="class" value="users" />
			<input type="hidden" name="action" value="login" />
		</form>

	</td></tr>
	<!-- end box_content -->

</table>
<img src="{VAR:baseurl}/img/trans.gif" width="1" height="7" border="0" alt="" /><br />
<!-- END SUB: login -->



<!-- SUB: logged -->
	
	<table width="155" align="center" border="0" cellpadding="0" cellspacing="0" background="{VAR:baseurl}/img/trans.gif">
	
	<tr><td class="boxtitle"><b>{VAR:uid}</b> ({VAR:date})</td></tr>

	<tr><td>
	
	<table border="0" cellpadding="0" cellspacing="2" width="100%" class="boxcontent">

<!-- SUB: MENU_LOGGED_L1_ITEM -->
	<tr><td colspan="2" class="boxcontent" valign="top">{VAR:text}</td></tr>



	<!-- SUB: MENU_LOGGED_L2_ITEM -->
	<tr><td class="boxcontent" align="right" valign="top"><font color="#D00000"><b>&#183;</b></font>&nbsp;</td>
	<td class="boxcontent" valign="top"><a
	href="{VAR:link}" {VAR:target}>{VAR:text}</a></td></tr>
	<!-- END SUB: MENU_LOGGED_L2_ITEM -->

	<!-- SUB: MENU_LOGGED_L2_ITEM_SEL -->
	<tr><td class="boxcontent" align="right" valign="top"><font color="#D00000"><b>&#183;</b></font>&nbsp;</td>
	<td class="boxcontent" valign="top"><b>{VAR:text}</b></td></tr>
	<!-- END SUB: MENU_LOGGED_L2_ITEM_SEL -->

	<!-- END SUB: MENU_LOGGED_L1_ITEM -->

	</tr></table>


	</td></tr>
	</table>


<!-- END SUB: logged -->





  </td>
  <td width="10" valign="top"><img src="{VAR:baseurl}/img/trans.gif" width="10" height="1" border="0" alt="" /></td>
  <!-- END SUB: LEFT_PANE -->


  <!-- center pane -->
  <td width="100%" valign="top">


  {VAR:doc_content}





  </td>
  <!-- end center pane -->




  <!-- SUB: RIGHT_PANE -->
     <td width="10" valign="top"><img src="{VAR:baseurl}/img/trans.gif" width="10" height="1" border="0" alt="" /></td>
  <td width="155" valign="top">






<!-- SUB: RIGHT_PROMO -->
<table width="155" border="0" cellpadding="5" cellspacing="0">

	<!-- SUB: SHOW_TITLE -->
	<tr><td class="boxtitle">{VAR:title}</td></tr>
	<!-- END SUB: SHOW_TITLE -->

	<tr><td class="boxcontent">
		{VAR:content}
	</td></tr>
</table>
<img src="{VAR:baseurl}/img/trans.gif" width="1" height="7" border="0" alt="" /><br />
<!-- END SUB: RIGHT_PROMO -->















  </td>
  <!-- END SUB: RIGHT_PANE -->
</tr>
</table>



<hr noshade size="1" width="660" />

<table width="634" border="0" cellpadding="0" cellspacing="0">
<tr>
  <td class="textsmall">Your Footer Information Here</td>
  <td class="menufooter">

		<!-- SUB: MENU_ALUMINE_L1_ITEM_BEGIN -->
		<a href="{VAR:link}" {VAR:target}>{VAR:text}</a> 
		<!-- END SUB: MENU_ALUMINE_L1_ITEM_BEGIN -->

		<!-- SUB: MENU_ALUMINE_L1_ITEM -->
		| <a href="{VAR:link}" {VAR:target}>{VAR:text}</a> 
		<!-- END SUB: MENU_ALUMINE_L1_ITEM -->

  </td>
</tr>
</table>


