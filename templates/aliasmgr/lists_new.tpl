<table width="100%" cellspacing="0" cellpadding="0" border="0">
<form name="foo" method="POST">
<tr><td class="tableborder">
	{VAR:toolbar}
</td>
</tr>
</form>
</table>

<script language="Javascript">
var chk_status = true;
var chlinks = new Array();
{VAR:chlinks}

function selall()
{
	len = document.aform.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.aform.elements[i].name.indexOf("check") != -1)
		{
			document.aform.elements[i].checked=chk_status;
		}
	}
	chk_status = !chk_status;
	return false;
}

function create_new_object()
{
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
			window.location.href="orb.{VAR:ext}?class="+cl+"&action=new&parent={VAR:parent}&period={VAR:period}&alias_to={VAR:id}&return_url={VAR:return_url}&reltype=" + rel_type;
		};
	};
};

function search_for_object()
{
	var search_url = "{VAR:search_url}";
	reltype = document.foo.reltype.options[document.foo.reltype.selectedIndex].value;
	window.location.href=search_url + "&reltype=" + reltype;
}

function awchange()
{
	len = document.aform.elements.length;
	cnt = 0;
	chk = 0;
	for (i = 0; i < len; i++)
	{
		with(document.aform.elements[i])
		{
			if (type == "checkbox" && name.indexOf("check") != -1)
			{
				if (checked)
				{
					cnt++;
					chk = value;
				}	
			}
		}
	};
	if (cnt == 1)
	{
		window.location.href = chlinks[chk];
	}
	else
	{
		alert('Palun valige 1 objekt muutmiseks');
	};
}

function awdelete()
{
	len = document.aform.elements.length;
	idx = 0;
	for (i = 0; i < len; i++)
	{
		with(document.aform.elements[i])
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
			document.aform.subaction.value = 'delete';
			document.aform.submit();
		};
	}
	else
	{
		alert('Vali kustutatavad objektid.');
	}
}

function saveform()
{
	document.aform.submit();
}
</script>

<table width="100%" border=0 cellspacing=0 cellpadding=0>
<tr>
<td colspan="2" class="title">
</td>
</form>
</tr>
<form name="aform" action="reforb.{VAR:ext}">
{VAR:table}
{VAR:reforb}
</form>
</table>

<script type="text/javascript">
// here I have to check whether this has been loaded in an iframe, and if so, resize the height 
// in the parent accordingly. Ugh, is this possible at all?
// alert(window.parent.frames["aliasmgr"].innerHeight);

</script>
