<style type="text/css">

.text {
	font-family:  Verdana, Arial, sans-serif;
	font-size: 11px;
	color: #000000;
	line-height: 18px;
	text-decoration: none;
}

.text a {
	color: #058AC1; 
	text-decoration:underline;
}

.text a:hover {
	color: #000000; 
	text-decoration: underline;
}
p {
	margin-left: 30px;
}

table {
	font-size: inherit;
}

table.class_info {
	width: 50%;
	border-collapse: collapse;
	margin-left: 30px;
}

table.class_info td {
	border: 1px solid silver;
	padding: 3px;
}

table.class_info th {
	border: 1px solid silver;
	background-color: rgb(255, 255, 169);
	padding: 3px;
}
</style>


<table border="0" width="100%" cellpadding="2" cellspacing="0">
<tr>
		<td colspan="7" width="2" height="2" bgcolor="#000000"><img src='{VAR:baseurl}/automatweb/images/trans.gif'></td>
</tr>
<tr>
	<td colspan="7">
		<table border="0" width="100%">
			<tr>
				<td width="50%">
					{VAR:type_name} <b>{VAR:name}</b> in file <b>{VAR:file}</b><br>
					Maintainer: {VAR:maintainer}<br>
					CVS Version: <a href='{VAR:cvsweb_url}'>{VAR:cvs_version}</a><br>
					Methods: {VAR:func_count} total / {VAR:api_func_count} API / {VAR:orb_func_count} ORB<br>
					View source: <a href="{VAR:view_class}">{VAR:name}</a>
				</td>
				<td valign="top" align="left" width="50%">
					{VAR:class_comment}
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
		<td colspan="7" width="2" height="2" bgcolor="#000000"><img src='{VAR:baseurl}/automatweb/images/trans.gif'></td>
</tr>
<tr>
	<td valign="top" class="text" width="25%">
		<b>Extends</b><br>
		<!-- SUB: EXTENDER -->
		{VAR:spacer}<img src='{VAR:baseurl}/automatweb/images/inherit.gif'><a href='{VAR:inh_link}'>{VAR:inh_name}</a><br>
		<!-- END SUB: EXTENDER -->
	</td>
	<td width="2" bgcolor="#000000"><img src='{VAR:baseurl}/automatweb/images/trans.gif'></td>

	<td valign="top"  width="25%" class="text"><B>Depends</b><Br>
		<!-- SUB: DEP -->
		&nbsp; - <a href='{VAR:link}'>{VAR:name}</a><br>
		<!-- END SUB: DEP -->

		<!-- SUB: VAR_DEP -->
		&nbsp; - This class also has variable dependencies!<br>
		<!-- END SUB: VAR_DEP -->

	</td>
	<td width="2" bgcolor="#000000"><img src='{VAR:baseurl}/automatweb/images/trans.gif'></td>

	<td valign="top"  width="25%" class="text"><B>Implements</b><br>
		<!-- SUB: IMPLEMENTS -->
		&nbsp; - <a href='{VAR:link}'>{VAR:name}</a><br>
		<!-- END SUB: IMPLEMENTS -->
	</td>
	<td width="2" bgcolor="#000000"><img src='{VAR:baseurl}/automatweb/images/trans.gif'></td>

	<td valign="top"  width="25%" class="text"><B>Throws</b><Br>
		<!-- SUB: THROWS -->
		&nbsp; - <a href='{VAR:link}'>{VAR:name}</a><br>
		<!-- END SUB: THROWS -->
		<!-- SUB: THROWS_UNSPECIFIC -->
		&nbsp; - Untrackable</a><br>
		<!-- END SUB: THROWS_UNSPECIFIC -->
	</td>
</tr>
<tr>
		<td colspan="7" width="2" height="2" bgcolor="#000000"><img src='{VAR:baseurl}/automatweb/images/trans.gif'></td>
</tr>

<tr>
	<td colspan="3" rowspan="4" class="text" valign="top">
		<!-- SUB: HAS_API -->
		<b>API methods:</b><br>
		<!-- SUB: API_FUNCTION -->
		<a href='{VAR:view_func}'>{VAR:name}</a><br>
		<i>{VAR:short_comment}</i>
		<!-- END SUB: API_FUNCTION -->
		<br>
		<!-- END SUB: HAS_API -->

		<!-- SUB: HAS_ORB -->
		<B>ORB methods:</b><br>
		<!-- SUB: ORB_FUNCTION -->
		<a href='{VAR:view_func}'>{VAR:name}</a><br>
		<i>{VAR:short_comment}</i>
		<!-- END SUB: ORB_FUNCTION -->
		<br>
		<!-- END SUB: HAS_ORB -->

		<!-- SUB: HAS_CB -->
		<b>class_base methods:</b><br>
		<!-- SUB: CB_FUNCTION -->
		<a href='{VAR:view_func}'>{VAR:name}</a><br>
		<i>{VAR:short_comment}</i>
		<!-- END SUB: CB_FUNCTION -->
		<br>
		<!-- END SUB: HAS_CB -->

		<!-- SUB: HAS_OTHER -->
		<b>other public methods</b><br>
		<!-- SUB: OTHER_FUNCTION -->
		<a href='{VAR:view_func}'>{VAR:name}</a><br>
		<i>{VAR:short_comment}</i>
		<!-- END SUB: OTHER_FUNCTION -->
		<br>
		<!-- END SUB: HAS_OTHER -->

		<!-- SUB: HAS_PRIVATE -->
		<b>private methods</b><br>
		<!-- SUB: PRIVATE_FUNCTION -->
		<a href='{VAR:view_func}'>{VAR:name}</a><br>
		<i>{VAR:short_comment}</i>
		<!-- END SUB: PRIVATE_FUNCTION -->
		<br>
		<!-- END SUB: HAS_PRIVATE -->
	</td>
	<td class="text" valign="top" colspan="4">
		<b>properties:</b><br>
		<table border="0" cellpadding="1" cellspacing="0" width="100%" class="class_info">
		<tr>
			<th>Name</th>
			<th>Type</th>
			<th>Comment</th>
		</tr>
		<!-- SUB: PROP -->
		<tr>
			<td>{VAR:name}</td>
			<td>{VAR:type}</td>
			<td>{VAR:comment}</td>
		</tr>
		<!-- END SUB: PROP -->
		</table>
	</td>
