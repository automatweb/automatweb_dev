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
var sel_form_cnt;
sel_form_cnt = 0;

var cur_arr;
cur_arr = 0;
var cur_arr_cnt;
cur_arr_cnt = 0;

function mk_ops()
{
	if (cur_arr != sel_form)
	{
		cur_arr = sel_form;
		if (eval("typeof(ops_"+sel_form+")") != "undefined")
		{
			eval("far = ops_"+sel_form);
			populate_list(q.op_id, far);
			populate_list(q.op_id_l, far);
			populate_list(q.op_id_cart, far);
		}
		else
		{
			clearList(q.op_id);
			clearList(q.op_id_l);
			clearList(q.op_id_cart);
		}
	}
}

function mk_ops_cnt()
{
	if (cur_arr_cnt != sel_form_cnt)
	{
		cur_arr_cnt = sel_form_cnt;
		if (eval("typeof(ops_"+sel_form_cnt+")") != "undefined")
		{
			eval("far = ops_"+sel_form_cnt);
			populate_list(q.cnt_form_op, far);
		}
		else
		{
			clearList(q.cnt_form_op);
		}
	}
}

function idxforvalue(el,val)
{
	for (i=0; i < el.options.length; i++)
	{
		if (el.options[i].value == val)
		{
			return i;
		}
	}
	return 0;
}
</script>

<form method="POST" action="reforb.{VAR:ext}" name='q'>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC">
<tr>
	<td class="fcaption2">T&uuml;&uuml;bi nimi:</td>
	<td class="fcaption2"><input type='text' name='name' value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Vali form:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='form_id' onChange="sel_form=this.options[this.selectedIndex].value;mk_ops();">{VAR:flist}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Vali v&auml;ljundi stiil nimekirjas:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='op_id'>{VAR:oplist}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Vali v&auml;ljundi stiil pikk:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='op_id_l'>{VAR:oplist}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Vali v&auml;ljundi stiil korvis:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='op_id_cart'>{VAR:oplist}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Vali kauba koguse valimise form:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='cnt_form' onChange="sel_form_cnt=this.options[this.selectedIndex].value;mk_ops_cnt();" >{VAR:flist}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Vali kauba koguse valimise formi v&auml;ljund ostuajaloos:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='cnt_form_op'>{VAR:oplist}</select></td>
</tr>
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Edasi">
	</td>
</tr>
</table>
{VAR:reforb}
</form>

<script language="javascript">
sel_form={VAR:form_id};
mk_ops();
sel_form_cnt={VAR:cnt_form_id};
mk_ops_cnt();
<!-- SUB: CHANGE -->
q.form_id.selectedIndex = idxforvalue(q.form_id,{VAR:form_id});
q.cnt_form.selectedIndex = idxforvalue(q.cnt_form,{VAR:cnt_form_id});
q.op_id.selectedIndex = idxforvalue(q.op_id,{VAR:op_id});
q.op_id_l.selectedIndex = idxforvalue(q.op_id_l,{VAR:op_id_l});
q.op_id_cart.selectedIndex = idxforvalue(q.op_id_cart,{VAR:op_id_cart});
q.cnt_form_op.selectedIndex = idxforvalue(q.cnt_form_op,{VAR:cnt_op_id});
<!-- END SUB: CHANGE -->
</script>