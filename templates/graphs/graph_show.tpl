<script language=javascript>
var st=1;
function selall()
{
<!-- SUB: SELLINE -->
	document.forms[0].elements[{VAR:row}].checked=st;
<!-- END SUB: SELLINE -->
st = !st;
return false;
}

function doSubmit(val)
{
	document.grr.action.value=val;
	document.grr.submit();
}

function doAsk(caption){
var answer=confirm(caption)
if (answer)
	return true;
else
	return false;
}

function doDelete()
{
<!-- SUB: DEL_LINE -->
if (document.forms[0].elements[{VAR:row}].checked)
{
	if (!doAsk("Oled kindel, et tahad graafikut {VAR:name} kustutada?"))
		document.forms[1].elements[{VAR:row}].checked=0;
}
<!-- END SUB: DEL_LINE -->
	doSubmit("graph_delete");
}
</script>
<form action='refcheck.aw' method=post name=grr>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<TABLE border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
	<td height="15" colspan="7" class="fgtitle">&nbsp;<b>{VAR:LC_GRAPH_GRAPHS}:&nbsp;
	<a href='{VAR:add}'>{VAR:LC_GRAPH_ADD}</a>&nbsp;|&nbsp;<a href='javascript:doDelete()'>{VAR:LC_GRAPH_DELETE}&nbsp;</a>
	</b></td>
</tr>
<tr>
	<td class=title>GID</td>
	<td class=title>{VAR:LC_GRAPH_NAME}</td>
	<td class=title>{VAR:LC_GRAPH_DERSCRIPTION}</td>
	<td class=title colspan=3 align=center>{VAR:LC_GRAPH_ACTIVITY}</td>
	<td align="center" colspan="1" class="title">&nbsp;<a href='#' onClick="selall();return false;">{VAR:LC_GRAPH_ALL}</a>&nbsp;</td>
</tr><!-- SUB: LINE -->
	<tr>
	<td class="fgtext">{VAR:id}</td>
	<td class="fgtext">{VAR:name}</td>
	<td class="fgtext">{VAR:comment}</td>
	<td class="fgtext" align=center>
	<a href='{VAR:change}'>{VAR:LC_GRAPH_CHANGE}</a>
	&nbsp;</td>	
	<td class="fgtext" align=center>
	<a href='{VAR:meta}'>Meta</a>
	&nbsp;</td>	
	<td class="fgtext" align=center>
	<a href='{VAR:preview}'>{VAR:LC_GRAPH_LOOK}</a>
	&nbsp;</td>
	<td class="chkbox" align=center>
	<input type='checkbox' NAME='grr_{VAR:id}' align=center>
	</td>
	</tr>
	<!-- END SUB: LINE -->
<input type='hidden' NAME="action" value="graph_delete"></form>
</TABLE>
</td></tr></TABLE>