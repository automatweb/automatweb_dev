<script src="{VAR:baseurl}/automatweb/js/popup_menu.js" type="text/javascript"></script>

<div id="{VAR:id}" class="menu" onmouseover="menuMouseover(event)">
<!-- SUB: MENU_ITEM -->
<a class="menuItem" href="{VAR:link}">{VAR:text}</a>
<!-- END SUB: MENU_ITEM -->

<!-- SUB: MENU_ITEM_DISABLED -->
<a class="menuItem" title="{VAR:title}" href="" onclick="return false;" style="color:gray">{VAR:text}</a>
<!-- END SUB: MENU_ITEM_DISABLED -->

<!-- SUB: MENU_ITEM_S -->
<a class="menuItem" onclick="{VAR:onclick}" href="{VAR:link}">{VAR:text}</a>
<!-- END SUB: MENU_ITEM_S -->

</div>
