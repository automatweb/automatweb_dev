<form action='reforb.{VAR:ext}' METHOD=POST NAME='foo'>
<script Language="JavaScript">

function Do(what)
{
foo.action.value=what;
foo.submit();
};

function Liiguta()
{
tere=foo.listsel.value;
a=tere.indexOf(":");
list=tere.substr(0,a);
foo.target.value=list;
group=tere.substr(a+1,tere.length-a-1);
foo.lgroup.value=group;
if (group=="n")
{
	foo.newgroupname.value=prompt("Sisesta uue grupi nimi:","");
};
foo.action.value="lf_movemembers";
foo.submit();
};

</script>
<INPUT TYPE="HIDDEN" NAME="newgroupname">
<INPUT TYPE="HIDDEN" NAME="target">
<INPUT TYPE="HIDDEN" NAME="lgroup">
{VAR:table}
{VAR:reforb}
</form>