form = document.forms.changeform
var len = form.elements.length
var users = 0
var elname = "sel"
function check_delete()
{
	for(i = 0; i < len; i++)
	{
		if (form.elements[i].name.indexOf(elname) != -1)
		{
			if(form.elements[i].checked)
			{
				tmp = form.elements[i].name.split("[")
				tmp = tmp[1].split("]")
				num = tmp[0]
				if(oids[num])
				{
					users += 1
				}
			}
		}
	}
	if(users>0)
	{
		var confm = confirm("NB! kasutaja kustutamisel ei ole v�imalik seda taastada, vaid tuleb luua uus, samanimeline kasutaja.")
		if(confm)
		{
			submit_changeform("delete_bms")
		}
	}
	else
	{
		submit_changeform("delete_bms")
	}
}