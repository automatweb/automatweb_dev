<script language="javascript">
var e_{VAR:form_id}_elname="",e_{VAR:form_id}_elname2="";
function setLink(li,title)
{
	for(i=0; i < document.fm_{VAR:form_id}.elements.length; i++)
	{
		if (document.fm_{VAR:form_id}.elements[i].name == e_{VAR:form_id}_elname)
		{
			document.fm_{VAR:form_id}.elements[i].value = title;
		}
		if (document.fm_{VAR:form_id}.elements[i].name == e_{VAR:form_id}_elname2)
		{
			document.fm_{VAR:form_id}.elements[i].value = li;
		}
	}
}
</script>
<form name='fm_{VAR:form_id}' action='{VAR:form_action}' METHOD=post enctype='multipart/form-data'>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='10000000'>

<!-- SUB: IMG_WRAP -->
<table border=0 cellpadding=2 cellspacing=0>
<tr>

<!-- SUB: IMAGE -->

<td bgcolor='#e5e5e5'><a href='javascript:popup({VAR:img_id});'><font face='tahoma, arial, geneva, helvetica' size="1">#{VAR:img_idx}#</font></a></td>

<!-- END SUB: IMAGE -->

<td bgcolor='#e5e5e5'><a href='/automatweb/images.{VAR:ext}?type=list&parent={VAR:entry_id}'><font face='tahoma, arial, geneva, helvetica' size="1">Muuda pilte</font></a></td>
</tr>
</table>

<!-- END SUB: IMG_WRAP -->

<img src="/images/transa.gif" width='20' height='3' border='0' vspace='0' hspace='0'><br>
<table{VAR:form_border}{VAR:form_bgcolor}{VAR:form_cellpadding}{VAR:form_cellspacing}{VAR:form_height}{VAR:form_width}{VAR:form_hspace}{VAR:form_vspace}>

<!-- SUB: LINE -->

<tr>
{VAR:COL}
</tr>

<!-- END SUB: LINE -->

</table>
{VAR:reforb}

<!-- SUB: EXTRAIDS -->

<input type='hidden' NAME='{VAR:var_name}' VALUE='{VAR:var_value}'>

<!-- END SUB: EXTRAIDS -->

</form>