</tr>
<tr>
	<td class="text" valign="top" colspan="5">
		<b>Reltypes:</b><br>
		<table border="0" cellpadding="1" cellspacing="0" width="100%" class="class_info">
			<th>Name</th>
			<th>Classes</th>
			<th>Comment</th>
		<!-- SUB: RELTYPE -->
		<tr>
			<td>{VAR:name}</td>
			<td>{VAR:clids}</td>
			<td>{VAR:comment}</td>
		</tr>
		<!-- END SUB: RELTYPE -->
		</table>
	</td>


</tr>
<tr>

	<td class="text" valign="top" colspan="5">
		<b>Database tables</b><br>
		<table border="0" cellpadding="1" cellspacing="0" width="100%" class="class_info">
			<th>Name</th>
			<th>Index</th>
			<th>Properties</th>
		<!-- SUB: TABLE -->
		<tr>
			<td>{VAR:name}</td>
			<td>{VAR:index}</td>
			<td>{VAR:properties}</td>
		</tr>
		<!-- END SUB: TABLE -->
		</table>
	</td>


</tr>
<tr>

	<td class="text" valign="top" colspan="5">
		<b>Defines:</b><br>
		<table border="0" cellpadding="1" cellspacing="0" width="100%" class="class_info">
			<th>Name</th>
			<th>Value</th>
		<!-- SUB: DEFINES -->
		<tr>
			<td>{VAR:name}</td>
			<td>{VAR:value}</td>
		</tr>
		<!-- END SUB: DEFINES -->
		</table>
		<br>
		<B>Templates, from folder {VAR:tpl_folder}:</b><br>
		<table border="0" cellpadding="1" cellspacing="0" width="100%" class="class_info">
			<th>Function</th>
			<th>Template file</th>
		<!-- SUB: TEMPLATE -->
		<tr>
			<td>{VAR:func}</td>
			<td>{VAR:tpl_file}</td>
		</tr>
		<!-- END SUB: TEMPLATE -->
		</table>
		<br>
	</td>


</tr>
</table>

<br><br><br>
<table border="0" width="100%" cellpadding="2" cellspacing="0">
<!-- SUB: LONG_FUNCTION -->
<tr>
		<td colspan="6" width="2" height="2" bgcolor="#000000"><img src='{VAR:baseurl}/automatweb/images/trans.gif'></td>
</tr>
<tr>
	<td class="text" colspan="6">
		<table border="0" width="100%">
			<tr>
				<td class="text"><a name='fn.{VAR:name}'></a><b>{VAR:proto}</b> - <a href='{VAR:view_usage}'>View usage</a> - <a href='{VAR:view_source}'>View source</a></td>
			</tr>
			<tr>
				<td class="text">
					<i>{VAR:short_comment}</i><br>
					<strong>Attributes:</strong>
					<table class="class_info">
						<tr>
							<th>Name</th>
							<th>Value</th>
						</tr>
					<!-- SUB: ATTRIB -->
						<tr>
							<td>{VAR:attrib_name}</td>
							<td>{VAR:attrib_value}</td>
						</tr>
					<!-- END SUB: ATTRIB -->
					</table>
					<strong>Parameters:</strong>
					<table class="class_info">
						<tr>
							<th>Name</th>
							<th>Required</th>
							<th>Type</th>
							<th>Comment</th>
						</tr>
					<!-- SUB: PARAM -->
						<tr>
							<td>{VAR:param_name}</td>
							<td>{VAR:param_required}</td>
							<td>{VAR:param_type}</td>
							<td>{VAR:param_comment}</td>
						</tr>
					<!-- END SUB: PARAM -->
					</table>

					<strong>Returns:</strong>
					<p class="returns">{VAR:returns}</p>
					<strong>Errors:</strong>
					<p class="errors">{VAR:errors}</p>
					<strong>Comment</strong>
					<p class="comment">{VAR:comment}</p>
					<strong>Examples:</strong>
					<p class="examples">{VAR:examples}</p>
				</td>
			</tr>
			<tr>
				<td class="text">{VAR:doc}</td>
			</tr>
		</table>
	</td>
</tr>
<!-- END SUB: LONG_FUNCTION -->
</table>
