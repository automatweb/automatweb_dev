<form action='reforb.aw' method='POST' name='foo'>
{VAR:header}
<script language="javascript">
function Do(what,gid)
{
if (what=="uus")
{
	document.foo.gname.value=prompt("Sisesta uue grupi nimi","");
	if (document.foo.gname.value=="null")
		return;
	what="filters_newgroup";
};
if (what=="filters_rengroup")
{
	document.foo.gname.value=prompt("Sisesta grupile uus nimi","");
	if (document.foo.gname.value=="null")
		return;
};
	document.foo.action.value=what;
	document.foo.gid.value=gid;
	document.foo.submit();
};
function Do2(what,gid,fid)
{
	document.foo.action.value=what;
	document.foo.gid.value=gid;
	document.foo.gname.value=fid;
	document.foo.submit();
};
</script>
<a href="javascript:Do('uus');">Uus grupp</a>
<table border=0 cellspacing=0 cellpadding=0>
<!-- SUB: fgroup -->
<tr>
<td width=100%>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr><td colspan="10" class="title"><strong>
<a href="javascript:Do('filters_rengroup',{VAR:gid})" alt="({VAR:gid})"><font color="black">{VAR:gname}</font></a></strong>: 
<a href="javascript:Do('filters_delgroup',{VAR:gid})">Kustuta Grupp</a> |
<a href="javascript:Do('filters_new',{VAR:gid})">Uus</a> |
<a href="javascript:Do('filters_cut',{VAR:gid})">Cut</a> |
<a href="javascript:Do('filters_paste',{VAR:gid})">Paste</a> |
<a href="javascript:Do('filters_del',{VAR:gid})">Kustuta</a>|
<a href="javascript:Do('filters_export',{VAR:gid})">Ekspordi</a>|
<a href='javascript:aw_popup("{VAR:l_import}","Impordi_Filtreid",500,100);'>Impordi</a>
</td></tr>
<tr>
<td class="title" align="left"><b><a>Nimi</a></b></td>
<td class="title" align="left"><b><a>SQL</a></b></td>
<td class="title" align="center">Vali</td>
</tr>
<!-- SUB: filter -->
<tr>
<td class="fgtext" align="left" style="background:{VAR:bgcolor};"><b><a href="{VAR:l_edit}&id={VAR:fid}">{VAR:fname}</a></b></td>
<td class="fgtext" align="left" style="background:{VAR:bgcolor};"><b>{VAR:sql}</b></td>
<td class="fgtext" align="center" style="background:{VAR:bgcolor};">
<input type="checkbox" name="sel[]" value="{VAR:fid}">
<a href="javascript:Do2('filters_up',{VAR:gid},{VAR:idx})"><img border=0 alt="kõrgemale" src="/images/up_r_arr.gif"></a>
<a href="javascript:Do2('filters_down',{VAR:gid},{VAR:idx})"><img  border=0 alt="madalamale" src="/images/down_r_arr.gif"></a>
</td>
</tr>
<!-- END SUB: filter -->
</table>
</td>
</tr>
</table>
<br>
</td>
</tr>
<!-- END SUB: fgroup -->
</table>

{VAR:reforb}
</form>

