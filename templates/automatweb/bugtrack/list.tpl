<!-- SUB: fgroup -->
<select name="fgroup{VAR:gid}" OnChange="DoFilter({VAR:gid})" class="small_button">
<option value='0' {VAR:sel}>{VAR:gname}</option>
<!-- SUB: filter -->
<option value='{VAR:fid}' {VAR:sel}>{VAR:fname}</option>
<!-- END SUB: filter -->
</select>
<!-- END SUB: fgroup -->

<form action='reforb.{VAR:ext}' method='POST' name='foo'>
<script language="javascript">
sel1_="";
function Do(what)
{
	document.foo.action.value=what;
	document.foo.submit();
};
function DoDelegate()
{
	if (document.foo.sel1 && sel1_)
		{remote(0,300,200,'{VAR:l_delegate}&id='+sel1_);}
		else alert('Midagi pole valitud!');
};
function DoFilter(what)
{
	eval("what=document.foo.fgroup"+what+".value");
	window.location="{VAR:l_setfilter}&_setfilter=1&setfilter="+what;
};
</script>
{VAR:table}
{VAR:reforb}
</form>