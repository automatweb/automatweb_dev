<BODY bgcolor="#F0F5F8" link="#002E73" alink="#002E73" vlink="#B4CFDC" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">
<table border=0 width="100%" cellspacing="0" cellpadding="0">
<tr>
<td align="left" class="yah">&nbsp;
<a href="{VAR:baseurl}/index.aw?section={VAR:rootmenu}&aip=1">AIP</a>
<!-- SUB: YAH_LINK -->
/ <a href="{VAR:baseurl}/index.aw?section={VAR:parent}&aip=1"><b>{VAR:pre}</b> {VAR:name}</a>
<!-- END SUB: YAH_LINK -->
/ Muudatused 
</td>
<td align="right" class="yah">{VAR:date}&nbsp;&nbsp;</td>
</tr>
<form action="reforb.{VAR:ext}" method="POST" name='q'>
</table>

{VAR:toolbar}


<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td class="awmenuedittableborder">
<table width="100%" cellspacing="1" cellpadding="3">

<tr>

		<td align=center class="awmenuedittablehead" >&nbsp;Nimi:&nbsp;</td>
		<td align=center class="awmenuedittablehead" >&nbsp;J&otilde;ustumise kuup&auml;ev:&nbsp;</td>
		<td align=center class="awmenuedittablehead" >&nbsp;Avaldamise kuup&auml;ev:&nbsp;</td>
		<td align=center class="awmenuedittablehead" >&nbsp;Muutja:&nbsp;</td>
		<td align=center class="awmenuedittablehead" >&nbsp;Muudetud:&nbsp;</td>
		<td align=center class="awmenuedittablehead" >&nbsp;Aktiivne AIP AMDT&nbsp;</td>
		<td align=center class="awmenuedittablehead" >&nbsp;Aktiivne AIRAC AIP AMDT&nbsp;</td>
		<td align="center" colspan="2" width="10%" class="awmenuedittablehead">&nbsp;Tegevused&nbsp;</td>
		<td align="center" width="10%" class="awmenuedittablehead">&nbsp;<a href='javascript:selall()'>Vali</a>&nbsp;</td>
	</tr>
<!-- SUB: LINE -->
<tr class="awmenuedittablerow"	>
<td class="awmenuedittabletext" style="background:#FCFCF4" align=center>&nbsp;{VAR:name}&nbsp;</td>
<td class="awmenuedittabletext" style="background:#FCFCF4" align=center>&nbsp;{VAR:time}&nbsp;</td>
<td class="awmenuedittabletext" style="background:#FCFCF4" align=center>&nbsp;{VAR:j_time}&nbsp;</td>
<td class="awmenuedittabletext" style="background:#FCFCF4" align=center>&nbsp;{VAR:modifiedby}&nbsp;</td>
<td class="awmenuedittabletext" style="background:#FCFCF4" align=center>&nbsp;{VAR:modified}&nbsp;</td>
<td class="awmenuedittabletext" style="background:#FCFCF4" align=center>&nbsp;
<!-- SUB: CH1 -->
	<input type='radio' name='act_1' value='{VAR:id}' {VAR:checked_1}>
<!-- END SUB: CH1 -->
&nbsp;</td>
<td class="awmenuedittabletext" style="background:#FCFCF4" align=center>&nbsp;
<!-- SUB: CH2 -->
<input type='radio' name='act_2' value='{VAR:id}' {VAR:checked_2}>
<!-- END SUB: CH2 -->
&nbsp;</td>
<td class="awmenuedittabletext" style="background:#FCFCF4" align=center><a href='{VAR:activate}'>K&auml;ivita</a></td>
<td class="awmenuedittabletext" style="background:#FCFCF4" align=center><a href='{VAR:change}'><IMG SRC="{VAR:baseurl}/automatweb/images/blue/obj_edit.gif" WIDTH="16" HEIGHT="16" BORDER=0 ALT="Muuda"></a></td>
<td class="awmenuedittabletext" style="background:#FCFCF4" align=center><input type='checkbox' name='sel[]' value='{VAR:id}'></td>
</tr>
<!-- END SUB: LINE -->
</table>
</td></tr></table>
</td></tr></table>
{VAR:reforb}
</form>
<script language="javascript">
function dodelete()
{
	document.q.is_del.value=1;
	document.q.submit();
}

var chk_status = true;

function selall()
{
	len = document.q.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.q.elements[i].name == "sel[]")
		{
			document.q.elements[i].checked=chk_status;
			window.status = ""+i+" / "+len;
		}
	}
	chk_status = !chk_status;
}
</script>

</body>