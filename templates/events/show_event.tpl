<table border="1" cellpadding="1" cellspacing="2" width="400">
<tr>
<td colspan="2" bgcolor="#FFCCAA" align="center"><strong>{VAR:name}</strong></td>
</tr>
<tr>
<td><strong>{VAR:LC_EVENTS_PLACE}</strong></td>
<td><a href="{VAR:self}?op=show_location&id={VAR:pid}">{VAR:pname}</a></td>
</tr>
<!-- SUB: DESC -->
<tr>
<td valign="top" colspan="2">
{VAR:description}
</td>
</tr>
<!-- END SUB: DESC -->
<tr>
<td><strong>{VAR:LC_EVENTS_TIMA}</strong></td>
<td>{VAR:start} - {VAR:end}</td>
</tr>
<!-- SUB: CONTACT -->
<tr>
<td><strong>{VAR:LC_EVENTS_CONTACT}</strong></td>
<td>{VAR:contact}</td>
</tr>
<!-- END SUB: CONTACT -->
<!-- SUB: URL -->
<tr>
<td><strong>Url</strong></td>
<td><a href="{VAR:url}">{VAR:url}</a></td>
</tr>
<!-- END SUB: URL -->
<!-- SUB: PRICE -->
<tr>
<td colspan="2" align="center">
{VAR:LC_EVENTS_PRICE}: <strong>{VAR:price}</strong>
&nbsp;
{VAR:LC_EVENTS_PRICE_WITH_FLYER}: <strong>{VAR:priceflyer}</strong>
</td>
</tr>
<!-- END SUB: PRICE -->
<!-- SUB: FREE -->
<tr>
<td colspan="2" align="center">
{VAR:LC_EVENTS_FREE_ENTRY}
</td>
</tr>
<!-- END SUB: FREE -->
<!-- SUB: FLYER -->
<tr>
<td><strong>{VAR:}Flaieri URL</strong></td>
<td><a href="{VAR:flyer}">{VAR:flyer}</a></td>
</tr>
<!-- END SUB: FLYER -->
</table>
