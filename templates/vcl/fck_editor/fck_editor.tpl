<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/fckeditor/2.6/fckeditor.js"></script>
<script type="text/javascript">
<!--

changed = 0;
function set_changed()
{
changed = 1;
}

function generic_loader()
{
	// set onchange event handlers for all form elements
	var els = document.changeform.elements;
	var cnt = els.length;
	for(var i = 0; i < cnt; i++)
	{
		if (els[i].attachEvent)
		{
			els[i].attachEvent("onChange",set_changed);
		}
		else
		{
			els[i].setAttribute("onChange",els[i].getAttribute("onChange")+ ";set_changed();");
		}
	}
}

function generic_unloader()
{
	if (changed)
	{
		if (confirm("{VAR:msg_leave}"))
		{
			document.changeform.submit();
			return false;
		}
	}
} 

function FCKeditor_OnComplete( editorInstance )
{
	var browser = navigator.userAgent.toLowerCase();
	var int_content_length_original = editorInstance.EditorDocument.body.innerHTML.length;
	var bool_changed=false;
	
	if (browser.indexOf('msie')>0)	{
		editorInstance.Events.AttachEvent( 'OnSelectionChange', FCKeditor_OnChange ) ;
		editorInstance.EditorDocument.attachEvent( 'onkeyup', FCKeditor_OnChange ) ;
		editorInstance.EditorDocument.attachEvent( 'onkeydown', FCKeditor_OnChange ) ;
	} 
	else 
	{
		editorInstance.Events.AttachEvent( 'OnSelectionChange', FCKeditor_OnChange ) ;
		editorInstance.EditorDocument.addEventListener( 'keyup', FCKeditor_OnChange, true ) ;
		editorInstance.EditorDocument.addEventListener( 'keydown', FCKeditor_OnChange, true ) ;
	}
	
	function FCKeditor_OnChange(  )
	{
		if (int_content_length_original!=editorInstance.EditorDocument.body.innerHTML.length && bool_changed == false)
		{
			bool_changed = true;
			set_changed();
		}
	}
}
oldload = window.onload;
window.onload = function()
{
	<!-- SUB: EDITOR -->
	var fck{VAR:name} = new FCKeditor("{VAR:name}");
	fck{VAR:name}.BasePath = "/automatweb/js/fckeditor/2.6/";
	fck{VAR:name}.Width = "{VAR:width}";
	fck{VAR:name}.Height = "{VAR:height}";
	fck{VAR:name}.Config["AutoDetectLanguage"] = false;
	fck{VAR:name}.Config["DefaultLanguage"] = "{VAR:lang}";
	fck{VAR:name}.ReplaceTextarea();
	fck{VAR:name}.Config["CustomConfigurationsPath"] = "/automatweb/orb.aw?class=fck_editor&action=get_fck_config" + ( new Date() * 1 ) ;
	<!-- END SUB: EDITOR -->
	
 	if (oldload)
	{
		oldload();
	}
}

-->
</script>
