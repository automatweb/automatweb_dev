<script language="javascript">
function save_template()
{
	document.edform.submit();
};

function preview_template()
{
	// bugi oli ysna lihtne. submit() lopetab skripti taitmise
	// document.edform.submit();
	window.location = '{VAR:self}?action=preview';
};

function add_dynamic() {
	toolbar = 0;
	width = 500;
	height = 600;
	file = "tplpopup.aw?tpl={VAR:tpl}";
        self.name = "tpledit";
	aw_popup(file,"remote",width,height);
};

function prop_window() {
	alert('Not yet implemented');
};

function delete_marked()
{
	if (confirm("Oled kindel, et soovid märgitud vormielemendid kustutada?"))
	{
		document.edform.action.value="delete";
		document.edform.submit();
	};
};

function add_static()
{
	self.name = "tpledit";
	aw_popup("{VAR:self}?action=objectpool&tpl={VAR:tpl}","remote",400,400);
};

</script>
<div class="pealkiri1">
Template: 
</div>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table bgcolor="#FFFFFF" border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
	<td colspan="6" class="fgtitle"><b>TPLEDIT</b>
	<a href="{VAR:self}?action=list">Nimekiri</a> |
	| <a href="javascript:save_template()">Salvesta template</a>
	| <a href="javascript:preview_template()">Template eelvaade</a>
	| <a href="javascript:add_dynamic()">Lisa dünaamiline</a>
	| <a href="javascript:add_static()">Lisa staatiline</a>
	</td>
</tr>
<tr>
	<td colspan="6" class="fgtitle" align="right">
		<a href="javascript:delete_marked()">Kustuta märgitud</a>
	</td>
</tr>
</table>
<table bgcolor="#FFFFFF" border="0" cellspacing="1" cellpadding="0" width="100%">
<tr>
<form method="POST" name="edform">
	<td class="fgtitle" align="center">Jrk</td>
	<td class="fgtitle">Key</td>
	<td class="fgtitle">Caption</td>
	<td class="fgtitle">&nbsp;</td>
	<td class="fgtitle">&nbsp;</td>
	<td class="fgtitle" align="center">Valitud</td>
<!-- SUB: line -->
<tr>
	<td class="checkbox" align="center"><input type="text" name="jrk[{VAR:key}]" size="2" maxlength="2" value="{VAR:jrk}"></td>
	<td class="fgtext">&nbsp;&nbsp;{VAR:keycap}
		<input type="hidden" name="name[{VAR:key}]" value="{VAR:name}">
	
	</td>
	<td class="checkbox"><input name="caption[{VAR:key}]" size="20" value="{VAR:caption}"></td>
	<td class="fgtext" align="center"><a href="javascript:prop_window()">Properties</a></td>
	<td class="fgtext">&nbsp;</td>
	<td class="checkbox" align="center"><input name="marked[{VAR:key}]" type="checkbox" {VAR:checked}></td>
</tr>
<!-- END SUB: line -->
<!-- SUB: dynamic -->
<tr>
	<td class="checkbox" align="center"><input type="text" name="jrk[{VAR:key}]" size="2" maxlength="2" value="{VAR:jrk}"></td>
	<td class="fgtext">&nbsp;&nbsp;{VAR:keycap}
			<input type="hidden" name="style[{VAR:key}]" value="{VAR:style}">
			<input type="hidden" name="dyn[{VAR:key}]" value="1">
	</td>
	<td class="checkbox"><input name="caption[{VAR:key}]" size="20" value="{VAR:caption}"></td>
	<td class="fgtext" align="center"><a href="javascript:prop_window()">Properties</a></td>
	<td class="fgtext" align="center">&nbsp;</td>
	<td class="checkbox" align="center"><input name="marked[{VAR:key}]" type="checkbox" {VAR:checked}></td>
</tr>
<!-- END SUB: dynamic -->
</table>

</td>
<input type="hidden" name="action" value="{VAR:action}">
<input type="hidden" name="tpl" value="{VAR:tpl}">
</form>
</tr>
</table>
