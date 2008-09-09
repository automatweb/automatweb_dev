	<?php
	$active_document_title = <<<EOF
	{VAR:active_document_title}
EOF;
	$active_document_title = trim(strip_tags($active_document_title));
	if (strlen($active_document_title)>0)
	{
		echo "<title>$active_document_title</title>\n";
	}
	else
	{
		echo "<title>AS Meie Firma</title>\n";
	}
	?>
</head>

<body id="frontpage">

<div id="wrapper">

<div id="content">
{VAR:doc_content}
</div><!-- content -->

<!-- SUB: RIGHT_PANE -->
<div id="right_pane">
<ul class="menu">
	<!-- SUB: MENU_MAIN_L1_ITEM -->
	<li><a href="{VAR:link}" {VAR:target}>{VAR:text}</a></li>
	<!-- END SUB: MENU_MAIN_L1_ITEM -->
	
	<!-- SUB: MENU_MAIN_L1_ITEM_SEL -->
	<li class="sel"><a href="{VAR:link}" {VAR:target}>{VAR:text}</a></li>
	<!-- END SUB: MENU_MAIN_L1_ITEM_SEL -->
</ul>
</div>
<!-- END SUB: RIGHT_PANE -->

<br class="clear" />

<div id="header"><div class="padding">
<span class="logo">PÄIS</span>
<!-- SUB: SEARCH_SEL -->
<form method="get" action="{VAR:baseurl}/index.{VAR:ext}">
<input type="hidden" name="class" value="site_search_content" />
<input type="hidden" name="action" value="do_search" />
<input type="hidden" name="section" value="5535" />
<input type="hidden" name="id" value="54" />
<table summary="">
<tr>
	<td>
		<label for="searchbox"></label><input type="text" name="str" class="text"
		value="Otsi siin" onfocus="if(this.value=='Otsi siin')this.value = ''"
		onblur="if(this.value=='')this.value='Otsi siin';" id="searchbox" />
	</td>
	<td><input type="submit" value="otsi" class="submit" /></td>

</tr>
</table>
</form>
</div></div><!-- header -->

<div id="footer">
<div class="padding">
	<!-- SUB: MENU_FOOTER_L1_ITEM -->
	{VAR:text}
	<!-- END SUB: MENU_FOOTER_L1_ITEM -->
</div>
</div><!-- footer -->

</div><!-- wrapper -->



<!--- temp fix; needed menus -->

<!-- SUB: MENU_YLEMINE_L1_ITEM_BEGIN -->
 <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_2_left.gif" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2content" background="{VAR:baseurl}/automatweb/images/aw04/tab2_back.gif"><a href='{VAR:link}' {VAR:target}>
		<!-- SUB: HAS_IMAGE -->
		<img src='{VAR:menu_image_0_url}' border='0' alt='{VAR:text}'>
		<!-- END SUB: HAS_IMAGE -->

		<!-- SUB: NO_IMAGE -->
		<span class="black">{VAR:text}</span>
		<!-- END SUB: NO_IMAGE -->
	</a></td>
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_2_right.gif" HEIGHT="24" BORDER="0" ALT=""></td>
<!-- END SUB: MENU_YLEMINE_L1_ITEM_BEGIN -->

<!-- SUB: MENU_YLEMINE_L1_ITEM -->
 <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_2_left.gif" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2content" background="{VAR:baseurl}/automatweb/images/aw04/tab2_back.gif"><a href='{VAR:link}' {VAR:target}>
		<!-- SUB: HAS_IMAGE -->
		<img src='{VAR:menu_image_0_url}' border='0' alt='{VAR:text}'>
		<!-- END SUB: HAS_IMAGE -->

		<!-- SUB: NO_IMAGE -->
		<span class="black">{VAR:text}</span>
		<!-- END SUB: NO_IMAGE -->
	</a></td>
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_2_right.gif" HEIGHT="24" BORDER="0" ALT=""></td>
<!-- END SUB: MENU_YLEMINE_L1_ITEM -->


