<applet codebase="{VAR:baseurl}/automatweb/java/menyy" code="mouseLeft.class" width="{VAR:width}" height="{VAR:height}">
<param name="mouse_over_icon" value="{VAR:icon_over}">
<param name="icon" value="{VAR:icon}">
<param name="menu_font" value="Arial">
<param name="menu_textsize" value="11">
<param name="font" value="Arial">
<param name="textsize" value="11">
<param name="style" value=""><!--B I-->
<param name="underline" value="">
<param name="text" value="{VAR:name}">
<param name="back_color" value="{VAR:bgcolor}">
<param name="fore_color" value="#000000">
<param name="x" value="0">
<param name="y" value="20">
<param name="onClick" value="1">
<param name="url" value="{VAR:url}"> 
<param name="urlparam1" value="&id={VAR:oid}">  
<!-- SUB: URLPARAM -->
<param name="urlparam{VAR:nr}" value="&{VAR:key}={VAR:val}">  
<!-- END SUB: URLPARAM -->
</applet>
