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
		cur_arr = sel_form;
		if (eval("typeof(ops_"+sel_form+")") != "undefined")
		{
			eval("far = ops_"+sel_form);
			populate_list(q.owner_form_op, far);
		}
		else
		{
			clearList(q.owner_form_op);
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
	<td class="fcaption2">Nimi:</td>
	<td class="fform"><input type="text" name="name" size="40" value='{VAR:name}'></td>
</tr>
<tr>
	<td class="fcaption2" valign="top">Kommentaar:</td>
	<td class="fform"><textarea name="comment" rows=5 cols=50>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Vali root kataloog:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='root'>{VAR:root}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Vali form, mille klient peab telimisel t&auml;itma:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select multiple name='order_form[]'>{VAR:of}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Vali millised nendest korduvad:</td>
</tr>
<!-- SUB: OF -->
<tr>
	<td class="fcaption2">{VAR:of_name}</td>
	<td class="fcaption2"><input type='checkbox' name='of_rep[{VAR:of_id}]' VALUE='1' {VAR:of_checked}>&nbsp;&nbsp;V&auml;ljund:&nbsp;<select name='of_op[{VAR:of_id}]'>{VAR:of_ops}</select></td>
</tr>
<!-- END SUB: OF -->
<tr>
	<td class="fcaption2" colspan=2>Komaga eraldatud e-mailiaadressid, kellele tellimus saata:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><input type='text' name='emails' size=50 value='{VAR:emails}'></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Poeomaniku rekvisiitide form:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='owner_form' onChange="sel_form=this.options[this.selectedIndex].value;mk_ops();">{VAR:forms}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>Poeomaniku rekvisiitide formi v&auml;ljund:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='owner_form_op'></select></td>
</tr>
<!-- SUB: CH_OWN -->
<tr>
	<td class="fcaption2" colspan=2><a href='{VAR:ch_own}'>Muuda poeomaniku rekvisiite</a></td>
</tr>
<!-- END SUB: CH_OWN -->

<!-- SUB: CHANGE -->
<tr>
	<td class="fcaption2" colspan=2><a href='{VAR:orders}'>Tellimused</a></td>
</tr>
<!-- END SUB: CHANGE -->
<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="Salvesta">
	</td>
</tr>
</table>
{VAR:reforb}
</form>
<script language="javascript">
sel_form={VAR:o_form_id};
mk_ops();

<!-- SUB: CHANGE2 -->

q.owner_form_op.selectedIndex = idxforvalue(q.owner_form_op,'{VAR:o_op_id}');

<!-- END SUB: CHANGE2 -->

</script>
