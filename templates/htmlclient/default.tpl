<!-- SUB: SHOW_CHANGEFORM -->
<form style="margin-top: 0px;" action='{VAR:handler}.{VAR:ext}' method='{VAR:method}' name='changeform' enctype='multipart/form-data' {VAR:form_target}>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='100000000'>

<!-- END SUB: SHOW_CHANGEFORM -->
<table id="{VAR:contenttbl_id}" width="100%" border="0" cellspacing="0" cellpadding="0"> <!-- aw06contenttable -->

<!-- SUB: SAVE_MESSAGE -->
<tr>
	<td colspan="2" class="DataSavedMessage">{VAR:message}</td>
</tr>
<!-- END SUB: SAVE_MESSAGE -->
	{VAR:content}

	<!-- SUB: ERROR -->
	<tr>
	    <td colspan="2" id="error">{VAR:error_text}</td>
	</tr>
	<!-- END SUB: ERROR -->

	<!-- SUB: PROP_ERR_MSG -->
	<tr>
	    <td width="100"></td>
	    <td id="error_msg">{VAR:err_msg}</td>
	</tr>
	<!-- END SUB: PROP_ERR_MSG -->

	<!-- SUB: LINE -->
	<tr>
	    <td width="100" id="linecaption" >{VAR:caption}</td>
	    <td id="lineelment">{VAR:element}</td>
	</tr>
	<!-- END SUB: LINE -->

	<!-- SUB: HEADER -->
	<tr>
	    <td></td>
	    <td id="header">{VAR:caption}</td>
	</tr>
	<!-- END SUB: HEADER -->

	<!-- SUB: SUB_TITLE -->
	<tr>
	    <td colspan="2" id="subtitle">{VAR:value}</td>
	</tr>
	<!-- END SUB: SUB_TITLE -->

	<!-- SUB: CONTENT -->
	<tr>
	    <td colspan="2" id="sitecontent">{VAR:value}</td>
	</tr>
	<!-- END SUB: CONTENT -->

	<!-- SUB: SBT_JS -->
