<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>{VAR:site_title}</title>
<meta http-equiv="Content-Type" content="text/html; charset={VAR:charset}">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/aw.css">
<link href="{VAR:baseurl}/css/intranet.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="{VAR:baseurl}/css/cssjsmenudhtml.css">
<link rel="stylesheet" type="text/css" href="{VAR:baseurl}/css/cssjsmenuhover.css" id="hoverJS">
<link rel="stylesheet" type="text/css" href="{VAR:baseurl}/css/cssjsmenustyle.css">
<script type="text/javascript" src="{VAR:baseurl}/js/dhtml.js"></script>



<script language=javascript>
<!--

function select_this(s){
	var d = s.options[s.selectedIndex].value;
	if (d != "_")
	{
		location.href=d;
	}
}


  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS'); 
      kill.disabled = true;
    }
	MM_preloadImages('{VAR:baseurl}/img/checked.gif');
  }



function mOvr(src,clrOver) { 
    if (!src.contains(event.fromElement)) { src.style.cursor = 'hand'; 
    src.background = clrOver; }}function mOut(src,clrIn) { if (!src.contains(event.toElement)) { src.style.cursor = 'default'; 
    src.background = clrIn; }} function mClk(src) { if(event.srcElement.tagName=='TD'){src.children.tags('A')[0].click();} }

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

//-->
</SCRIPT>


<!-- SUB: logged -->
<script type="text/javascript" src="{VAR:baseurl}/js/cssjsmenu.js"></script>
</head>
<body onLoad="init();" bgcolor="#FFFFFF">
<table width="770" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>

    <td height="29" class="bgQuick"><table width="770" height="29" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="20" valign="bottom">&nbsp;</td>

<!-- A -->

               <td class="quickMenuPad" width="100"><select name="select" class="quickMenu"  onchange="select_this(this);">
								<option value="{VAR:baseurl}">Kiirvalik</option>

									<!-- SUB: MENU_QUICK_L1_ITEM -->
                  <option value="{VAR:link}">{VAR:text}</option>
									<!-- END SUB: MENU_QUICK_L1_ITEM -->

									<!-- SUB: MENU_QUICK_L1_ITEM_SEL -->
                  <option selected>{VAR:text}</option>
									<!-- END SUB: MENU_QUICK_L1_ITEM_SEL -->
                </select></td>



<!-- A -->

          <td align="lefta" valign="middle"><table border="0" cellspacing="0" cellpadding="0">
              <tr>

					<td class="topBaro" >
						<div id="navbar">
						</div>
					</td>

              </tr>
            </table>          </td>

          <td valign="bottom" align="right">


		  <table border="0" cellspacing="0" cellpadding="0">
            <tr>

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



            </tr>
          </table></td>

          <td width="20">&nbsp;</td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td height="68" class="bgBanner">
	<table width="770" height="68" border="0" cellpadding="0" cellspacing="0">

      <tr>
        <td align="left" valign="bottom">




<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td nowrap>&nbsp;&nbsp;</td>

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
</tr>
</table>






          </td>
        <td width="200">
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td></td><td><a href='{VAR:baseurl}'><img src='{VAR:baseurl}/img/intraneti_logo.gif' border='0'></a>&nbsp;&nbsp;&nbsp;</td>
				</tr>
			</table>
		</td>

	</tr>
    </table></td>
  </tr>

  <tr>
    <td height="22" class="mainSub">
		<!-- SUB: MENU_P6HI_L2_ITEM_BEGIN -->
		<a {VAR:target} href='{VAR:link}'>{VAR:text}</a>
		<!-- END SUB: MENU_P6HI_L2_ITEM_BEGIN -->

		<!-- SUB: MENU_P6HI_L2_ITEM -->
		&bull; <a {VAR:target} href='{VAR:link}'>{VAR:text}</a> 
		<!-- END SUB: MENU_P6HI_L2_ITEM -->

		<!-- SUB: MENU_P6HI_L2_ITEM_BEGIN_SEL -->
		<a {VAR:target} href='{VAR:link}'><b>{VAR:text}</b></a>
		<!-- END SUB: MENU_P6HI_L3_ITEM_BEGIN_SEL -->

		<!-- SUB: MENU_P6HI_L2_ITEM_SEL -->
		&bull; <a {VAR:target} href='{VAR:link}'><b>{VAR:text}</b></a> 
		<!-- END SUB: MENU_P6HI_L2_ITEM_SEL -->
	</td>

  </tr>
  <tr>
    <td bgcolor="#B1B9DF"><img src="{VAR:baseurl}/img/one.gif" width="1" height="1"></td>
  </tr>
  <tr>
    <td height="25" class="yah">
		<a href="{VAR:baseurl}">Avaleht</a> &gt; 
		
		<!-- SUB: YAH_LINK -->
		<a href="{VAR:link}">{VAR:text}</a> &gt; 
		<!-- END SUB: YAH_LINK -->

		<!-- SUB: YAH_LINK_END -->
		<a href="{VAR:link}">{VAR:text}</a> 
		<!-- END SUB: YAH_LINK_END -->
