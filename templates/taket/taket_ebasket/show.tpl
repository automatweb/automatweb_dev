<br>
<script>
	function fillKorv()
	{
		document.getElementById('seesperenimi').value=document.transa.eesperenimi.value;
		document.getElementById('skontakttelefon').value=document.transa.kontakttelefon.value;
		document.getElementById('stransport').value=document.transa.transport.value;
		document.getElementById('sinfo').value=document.transa.info.value;
	}

	function checkIfValid(){		
		document.getElementById("transport_name").value = document.transa.transport.options[document.transa.transport.options.selectedIndex].text;

		if(document.getElementById('canWeProceed').value==1)
			return true;
		else{
			alert('{VAR:trans_error1}');
			return false;
		}
	}
</script>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td bgcolor="#B9BED2"><table width="100%"  border="0" cellspacing="1" cellpadding="0">
                  <tr>
                    <td height="70" valign="middle" bgcolor="#FFFFFF"><table border=0 cellspacing=0 cellpadding=2 width=100% align=center>
                      <form method=POST action='index.aw' onSubmit="fillKorv();">
                        <tr>
                          <td colspan="7"><table width="100%" height="30"  border="0" cellpadding="0" cellspacing="0">
                              <tr>
                                <td class="lrgTitle">{VAR:trans_basket} </td>
                                <td align="right">
										  	<span class="listItem"><a href="javascript:void()" onClick="window.open('156','','directory=0,height=290,width=350,resizable=1, statusbar=0, hotkeys=0,menubar=0,scrollbars=0,status=0,toolbar=0')"><img src="img/qmark.gif" width="20" height="20" hspace="10" border="0"></a></span>
											<input type='hidden' name='seesperenimi' id='seesperenimi'>
											<input type='hidden' name='skontakttelefon' id='skontakttelefon'>
											<input type='hidden' name='stransport' id='stransport'>
											<input type='hidden' name='sinfo' id='sinfo'>
											</td>
                              </tr>
                            </table></td>
                          </tr>
<tr>
	<td width="19%" class="{VAR:product_codecss}"><a href="?class=taket_ebasket&action=show&sort=product_code&dir={VAR:product_codedir}">{VAR:trans_product_code}</a></td>
	<td width="18%" class="{VAR:product_namecss}"><a href="?class=taket_ebasket&action=show&sort=product_name&dir={VAR:product_namedir}">{VAR:trans_name}</a></td>
	<td width="10%" class="{VAR:pricecss}"><a href="?class=taket_ebasket&action=show&sort=price&dir={VAR:pricedir}">{VAR:trans_original_price}</a></td>
	<td width="10%" class="{VAR:discountcss}"><a href="?class=taket_ebasket&action=show&sort=discount&dir={VAR:discountdir}">{VAR:trans_percentage}</a></td>
	<td width="5%" class="{VAR:finalpricecss}"><a href="?class=taket_ebasket&action=show&sort=finalprice&dir={VAR:finalpricedir}">{VAR:trans_finalprice}</a></td>
	<td width="10%" class="{VAR:quantitycss}"><a href="?class=taket_ebasket&action=show&sort=quantity&dir={VAR:quantitydir}">{VAR:trans_choose_quantity}</a></td>
	<td width="10%" class="listTitle"><a href="#">{VAR:trans_instock}</a></td>
</tr>								
{VAR:toodeParsed}
<!-- SUB: toode -->
                        <tr onmouseover="setPointer(this, '#EEEFF4')" onmouseout="setPointer(this, '#FFFFFF')">
                          <td class="listItem" >{VAR:product_code}</td>
                          <td class="listItemSec" >{VAR:product_name}</td>
                          <td class="listItemSec" >{VAR:price}</td>
                          <td class="listItemSec" >{VAR:discount}</td>
                          <td class="listItemSec" >{VAR:finalprice}</td>
                          <td class="listItemSec" >
                          	<input id="koguseId{VAR:i}" name='quantity[]' type="text" class="formBox" size="2" value='{VAR:quantity}'>
									<a href="javascript:void()" onClick='addOne(document.getElementById("koguseId{VAR:i}"))'><img src="img/sym_inc.gif" width="7" height="7" border="0"></a>
									<a href="javascript:void()" onClick='subtractOne(document.getElementById("koguseId{VAR:i}"))'><img src="img/sym_deg.gif" width="7" height="7" border="0"></a>
                            <input type='hidden' name='productId[]' value='{VAR:product_code}'>
                          </td>
                          <td class="orange">{VAR:instock_parsed}&nbsp;</td>
                        </tr>
