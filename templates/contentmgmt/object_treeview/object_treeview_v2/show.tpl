<form name="objlist" action="{VAR:baseurl}/orb.{VAR:ext}" method="POST">

<script src="{VAR:baseurl}/automatweb/js/popup_menu.js" type="text/javascript"></script>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/obj_tree.css" />


<style type='text/css'>
.fgtext_bad {
font-family: Verdana, Arial, sans-serif;
font-size: 11px;
color: #000000;
text-decoration: none;
}
.fgtext_bad a {color: #006FC5; text-decoration:underline;}
.fgtext_bad a:hover {color: #04BDE3; text-decoration:underline;}



</style>

<!-- SUB: FOLDERS -->

<!-- END SUB: FOLDERS -->
<table border="0" width="100%" cellpadding="3" cellspacing="0">
	<tr bgcolor="{VAR:header_bgcolor}">

		<!-- SUB: HEADER -->
		<td class="{VAR:header_css_class}">{VAR:h_text}</td>
		<!-- END SUB: HEADER -->

	</tr>
	<!-- SUB: FILE -->
	<tr bgcolor="{VAR:bgcolor}">

		<!-- SUB: COLUMN -->
		<td class="{VAR:css_class}">{VAR:content}</td>
		<!-- END SUB: COLUMN -->
	</tr>
	<!-- END SUB: FILE -->
	<!-- SUB: FILE_GROUP -->
	<tr bgcolor="{VAR:group_bgcolor}">
		<td class="{VAR:group_css_class}" colspan="{VAR:cols_count}">{VAR:content}</td>
	</tr>
	<!-- END SUB: FILE_GROUP -->
</table>
<center>
<!-- SUB: ALPHABET -->
<a href="{VAR:char_url}">{VAR:char}</a>&nbsp;&nbsp; 
<!-- END SUB: ALPHABET -->
</center>
{VAR:reforb}

</form>
