<table border="0" cellspacing="0" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fgtitle" colspan=10 align="left">Filter: <A HREF="{VAR:minu}">Minule m‰‰ratud bugid</A> | <A HREF="{VAR:all}">Kıik bugid</A></td>
</tr>
<tr>
	<td class="fcaption2" colspan=10 align="left">&nbsp;</td>
</tr>
<tr>
	<td class="ftitle2" align="right">#</td>
	<td class="ftitle2" align="center">Pealkiri</td>
	<td class="ftitle2" colspan="2" align="center">PRI</td>
	<td class="ftitle2" align="center">Staatus</td>
	<td class="ftitle2" align="center">Aeg</td>
	<td class="ftitle2" align="center">Kes</td>
	<td class="ftitle2" align="center">Site</td>
	<td class="ftitle2" colspan="2">Tegevus</td>
</tr>
<!-- SUB: line -->
<tr>
	<td class="{VAR:style}">{VAR:rec}</td>
	<td class="{VAR:style}">&nbsp;&nbsp;{VAR:title}&nbsp;&nbsp;</td>
	<td class="{VAR:style}">&nbsp;{VAR:pri}&nbsp;</td>
	<td bgcolor="{VAR:pricolor}">&nbsp;&nbsp;&nbsp;</td>
	<td class="{VAR:style}">&nbsp;&nbsp;{VAR:status}&nbsp;&nbsp;</td>
	<td class="{VAR:style}">&nbsp;&nbsp;{VAR:when}&nbsp;&nbsp;</td>
	<td class="{VAR:style}">&nbsp;&nbsp;{VAR:uid}&nbsp;&nbsp;</td>
	<td class="{VAR:style}">&nbsp;&nbsp;{VAR:site}&nbsp;&nbsp;</td>
	<td class="{VAR:style}" align="center">&nbsp;&nbsp;<a href="{VAR:vaataurl}">Vaata</a>&nbsp;&nbsp;</td>
	<td class="{VAR:style}" align="center">&nbsp;&nbsp;<a href="javascript:box2('Kustutada see sissekanne?','{VAR:kustutaurl}')">Kustuta</a>&nbsp;&nbsp;</td>
</tr>
<!-- END SUB: line -->
</table>
<p>
Prioriteetide v‰rvid:
<table border=0 cellspacing=1 cellpadding=2>
<tr>
	<td bgcolor="#dadada">1<td>
	<td bgcolor="#dad0d0">2<td>
	<td bgcolor="#dacaca">3<td>
	<td bgcolor="#dac0c0">4<td>
	<td bgcolor="#dababa">5<td>
	<td bgcolor="#dab0b0">6<td>
	<td bgcolor="#daaaaa">7<td>
	<td bgcolor="#da9090">8<td>
	<td bgcolor="#da8a8a">9<td>
<tr>
</table>
Kokku: {VAR:total}<Br>
Avatud: {VAR:open} Suletud: {VAR:closed} Rejected: {VAR:rejected}