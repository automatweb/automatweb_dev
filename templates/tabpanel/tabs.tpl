{VAR:toolbar}

<style type="text/css">
.tabpanelheaderstyle {
	border-top: 1px solid #FFFFFF;
	border-right: 1px solid #BDBDBD;
	padding-top: 6px;
	padding-left: 6px;
	background-color: #E1E1E1;
	background-repeat: repeat-x;
	background-image: url('{VAR:baseurl}/automatweb/images/aw04/tab2_table_back.gif');
	height: 24px;
}
</style>

<!-- SUB: tabs_L1 -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>

<td valign="bottom" class="tabpanelheaderstyle">
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

  </table>
  </td>

<!-- SUB: ADDITIONAL_TEXT -->
  <td valign="right" align="center" class="tabpanelheaderstyle">
<script language="javascript">

function select_this(s){
	var d = s.options[s.selectedIndex].value;
	if (d.indexOf('http') == -1 && d != "_")
	{
		eval(d);
		return true;
	}

	if (d != "_")
	{
		location.href=d;
	}
}
</script>

  <select name="foo" onChange='select_this(this)'>{VAR:adds}</select></span>
  </td>
<!-- END SUB: ADDITIONAL_TEXT -->

<!-- SUB: SHOW_HELP -->
  <td valign="right" align="center" class="tabpanelheaderstyle">

  <span class="aw04tab2content"><a href="javascript:showhide_help();">{VAR:open_help_text}</a></span>
  </td>
  <!-- END SUB: SHOW_HELP -->
  </tr>

</tr>
</table>

<script type="text/javascript">
function showhide_help()
{
	help_layer = document.getElementById('help_layer');
	if (help_layer.style.display == 'none')
	{
		show_help();
	}
	else
	{
		close_help();
	};
}

function show_help()
{
	help_layer = document.getElementById('help_layer');
	help_layer.style.display = 'block';
}

function close_help()
{
	help_layer = document.getElementById('help_layer');
	help_layer.style.display = 'none';
}

function show_property_help(propname)
{
	prophelp_layer = document.getElementById('property_' + propname + '_help');
	if (prophelp_layer)
	{
		helptext_layer = document.getElementById('helptext_layer');
		helptext_layer.innerHTML = prophelp_layer.innerHTML;
		show_help();
	}

}
</script>


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
<div id="help_layer" style="background-color: #F7F7F7; border: 1px solid #91DA52; display: none; padding: 5px;">
<div id="helptext_layer" style="font-family: verdana, sans-serif; font-size: 11px; font-weight: normal; color: #000000; height: 28px; background-color: #F7F7F7; ">
{VAR:help}
</div>
<div style="text-align: right; width: 100%; font-family: verdana, sans-serif; font-size: 11px; font-weight: normal; color: #000000;">
<a href="javascript:void(0);" onclick="window.open('{VAR:translate_url}','awtrans','width=600,height=400');">{VAR:translate_text}</a> |
<a href="javascript:void(0);" onclick="window.open('{VAR:help_url}','awhelp','width=600,height=400,resizable=1,scrollbars=1');">{VAR:more_help_text}</a> | <a href="javascript:close_help();">{VAR:close_help_text}</a>
</div>
</div>

<!-- SUB: tabs_L3 -->
<div class="aw04tab2divl2">

		<table border="0" cellpadding="0" cellspacing="0">
		<tr>
	
			<!-- SUB: tab_L3 -->
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td nowrap class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_back.gif"><a href="{VAR:link}">{VAR:caption}</a></td>
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
			<!-- END SUB: tab_L3 -->

			<!-- SUB: sel_tab_L3 -->
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td nowrap class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_back.gif"><b><a style="color: white;" href="{VAR:link}">{VAR:caption}</a></b></td>
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
			<!-- END SUB: sel_tab_L3 -->

			<!-- SUB: disabled_tab_L3 -->
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td nowrap class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_back.gif">{VAR:caption}</td>
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
			<!-- END SUB: disabled_tab_L3 -->

			</tr>
			</table>
</div>
<!-- END SUB: tabs_L3 -->

