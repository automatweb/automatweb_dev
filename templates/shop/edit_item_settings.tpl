<script language="Javascript">
<!--
function remote(toolbar,width,height,file) {
	self.name = "root";
	var wprops = "toolbar=" + toolbar + ",location=0,directories=0,status=0, "+
	"menubar=0,scrollbars=1,resizable=1,width=" + width + ",height=" + height;
	openwindow = window.open(file,"remote",wprops);
}

function box2(caption,url){
var answer=confirm(caption)
if (answer)
window.location=url
}
// -->
</script>

<form action='reforb.{VAR:ext}' method=POST>
<table border="0" cellspacing="1" cellpadding="2" bgcolor="#CCCCCC" >
	<tr>
		<td class="fcaption2">Kogusega artikkel</td>
		<td class="fcaption2"><input type='checkbox' name='has_max' value=1 {VAR:has_max}></td>
		<td class="fcaption2"><input size=3 type='text' name='max_items' VALUE='{VAR:max_items}'> artiklit kokku</td>
	</tr>
	<tr>
		<td class="fcaption2">Perioodiline artikkel</td>
		<td class="fcaption2"><input type='checkbox' name='has_period' value=1 {VAR:has_period}></td>
		<td class="fcaption2"><input type='text' name='per_cnt' class='small_button' size=3 VALUE='{VAR:per_cnt}'> kordust kokku. <a href='javascript:remote("no",500,500,"{VAR:sel_period}")'>Vali periood</a></td>
	</tr>
	<tr>
		<td class="fcaption2">Perioodi algus</td>
		<td class="fcaption2" colspan=2>{VAR:per_from}</td>
	</tr>
	<tr>
		<td class="fcaption2">Igale artiklile oma kalender:</td>
		<td class="fcaption2" colspan=2><input type='checkbox' name='has_objs' value=1 {VAR:has_objs}></td>
	</tr>
	<tr>
		<td class="fcaption2">Koguse vorm:</td>
		<td class="fcaption2" colspan=2>{VAR:cnt_form_name}</td>
	</tr>
	<tr>
		<td class="fcaption2">Hinna arvutamise valem:</td>
		<td class="fcaption2" colspan=2>{VAR:price_eq}</td>
	</tr>
	<tr>
		<td class="fcaption2">Koguse vorm:</td>
		<td class="fcaption2" colspan=2><select name='sel_cnt_form'>{VAR:cnt_form}</select></td>
	</tr>
	<tr>
		<td class="fcaption2">Hinna arvutamise valem:</td>
		<td class="fcaption2" colspan=2><select name='sel_formula'>{VAR:item_eq}</select></td>
	</tr>
</table>
{VAR:reforb}
</form>
