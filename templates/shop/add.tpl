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
			populate_list(q.owner_form_op_voucher, far);
			populate_list(q.owner_form_op_issued, far);
		}
		else
		{
			clearList(q.owner_form_op);
			clearList(q.owner_form_op_voucher);
			clearList(q.owner_form_op_issued);
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
	<td class="fcaption2">{VAR:LC_SHOP_NAME1}:</td>
	<td class="fform"><input type="text" name="name" size="40" value='{VAR:name}'></td>
</tr>
<!-- SUB: CHANGE -->
<tr>
	<td class="fcaption2" colspan=2><a href='{VAR:orders}'><b><font size=3>{VAR:LC_SHOP_ORDERS}</font></b></a></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><a href='{VAR:tables}'>{VAR:LC_SHOP_BILL_TABLE}</a></td>
</tr>
<!-- END SUB: CHANGE -->

<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_EMAIL_TOYOU}:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><input type='text' name='emails' size=50 value='{VAR:emails}'></td>
</tr>


<tr>
	<td class="fcaption2" valign="top">{VAR:LC_SHOP_COMM}:</td>
	<td class="fform"><textarea name="comment" rows=5 cols=50>{VAR:comment}</textarea></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_CHOOSE_ROOT}:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='root'>{VAR:root}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_CLIENT_FORM}</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select multiple name='order_form[]'>{VAR:of}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_CHOOSE_ELSE}:</td>
</tr>
<!-- SUB: OF -->
<tr>
	<td class="fcaption2">{VAR:of_name}</td>
	<td class="fcaption2"><input type='checkbox' name='of_rep[{VAR:of_id}]' VALUE='1' {VAR:of_checked}>&nbsp;&nbsp;Output:&nbsp;<select name='of_op[{VAR:of_id}]'>{VAR:of_ops}</select></td>
</tr>
<!-- END SUB: OF -->
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_OWNER_FORM}:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='owner_form' onChange="sel_form=this.options[this.selectedIndex].value;mk_ops();">{VAR:forms}</select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_OWNER_FORM_OUTPUT}:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='owner_form_op'></select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_OWNER_FORM_VOU}:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='owner_form_op_voucher'></select></td>
</tr>
<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_OWNER_FORM_ISSU}:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='owner_form_op_issued'></select></td>
</tr>
<!-- SUB: CH_OWN -->
<tr>
	<td class="fcaption2" colspan=2><a href='{VAR:ch_own}'>{VAR:LC_SHOP_PROPERTY}</a></td>
</tr>
<!-- END SUB: CH_OWN -->

<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_COMMISSION_EQ}:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='commission_eq'>{VAR:commission_eq}</select></td>
</tr>

<tr>
	<td class="fcaption2" colspan=2>{VAR:LC_SHOP_DEFAULT_CUR}:</td>
</tr>
<tr>
	<td class="fcaption2" colspan=2><select name='def_cur'>{VAR:def_cur}</select></td>
</tr>

<tr>
	<td class="fform" align="center" colspan="2"><input type="submit" value="{VAR:LC_SHOP_SAVE}">
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
q.owner_form_op_voucher.selectedIndex = idxforvalue(q.owner_form_op_voucher,'{VAR:o_op_id_voucher}');
q.owner_form_op_issued.selectedIndex = idxforvalue(q.owner_form_op_issued,'{VAR:o_op_id_issued}');

<!-- END SUB: CHANGE2 -->

</script>