</td>

  </tr>
  <tr>
    <td height="20"><table width="770" border="0" cellspacing="0" cellpadding="0">
      <tr>
	<!-- SUB: LEFT_PANE -->
       <td width="194" valign="top" class="bgLeftPane">





          <table width="194" border="0" cellpadding="0" cellspacing="0" class="leftNav">
            <tr>
              <td><img src="{VAR:baseurl}/img/one.gif" width="1" height="10"></td>
              </tr>

					<!-- SUB: MENU_P6HI_L3_ITEM -->
		            <tr>
        		      <td class="leftMenu"><img src="{VAR:baseurl}/img/arrow_lrg.gif" width="16" height="7" border="0" align="absmiddle"> &nbsp;&nbsp;<a {VAR:target} href="{VAR:link}">{VAR:text}</a></td>
					</tr>
					<!-- END SUB: MENU_P6HI_L3_ITEM -->

					<!-- SUB: MENU_P6HI_L3_ITEM_BEGIN -->
		            <tr>
        		      <td class="leftMenu"><img src="{VAR:baseurl}/img/arrow_lrg.gif" width="16" height="7" border="0" align="absmiddle"> &nbsp;&nbsp;<a {VAR:target} href="{VAR:link}">{VAR:text}</a></td>
					</tr>
					<!-- END SUB: MENU_P6HI_L3_ITEM_BEGIN -->

					<!-- SUB: MENU_P6HI_L3_ITEM_END -->
		            <tr>
        		      <td class="leftMenu"><img src="{VAR:baseurl}/img/arrow_lrg.gif" width="16" height="7" border="0" align="absmiddle"> &nbsp;&nbsp;<a {VAR:target} href="{VAR:link}">{VAR:text}</a></td>
					</tr>
					<!-- END SUB: MENU_P6HI_L3_ITEM_END -->


					<!-- SUB: MENU_P6HI_L3_ITEM_SEL -->
					<tr>
					 <td>
						  <table width="194" border="0" cellpadding="0" cellspacing="0">
						   <tr>
						      <td colspan="2" class="leftMenuSel"><img src="{VAR:baseurl}/img/arrow_lrg.gif" width="16" height="7" border="0" align="absmiddle"> &nbsp;&nbsp;<a {VAR:target} href="{VAR:link}"><span class="black">{VAR:text}</span></a></td>
						    </tr>
		
							<!-- SUB: MENU_P6HI_L4_ITEM -->
							 <tr>
							   <td class="leftMenuSel" width="40" valign="middle" align="right"><img src="{VAR:baseurl}/img/arrow_sm.gif" width="10" height="7" border="0"></td>
							     <td class="leftMenuSel" width="154" align="left"><a {VAR:target} href="{VAR:link}" >{VAR:text}</a></td>

			                  </tr>
							<!-- END SUB: MENU_P6HI_L4_ITEM -->

							<!-- SUB: MENU_P6HI_L4_ITEM_SEL -->
					          <tr>
			                    <td class="leftMenuSel" width="40" valign="middle" align="right"><img src="{VAR:baseurl}/img/arrow_sm.gif" width="10" height="7" border="0"></td>
								<td class="leftMenuSel" width="154" align="left"><a {VAR:target} href="{VAR:link}"><font 	color="#000000">{VAR:text}</font></a></td>

			                  </tr>	
							<!-- END SUB: MENU_P6HI_L4_ITEM_SEL -->

							</table>
						</td>
					</tr>

					<!-- END SUB: MENU_P6HI_L3_ITEM_SEL -->

					<!-- SUB: MENU_P6HI_L3_ITEM_END_SEL -->
			        <tr>
						<td>
							<table width="194" border="0" cellpadding="0" cellspacing="0">
								<tr>
				                    <td colspan="2" class="leftMenuSel"><img src="{VAR:baseurl}/img/arrow_lrg.gif" width="16" height="7" border="0" align="absmiddle"> &nbsp;&nbsp;<a {VAR:target} href="{VAR:link}"><span class="black">{VAR:text}</span></a></td>
			                  </tr>

							<!-- SUB: MENU_P6HI_L4_ITEM -->
							  <tr>
								 <td class="leftMenuSel" width="40" valign="middle" align="right"><img 	src="{VAR:baseurl}/img/arrow_sm.gif" width="10" height="7" border="0"></td>
								   <td class="leftMenuSel"><a {VAR:target} href="{VAR:link}" >{VAR:text}</a></td>
		
							   </tr>
							<!-- END SUB: MENU_P6HI_L4_ITEM -->

							<!-- SUB: MENU_P6HI_L4_ITEM_SEL -->
					          <tr>
			                    <td class="leftMenuSel" width="40" valign="middle" align="right"><img src="{VAR:baseurl}/img/arrow_sm.gif" width="10" height="7" border="0"></td>
								<td class="leftMenuSel" width="154" align="left"><a {VAR:target} href="{VAR:link}"><font 	color="#000000">{VAR:text}</font></a></td>

			                  </tr>	
							<!-- END SUB: MENU_P6HI_L4_ITEM_SEL -->

						</table>
					</td>
				</tr>
				<!-- END SUB: MENU_P6HI_L3_ITEM_END_SEL -->


				<!-- SUB: MENU_P6HI_L3_ITEM_BEGIN_SEL -->
				<tr>
					<td>
						<table width="194" border="0" cellpadding="0" cellspacing="0">
							<tr>
							    <td colspan="2" class="leftMenuSel"><img src="{VAR:baseurl}/img/arrow_lrg.gif" width="16" height="7" border="0" align="absmiddle"> &nbsp;&nbsp;<a {VAR:target} href="{VAR:link}"><span class="black">{VAR:text}</span></a></td>
			                 </tr>

							<!-- SUB: MENU_P6HI_L4_ITEM -->
			                  <tr>
			                    <td class="leftMenuSel" width="40" valign="middle" align="right"><img src="{VAR:baseurl}/img/arrow_sm.gif" width="10" height="7" border="0"></td>
						        <td class="leftMenuSel"><a {VAR:target} href="{VAR:link}" >{VAR:text}</a></td>
			                  </tr>
							<!-- END SUB: MENU_P6HI_L4_ITEM -->
							<!-- SUB: MENU_P6HI_L4_ITEM_SEL -->
					          <tr>
			                    <td class="leftMenuSel" width="40" valign="middle" align="right"><img src="{VAR:baseurl}/img/arrow_sm.gif" width="10" height="7" border="0"></td>
								<td class="leftMenuSel" width="154" align="left"><a {VAR:target} href="{VAR:link}"><font 	color="#000000">{VAR:text}</font></a></td>

			                  </tr>	
							<!-- END SUB: MENU_P6HI_L4_ITEM_SEL -->


		              </table>
					 </td>
              </tr>
					<!-- END SUB: MENU_P6HI_L3_ITEM_BEGIN_SEL -->

	            <tr>
			          <td><img src="{VAR:baseurl}/img/one.gif" width="1" height="10"></td>
				</tr>
	          </table>

  		

