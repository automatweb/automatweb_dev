	<!-- SUB: tabs_L1 -->
	<div class="tabs">
		<!-- SUB: tab_L1 -->
		<div class="norm">
			<div class="right">
				<a {VAR:target} href="{VAR:link}">{VAR:caption}</a>
			</div>
		</div>
		<!-- END SUB: tab_L1 -->
		<!-- SUB: disabled_tab_L1 -->
		<!-- END SUB: disabled_tab_L1 -->
		<!-- SUB: sel_tab_L1 -->
		<div class="akt">
			<div class="right">
				<a {VAR:target} href="{VAR:link}">{VAR:caption}</a>
			</div>
		</div>
		<!-- END SUB: sel_tab_L1 -->
	</div>
	<!-- END SUB: tabs_L1 -->

<!-- SUB: ADDITIONAL_TEXT -->
	{VAR:addt_content}
<!-- END SUB: ADDITIONAL_TEXT -->

  <!-- SUB: disabled_tab_L1 -->
  <!-- END SUB: disabled_tab_L1 -->

<!-- SUB: HAS_TABS -->
	<br class="clear" />
	<!-- SUB: NOT_POPUP -->
	<div class="toiming">
		{VAR:qa_pop}
		{VAR:bm_pop}
		{VAR:history_pop}
<!--		<a href="#" class="nupp"><img src="{VAR:baseurl}/automatweb/images/aw06/ikoon_ajalugu.gif" alt="" width="13" height="13" border="0" class="ikoon" />Ajalugu <img src="{VAR:baseurl}/automatweb/images/aw06/ikoon_nool_alla.gif" alt="#" width="5" height="3" border="0" style="margin: 0 -3px 1px 0px" /></a> -->
		<a href="{VAR:srch_link}" class="nupp"><img src="{VAR:baseurl}/automatweb/images/aw06/ikoon_luup.gif" alt="" width="13" height="13" border="0" class="ikoon" />{VAR:search_text} <img src="{VAR:baseurl}/automatweb/images/aw06/ikoon_nool_alla.gif" alt="#" width="5" height="3" border="0" style="margin: 0 -3px 1px 0px" /></a>
	</div>
	<!-- END SUB: NOT_POPUP -->

</div>
<!-- //päis -->

	<div id="k_menyy">
		<!-- SUB: tabs_L2 -->
		<div class="tabs">
			<!-- SUB: tab_L2 -->
			<div class="norm">
				<div class="right">
					<a {VAR:target} href="{VAR:link}">{VAR:caption}</a>
				</div>
			</div>
			<!-- END SUB: tab_L2 -->
			<!-- SUB: disabled_tab_L2 -->
			<!-- END SUB: disabled_tab_L2 -->
			<!-- SUB: sel_tab_L2 -->
			<div class="akt">
				<div class="right">
					<a {VAR:target} href="{VAR:link}">{VAR:caption}</a>
				</div>
			</div>
			<!-- END SUB: sel_tab_L2 -->
		</div>
		<!-- END SUB: tabs_L2 -->
		<div class="p">
			<img src="{VAR:baseurl}/automatweb/images/aw06/ikoon_tagasiside.gif" name="ico1" alt="tagasiside" width="19" height="16" vspace="2" /><a href="{VAR:feedback_link}" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('ico1','','{VAR:baseurl}/automatweb/images/aw06/ikoon_tagasiside_ov.gif',1)">{VAR:feedback_text}</a>
			
			<img src="{VAR:baseurl}/automatweb/images/aw06/ikoon_kasutajatugi.gif" name="ico2" alt="kasutajatugi" width="16" height="16" /><a href="{VAR:feedback_m_link}" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('ico2','','{VAR:baseurl}/automatweb/images/aw06/ikoon_kasutajatugi_ov.gif',1)">{VAR:feedback_m_text}</a>
			
			<img src="{VAR:baseurl}/automatweb/images/aw06/ikoon_abi.gif" name="ico3" alt="abi" width="16" height="16" /><a href="javascript:showhide_help();"  onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('ico3','','{VAR:baseurl}/automatweb/images/aw06/ikoon_abi_ov.gif',1)">{VAR:help_text}</a>
		</div>
		<br class="clear" />
	</div>

<!-- END SUB: HAS_TABS -->
<!-- SUB: NO_TABS -->
</div>
<!-- END SUB: NO_TABS -->

<div id="help_layer" style="background-color: #F7F7F7; border: 1px solid #91DA52; display: none; padding: 5px;">
<div id="helptext_layer" style="font-family: verdana, sans-serif; font-size: 11px; font-weight: normal; color: #000000; height: 28px; background-color: #F7F7F7; ">
{VAR:help}
</div>
<div style="text-align: right; width: 100%; font-family: verdana, sans-serif; font-size: 11px; font-weight: normal; color: #000000;">
{VAR:translate_url}
<a href="javascript:void(0);" onclick="window.open('{VAR:help_url}','awhelp','width=750,height=550,resizable=1,scrollbars=1');">{VAR:more_help_text}</a> | <a href="javascript:close_help();">{VAR:close_help_text}</a>
</div>
</div>


<script type="text/javascript">
function showhide_help()
{
        help_layerv = document.getElementById('help_layer');
        if (help_layerv.style.display == 'none')
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
        help_layerv = document.getElementById('help_layer');
        help_layerv.style.display = 'block';
}

function close_help()
{
        help_layerv = document.getElementById('help_layer');
        help_layerv.style.display = 'none';
}

function show_property_help(propname)
{
        prophelp_layerv = document.getElementById('property_' + propname + '_help');
        if (prophelp_layerv)
        {
                helptext_layerv = document.getElementById('helptext_layer');
                helptext_layerv.innerHTML = prophelp_layerv.innerHTML;
                if (help_layerv.style.display == 'none')
                {
                        show_help();
                }
                else
                {
                        close_help();
                }
        }

}
</script>

<!-- sisu -->
	<div id="sisu">{VAR:content}</div>
<!-- //sisu -->

