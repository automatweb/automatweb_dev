<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/fckeditor/{VAR:fck_version}/fckeditor.js"></script>
<script type="text/javascript">
<!--
var document_form = new Array();
var document_form_original = new Array();
var changed = false;

/*
 * here we set change listeners
 */
function FCKeditor_OnComplete( editorInstance )
{
	if ($.browser.msie)	{
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
	
	document_form_original[editorInstance.Name] = editorInstance.EditorDocument.body.innerHTML;
	
	function FCKeditor_OnChange()
	{
		document_form[editorInstance.Name] = editorInstance.GetHTML();
		if (document_form_original[editorInstance.Name].length != document_form[editorInstance.Name].length)
		{
			set_changed();
		}
	}
}

function FCKeditor_CreateEditor(name, version, width, height, lang)
{
	var oFCKeditor = new FCKeditor(name);
	oFCKeditor.BasePath = "/automatweb/js/fckeditor/"+version+"/";
	oFCKeditor.Width = width;
	oFCKeditor.Height = height;
	oFCKeditor.Config["AutoDetectLanguage"] = false;
	oFCKeditor.Config["DefaultLanguage"] = lang;
	oFCKeditor.ReplaceTextarea();
	oFCKeditor.Config["CustomConfigurationsPath"] = "/automatweb/orb.aw?class=fck_editor&action=get_fck_config" + ( new Date() * 1 ) ;
}


/*
 * prototype for array
 */
function serializeArray (arr) {
	s_out = "";
	a_keys = new Array();
	
	for (key in arr)
	{
		a_keys[a_keys.length] = key;
	}
	
	for(i=0;i<a_keys.length-1;i++)
	{
		key = a_keys[i];
		s_out += encodeURIComponent(key)+"="+encodeURIComponent(this[key])+"&";
	}
	s_out = s_out.substr(0, s_out.length-1);
	return s_out;
}

/*
 * if executed, content has been modified
 */
function set_changed()
{
	changed = true;
}

if ($.browser.opera && jQuery.browser.version>="9.50")
{	
	// don't really know what i need for new opera here... nothing seems to work
}
else
{
	$(window).unload( function () {
		unloadHandler ();
	});
}

/*
 * executed after leaving page if content has changed and not saved
 */
function unloadHandler()
{
	if(changed)
	{
		var prompt = "{VAR:msg_leave}";
		if(confirm(prompt))
		{
			 $.ajax({
				type: "POST",
				url: "orb.aw",
				data: $("form").serialize()+"&"+serializeArray(document_form)+"&posted_by_js=1",
				async: false,
				success: function(msg){
				 //alert( "Data Saved: " + msg );
				},
				error: function(msg){
					alert( "{VAR:msg_leave_error}");
				}

			});
		}
	}
}

<!-- SUB: EDITOR_FCK -->
FCKeditor_CreateEditor("{VAR:name}", "{VAR:fck_version}", "{VAR:width}", "{VAR:height}", "{VAR:lang}")
<!-- END SUB: EDITOR_FCK -->

<!-- SUB: EDITOR_ONDEMAND -->
$("#{VAR:name}").css("width", "{VAR:width}").click(function(){
	FCKeditor_CreateEditor("{VAR:name}", "{VAR:fck_version}", "{VAR:width}", "{VAR:height}", "{VAR:lang}")
});
<!-- END SUB: EDITOR_ONDEMAND -->

-->
</script>
