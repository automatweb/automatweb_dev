<!-- SUB: MENU -->
<div id="{VAR:menu_id}" class="menu">
<!-- SUB: MENU_ITEM -->
<a class="menuItem" href="{VAR:url}">{VAR:caption}</a>
<!-- END SUB: MENU_ITEM -->

<!-- SUB: MENU_ITEM_SUB -->
<a class="menuItem" href=""
	onclick="return false;"
	onmouseover="menuItemMouseover(event, '{VAR:sub_menu_id}');"
      ><span class="menuItemText">{VAR:caption}</span><span class="menuItemArrow">&gt;</span></a>
<!-- END SUB: MENU_ITEM_SUB -->

<!-- SUB: MENU_SEPARATOR -->
<div class="menuItemSep"></div>
<!-- END SUB: MENU_SEPARATOR -->

</div>
<!-- END SUB: MENU -->
