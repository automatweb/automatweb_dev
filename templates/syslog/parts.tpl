<!-- SUB: hits -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#EEEEEE">
<tr>
<td>
	<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
	<tr>
		<td colspan="3" class="fgtitle">{VAR:lefttitle}</td>
	</tr>

	<tr>
		<td class="fgtitle">{VAR:LC_SYSLOG_PERIOD}</td>
		<td class="fgtitle">{VAR:LC_SYSLOG_LOOKS}</td>
		<td class="fgtitle">&nbsp;</td>
	</tr>
	<!-- SUB: hits_line -->
	<tr>
	<td class="{VAR:style}" width="10%" nowrap>
		{VAR:period}
	</td>
	<td class="{VAR:style}" width="10%" nowrap>
		{VAR:hits}
	</td>
	<td class="{VAR:style}" width="80%">
		<img src="images/bar.gif" width="{VAR:width}" height="5">
	</td>
	</tr>
	<!-- END SUB: hits_line -->
	<tr>
		<td class="fgtext">
		<strong>{VAR:LC_SYSLOG_TOGETHER}:</strong>
		</td>
		<td class="fgtext" colspan="2">
		<strong>{VAR:total}</strong>
		</td>
	</tr>
	<tr>
		<td class="fgtext" colspan="3" align="center">
		<img src="{VAR:self}?display=graph&id={VAR:uniqid}">
		</td>
	</tr>
	</table>
</td>
</tr>
</table>
<!-- END SUB: hits -->
<!-- SUB: logins -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#EEEEEE">
<tr>
<td>
	<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
	<tr>
		<td colspan="3" class="fgtitle">{VAR:title}</td>
	</tr>

	<tr>
		<td class="fgtitle">{VAR:LC_SYSLOG_USER}</td>
		<td class="fgtitle">{VAR:LC_SYSLOG_LOGS}</td>
		<td class="fgtitle">&nbsp;</td>
	</tr>
	<!-- SUB: login_line -->
	<tr>
	<td class="{VAR:style}" width="10%" nowrap>
		{VAR:uid}
	</td>
	<td class="{VAR:style}" width="10%" nowrap>
		{VAR:logins}
	</td>
	<td class="{VAR:style}" width="80%">
		<img src="images/bar.gif" width="{VAR:width}" height="5">
	</td>
	</tr>
	<!-- END SUB: login_line -->
	<tr>
		<td class="fgtext">
		<strong>{VAR:LC_SYSLOG_TOGETHER}:</strong>
		</td>
		<td class="fgtext" colspan="2">
		<strong>{VAR:total}</strong>
		</td>
	</tr>
	</table>
</td>
</tr>
</table>
<!-- END SUB: logins -->
<!-- SUB: hosts -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#EEEEEE">
<tr>
<td>
	<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
	<tr>
		<td colspan="4" class="fgtitle">Top 30 {VAR:LC_SYSLOG_LOOKER_BY_ADDRESS}</td>
	</tr>

	<tr>
		<td class="fgtitle">#</td>
		<td class="fgtitle">IP</td>
		<td class="fgtitle">Hitte</td>
		<td class="fgtitle">&nbsp;</td>
	</tr>
	<!-- SUB: hosts_line -->
	<tr>
	<td class="{VAR:style}" width="10%" nowrap>
		{VAR:cnt}
	</td>
	<td class="{VAR:style}" width="10%" nowrap>
		<a href="javascript:ipexplorer('{VAR:ip}')">{VAR:ip}</a>
	</td>
	<td class="{VAR:style}" width="10%" nowrap>
		{VAR:hits}
	</td>
	<td class="{VAR:style}" width="80%">
		<img src="images/bar.gif" width="{VAR:width}" height="5">
	</td>
	</tr>
	<!-- END SUB: hosts_line -->
	<tr>
	<td class="fgtext" colspan="2">
	<strong>{VAR:LC_SYSLOG_TOGETHER}:</strong>
	</td>
	<td class="fgtext" colspan="2">
	<strong>{VAR:total}</strong>
	</td>
	</tr>
	</table>
</td>
</tr>
</table>
<!-- END SUB: hosts -->
<!-- SUB: menus -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#EEEEEE">
<tr>
<td>
	<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
	<tr>
		<td colspan="4" class="fgtitle">Top {VAR:LC_SYSLOG_CHANGED_MENUS}</td>
	</tr>

	<tr>
		<td class="fgtitle">#</td>
		<td class="fgtitle">{VAR:LC_SYSLOG_MENU}</td>
		<td class="fgtitle">{VAR:LC_SYSLOG_CHANGES}</td>
		<td class="fgtitle">&nbsp;</td>
	</tr>
	<!-- SUB: menus_line -->
	<tr>
	<td class="{VAR:style}" width="10%" nowrap>
		{VAR:cnt}
	</td>
	<td class="{VAR:style}" width="10%" nowrap>
		{VAR:menu}
	</td>
	<td class="{VAR:style}" width="10%" nowrap>
		{VAR:changes}
	</td>
	<td class="{VAR:style}" width="80%">
		<img src="images/bar.gif" width="{VAR:width}" height="5">
	</td>
	</tr>
	<!-- END SUB: menus_line -->
	<tr>
	<td class="fgtext" colspan="2">
	<strong>{VAR:LC_SYSLOG_TOGETHER}:</strong>
	</td>
	<td class="fgtext" colspan="2">
	<strong>{VAR:total}</strong>
	</td>
	</tr>
	</table>
