<table width="100%" cellspacing="0" cellpadding="0" border="0">
<form name="foo" method="POST" id="foo">
<tr><td class="tableborder">
	{VAR:toolbar}
</td>
</tr>
</form>
</table>

<script language="Javascript">
var chk_status = true;
function selall()
{
	len = document.changeform.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.changeform.elements[i].name.indexOf("check") != -1)
		{
			document.changeform.elements[i].checked=chk_status;
		}
	}
	chk_status = !chk_status;
	return false;
}

function create_new_object()
{
var clids = new Array();
{VAR:class_ids}
	with(document.foo)
	{
		cl = aselect.options[aselect.selectedIndex].value;
		if (cl == "capt_new_object")
		{
			alert('Vali objekti tüüp!');
		}
		else
		{
			rel_type = reltype.options[reltype.selectedIndex].value;
			window.location.href="orb.{VAR:ext}?class=" + clids[cl] + "&action=new&parent={VAR:parent}&period={VAR:period}&alias_to={VAR:id}&return_url={VAR:return_url}&reltype=" + rel_type;
		};
	};
};

function search_for_object()
{
	var search_url = "{VAR:search_url}";
	reltype = document.foo.reltype.options[document.foo.reltype.selectedIndex].value;
	objtype = document.foo.aselect.value;

	window.location.href=search_url + "&reltype=" + reltype + "&objtype=" + objtype;
}

function awdelete()
{
	len = document.changeform.elements.length;
	idx = 0;
	for (i = 0; i < len; i++)
	{
		with(document.changeform.elements[i])
		{
			if (type == "checkbox" && name.indexOf("check") != -1 )
			{
				if (checked)
				{
					idx++;
				};
			}
		}
	};

	if (idx > 0)
	{
		if (confirm('Kustutada need ' + idx + ' aliast?'))
		{
			document.changeform.subaction.value = 'delete';
			document.changeform.submit();
		};
	}
	else
	{
		alert('Vali kustutatavad objektid.');
	}
}

function saveform()
{
	document.changeform.submit();
}

</script>

<table width="100%" border=0 cellspacing=0 cellpadding=0>
<tr>
<td colspan="2" class="title">
</td>
</form>
</tr>
<form name="changeform" action="reforb.{VAR:ext}">
{VAR:table}
{VAR:reforb}
</form>
</table>
<script language= "javascript">
init();
</script>

