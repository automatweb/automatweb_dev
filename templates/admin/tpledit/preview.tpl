<script language="javascript">
function edit_template()
{
	window.location = '{VAR:self}';
};

function add_dynamic() {
	toolbar = 0;
	width = 500;
	height = 600;
	file = "popup.html";
        self.name = "tpledit";
	 var wprops = "toolbar=" + toolbar + ",location=0,directories=0,status=1, "+
	        "menubar=0,scrollbars=1,resizable=1,width=" + width + ",height=" + height;
	        openwindow = window.open(file,"remote",wprops);
};

function add_static()
{
	alert("Not yet implemented");
};

</script>
<div class="pealkiri1">
Template: 
</div>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">
<table bgcolor="#FFFFFF" border="0" cellspacing="1" cellpadding="0" width=100%>
<tr>
	<td colspan="6" class="fgtitle"><b>Template eelvaade</b>
	| <a href="javascript:edit_template()">Muuda templatet</a>
	| <a href="javascript:add_dynamic()">Lisa dünaamiline</a>
	| <a href="javascript:add_static()">Lisa staatiline</a>
	</td>
</tr>
</table>
<table border="0" cellspacing="0" cellpadding="0" width=100% bgcolor="#FFFFFF">
<tr>
<td>
<table border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
<form>

<!-- SUB: line -->
{VAR:content}
<!-- END SUB: line -->

</form>
</table>

</td>
</tr>
</table>
</td>
</tr>
</table>
