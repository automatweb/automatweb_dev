<form name='boo' action='reforb.{VAR:ext}' method=post>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>Vali profiilide koostamise form: <a href='javascript:boo.submit()'>Salvesta</a></b></td>
</tr>
<tr>
<td align="center" class="title">&nbsp;ID&nbsp;</td>
<td align="center" class="title">&nbsp;Nimi&nbsp;</td>
<td align="center" class="title">&nbsp;&nbsp;</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fgtext">&nbsp;{VAR:id}&nbsp;</td>
<td class="fgtext">&nbsp;{VAR:name}&nbsp;</td>
<td class="fgtext">&nbsp;<input type='radio' name='sel' value='{VAR:id}' {VAR:sel}>&nbsp;</td>
</tr>
<!-- END SUB: LINE -->
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
<Br><br>
Statistika: <br>
Kokku bannerite n&auml;itamisi: {VAR:t_views}<br>
Kokku klikke: {VAR:t_clicks}<br>
Kokku profiile: {VAR:t_profiles}<br>
Kokku bannereid: {VAR:t_banners}<br>
Kokku kliente: {VAR:t_clients}<br>
Kokku banneri kasutajaid: {VAR:t_busers}<br>
