<!-- SUB: MENU -->
<div id="{VAR:menu_id}" class="menu">
<!-- SUB: MENU_ITEM -->
<a class="menuItem" href="{VAR:url}">{VAR:caption}</a>
<!-- END SUB: MENU_ITEM -->

<!-- SUB: MENU_ITEM_SHOW -->
<span class="menuItemTextSep">{VAR:caption}</span>
<!-- END SUB: MENU_ITEM_SHOW -->

<!-- SUB: MENU_ITEM_SUB -->
<a class="menuItem" href=""
	onclick="return false;"
	onmouseover="menuItemMouseover(event, '{VAR:sub_menu_id}');"
      ><span class="menuItemText">{VAR:caption}</span><span class="menuItemArrow"><img style="border:0px" src="{VAR:baseurl}/automatweb/images/arr.gif" alt=""></span></a>
<!-- END SUB: MENU_ITEM_SUB -->

<!-- SUB: MENU_SEPARATOR -->
<div class="menuItemSep"></div>
<!-- END SUB: MENU_SEPARATOR -->

</div>
<!-- END SUB: MENU -->

<!-- SUB: MENU2 -->
<div id="{VAR:menu_id}" class="menu" onmouseover="menuMouseover(event)">
{VAR:items}
</div>
<!-- END SUB: MENU2 -->