this.disabled=true;self.disabled=true;
	<!-- END SUB: SBT_JS -->

	<!-- SUB: SUBMIT -->
	<tr>
	    <td width="100"></td>
	    <td id="buttons">
		<!-- SUB: BACK_BUTTON -->
		<input id="button" type="submit" name="{VAR:back_button_name}" value="{VAR:back_button_caption}" />
		<!-- END SUB: BACK_BUTTON -->
		<input id="button" type="submit" name="{VAR:name}" value="{VAR:sbt_caption}" accesskey='s' onClick='{VAR:sbt_js}submit_changeform("{VAR:action}"); return false;'/>
		<!-- SUB: FORWARD_BUTTON -->
		<input id="button" type="submit" name="{VAR:forward_button_name}" value="{VAR:forward_button_caption}" />
		<!-- END SUB: FORWARD_BUTTON -->
	    </td>
	</tr>
	<!-- END SUB: SUBMIT -->



			<!-- SUB: SUBITEM -->
		<span style='color: red'>{VAR:err_msg}</span>
		{VAR:element}
		<span class="aw04contentcellright">{VAR:caption}</span>
		&nbsp;
		<!-- END SUB: SUBITEM -->

		<!-- SUB: SUBITEM2 -->
		<span style='color: red'>{VAR:err_msg}</span>
		<div class="aw04contentcellleft">{VAR:caption}</div>
		<div class="aw04contentcellright">{VAR:element}</div>
		<!-- END SUB: SUBITEM2 -->

		<!-- SUB: GRIDITEM -->
		<div class="aw04gridcell_caption">
			<!-- SUB: GRID_ERR_MSG -->
			<span style='color: red;'>{VAR:err_msg}</span>
			<!-- END SUB: GRID_ERR_MSG -->

			<!-- SUB: CAPTION_TOP -->
			{VAR:caption}:<br/>
			{VAR:element}
			<!-- END SUB: CAPTION_TOP -->
			<!-- SUB: CAPTION_LEFT -->
			<table border="0" width="100%">
			<tr>
				<td width="20%" align="right">
			{VAR:caption}:
				</td>
				<td width="70%">
			{VAR:element}
				</td></tr></table>
			<!-- END SUB: CAPTION-LEFT -->
		</div>
		<!-- END SUB: GRIDITEM -->

		<!-- SUB: GRIDITEM_NO_CAPTION -->
		<div class="aw04gridcell_no_caption">{VAR:element}</div>
		<!-- END SUB: GRIDITEM_NO_CAPTION -->

		<!-- SUB: GRID_HBOX_OUTER -->
		<div id="{VAR:grid_outer_name}">
			<!-- SUB: GRID_HBOX -->
				<!-- SUB: GRID_NO_CLOSER -->
			<div id="{VAR:grid_name}">
				<!-- END SUB: GRID_NO_CLOSER -->
				<!-- SUB: GRID_HAS_CLOSER -->
			<div id="vbox">
				<div class="pais">
					<div class="caption">{VAR:area_caption}</div>
					<div class="closer"><a href="#" onClick='el=document.getElementById("{VAR:grid_name}");im=document.getElementById("{VAR:grid_name}_closer_img");if (el.style.display=="none") { el.style.display="block";im.src="{VAR:baseurl}/automatweb/images/aw06/closer_up.gif"; im.alt=im.title="{VAR:close_text}"; aw_get_url_contents("{VAR:open_layer_url}");} else { el.style.display="none";im.src="{VAR:baseurl}/automatweb/images/aw06/closer_down.gif"; im.alt=im.title="{VAR:open_text}"; aw_get_url_contents("{VAR:close_layer_url}");}'><img src="{VAR:baseurl}/automatweb/images/aw06/closer_{VAR:closer_state}.gif" title="{VAR:start_text}" alt="{VAR:start_text}" width="20" height="15" border="0" class="btn" id="{VAR:grid_name}_closer_img"/></a></div>
				</div>
				<div class="sisu" id="{VAR:grid_name}" style="display: {VAR:display}">
				<!-- END SUB: GRID_HAS_CLOSER -->

				<table border=0 cellspacing=0 cellpadding=0 width='100%'>
					<tr>
						<!-- SUB: GRID_HBOX_ITEM -->
						<td valign='top' {VAR:item_width} style='padding-left: 0px;'>{VAR:item}</td>
						<!-- END SUB: GRID_HBOX_ITEM -->
					</tr>
				</table>
				<!-- SUB: GRID_CLOSER_END -->
				</div></div>
				<!-- END SUB: GRID_CLOSER_END -->
				<!-- SUB: GRID_NO_CLOSER_END -->
			</div>
				<!-- END SUB: GRID_NO_CLOSER_END -->
			<!-- END SUB: GRID_HBOX -->
		</div>
		<!-- END SUB: GRID_HBOX_OUTER -->
		<!-- SUB: GRID_VBOX_OUTER -->
		<div id="{VAR:grid_outer_name}">
			<!-- SUB: GRID_VBOX -->
			<!-- SUB: VGRID_NO_CLOSER -->
			<div id="{VAR:grid_name}">
			<!-- END SUB: VGRID_NO_CLOSER -->
			<!-- SUB: VGRID_HAS_CLOSER -->
			<div id="vbox">
				<div class="pais">
					<div class="caption">{VAR:area_caption}</div>
					<div class="closer"><a href="#" onClick='el=document.getElementById("{VAR:grid_name}");im=document.getElementById("{VAR:grid_name}_closer_img");if (el.style.display=="none") { el.style.display="block";im.src="{VAR:baseurl}/automatweb/images/aw06/closer_up.gif"; im.alt=im.title="{VAR:close_text}"; aw_get_url_contents("{VAR:open_layer_url}");} else { el.style.display="none";im.src="{VAR:baseurl}/automatweb/images/aw06/closer_down.gif"; im.alt=im.title="{VAR:open_text}"; aw_get_url_contents("{VAR:close_layer_url}");}'><img src="{VAR:baseurl}/automatweb/images/aw06/closer_{VAR:closer_state}.gif" title="{VAR:start_text}" alt="{VAR:start_text}" width="20" height="15" border="0" class="btn"  id="{VAR:grid_name}_closer_img"/></a></div>
				</div>
				<div class="sisu" id="{VAR:grid_name}" style="display: {VAR:display}">
				<!-- SUB: VGRID_HAS_PADDING -->
				<div class="sisu2">
				<!-- END SUB: VGRID_HAS_PADDING -->

				<!-- SUB: VGRID_NO_PADDING -->
				<div class="sisu2nop">
				<!-- END SUB: VGRID_NO_PADDING -->
			<!-- END SUB: VGRID_HAS_CLOSER -->

				<!-- SUB: GRID_VBOX_ITEM -->
					<div class="sisu3">{VAR:item}</div>

				<!-- END SUB: GRID_VBOX_ITEM -->

					<!-- SUB: GRID_VBOX_SUBITEM -->
					<div class="sisu3">{VAR:item}</div>
					<!-- END SUB: GRID_VBOX_SUBITEM -->

				<!-- SUB: VGRID_CLOSER_END -->
				</div>
				</div>
			</div>
				<!-- END SUB: VGRID_CLOSER_END -->
				<!-- SUB: VGRID_NO_CLOSER_END -->
				</div>
				<!-- END SUB: VGRID_NO_CLOSER_END -->
			<!-- END SUB: GRID_VBOX -->
		</div>
		<!-- END SUB: GRID_VBOX_OUTER -->

		<!-- SUB: GRID_TABLEBOX -->
		<div id="tablebox">
		    <div class="pais">
			<div class="caption">Tabeli pealkiri</div>
			<div class="navigaator">
			    <!-- siia tuleb �hel ilusal p�eval lehtede kruttimise navigaator, homseks seda vaja pole, seega las see div j��b t�hjaks -->
			</div>
		    </div>
		    <div class="sisu">
		    <!-- SUB: GRID_TABLEBOX_ITEM -->
			{VAR:item}
		    <!-- END SUB: GRID_TABLEBOX_ITEM -->
		    </div>
		</div>
		<!-- END SUB: GRID_TABLEBOX -->

		<!-- SUB: PROPERTY_HELP -->
		<div id="property_{VAR:property_name}_help" style="display: none;">
		<strong>{VAR:property_caption} - {VAR:property_comment}</strong>
		<p>{VAR:property_help}</p>
		</div>
		<!-- END SUB: PROPERTY_HELP -->



