<style type="text/css">
.awtab {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #1664B9;
background-color: #CDD5D9;
}
.awtab a {color: #1664B9; text-decoration:none;}
.awtab a:hover {color: #000000; text-decoration:none;}

.awtabdis {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #686868;
background-color: #CDD5D9;
}

.awtabsel {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #FFFFFF;
background-color: #478EB6;
}
.awtabsel a {color: #FFFFFF; text-decoration:none;}
.awtabsel a:hover {color: #000000; text-decoration:none;}

.awtabseltext {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #FFFFFF;
background-color: #478EB6;
}
.awtabseltext a {color: #FFFFFF; text-decoration:none;}
.awtabseltext a:hover {color: #000000; text-decoration:none;}

.awtablecellbackdark {
font-family: verdana, sans-serif;
font-size: 10px;
background-color: #478EB6;
}

.awtablecellbacklight {
background-color: #DAE8F0;
}

.awtableobjectid {
font-family: verdana, sans-serif;
font-size: 10px;
text-align: left;
color: #DBE8EE;
background-color: #478EB6;
}


</style>
{VAR:toolbar}
<table border="0" cellspacing="0" cellpadding="0">
<!-- SUB: tabs_L1 -->
<tr>
<td>
	<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<!-- SUB: tab_L1 -->
			<td>
			<table border=0 cellpadding=0 cellspacing=0>
			<tr><td><div style="width:1px;height:20px" /></td>
			<td valign="top" class="awtab"><div style="width:10px;height:20px" /></td>
			<td nowrap valign="middle" class="awtab"><a href="{VAR:link}">{VAR:caption}</a></td>
			<td valign="top" width="10" class="awtab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/awtab-1_nurk.gif" WIDTH="10" HEIGHT="4" BORDER=0 ALT=""></td>
			<td><div style="width:1px;height:20px" /></td>
			</tr></table>
			</td>
		<!-- END SUB: tab_L1 -->
		
		<!-- SUB: disabled_tab_L1 -->
			<td>
			<table border=0 cellpadding=0 cellspacing=0>
			<tr><td><div style="width:1px;height:20px" /></td>
			<td valign="top" class="awtab"><div style="width:10px;height:20px" /></td>
			<td nowrap valign="middle" class="awtabdis">{VAR:caption}</td>
			<td valign="top" width="10" class="awtab"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/awtab-1_nurk.gif" WIDTH="10" HEIGHT="4" BORDER=0 ALT=""></td>
			<td><div style="width:1px;height:20px" /></td>
			</tr></table>
			</td>
		<!-- END SUB: disabled_tab_L1 -->

		<!-- SUB: sel_tab_L1 -->
			<td>
			<table border=0 cellpadding=0 cellspacing=0><tr>
			<td><div style="width:1px;height:20px" /></td>
			<td valign="top" class="awtabsel"><div style="width:10px;height:20px" /></td>
			<td nowrap valign="middle" class="awtabsel"><a href="{VAR:link}">{VAR:caption}</a></td>
			<td valign="top" width="10" class="awtabsel"><IMG SRC="{VAR:baseurl}/automatweb/images/blue/awtab-1_nurk.gif" WIDTH="10" HEIGHT="4" BORDER=0 ALT=""></td>
			<td><div style="width:1px;height:20px" /></td>
			</tr></table>
			</td>
		<!-- END SUB: sel_tab_L1 -->

	</tr>
	</table>
</td>
</tr>
<!-- END SUB: tabs_L1 -->
</table>
{VAR:content}