<!-- SUB: tabs_L4 -->
<div class="aw04tab2divl2">

		<table border="0" cellpadding="0" cellspacing="0">
		<tr>
	
			<!-- SUB: tab_L4 -->
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td nowrap class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_back.gif"><a href="{VAR:link}">{VAR:caption}</a></td>
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
			<!-- END SUB: tab_L4 -->

			<!-- SUB: sel_tab_L4 -->
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td nowrap class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_back.gif"><b><a style="color: white;" href="{VAR:link}">{VAR:caption}</a></b></td>
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_sel_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
			<!-- END SUB: sel_tab_L4 -->

			<!-- SUB: disabled_tab_L4 -->
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_left.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
		  <td nowrap class="aw04tab2smallcontent" background="{VAR:baseurl}/automatweb/images/aw04/tab2small_back.gif">{VAR:caption}</td>
		  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2small_right.gif" WIDTH="7" HEIGHT="18" BORDER="0" ALT=""></td>
			<!-- END SUB: disabled_tab_L4 -->

			</tr>
			</table>
</div>
<!-- END SUB: tabs_L4 -->

<!-- SUB: left_tabs_L1 -->
<table width="178" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">

  <!-- SUB: left_tab_L1 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;"><a href="{VAR:link}">{VAR:caption}</a></td>

      </tr>

  <!-- END SUB: left_tab_L1 -->

  <!-- SUB: left_disabled_tab_L1 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;">{VAR:caption}</td>

      </tr>
  <!-- END SUB: left_disabled_tab_L1 -->

  <!-- SUB: left_sel_tab_L1 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;"><a href="{VAR:link}"><strong>{VAR:caption}</strong></a></td>

      </tr>
  <!-- END SUB: left_sel_tab_L1 -->

</table>
<!-- END SUB: left_tabs_L1 -->

<!-- SUB: left_tabs_L2 -->
<table width="178" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">

  <!-- SUB: left_tab_L2 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;"><a href="{VAR:link}">{VAR:caption}</a></td>

      </tr>

  <!-- END SUB: left_tab_L2 -->

  <!-- SUB: left_disabled_tab_L2 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;">{VAR:caption}</td>

      </tr>
  <!-- END SUB: left_disabled_tab_L2 -->

  <!-- SUB: left_sel_tab_L2 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;"><a href="{VAR:link}"><strong>{VAR:caption}</strong></a></td>

      </tr>
  <!-- END SUB: left_sel_tab_L2 -->

</table>
<!-- END SUB: left_tabs_L2 -->

<!-- SUB: left_tabs_L3 -->
<table width="178" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">

  <!-- SUB: left_tab_L3 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;"><a href="{VAR:link}">{VAR:caption}</a></td>

      </tr>

  <!-- END SUB: left_tab_L3 -->

  <!-- SUB: left_disabled_tab_L3 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;">{VAR:caption}</td>

      </tr>
  <!-- END SUB: left_disabled_tab_L3 -->

  <!-- SUB: left_sel_tab_L3 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;"><a href="{VAR:link}"><strong>{VAR:caption}</strong></a></td>

      </tr>
  <!-- END SUB: left_sel_tab_L3 -->

</table>
<!-- END SUB: left_tabs_L3 -->

<!-- SUB: left_tabs_L4 -->
<table width="178" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">

  <!-- SUB: left_tab_L4 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;"><a href="{VAR:link}">{VAR:caption}</a></td>

      </tr>

  <!-- END SUB: left_tab_L4 -->

  <!-- SUB: left_disabled_tab_L4 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;">{VAR:caption}</td>

      </tr>
  <!-- END SUB: left_disabled_tab_L4 -->

  <!-- SUB: left_sel_tab_L4 -->
    <tr>
      <td width="22" height="16" align="right"><img src="{VAR:baseurl}/img/link_arrow.gif" width="7" height="4"></td>
      <td colspan="2" class="link11px" style="padding-left:7px;"><a href="{VAR:link}"><strong>{VAR:caption}</strong></a></td>

      </tr>
  <!-- END SUB: left_sel_tab_L4 -->

</table>
<!-- END SUB: left_tabs_L4 -->


<div class="aw04content" style="background-image: url('{VAR:baseurl}/automatweb/images/aw04/content_back2.gif')">
{VAR:content}
</div>



{VAR:toolbar2}


<!-- content ends  -->


