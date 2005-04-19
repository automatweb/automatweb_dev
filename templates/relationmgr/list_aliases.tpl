<script language="Javascript">
function create_new_object()
{
var clids = new Array();
{VAR:class_ids}
	with(document.changeform)
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
	reltype = document.changeform.reltype.options[document.changeform.reltype.selectedIndex].value;
	objtype = document.changeform.aselect.value;

	window.location.href=search_url + "&reltype=" + reltype + "&aselect=" + objtype;
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
</script>
<script language= "javascript">init();</script>
<input type="hidden" name="subaction" id="subaction" value="">


