<script src="{VAR:baseurl}/automatweb/js/popup_menu.js" type="text/javascript">
</script>
<div id="{VAR:id}" class="menu" onmouseover="menuMouseover(event)">
<!-- SUB: MENU_ITEM -->
<a class="menuItem" href="{VAR:link}">{VAR:text}</a>
<!-- END SUB: MENU_ITEM -->
<!-- SUB: MENU_ITEM_DISABLED -->
&nbsp;&nbsp;&nbsp;&nbsp;<font color="gray">{VAR:text}</font>
<!-- END SUB: MENU_ITEM_DISABLED -->
</div>