<!-- SUB: SHOW_CHANGEFORM2 -->
	{VAR:reforb}
		<script type="text/javascript">
		{VAR:scripts}
		function submit_changeform(action)
		{
			changed = 0;
			{VAR:submit_handler}
			if (typeof(aw_submit_handler) != "undefined")
			{
				if (aw_submit_handler() == false)
				{
					document.getElementById('button').disabled=false;
					return false;
				}
			}
			if (typeof action == "string" && action.length>0)
			{
				document.changeform.action.value = action;
			};
			document.changeform.submit();
		}
		</script>
	</form>
	<!-- END SUB: SHOW_CHANGEFORM2 -->
</table>

<!-- SUB: iframe_body_style -->
body {
        background-color: #FFFFFF;
        margin: 0px;
        overflow-y: hidden;
        overflow:hidden;
}
<!-- END SUB: iframe_body_style -->
<!-- SUB: CHECK_LEAVE_PAGE -->
<script language="javascript">

changed = 0;
function set_changed()
{
	changed = 1;
}

function generic_loader2()
{
	// set onchange event handlers for all form elements
	var els = document.changeform.elements;
	var cnt = els.length;
	for(var i = 0; i < cnt; i++)
	{
		if (els[i].attachEvent)
		{
			els[i].attachEvent('onChange',set_changed);
		}
		else
		{
			els[i].setAttribute("onChange",els[i].getAttribute("onChange")+ ";set_changed();");
		}
	}
}

function generic_unloader()
{
	if (changed)
	{
		if (confirm("{VAR:confirm_unchanged_text}"))
		{
			document.changeform.submit();
		}
	}
}
</script>

<!-- SUB: CHECK_LEAVE_PAGE -->
