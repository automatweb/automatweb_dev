<html>
<head>
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/aw.css">
<script type="text/javascript">
function showhide()
{
        el = document.getElementById('clock');
        old = el.innerHTML;
        dt = new Date();
        newstr = zeropad(dt.getHours().toString()).concat(old.indexOf(":")>0?" ":":",zeropad(dt.getMinutes().toString()));
        el.innerHTML = newstr;
}

function zeropad(str)
{
        return str.length == 1 ? 0 + str : str;
}
</script>
</head>
<body bgcolor="#eeeeee" onload="setInterval('showhide()',1000)">
<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td align="left" class="yah">&nbsp;{VAR:uid} @ <span id="clock" style="font-family:monospace;"></span></td>
	</tr>
	<!-- SUB: has_toolbar -->
	<tr>
		<form action='orb.{VAR:ext}' method='get' name='pform'>
		<td>{VAR:toolbar}</td>
		</form>
	</tr>
	<!-- END SUB: has_toolbar -->
</table>

{VAR:TREE}

</html>
