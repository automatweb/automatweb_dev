<script type="text/javascript">
function toggle_children(objref) {
        elemID = objref.getAttribute("attachedsection");
        thisElem = document.getElementById(elemID);
        thisDisp = thisElem.style.display;
        icon = document.getElementById("icon-"+elemID);
        if (thisDisp == 'none')
        {
                thisElem.style.display = 'block';
                if (icon)
                        icon.innerHTML = tree_collapseMeHTML;
        }
        else
        {
                thisElem.style.display = 'none';
                if (icon)
                        icon.innerHTML = tree_expandMeHTML;
        }
        return false;
}

tree_expandMeHTML = '<img src="{VAR:baseurl}/automatweb/images/ftv2pnode.gif" border="0">';
tree_collapseMeHTML = '<img src="{VAR:baseurl}/automatweb/images/ftv2mnode.gif" border="0">';

</script>
<div class="fgtext_bad">
<a href="{VAR:rooturl}" target="{VAR:target}"><img src="{VAR:icon_root}" border="0">{VAR:rootname}</a>
</div>
<!-- SUB: TREE_NODE -->
<div class="fgtext_bad" ><a attachedsection="{VAR:idx}" onClick="toggle_children(this);return false;" href="javascript:void();"><span id="icon-{VAR:idy}"><img src="{VAR:baseurl}/automatweb/images/ftv2pnode.gif" border="0"></span></a><a href="{VAR:url}" target="{VAR:target}"><img src="{VAR:iconurl}" border="0">{VAR:name}</a>
<!-- SUB: SUB_NODES -->
<div id="{VAR:idz}" style="padding-left: 16px; display: none;">
<!-- SUB: SINGLE_NODE -->
<div class="fgtext_bad"><img src="{VAR:baseurl}/automatweb/images/ftv2lastnode.gif" border="0"><img src="{VAR:iconurl}" border="0"><a target="{VAR:target}" href="{VAR:url}">{VAR:name}</a></div>
<!-- END SUB: SINGLE_NODE -->
<!-- END SUB: SUB_NODES -->
</div></div>
<!-- END SUB: TREE_NODE -->

