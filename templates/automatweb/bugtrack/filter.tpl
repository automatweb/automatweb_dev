<form action="reforb.{VAR:ext}" METHOD="POST" name="fr">
{VAR:reforb}
<input type="hidden" name="setfilt" id="setfilt">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr><td class="title"></td></tr>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width="100%">
<tr>
<td colspan="3" class="title">
Lisa filter
</td></tr>
<tr><td class="ftitle2" width="20%">vırdlus</td><td class="fgtext" colspan="2"><input type="radio" name="op" id="opand" value="and" checked>JA&nbsp;&nbsp;<input type="radio" name="op" id="opor" value="or">V’I</td></tr>
<tr><td class="ftitle2" width="20%">v‰li</td><td class="fgtext" colspan="2"><select class="small_button" id="fie" name="fie" onchange="onfiechange(document.forms.fr.fie.selectedIndex)">
{VAR:fieldlist}
</select></td></tr>
<tr><td class="ftitle2">avaldis</td><td class="fgtext" colspan="2">
<select class="small_button" id="expr" name="expr"></select>
</td></tr>
<tr><td class="ftitle2">v‰‰rtus</td><td class="fgtext" colspan="2">
<span id="dval">
<input class="small_button" type=text id="val" name="val">
</span>
<span id="dvalhelp" valign="top">
<select class="small_button" id="valhelp" onchange="javascript:document.forms.fr.val.value=document.forms.fr.valhelp.selectedIndex;"></select>
</span>
<span id="ddateval" valign="top">
{VAR:dedit}
</span>
</td></tr>
<tr><td class="ftitle2">
<input class="small_button" type=submit onclick="javascript:sendit();" value="lisa">
</td>
<td class="fgtext"><input class="small_button" type=submit onclick="javascript:sendclear();" value="T¸hjenda filter"></td>
<td class="fgtext"><a href="javascript:window.close();">Sulge aken</a></td>
</tr>
<tr><td colspan=3 class="fgtext">Filter={VAR:filta}</td></tr>
</table>
</td></tr>
</table>
</form>
<script language="javascript">
{VAR:sendupdate}
document.title="Lisa Filter";
exprs=new Array("=","!=","LIKE",">","<",">=","<=");
validexprs=new Array(new Array(0,1,2),new Array(0,1,3,4,5,6),new Array(0,1,3,4,5,6));
ftnames=new Array("string","number","aeg");
ftypes=new Array({VAR:ftypes});
vhelp=new Array({VAR:foptions});

function sendit()
{
document.forms.fr.setfilt.value="add";
};

function sendclear()
{
document.forms.fr.setfilt.value="clear";
};

function n2ita(m,n)
{
if (n)
{
 if (document.all)
 {
  eval("document.all."+m+".style.display='';");
 } else
 {
  eval("document.all."+m+".style.display='';");
 };
} else
{
 if (document.all)
 {
  eval("document.all."+m+".style.display='none';")
 } else
 {
  eval("document.all."+m+".style.display='none';");
 };

};
};

function onfiechange(sel)
{
l=document.forms.fr.expr.length;
for (i=0;i<l;i++)
	document.forms.fr.expr.options[0]=null;
for (i=0;i<validexprs[ftypes[sel]].length;i++)
	document.forms.fr.expr.options[i]=new Option(exprs[validexprs[ftypes[sel]][i]],exprs[validexprs[ftypes[sel]][i]]);

l=document.forms.fr.valhelp.length;
for (i=0;i<l;i++)
	document.forms.fr.valhelp.options[0]=null;
document.forms.fr.valhelp.options[0]=new Option("                    ");
for (i=0;i<vhelp[sel].length;i++)
	document.forms.fr.valhelp.options[i]=new Option(vhelp[sel][i]);

if (vhelp[sel].length>0)
{
	document.forms.fr.val.value="0" //kuna esimene on valitud
	n2ita("dvalhelp",1);
	n2ita("dval",0);
}
else
{
	document.forms.fr.val.value="kirjuta "+ftnames[ftypes[sel]];
	n2ita("dvalhelp",0);
	n2ita("dval",1);
};

if (ftypes[sel]==2)
{
 n2ita("ddateval",1);
 n2ita("dval",0);
 document.forms.fr.val.value="_date";
} else
{
 n2ita("ddateval",0);
};
};

onfiechange(0);
</script>