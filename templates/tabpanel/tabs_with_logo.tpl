{VAR:toolbar}


<style type="text/css">
.tabpanelheaderstyle {
	border-top: 1px solid #FFFFFF;
	border-right: 1px solid #BDBDBD;
	padding-top: 6px;
	padding-left: 6px;
	background-color: #E1E1E1;
	background-repeat: no-repeat;
	background-image: url('http://klient.struktuur.ee/personal/www/01/img/banner_bg_2.jpg');
	height: 68px;
}
</style>

<!-- SUB: tabs_L1 -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>

<td valign="bottom" class="tabpanelheaderstyle">

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td valign="bottom">

<table border="0" cellpadding="0" cellspacing="0">
<tr>


  <!-- SUB: tab_L1 -->
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_left.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2content" background="{VAR:baseurl}/automatweb/images/aw04/tab2_back.gif"><a href="{VAR:link}">{VAR:caption}</a></td>
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_right.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <!-- END SUB: tab_L1 -->

  <!-- SUB: disabled_tab_L1 -->
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_left.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2discontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2_back.gif">{VAR:caption}</td>
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_right.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <!-- END SUB: disabled_tab_L1 -->

  <!-- SUB: sel_tab_L1 -->
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_sel2_left.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2selcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2_sel2_back.gif"><a href="{VAR:link}">{VAR:caption}</a></td>
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_sel2_right.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <!-- END SUB: sel_tab_L1 -->

  </tr>
  </table>

</td>
<td><img src="http://klient.struktuur.ee/personal/www/01/img/logo.gif"></td>
</tr>
</table>
</td>
</tr>
</table>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td class="aw04tab2divvahe"><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/trans.gif" WIDTH="1" HEIGHT="8" BORDER="0" ALT=""></td></tr></table>

<!-- END SUB: tabs_L1 -->





<!-- SUB: tabs_L2 -->
<div class="aw04tab2divl2">

		<table border="0" cellpadding="0" cellspacing="0">
		<tr>
	
			<!-- SUB: tab_L2 -->
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td nowrap class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_back.gif"><a href="{VAR:link}">{VAR:caption}</a></td>
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
			<!-- END SUB: tab_L2 -->

			<!-- SUB: sel_tab_L2 -->
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td nowrap class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_back.gif"><b><a style="color: white;" href="{VAR:link}">{VAR:caption}</a></b></td>
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
			<!-- END SUB: sel_tab_L2 -->

			<!-- SUB: disabled_tab_L2 -->
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td nowrap class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_back.gif">{VAR:caption}</td>
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
			<!-- END SUB: disabled_tab_L2 -->

			</tr>
			</table>


		

		
			
		
</div>
<!-- END SUB: tabs_L2 -->

<!-- SUB: left_tabs_L1 -->
<table border="1">

  <!-- SUB: left_tab_L1 -->
  <tr>
  <td>A: <a href="{VAR:link}">{VAR:caption}</a></td>
  </tr>
  <!-- END SUB: left_tab_L1 -->


  <!-- SUB: left_disabled_tab_L1 -->
  <tr>
  <td>B: <a href="{VAR:link}">{VAR:caption}</a></td>
  </tr>
  <!-- END SUB: left_disabled_tab_L1 -->

  <!-- SUB: left_sel_tab_L1 -->
  <tr>
  <td>C: <a href="{VAR:link}">{VAR:caption}</a></td>
  </tr>
  <!-- END SUB: left_sel_tab_L1 -->

</table>
<!-- END SUB: left_tabs_L1 -->



<div class="aw04content" style="background-image: url('{VAR:baseurl}/automatweb/images/aw04/content_back2.gif')">
{VAR:content}
</div>



{VAR:toolbar2}


<!-- content ends  -->


