<script language="javascript">
var ops=new Array()
<!-- SUB: FORM -->
ops_{VAR:form_id} = new Array();
<!-- SUB: FORM_OP -->
ops_{VAR:form_id}[{VAR:cnt}] = new Array({VAR:op_id},"{VAR:op_name}");
<!-- END SUB: FORM_OP -->

<!-- END SUB: FORM -->

function clearList(list)
{
	var listlen = list.length;

	for(i=0; i < listlen; i++)
		list.options[0] = null;
}

function addItem(list, arr)
{
	list.options[list.length] = new Option(arr[1],""+arr[0],false,false);
}

function populate_list(el,arr)
{
	clearList(el);
	for (i = 0; i < arr.length; i++)
		addItem(el,arr[i]);
}

var sel_form;
sel_form = 0;

var cur_arr;
cur_arr = 0;

function mk_ops()
{
	if (cur_arr != sel_form)
	{
		if (eval("typeof(ops_"+sel_form+")") != "undefined")
		{
			eval("far = ops_"+sel_form);
			populate_list(q.op_id, far);
			cur_arr = sel_form;
		}
	}
}

</script>

<form method="GET" action="orb.{VAR:ext}" name='q'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2" colspan=2>Vali form:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='form_id' onChange="sel_form=this.options[this.selectedIndex].value;mk_ops();">{VAR:flist}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Vali v&auml;ljundi stiil:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='op_id'>{VAR:oplist}</select></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Edasi">
	</td>
</tr>
</table>
{VAR:reforb}
</form>

<script language="javascript">
sel_form=document.q.form_id.options[document.q.form_id.selectedIndex].value;
mk_ops();
</script>