<!-- SUB: LEFT_PROMO -->
<br>
          <table width="194" border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td width="194" height="22" class="barTitle">{VAR:title}</td>
              </tr>
			
            <tr>
              <td width="194" class="promoB">
							{VAR:content}
				</td>
              </tr>
			  <tr><td class="bgWhite"><img src="{VAR:baseurl}/img/one.gif" width="1" height="1"></td></tr>
          </table>

<!-- END SUB: LEFT_PROMO -->

          <table width="194" border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td width="194" height="22" class="barTitle">{VAR:uid} ({VAR:date})</td>
              </tr>
			
            <tr>
              <td width="194" class="promoB">
				<!-- SUB: MENU_LOGGED_L1_ITEM -->
				{VAR:text}<br>
				<!-- SUB: MENU_LOGGED_L2_ITEM -->
			  <a {VAR:target} href="{VAR:link}"><img src="{VAR:baseurl}/img/arrow_sm.gif" width="10" height="7" border="0">{VAR:text}</a><br>
				<!-- END SUB: MENU_LOGGED_L2_ITEM -->

				<!-- END SUB: MENU_LOGGED_L1_ITEM -->
				</td>
              </tr>
			  <tr><td class="bgWhite"><img src="{VAR:baseurl}/img/one.gif" width="1" height="1"></td></tr>
          </table>



					
					
					
					
					<br><br></td>
