<table width="100%" border="0" cellpadding="3" cellspacing="0">
<tr>
<td class="caldayheadday">
<a href="{VAR:prev}"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_left.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT="&lt;&lt;"></a> {VAR:caption}  <a href="{VAR:next}"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/cal_nool_right.gif" WIDTH="19" HEIGHT="8" BORDER=0 ALT="&gt;&gt;"></a></td>
</tr>
</table>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td width="99%" valign="top">
{VAR:content}
</td>
<!-- SUB: NAVPANEL -->
<td width="1" class="caltablebordertume"></td>
<td valign="top">

<!-- SUB: navigator -->
<table border="0" cellpadding="0" cellspacing="0">	
<tr>
<td valign="top" class="caldaysback">
{VAR:navi0}
</td>
</tr>
<tr>
<td valign="top" class="caldaysback">
{VAR:navi1}
</td>
</tr>
<tr>
<td valign="top" class="caldaysback">
{VAR:navi2}
</td>
</tr>
</table>
<!-- END SUB: navigator -->


<table border="0" cellpadding="0" cellspacing="0">
<tr><td colspan="3"></td></tr>
<!-- SUB: summary_header -->
<tr class="calmonthback">
<td width="25" align="center"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/cal_notes.gif" WIDTH="15" HEIGHT="15" BORDER=0 ALT=""></td>
<td height="23" class="celltext" align="left">{VAR:caption}</td>
<td width="20" align="center" valign="top"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/cal_config.gif" WIDTH="18" HEIGHT="18" BORDER=0 ALT=""></td>
</tr>
<tr><td class="caltableborderhele" colspan="3"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td></tr>
<!-- END SUB: summary_header -->
<!-- SUB: summary_line -->
<tr><td colspan="3">
<a href="{VAR:url}">{VAR:caption}</a><br>
<blockquote>
{VAR:desc}
</blockquote>
</td></tr>
<!-- END SUB: summary_line -->
</table>




</td>
<!-- END SUB: NAVPANEL -->
</tr>
</table>


<span class="header1">{VAR:menudef}</a>
<br>
<font color="red"><b>{VAR:status_msg}</b></font>
</span>
