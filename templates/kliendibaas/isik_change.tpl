<script language='javascript'>

function put_value(target,value)
{
	if (target == "linn")
		document.add.elements["isik[linn]"].value = value;

} 

function pop_select(url)
{
	aken=window.open(url,"selector","HEIGHT=300,WIDTH=310,TOP=400,LEFT=500")
 	aken.focus()
}
</script>

<form action='reforb.{VAR:ext}' method=post name="add">
<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
	<tr>
		<td class="tableborder">
			<!--tabelshadow-->
			<table width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td width="1" class="tableshadow"><IMG SRC="images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td>
					<td class="tableshadow"><IMG SRC="images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br />
						<!--tabelsisu-->
						<table width="100%" cellspacing="0" cellpadding="0">
							<tr>
								<td class="tableinside" height="29">
									<table border="0" cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td width="5"><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>
											<td>
														{VAR:toolbar}
											</td>
										</tr>
									</table>
									<table class="aste01" cellpadding=3 cellspacing=1 border=0>
										<tr>
											<td class="celltext">
											<fieldset><legend>Isiku andmed</legend>
											<table class="celltext" border=1>
<!-- SUB: textbox -->
<tr><td>{VAR:desc}:</td><td> <input class=formtext type=text name="{VAR:name}" value="{VAR:value}" size="{VAR:size}" maxlength="{VAR:maxlength}"><br /></td></tr>
<!-- END SUB: textbox -->
											{VAR:form}

											</table>
											</fieldset>
											</td>
										</tr>
									</table>

												
												

								
								
								
								kommentaarid: <textarea name="comment" rows=3 cols=40  class="formtext">{VAR:comment}</textarea>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
[{VAR:abx}]

{VAR:reforb}
</form>
<!--<iframe name=vali width="100%" height="400" frameborder="1" src="">whee</iframe>-->