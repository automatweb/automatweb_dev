<!-- SUB: MENU_YLEMINE_L1_ITEM_BEGIN -->
<a {VAR:target} href="{VAR:link}">{VAR:text}</a>
<!-- END SUB: MENU_YLEMINE_L1_ITEM_BEGIN -->
<!-- SUB: MENU_YLEMINE_L1_ITEM -->
| <a {VAR:target} href="{VAR:link}">{VAR:text}</a>
<!-- END SUB: MENU_YLEMINE_L1_ITEM -->

<!--YAH-->
<table width="780" border="0" cellpadding="0" cellspacing="0">
<tr><td height="25" class="taust"><span class="yah"><IMG src="{VAR:baseurl}/img/trans.gif" WIDTH="10" HEIGHT="1" BORDER=0 ALT="">
<!--{VAR:date}&nbsp;|-->
<a href="{VAR:baseurl}">Home</a> 
<!-- SUB: YAH_LINK -->
> <a href="{VAR:link}">{VAR:text}</a> 
<!-- END SUB: YAH_LINK -->
</span>
</td>
</table>


<!-- SUB: SEARCH_SEL -->
<table border="0" cellspacing="3" cellpadding="0">
<form method="get" action="{VAR:baseurl}/index.{VAR:ext}">
<tr>
<td><select name="parent" class="formselect2">{VAR:search_sel}</select></td>
<td><input type="text" size="18" class="formsearch2" name="str"></td>
<td><input type="submit" value=" {VAR:LC_SEARCH_BTN} " class="formbutton"></td>
<input type="hidden" name="class" value="document">
<input type="hidden" name="action" value="search">
<input type="hidden" name="section" value="{VAR:section}">
</tr>
</form>
</table>
<!-- END SUB: SEARCH_SEL -->


					{VAR:doc_content}

<!-- SUB: login -->
<table width="143" border="0" cellspacing="0" cellpadding="0">
<form action='{VAR:baseurl}/reforb.{VAR:ext}' method="POST">
                    <tr> 
                      <td align="left" valign="top"> 
                        <table width="143" border="0" cellspacing="0" cellpadding="2" class="text">
                          <tr> 
                            <td align="left" valign="top" width="123">Nimi:</td>
                            <td align="left" valign="top" width="123"><input type="text" name="uid" size="7" class="input-rkool"></td>
                          </tr>
                          <tr> 
                            <td align="left" valign="top" width="123">Parool:</td>
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
<!-- END SUB: login -->

<!-- SUB: logged -->
<table border="0" width="143" border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<!-- SUB: MENUEDIT_ACCESS -->
* <a href='{VAR:baseurl}/automatweb/'>Tee T&ouml;&ouml;d</a><br>
<!-- END SUB: MENUEDIT_ACCESS -->
<!-- SUB: CHANGEDOCUMENT -->
* <a target="_blank" href='{VAR:baseurl}/automatweb/orb.{VAR:ext}?class=document&action=change&id={VAR:docid}'>Muuda dokumenti</a><br>
<!-- END SUB: CHANGEDOCUMENT -->
* <a target="_blank" href='{VAR:baseurl}/automatweb/orb.{VAR:ext}?class=document&action=new&parent={VAR:sel_menu_id}&period=0'>Lisa dokument</a><br>

* <a href='{VAR:baseurl}/orb.{VAR:ext}?class=syslog'>DR. ONLINE</a><br>
* <a href='{VAR:baseurl}/orb.{VAR:ext}?class=users&action=logout'>Logi välja</a><br>

<!-- END SUB: logged -->

	