<!--        <td width="1"  class="bgJoon"><img src="{VAR:baseurl}/img/one.gif" width="1" height="1"></td>-->
        <td width="15" valign="top"><img src="{VAR:baseurl}/img/one.gif" width="15" height="1"></td>

			  <!-- END SUB: LEFT_PANE -->
		<td width="100%" valign="top"><br>{VAR:doc_content}
				
				<!-- SUB: DOWN_PROMO -->
						<table width="390" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td class="bgGreen"><img src="{VAR:baseurl}/img/one.gif" width="1" height="4"></td>
							</tr>
						</table>
						<table width="390" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td  class="bgLeftPane">
									<table width="390" border="0" cellspacing="0" cellpadding="0">
										<tr>
											<td class="midTitle"><img src="{VAR:baseurl}/img/star.gif" width="19" height="19" align="absmiddle">&nbsp;{VAR:title}</td>
											<td width="52" align="left" class="bgLeftPane"><a href="{VAR:link}"><img src="{VAR:baseurl}/img/veel.gif" alt="Loe veel" width="42" height="9" border="0"></a></td>
											<td width="4" class="bgGreen"><img src="{VAR:baseurl}/img/one.gif" width="4" height="23"></td>
										</tr>
									</table>
							</td>
						</tr>
						<tr>
							<td><img src="{VAR:baseurl}/img/one.gif" width="1" height="1"></td>
						</tr>
						<tr>
							<td>
								{VAR:content}
							</td>
						</tr>
					</table>

				<!--{VAR:title}<br>
				{VAR:content}-->
				
				<!-- END SUB: DOWN_PROMO -->
				<br></td>
		<!-- SUB: RIGHT_PANE -->
		<td width="15" valign="top"><img src="{VAR:baseurl}/img/one.gif" width="15" height="1"></td>
