<!-- SUB: writer -->
<script type="text/javascript">
// default to the first area on page
var sel_el = "{VAR:name}_edit";
function write_editor(el_name,width,height)
{
	val = document.forms['changeform'].elements[el_name].value;
        if (browser.isIE5up || browser.has_midas)
	{
                write_rte(el_name,width*10,height*10);
        }
        else
	{
                //other browser
                write_default(el_name,width,height,val)
        }
};

function write_rte(el_name,width,height)
{
	realname = el_name + '_edit';
	tb = document.getElementById('rte_toolbar').innerHTML;
	document.writeln('<div id="'+realname+'toolbar" style="width: 600px; text-align: left; vertical-align: middle; height: 25px; overflow: hidden;">');
	document.writeln(tb);
	document.writeln('</div>');
	document.writeln('<iframe onFocus="sel_el=\''+realname+'\';" class="rtebox" id="'+realname+'" width="'+width+'px" height="'+height+'px" frameborder="1"></iframe>');
	document.writeln('<script>setTimeout("enable_design_mode(\''+el_name+'\')",10); <' + '/script>');
	
};

function write_default(el_name,width,height,val)
{
	document.writeln('<textarea name="' + el_name + '" id="' + el_name + '" cols="'+width+'" rows="'+height+'">'+val+'</textarea>');
};

function activate_editor(frm)
{
	sel_el = frm.target.aw_owner_frame;
}

function deactivate_editor(frm)
{

}

// Setting document.designMode must NOT be done in the script section of the head. We suggest the onLoad
// function for the body where the iframe is contained.
//	 Mozilla docs

function enable_design_mode(frm)
{
	val = document.forms['changeform'].elements[frm].value;

	// iframe v‰‰rtustamisele on tegelikult veel 1 alternatiiv .. anda iframele lihtsalt
	// source .. ja siis ta muutub ju automaatselt muudetavaks contentEditable seadmisega
	// see peaks pealegi oluliselt veakindlam olema .. kuigi ehk muudab mında asja
	// aeglasemaks.

	// I like that approach a LOT more than the current one with hidden form elements
	// and what else


	// but, right now I need to solve the saving problem, so that this rte can actually
	// start being useful

	victim = frm+'_edit';

        if (browser.isIE5up) {
		realvictim = frames(victim).document;
                frames(victim).document.designMode = "On";
		frames(victim).document.write("<body style='border: 1px; margin: 1px;'>");
		frames(victim).document.write(val);
		frames(victim).document.close();
		frames(victim).document.aw_owner_frame = victim;
        }
        else {
		realvictim = document.getElementById(victim);
                realvictim.contentDocument.designMode = "on"
		realvictim.contentWindow.document.write("<body style='border: 1px; margin: 1px;'>");
                realvictim.contentWindow.document.write(val);
                realvictim.contentWindow.document.close();

                realvictim.contentWindow.document.aw_owner_frame = victim;
                realvictim.contentWindow.document.addEventListener("focus",activate_editor,true);
        }

};

function format_selection(huh)
{
	option = "";

	victim = document.getElementById(sel_el).contentWindow;

	victim.focus();
	victim.document.execCommand(huh, false, option);
	victim.focus();

}

</script>

<style>
.rtebox {
	border: 1px solid gray;
	margin: 1px;
	padding: 1px;
}
</style>

<!-- END SUB: writer -->


<!-- SUB: toolbar -->
<div id="rte_toolbar" style="visibility: hidden; height:1; width: 0; overflow:hidden">
	<input type="image" src="{VAR:baseurl}/automatweb/images/editor/post_button_bold.gif" onClick="format_selection('bold'); return false;">
	<input type="image" src="{VAR:baseurl}/automatweb/images/editor/post_button_italic.gif" onClick="format_selection('italic'); return false;">
	<input type="image" src="{VAR:baseurl}/automatweb/images/editor/post_button_underline.gif" onClick="format_selection('underline'); return false;">
	&nbsp;
	<input type="image" src="{VAR:baseurl}/automatweb/images/editor/post_button_left_just.gif" onClick="format_selection('justifyleft'); return false;">
	<input type="image" src="{VAR:baseurl}/automatweb/images/editor/post_button_centre.gif" onClick="format_selection('justifycenter'); return false;">
	<input type="image" src="{VAR:baseurl}/automatweb/images/editor/post_button_right_just.gif" onClick="format_selection('justifyright'); return false;">
	&nbsp;
	<input type="image" src="{VAR:baseurl}/automatweb/images/editor/post_button_numbered_list.gif" onClick="format_selection('insertorderedlist'); return false;">
	<input type="image" src="{VAR:baseurl}/automatweb/images/editor/post_button_list.gif" onClick="format_selection('insertunorderedlist'); return false;">
	&nbsp;
	<input type="image" src="{VAR:baseurl}/automatweb/images/editor/post_button_outdent.gif" onClick="format_selection('outdent'); return false;">
	<input type="image" src="{VAR:baseurl}/automatweb/images/editor/post_button_indent.gif" onClick="format_selection('indent'); return false;">
</div>
<!-- END SUB: toolbar -->

<!-- SUB: field -->
<input type="hidden" name="{VAR:name}" value="{VAR:value}">
<script type="text/javascript">
write_editor('{VAR:name}','{VAR:width}','{VAR:height}');
</script>
<!-- END SUB: field -->
