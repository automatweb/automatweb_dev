<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td bgcolor="#CCCCCC">
<table bgcolor="#FFFFFF" cellpadding=2 cellspacing=1 border=0 width="100%">
	<form method="POST" action="periods.{VAR:ext}" name="periodform">
	<tr>
	<td colspan="8" class="fgtitle">
	&nbsp;<b>Perioodid: <a href="javascript:document.periodform.submit()">Salvesta</a>
	|
	<a href="{VAR:self}?oid={VAR:oid}&type=add">Lisa</a></b>
	</td>
	</tr>
	<tr bgcolor="#C9EFEF">
		<td class="title">ID</td>
		<td class="title">Kirjeldus</td>
		<td class="title">Loodud</td>
		<td class="title">Jrk</td>
		<td class="title">Arhiivis</td>
		<td class="title">Aktiivne</td>
		<td colspan="2" class="title">Tegevus</td>
	</tr>
<!-- SUB: LINE -->
	<tr>
		<td class="{VAR:rs}">
			{VAR:id}
		</td>
		<td class="{VAR:rs}">
			{VAR:description}
		</td>
		<td class="{VAR:rs}">
			{VAR:created}
		</td>
		<td class="{VAR:rs}">
			<input type="text" size="3" maxlength="3" name="jrk[{VAR:id}]" value="{VAR:jrk}">
			<input type="hidden" name="oldjrk[{VAR:id}]" value="{VAR:jrk}">
		</td>
		<td class="{VAR:rs}" align="center">
			<input type="checkbox" name="arc[{VAR:id}]" {VAR:archived}>
			<input type="hidden" name="oldarc[{VAR:id}]" value="{VAR:oldarc}">
		</td>
		<td class="{VAR:rs}" align="center">
			<input type="radio" name="activeperiod" {VAR:active} value="{VAR:id}">
		</td>
		<td class="{VAR:rs}" align="center">
			<a href="periods.{VAR:ext}?type=edit&id={VAR:id}">Muuda</a>
		</td>
		<td class="{VAR:rs}" align="center">
			<a href="menuedit.{VAR:ext}?period={VAR:id}">Sisu</a>
		</td>
	</tr>
<!-- END SUB: LINE -->
	<tr>
		<td class="fform" colspan="8" align="center">
			<!--
			<input type="submit" value="Salvesta">
			-->
			<input type="hidden" name="oid" value="{VAR:oid}">
			<input type="hidden" name="oldactiveperiod" value="{VAR:oldactiveperiod}">
			<input type="hidden" name="type" value="savestatus">
		</td>
	</tr>
</form>
</table>
</td>
</tr>
</table>
