<form action='reforb.{VAR:ext}' method=post>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="fform">UID</td><td class="fform">Millal</td><td class="fform">Muuda</td><td class="fform">Kustuta</td>
</tr>
<!-- SUB: LINE -->
<tr>
<td class="fform">{VAR:uid}</td><td class="fform">{VAR:tm}</td><td class="fform"><a href='{VAR:change}'>Muuda</a></td><td class="fform"><a href='{VAR:delete}'>Kustuta</a></td>
</tr>
<!-- END SUB: LINE -->
</table>
{VAR:reforb}
</form>
