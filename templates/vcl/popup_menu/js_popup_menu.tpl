<span id="menuBar" style="height:15px;text-align: center; background-color: transparent; ">
	
	<a class="menuButton" href="" onclick="return buttonClick(event, '{VAR:menu_id}');" oncontextmenu="return buttonClick(event, '{VAR:menu_id}');" alt="{VAR:alt}" title="{VAR:alt}" id='href_{VAR:menu_id}'>

		<!-- SUB: HAS_ICON -->
		<img alt="{VAR:alt}" title="{VAR:alt}" border="0" src='{VAR:menu_icon}' id='mb_{VAR:menu_id}' >
		<!-- END SUB: HAS_ICON -->

		<!-- SUB: HAS_TEXT -->
		<span id='mb_{VAR:menu_id}' >{VAR:text}&nbsp;<img src="{VAR:baseurl}/automatweb/images/aw06/ikoon_nool_alla.gif" alt="#" width="5" height="3" border="0" style="margin: 0 -3px 1px 0px" /></span>
		<!-- END SUB: HAS_TEXT -->

	</a>
	{VAR:ss}

</span>

<!-- SUB: IS_TOOLBAR -->
<a href="#" onClick="tb_tb_lod{VAR:tb_lod_num}()"><img src="/automatweb/images/icons/downarr.png" border="0"></a></td>
<!-- END SUB: IS_TOOLBAR -->
