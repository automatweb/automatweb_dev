<form id="fr">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr><td class="title"></td></tr>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="2" width="100%">
<tr>
<td colspan="3" class="title">
Lisa filter
</td></tr>
<tr><td class="ftitle2" width="20%">võrdlus</td><td class="fgtext" colspan="2"><input type="radio" name="op" id="opand" value="and" checked>JA&nbsp;&nbsp;<input type="radio" name="op" id="opor" value="or">VÕI</td></tr>
<tr><td class="ftitle2" width="20%">väli</td><td class="fgtext" colspan="2"><select class="small_button" id="fie" onchange="onfiechange(document.forms.fr.fie.selectedIndex)">
{VAR:fieldlist}
</select></td></tr>
<tr><td class="ftitle2">avaldis</td><td class="fgtext" colspan="2"><select class="small_button" id="expr"></select></td></tr>
<tr><td class="ftitle2">väärtus</td><td class="fgtext"><input class="small_button" type=text id="val"></td>
<td class="fgtext" align="right" width="1"><select class="small_button" id="valhelp" onchange="javascript:document.forms.fr.val.value=document.forms.fr.valhelp.selectedIndex;"></select></td></tr>
<tr><td class="ftitle2">
<input class="small_button" type=button onclick="javascript:sendit();" value="lisa">
</td>
<td class="fgtext"><a href="javascript:sendclear();">Tühjenda filter</a></td>
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
validexprs=new Array(new Array(0,1,2),new Array(0,1,3,4,5,6));
ftnames=new Array("string","number");
ftypes=new Array({VAR:ftypes});
vhelp=new Array({VAR:foptions});

function sendit()
{
if (document.forms.fr.opand.checked)
	opp="and";
else opp="or";
window.location='{VAR:sendupdateurl}&setfilt=add&field='+document.forms.fr.fie.value+'&expr='+document.forms.fr.expr.value+'&value='+document.forms.fr.val.value+'&op='+opp;
};

function sendclear()
{
window.location='{VAR:clearurl}';
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
	document.forms.fr.val.value="0" //kuna esimene on valitud
else
	document.forms.fr.val.value="kirjuta "+ftnames[ftypes[sel]];

};

onfiechange(0);
</script>