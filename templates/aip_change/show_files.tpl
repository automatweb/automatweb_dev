<span class="text">
<b>{VAR:LC_CHANGE_PDFS}:</b><BR>

<!-- SUB: CHANGE_PDF -->
<a target="_blank" href='{VAR:url}'>{VAR:name}</a><br>
<IMG SRC="{VAR:baseurl}/img/trans.gif" WIDTH="1" HEIGHT="5" BORDER=0 ALT=""><br>
<!-- END SUB: CHANGE_PDF -->
</span>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td class="filestableborder">


<table width="100%" border="0" cellpadding="3" cellspacing="1">
<tr>
	<td class="filestablehead" >{VAR:LC_NAME}</td>
	<td class="filestablehead" >{VAR:LC_PUB_DATE}</td>
	<td class="filestablehead" >{VAR:LC_ACT_DATE}</td>
	<td class="filestablehead" >{VAR:LC_LINK}</td>
</tr>
<!-- SUB: LINE -->
<tr class="filestablefilesback">
	<td  class="filestabletextfiles">{VAR:name}</td>
	<td  class="filestabletextfiles">{VAR:j_time}</td>
	<td  class="filestabletextfiles">{VAR:act_time}</td>
	<td  class="filestabletextfiles"><a href='{VAR:link}'>{VAR:LC_LINK_CAPT}</a></td>
</tr>
<!-- END SUB: LINE -->

</table>


</td></tr></table>