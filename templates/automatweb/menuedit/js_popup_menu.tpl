<div class="menuBar" style="width:80%; text-align: center;">
<a class="menuButton" href="" onclick="return buttonClick(event, '{VAR:menu_id}');" ><img border="0" src='{VAR:menu_icon}' id='mb_{VAR:menu_id}' width='16' height='16'></a>
</div>
<div id="{VAR:menu_id}" class="menu" onmouseover="menuMouseover(event)">
<!-- SUB: MENU_ITEM -->
<a class="menuItem" href="{VAR:link}">{VAR:text}</a>
<!-- END SUB: MENU_ITEM --></div>