<!-- END SUB: toode --> 
<!-- SUB: instockyes -->
	{VAR:trans_yes}
<!-- END SUB: instockyes -->
<!-- SUB: instockno -->
	{VAR:trans_no}
<!-- END SUB: instockno -->
                        <tr bgcolor="#B9BED2">
                          <td colspan="7"><table width="100%"  border="0" cellspacing="1" cellpadding="0">
                            <tr bgcolor="#FFFFFF">
                              <td width="70%" align="right" class="listItem">{VAR:trans_price_wo_vat}</td>
                              <td align="right" class="listItem">{VAR:priceWithoutTax}&nbsp;</td>
                            </tr>
                            <tr bgcolor="#FFFFFF">
                              <td align="right" class="listItem">{VAR:trans_vat}</td>
                              <td align="right" class="listItem">{VAR:tax}&nbsp;</td>
                            </tr>
                            <tr bgcolor="#FFFFFF">
                              <td align="right" class="listItem">{VAR:trans_summed_price} </td>
                              <td align="right" class="listItem"><b>{VAR:priceGrandTotal}&nbsp;</b>
<input type='hidden' value='{VAR:tmpFlag}' id='canWeProceed'>{VAR:reforb}
										</td>
                            </tr>
                          </table>                            </td>
                          </tr>
                        <tr>
                          <td colspan="7">&nbsp;</td>
                        </tr>
                        <tr align="right">
                          <td colspan="7">
								  	<input type='hidden' name='sort' value='{VAR:sort}'>
									<input type='hidden' name='dir' value='{VAR:dir}'> 
								  	<input name="Submit" type="submit" class="formButton" value="{VAR:trans_save_basket}">&nbsp;</form><form method=POST action='index.aw' name='transa' onSubmit='return checkIfValid()'></td>
                        </tr>                        								
{VAR:vormistaParsed}
<!-- SUB: vormista -->
                        <tr>
                          <td colspan="7">&nbsp;</td>
                        </tr>
{VAR:inputErrParsed}
<!-- SUB: inputErr -->                       
                        <tr>
                          <td colspan="7"><b>{VAR:trans_all_fields_must_be_filled}</b></td>
                        </tr>
<!-- END SUB: inputErr -->
<!-- SUB: inputErr2 -->                       
                        <tr>
                          <td colspan="7"><b>{VAR:trans_choose_smthing}</b></td>
                        </tr>
<!-- END SUB: inputErr2 -->

								<tr>
									<td colspan="7">
										<table width="100%" border="0">
											<tr>
												<td class='listItem'>{VAR:trans_names}</td>
												<td class="listItem">{VAR:trans_phone}</td>
												<td class="listItem">{VAR:trans_info}</td>
												<td class="listItem">{VAR:trans_transport}</td>
												<td colspan="3" class="listItem">&nbsp;</td>
												</tr>
											<tr>
												<td class="listItem">
													<span class="formText">
				                            <input class=formBox size=23 name='eesperenimi' value='{VAR:eesperenimi}'>
													</span>
												</td>
												<td class="listItem">
													<span class="formText">
														<input class=formBox size=23 name='kontakttelefon' value='{VAR:kontakttelefon}'>
				                          </span>
												</td>
												<td class="listItem">
													<span class="formText">
				                            <input class=formBox size=23 name='info' value='{VAR:info}'>
													</span>
												 </td>
												<td class="listItem">
												  <input type='hidden' name='transport_name' id='transport_name'>
													<select name="transport" class="formBox" id='transport' style="width: 130px;">
														{VAR:transportParsed}
									<!-- SUB: transport -->
													 <option value="{VAR:transport_id}" {VAR:tselected}>{VAR:transport_name}</option>
									<!-- END SUB: transport -->
												  </select>
												 </td>
												<td colspan="3" class="listItem" align="right">
													<span>
																	<input type="submit" class="formButton" value="{VAR:trans_send_order}">
													</span>
												 </td>

											</tr>
										</table>
									</td>
								</tr>
{VAR:reforb2}
 </form>                       
<!-- END SUB: vormista -->
                    </table></td>
                  </tr>
              </table></td>
              <td width="2" valign="top" bgcolor="#B9BED2"><img src="img/one_w.gif" width="2" height="2"></td>
            </tr>
            <tr>
              <td align="left" bgcolor="#B9BED2"><img src="img/one_w.gif" width="2" height="2"></td>
              <td bgcolor="#B9BED2"><img src="img/one.gif" width="2" height="2"></td>
            </tr>
          </table>
