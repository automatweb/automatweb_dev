

<!-- begin header menu -->
<table width="660" border="0" cellpadding="5" cellspacing="0">
<tr>
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
  <td><a href="{VAR:baseurl}"><IMG SRC="{VAR:baseurl}/img/automatweb.gif" BORDER="0" ALT=""></a></td>
  <td align="right">


<!-- SUB SEARCH_SEL -->
<table border="0" cellspacing="5" cellpadding="0">
<form method="get" action="{VAR:baseurl}/index.{VAR:ext}" name="search">
<tr>
<td><select name="parent" class="formselect">{VAR:search_sel}</select></td>
<td><input type="text" size="15" class="formtext" name="str"></td>
<td>
<!--[<a href="#" onClick="document.search.submit()"><b>OTSI</b></a>]-->

<input type="submit" value=" {VAR:LC_SEARCH_BTN} " class="formbutton"></td>
<input type="hidden" name="class" value="document">
<input type="hidden" name="action" value="search">
<input type="hidden" name="section" value="{VAR:section}">
</tr>
</form>
</table>
<!-- END SUB SEARCH_SEL -->

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

<IMG SRC="{VAR:baseurl}/img/trans.gif" WIDTH="1" HEIGHT="7" BORDER="0" ALT=""><br>

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
<IMG SRC="{VAR:baseurl}/img/trans.gif" WIDTH="1" HEIGHT="7" BORDER="0" ALT=""><br>



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
					<td><input type="text" name="uid" size="8" class="formtext"></td>
				</tr>
				<tr>
					<td class="textsmall" align="right">Password:</td>
					<td><input type="password" name="password" size="8" class="formtext"></td>
				</tr>
				<tr>
				    <td></td>
					<td><input type="submit" value="Log In" class="formbutton"></td>
				</tr>
			</table>
			<input type="hidden" name="class" value="users">
			<input type="hidden" name="action" value="login">
		</form>

	</td></tr>
	<!-- end box_content -->

</table>
<IMG SRC="{VAR:baseurl}/img/trans.gif" WIDTH="1" HEIGHT="7" BORDER="0" ALT=""><br>
<!-- END SUB: login -->



<!-- SUB: logged -->
<table width="155" border="0" cellpadding="5" cellspacing="0">

	<!-- box_title -->
	<tr><td class="boxtitle">{VAR:uid} / {VAR:date}</td></tr>
	<!-- end_box_title -->

	<!-- box_content -->
	<tr><td class="boxcontent">
	
		<!-- SUB: MENU_LOGIN_L1_ITEM -->		
		&gt; <a href="{VAR:link}" {VAR:target}>{VAR:text}</a><br>
		<!-- END SUB: MENU_LOGIN_L1_ITEM -->


	</td></tr>
	<!-- end box_content -->

</table>
<IMG SRC="{VAR:baseurl}/img/trans.gif" WIDTH="1" HEIGHT="7" BORDER="0" ALT=""><br>
<!-- END SUB: logged -->





  </td>
  <td width="10" valign="top"><IMG SRC="{VAR:baseurl}/img/trans.gif" WIDTH="10" HEIGHT="1" BORDER="0" ALT=""></td>
  <!-- END SUB: LEFT_PANE -->


  <!-- center pane -->
  <td width="100%" valign="top">


  {VAR:doc_content}





  </td>
  <!-- end center pane -->




  <!-- SUB: RIGHT_PANE -->
     <td width="10" valign="top"><IMG SRC="{VAR:baseurl}/img/trans.gif" WIDTH="10" HEIGHT="1" BORDER="0" ALT=""></td>
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
<IMG SRC="{VAR:baseurl}/img/trans.gif" WIDTH="1" HEIGHT="7" BORDER="0" ALT=""><br>
<!-- END SUB: RIGHT_PROMO -->















  </td>
  <!-- END SUB: RIGHT_PANE -->
</tr>
</table>



<hr noshade size="1" width="660">

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


