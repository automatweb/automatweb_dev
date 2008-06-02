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