</td>
</tr>
</table>
<!-- END SUB: menus -->
<!-- SUB: objects -->
<table border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="#EEEEEE">
<tr>
<td>
	<table border="0" cellspacing="1" cellpadding="2" width="100%" bgcolor="#FFFFFF">
	<tr>
		<td colspan="5" class="fgtitle">
		Top
		<select name="count">
		<option value="50">50</option>
		</select>
		<select name="type">
		<option value="pageview">{VAR:LC_SYSLOG_LOOKS1}</option>
		</select> {VAR:LC_SYSLOG_THIS_PERIOD}
		</td>
	</tr>

	<tr>
		<td class="fgtitle">#</td>
		<td class="fgtitle">Oid</td>
		<td class="fgtitle">{VAR:LC_SYSLOG_NAME}</td>
		<td class="fgtitle">Hitte</td>
		<td class="fgtitle">&nbsp;</td>
	</tr>
	<!-- SUB: objects_line -->
	<tr>
		<td class="{VAR:style}">{VAR:cnt}</td>
		<td class="{VAR:style}" nowrap align="right">{VAR:oid}</td>
		<td class="{VAR:style}" nowrap><a target="new" href="{VAR:baseurl}/automatweb/metainfo.{VAR:ext}?oid={VAR:oid}">{VAR:name}</a>&nbsp;</td>
		<td class="{VAR:style}">{VAR:hits}</td>
		<td class="{VAR:style}">
			<img src="images/bar.gif" width="{VAR:width}" height="5">
		</td>
	</tr>
	<!-- END SUB: objects_line -->
	<tr>
	<td class="fgtext" colspan="3">
	<strong>{VAR:LC_SYSLOG_TOGETHER}:</strong>
	</td>
	<td class="fgtext" colspan="2">
	<strong>{VAR:total}</strong>
	</td>
	</tr>
	</table>
</td>
</tr>
</table>
<!-- END SUB: objects -->
<!-- SUB: selectors -->
<table border=1 cellpadding=0 cellspacing=1 width=100%>

<tr>
<td bgcolor=#f8f8f8 class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='auth' {VAR:auth_sel}>Login
</small></td>
<td bgcolor=#ffffff class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='mlist' {VAR:mlist_sel}>{VAR:LC_SYSLOG_LISTS}
</small></td>
<td bgcolor=#f8f8f8 class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='document' {VAR:document_sel}>{VAR:LC_SYSLOG_DOCUMENTS}
</small></td>
<td bgcolor=#ffffff class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='form' {VAR:form_sel}>{VAR:LC_SYSLOG_FORMS}
</small></td>
<td bgcolor=#f8f8f8 class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='user' {VAR:user_sel}>{VAR:LC_SYSLOG_USERS}
</small></td>
<td bgcolor=#ffffff class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='error' {VAR:alias_sel}>{VAR:LC_SYSLOG_ERRORS}
</small></td>
</tr>

<tr>
<td bgcolor=#f8f8f8 class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='auth' {VAR:auth_sel}>Logout
</small></td>
<td bgcolor=#ffffff class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='ml_var' {VAR:ml_var_sel}>{VAR:LC_SYSLOG_VARIABLES}
</small></td>
<td bgcolor=#f8f8f8 class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='image' {VAR:image_sel}>{VAR:LC_SYSLOG_IMAGES}
</small></td>
<td bgcolor=#ffffff class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='style' {VAR:style_sel}>{VAR:LC_SYSLOG_STYLES}
</small></td>
<td bgcolor=#f8f8f8 class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='group' {VAR:group_sel}>{VAR:LC_SYSLOG_GROUPS}
</small></td>
<td bgcolor=#ffffff class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='bug' {VAR:bug_sel}>Bug
</small></td>
</tr>

<tr>
<td bgcolor=#f8f8f8 class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='pageview' {VAR:pageview_sel}>{VAR:LC_SYSLOG_LOOKING}
</small></td>
<td bgcolor=#ffffff class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='e-mail' {VAR:e-mail_sel}>{VAR:LC_SYSLOG_MAILS}
</small></td>
<td bgcolor=#f8f8f8 class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='alias' {VAR:alias_sel}>{VAR:LC_SYSLOG_ALIASES}
</small></td>
<td bgcolor=#ffffff class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='menuedit' {VAR:menuedit_sel}>{VAR:LC_SYSLOG_MENUEDITOR}
</small></td>
<td bgcolor=#f8f8f8 class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='object' {VAR:object_sel}>{VAR:LC_SYSLOG_OBJECTS}
</small></td>
<td bgcolor=#ffffff class="plain">&nbsp;</td>
</tr>

<tr>
<td bgcolor=#f8f8f8 class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='link' {VAR:link_sel}>{VAR:LC_SYSLOG_LINK_CLICK}
</small></td>
<td bgcolor=#ffffff class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='mliki' {VAR:mliki_sel}>{VAR:LC_SYSLOG_MAIL_CLICK}
</small></td>
<td bgcolor=#f8f8f8 class="plain"><small>&nbsp;</td>
<td bgcolor=#ffffff class="plain"><small>
<input type='checkbox' NAME='types[]' VALUE='promo' {VAR:promo_sel}>{VAR:LC_SYSLOG_PROMOBOX}
</small></td>
<td bgcolor=#f8f8f8 class="plain"><small>&nbsp;</td>
<td bgcolor=#ffffff class="plain"><small>&nbsp;</td>
</tr></table>
<!-- END SUB: selectors -->
