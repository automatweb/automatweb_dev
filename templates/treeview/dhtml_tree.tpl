<script type="text/javascript">
var feeding_node;
function toggle_children(objref) {
        elemID = objref.getAttribute("attachedsection");
        thisElem = document.getElementById(elemID);
	data_loaded = thisElem.getAttribute("data_loaded");
        thisDisp = thisElem.style.display;
        icon = document.getElementById("icon-"+elemID);
	iconfld = document.getElementById("iconfld-"+elemID);

	// now I need to figure out whether to use the asynchronous loader or not
        if (thisDisp == 'none')
        {
		if (get_branch_func != "" && data_loaded == "false")
		{
			thisElem.innerHTML = '<span style="color: #CCC; margin-left: 20px;">loading....</span>';
			// fire treeloader
			feeding_node = elemID;
			fetch_node(elemID);
		};
                thisElem.style.display = 'block';
                if (icon)
                        icon.innerHTML = tree_collapseMeHTML;
		if (iconfld.src == tree_closed_fld_icon)
			iconfld.src = tree_open_fld_icon;
        }
        else
        {
                thisElem.style.display = 'none';
                if (icon)
                        icon.innerHTML = tree_expandMeHTML;
		if (iconfld.src == tree_open_fld_icon)
			iconfld.src = tree_closed_fld_icon;
        }
        return false;
}

function onload_handler(arg)
{
	if (window.event)
                el = window.event.srcElement.id.substr(1);
        else
                el = arg;
        document.getElementById(el).innerHTML = document.getElementById("f"+el).contentWindow.document.body.innerHTML;
	document.getElementById(el).setAttribute("data_loaded",true);
}

function fetch_node(node)
{
	uri = get_branch_func + parseInt(node);
	var frame = document.createElement("iframe");
        frame.setAttribute("width",0);
        frame.setAttribute("height",1);
        frame.setAttribute("frameborder",0);
        frame.setAttribute("id","f"+node);
	frame.setAttribute("src",uri);
	if (frame.attachEvent)
                frame.attachEvent('onload',onload_handler);
        else
                frame.setAttribute("onload","onload_handler('" + node + "');");
        document.body.appendChild(frame);

}

//tree_expandMeHTML = '<img src="{VAR:baseurl}/automatweb/images/plusnode.gif" border="0" style="vertical-align: middle;"><img src="{VAR:baseurl}/automatweb/images/closed_folder.gif" border="0" style="vertical-align:middle;">';
//tree_collapseMeHTML = '<img src="{VAR:baseurl}/automatweb/images/minusnode.gif" border="0" style="vertical-align:middle;"><img src="{VAR:baseurl}/automatweb/images/open_folder.gif" border="0" style="vertical-align:middle;">';
tree_expandMeHTML = '<img src="{VAR:baseurl}/automatweb/images/plusnode.gif" border="0" style="vertical-align: middle;">';
tree_collapseMeHTML = '<img src="{VAR:baseurl}/automatweb/images/minusnode.gif" border="0" style="vertical-align:middle;">';

tree_closed_fld_icon = "{VAR:baseurl}/automatweb/images/closed_folder.gif";
tree_open_fld_icon = "{VAR:baseurl}/automatweb/images/open_folder.gif";

get_branch_func = '{VAR:get_branch_func}';
</script>
<style>
.iconcontainer {
	margin-left: 4px;
}
.nodetext {
	color: black;
	font-family: Arial,Helvetica,sans-serif;
	font-size: 11px;
	text-decoration: none;
	vertical-align: middle;
}

.nodetext a {
	color: black;
}
</style>
<!-- SUB: HAS_ROOT -->
<div class="nodetext">
<a href="{VAR:rooturl}" target="{VAR:target}"><img style="vertical-align: middle;" src="{VAR:icon_root}" border="0">{VAR:rootname}</a>
</div>
<!-- END SUB: HAS_ROOT -->
<!-- hästi tore oleks, kui ma saaks need folderite ikoonid kuidagi automaatselt lisada -->
<!-- SUB: TREE_NODE -->
<div class="nodetext"><a attachedsection="{VAR:idx}" onClick="toggle_children(this);return false;" href="javascript:void();"><span id="icon-{VAR:idy}" class="iconcontainer"><img src="{VAR:baseurl}/automatweb/images/plusnode.gif" border="0" style="vertical-align:middle;"></span><span><img id="iconfld-{VAR:idy}" src="{VAR:iconurl}" border="0" style="vertical-align:middle;"></span></a>&nbsp;<a href="{VAR:url}" target="{VAR:target}">{VAR:name}</a>
<!-- SUB: SUB_NODES -->
<div id="{VAR:idz}" data_loaded="false" style="padding-left: 16px; display: none; ">
<!-- SUB: SINGLE_NODE -->
<div class="nodetext"><span class="iconcontainer"><img src="{VAR:iconurl}" border="0" style="vertical-align:middle; margin-left: 16px;"></span>&nbsp;<a target="{VAR:target}" href="{VAR:url}">{VAR:name}</a></div>
<!-- END SUB: SINGLE_NODE -->
<!-- END SUB: SUB_NODES -->
</div></div>
<!-- END SUB: TREE_NODE -->