<!-- SUB: MENU_YLEMINE_L1_ITEM_END -->
 <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_2_left.gif" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2content" background="{VAR:baseurl}/automatweb/images/aw04/tab2_back.gif"><a href='{VAR:link}' {VAR:target}>
		<!-- SUB: HAS_IMAGE -->
		<img src='{VAR:menu_image_0_url}' border='0' alt='{VAR:text}'>
		<!-- END SUB: HAS_IMAGE -->

		<!-- SUB: NO_IMAGE -->
		<span class="black">{VAR:text}</span>
		<!-- END SUB: NO_IMAGE -->
	</a></td>
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_2_right.gif" HEIGHT="24" BORDER="0" ALT=""></td>
<!-- END SUB: MENU_YLEMINE_L1_ITEM_END -->

			
<!-- SUB: MENU_QUICK_L1_ITEM -->
 <option value="{VAR:link}">{VAR:text}</option>
<!-- END SUB: MENU_QUICK_L1_ITEM -->

<!-- SUB: MENU_QUICK_L1_ITEM_SEL -->
 <option selected>{VAR:text}</option>
<!-- END SUB: MENU_QUICK_L1_ITEM_SEL -->
									
									
									
									
									
									 <!-- SUB: MENU_P6HI_L1_ITEM_BEGIN -->
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_left.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2content" background="{VAR:baseurl}/automatweb/images/aw04/tab2_back.gif"><a {VAR:target} href="{VAR:link}">{VAR:text}</a></td>
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_right.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
 <!-- END SUB: MENU_P6HI_L1_ITEM_BEGIN -->

 <!-- SUB: MENU_P6HI_L1_ITEM -->
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_left.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2content" background="{VAR:baseurl}/automatweb/images/aw04/tab2_back.gif"><a {VAR:target} href="{VAR:link}">{VAR:text}</a></td>
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_right.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
 <!-- END SUB: MENU_P6HI_L1_ITEM -->

  <!-- SUB: MENU_P6HI_L1_ITEM_END -->
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_left.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2content" background="{VAR:baseurl}/automatweb/images/aw04/tab2_back.gif"><a {VAR:target} href="{VAR:link}">{VAR:text}</a></td>
  <td nowrap><IMG SRC="{VAR:baseurl}/automatweb/images/aw04/tab2_right.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
 <!-- END SUB: MENU_P6HI_L1_ITEM_END -->

  <!-- SUB: MENU_P6HI_L2_ITEM_BEGIN_SEL -->
  <td nowrap><IMG SRC="{VAR:baseurl}/img/tab2_sel2_left.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2selcontent" background="{VAR:baseurl}/img/tab2_sel2_back.gif"><a {VAR:target} href="{VAR:link}"><font color="#FFFFFF">{VAR:text}</font></a></td>
  <td nowrap><IMG SRC="{VAR:baseurl}/img/tab2_sel2_right.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <!-- END SUB: MENU_P6HI_L1_ITEM_BEGIN_SEL -->

  <!-- SUB: MENU_P6HI_L1_ITEM_SEL -->
  <td nowrap><IMG SRC="{VAR:baseurl}/img/tab2_sel2_left.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2selcontent" background="{VAR:baseurl}/img/tab2_sel2_back.gif"><a {VAR:target} href="{VAR:link}"><font color="#FFFFFF">{VAR:text}</font></a></td>
  <td nowrap><IMG SRC="{VAR:baseurl}/img/tab2_sel2_right.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <!-- END SUB: MENU_P6HI_L1_ITEM_SEL -->

    <!-- SUB: MENU_P6HI_L1_ITEM_END_SEL -->
  <td nowrap><IMG SRC="{VAR:baseurl}/img/tab2_sel2_left.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <td nowrap class="aw04tab2selcontent" background="{VAR:baseurl}/img/tab2_sel2_back.gif"><a {VAR:target} href="{VAR:link}"><font color="#FFFFFF">{VAR:text}</font></a></td>
  <td nowrap><IMG SRC="{VAR:baseurl}/img/tab2_sel2_right.gif" WIDTH="13" HEIGHT="24" BORDER="0" ALT=""></td>
  <!-- END SUB: MENU_P6HI_L1_ITEM_END_SEL -->
  
  
  
  
  
<!-- SUB: YAH_LINK -->
<a href="{VAR:link}">{VAR:text}</a> &gt; 
<!-- END SUB: YAH_LINK -->

<!-- SUB: YAH_LINK_END -->
<a href="{VAR:link}">{VAR:text}</a> 
<!-- END SUB: YAH_LINK_END -->