<!--        <td width="1"  class="bgJoon"><img src="{VAR:baseurl}/img/one.gif" width="1" height="1"></td>-->
		<td width="150" valign="top" class="bgRightPane">

			<table width="150" border="0" cellspacing="0" cellpadding="0">

			<!-- SUB: HAS_SUBITEMS_P6HI_L4_SEL -->
          <tr>
              <td height="22" class="bgJoon">
				<table width="150" height="22" border="0" cellpadding="0" cellspacing="1">
                  <tr>
                    <td class="bgRightPane">
						<table border="0" cellpadding="10" cellspacing="0">
						<tr><td>
						<table border="0" cellpadding="0" cellspacing="0">
						
						<!-- SUB: MENU_P6HI_L5_ITEM -->
						<tr>
							<td class="rightmenubl" colspan="2">{VAR:text}</td>
						</tr>
						<!-- SUB: MENU_P6HI_L6_ITEM -->
						<tr>
							<td valign="top" align="right" class="rightmenu" width="10"><img src="{VAR:baseurl}/img/arrow_sm.gif" width="10" height="7" border="0"></td>
							<td class="rightmenu"><a href='{VAR:link}' {VAR:target}>{VAR:text}</a></td>
						</tr>
						<!-- END SUB: MENU_P6HI_L6_ITEM -->

						<!-- SUB: MENU_P6HI_L6_ITEM_SEL -->
						<tr>
							<td valign="top" align="right" class="rightmenu" width="10"><img src="{VAR:baseurl}/img/arrow_sm.gif" width="10" height="7" border="0"></td>
							<td class="rightmenu"><a href='{VAR:link}' {VAR:target}><font color="#000000">{VAR:text}</font></a></td>
						</tr>
						<!-- END SUB: MENU_P6HI_L6_ITEM_SEL -->
				

						<!-- END SUB: MENU_P6HI_L5_ITEM -->
						</table>
						</td></tr></table>
					</td>
                  </tr>
                </table></td>
          </tr>
			<!-- END SUB: HAS_SUBITEMS_P6HI_L4_SEL -->
			<tr><td>





		<table  width="120" align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
			<form method="get" action="{VAR:baseurl}/index.{VAR:ext}" name="search">
          
            <td width="10" class="searchBox"><input type="text" class="search" name="str" size="13"></td>

            <td class="searchBox">
			<table width="19" height="16" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td width="9"><img src="{VAR:baseurl}/img/one.gif" width="9" height="1"></td>
                <td><input type="image" src="{VAR:baseurl}/img/search_button.gif"></td>
              </tr>
            </table></td>
          </tr>
			<input name="class" value="site_search_content" type="hidden">
			<input name="action" value="do_search" type="hidden">
			<input name="section" value="{VAR:sel_menu_id}" type="hidden">
			</form>
			<tr>
				<td class="searchBox">&nbsp;</td>
			</tr>
        </table>

			</td></tr>

		<!-- SUB: RIGHT_PROMO -->
          <tr>
              <td height="22" class="barTitle">{VAR:title}</td></td>
          </tr>
          <tr>

            <td class="promoB">{VAR:content}</td>
          </tr>
		  <tr><td class="bgWhite"><img src="{VAR:baseurl}/img/one.gif" width="1" height="1"></td></tr>
		<!-- END SUB: RIGHT_PROMO -->
        </table>          
		</td>
		<!-- END SUB: RIGHT_PANE -->
      </tr>
    </table></td>
  </tr>
  <tr>
    <td class="bgJoon"><img src="{VAR:baseurl}/img/one.gif" width="1" height="1"></td>

  </tr>
  <tr>
    <td><table width="770" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="footer">K&otilde;ik &otilde;igused kaitstud 2003.    </td>
        <td align="right" class="footer">Aadress: P&auml;rnu mnt. 154, 11317 Tallinn &middot; Tel: 0 655 8334 &middot; E-mail: <a href="5675">info@struktuur.ee </a></td>

      </tr>
    </table></td>
  </tr>
</table>
<!-- END SUB: logged -->

<!-- SUB: login -->
</head>
<body>
<center>
									<table width="143" border="0" cellspacing="0" cellpadding="0">
										<form action='{VAR:baseurl}/reforb.{VAR:ext}' method="POST">
                    <tr> 
                      <td align="left" valign="top"> 
                        <table width="143" border="0" cellspacing="0" cellpadding="2" class="leftmenu">
                          <tr> 
                            <td align="left" valign="top" width="123"><b>Nimi</b>:</td>
                            <td align="left" valign="top" width="123"><input type="text" name="uid" size="7" class="input-rkool"></td>
                          </tr>
                          <tr> 
                            <td align="left" valign="top" width="123">Password:</td>
                            <td align="left" valign="top" width="123"><input type="password" name="password" size="7" class="input-rkool"></td>
                          </tr>
                          <tr> 
                            <td colspan=2 align="left" valign="top" width="123"><input type="submit" name="Submit" value="Login" class="submit"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
										<input type='hidden' NAME='action' VALUE='login'>
										<input type='hidden' NAME='class' VALUE='users'>
										</form>
                  </table>

</center>
<!-- END SUB: login -->

</body>
</html>






