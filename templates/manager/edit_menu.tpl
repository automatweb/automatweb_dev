<html>
<head>
<title></title>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/aw.css">
<script src="{VAR:baseurl}/automatweb/js/mm.js"></script>
</head>
<body bgcolor="#eeeeee" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<table border=0 width="100%" cellspacing="0" cellpadding="2">
<tr>
<td align="left" class="yah">&nbsp;
<!-- SUB: YAH -->
<a href='{VAR:yah_link}'>{VAR:yah_name}</a> / 
<!-- END SUB: YAH -->
</td>
</tr>
</table>




<table border="0" cellpadding=2 cellspacing=0>
<form method="post" action="reforb.{VAR:ext}">
<tr>
<td clasS="celltext">{VAR:LC_MANAGER_NAME}</td>
<td><input type="text" name="name" size="40" value="{VAR:name}" class="formtext"></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><input type="submit" value="{VAR:LC_MANAGER_SAVE}" class="formbutton"></td>
{VAR:reforb}
</tr>
</form>
</table>


</body>
</html>
